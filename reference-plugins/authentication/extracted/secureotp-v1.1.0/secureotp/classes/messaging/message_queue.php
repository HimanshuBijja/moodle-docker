<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Redis-based async message queue for SMS/Email delivery
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\messaging;

defined('MOODLE_INTERNAL') || die();

/**
 * Message queue for asynchronous SMS/Email delivery
 */
class message_queue {

    /**
     * @var \Redis Redis connection
     */
    protected $redis;

    /**
     * @var bool Redis availability status
     */
    protected $redis_available = false;

    /**
     * @var string Queue name prefix
     */
    const QUEUE_PREFIX = 'auth_secureotp:queue:';

    /**
     * @var string SMS queue name
     */
    const SMS_QUEUE = 'sms';

    /**
     * @var string Email queue name
     */
    const EMAIL_QUEUE = 'email';

    /**
     * @var int Maximum retry attempts
     */
    const MAX_RETRIES = 3;

    /**
     * Constructor - Initialize Redis connection if available
     */
    public function __construct() {
        $this->redis_available = false;

        if (!class_exists('Redis')) {
            return;
        }

        $config = get_config('auth_secureotp');
        $redis_host = !empty($config->redis_host) ? $config->redis_host : '127.0.0.1';
        $redis_port = !empty($config->redis_port) ? (int)$config->redis_port : 6379;

        try {
            $this->redis = new \Redis();
            // Short timeout (0.5s) to avoid blocking when Redis is down.
            $connected = @$this->redis->connect($redis_host, $redis_port, 0.5);

            if (!$connected) {
                $this->redis = null;
                return;
            }

            $redis_password = !empty($config->redis_password) ? $config->redis_password : '';
            if (!empty($redis_password)) {
                $this->redis->auth($redis_password);
            }

            $redis_db = !empty($config->redis_db) ? (int)$config->redis_db : 0;
            $this->redis->select($redis_db);

            $this->redis_available = true;

        } catch (\Exception $e) {
            $this->redis = null;
            $this->redis_available = false;
        }
    }

    /**
     * Queue SMS message for sending
     *
     * @param string $to Recipient mobile number
     * @param string $message Message content
     * @param array $options Additional options
     * @return array Result with success and job_id
     */
    public function queue_sms($to, $message, $options = array()) {
        $job = array(
            'id' => $this->generate_job_id(),
            'type' => 'sms',
            'to' => $to,
            'message' => $message,
            'options' => $options,
            'retry_count' => 0,
            'queued_at' => time(),
            'status' => 'pending'
        );

        if ($this->redis_available) {
            try {
                $queue_name = self::QUEUE_PREFIX . self::SMS_QUEUE;
                $this->redis->rPush($queue_name, json_encode($job));

                return array(
                    'success' => true,
                    'job_id' => $job['id'],
                    'queued' => true
                );

            } catch (\Exception $e) {
                debugging('Failed to queue SMS to Redis: ' . $e->getMessage(), DEBUG_DEVELOPER);
                // Fallback to direct send.
                return $this->send_sms_direct($to, $message, $options);
            }
        } else {
            // Redis not available, send directly.
            return $this->send_sms_direct($to, $message, $options);
        }
    }

    /**
     * Queue email message for sending
     *
     * @param object $user Moodle user object
     * @param string $otp OTP code
     * @param array $options Additional options
     * @return array Result
     */
    public function queue_email($user, $otp, $options = array()) {
        $job = array(
            'id' => $this->generate_job_id(),
            'type' => 'email',
            'userid' => $user->id,
            'otp' => $otp,
            'options' => $options,
            'retry_count' => 0,
            'queued_at' => time(),
            'status' => 'pending'
        );

        if ($this->redis_available) {
            try {
                $queue_name = self::QUEUE_PREFIX . self::EMAIL_QUEUE;
                $this->redis->rPush($queue_name, json_encode($job));

                return array(
                    'success' => true,
                    'job_id' => $job['id'],
                    'queued' => true
                );

            } catch (\Exception $e) {
                debugging('Failed to queue email to Redis: ' . $e->getMessage(), DEBUG_DEVELOPER);
                // Fallback to direct send.
                return $this->send_email_direct($user, $otp, $options);
            }
        } else {
            // Redis not available, send directly.
            return $this->send_email_direct($user, $otp, $options);
        }
    }

    /**
     * Process next SMS message from queue
     *
     * @return array Result with success and details
     */
    public function process_sms_queue() {
        if (!$this->redis_available) {
            return array('success' => false, 'error' => 'Redis not available');
        }

        try {
            $queue_name = self::QUEUE_PREFIX . self::SMS_QUEUE;

            // Blocking pop with 1 second timeout.
            $result = $this->redis->blPop($queue_name, 1);

            if ($result === false || empty($result[1])) {
                return array('success' => false, 'error' => 'Queue empty');
            }

            $job = json_decode($result[1], true);

            // Send SMS.
            $send_result = $this->send_sms_direct(
                $job['to'],
                $job['message'],
                $job['options']
            );

            if (!$send_result['success'] && $job['retry_count'] < self::MAX_RETRIES) {
                // Re-queue with incremented retry count.
                $job['retry_count']++;
                $job['last_error'] = $send_result['error'];
                $job['retry_at'] = time();

                // Push to retry queue.
                $retry_queue = self::QUEUE_PREFIX . self::SMS_QUEUE . ':retry';
                $this->redis->rPush($retry_queue, json_encode($job));
            }

            return $send_result;

        } catch (\Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    /**
     * Process next email message from queue
     *
     * @return array Result
     */
    public function process_email_queue() {
        global $DB;

        if (!$this->redis_available) {
            return array('success' => false, 'error' => 'Redis not available');
        }

        try {
            $queue_name = self::QUEUE_PREFIX . self::EMAIL_QUEUE;

            // Blocking pop with 1 second timeout.
            $result = $this->redis->blPop($queue_name, 1);

            if ($result === false || empty($result[1])) {
                return array('success' => false, 'error' => 'Queue empty');
            }

            $job = json_decode($result[1], true);

            // Load user object.
            $user = $DB->get_record('user', array('id' => $job['userid']));

            if (!$user) {
                return array('success' => false, 'error' => 'User not found');
            }

            // Send email.
            $send_result = $this->send_email_direct($user, $job['otp'], $job['options']);

            if (!$send_result['success'] && $job['retry_count'] < self::MAX_RETRIES) {
                // Re-queue with incremented retry count.
                $job['retry_count']++;
                $job['last_error'] = $send_result['error'];
                $job['retry_at'] = time();

                $retry_queue = self::QUEUE_PREFIX . self::EMAIL_QUEUE . ':retry';
                $this->redis->rPush($retry_queue, json_encode($job));
            }

            return $send_result;

        } catch (\Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    /**
     * Send SMS directly without queueing
     *
     * @param string $to Recipient
     * @param string $message Message
     * @param array $options Options
     * @return array Result
     */
    protected function send_sms_direct($to, $message, $options = array()) {
        $config = get_config('auth_secureotp');

        $gateway_config = array(
            'account_sid' => isset($config->twilio_account_sid) ? $config->twilio_account_sid : '',
            'auth_token' => isset($config->twilio_auth_token) ? $config->twilio_auth_token : '',
            'from_number' => isset($config->twilio_from_number) ? $config->twilio_from_number : ''
        );

        $gateway = new twilio_gateway($gateway_config);

        return $gateway->send_sms($to, $message, $options);
    }

    /**
     * Send email directly without queueing
     *
     * @param object $user User object
     * @param string $otp OTP code
     * @param array $options Options
     * @return array Result
     */
    protected function send_email_direct($user, $otp, $options = array()) {
        $gateway = new email_gateway();
        return $gateway->send_otp_email($user, $otp, $options);
    }

    /**
     * Generate unique job ID
     *
     * @return string Job ID
     */
    protected function generate_job_id() {
        return uniqid('msg_', true);
    }

    /**
     * Get queue length
     *
     * @param string $queue_type 'sms' or 'email'
     * @return int Queue length
     */
    public function get_queue_length($queue_type) {
        if (!$this->redis_available) {
            return 0;
        }

        try {
            $queue_name = self::QUEUE_PREFIX . $queue_type;
            return $this->redis->lLen($queue_name);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clear queue
     *
     * @param string $queue_type 'sms' or 'email'
     * @return bool Success
     */
    public function clear_queue($queue_type) {
        if (!$this->redis_available) {
            return false;
        }

        try {
            $queue_name = self::QUEUE_PREFIX . $queue_type;
            $this->redis->del($queue_name);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

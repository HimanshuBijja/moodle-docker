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
 * Abstract SMS gateway base class
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\messaging;

defined('MOODLE_INTERNAL') || die();

/**
 * Abstract base class for SMS gateway implementations
 */
abstract class sms_gateway {

    /**
     * @var array Configuration options for the gateway
     */
    protected $config;

    /**
     * @var \auth_secureotp\security\audit_logger Audit logger instance
     */
    protected $audit_logger;

    /**
     * Constructor
     *
     * @param array $config Configuration options
     */
    public function __construct($config = array()) {
        $this->config = $config;
        $this->audit_logger = new \auth_secureotp\security\audit_logger();
    }

    /**
     * Send SMS message
     *
     * @param string $to Recipient mobile number (E.164 format recommended)
     * @param string $message Message content
     * @param array $options Additional options (priority, validity, etc)
     * @return array Result with keys: success (bool), message_id (string), error (string)
     */
    abstract public function send_sms($to, $message, $options = array());

    /**
     * Check SMS gateway account balance/credits
     *
     * @return array Result with keys: success (bool), balance (float), currency (string), error (string)
     */
    abstract public function check_balance();

    /**
     * Get delivery status of a sent message
     *
     * @param string $message_id Message ID returned from send_sms()
     * @return array Result with keys: success (bool), status (string), delivered_at (int), error (string)
     */
    abstract public function get_delivery_status($message_id);

    /**
     * Validate mobile number format
     *
     * @param string $mobile Mobile number to validate
     * @return bool True if valid
     */
    public function validate_mobile($mobile) {
        // Remove all non-digit characters.
        $cleaned = preg_replace('/[^0-9]/', '', $mobile);

        // Indian mobile numbers: 10 digits starting with 6-9.
        if (preg_match('/^[6-9][0-9]{9}$/', $cleaned)) {
            return true;
        }

        // International E.164 format: 1-15 digits.
        if (strlen($cleaned) >= 10 && strlen($cleaned) <= 15) {
            return true;
        }

        return false;
    }

    /**
     * Format mobile number to E.164 standard
     *
     * @param string $mobile Mobile number to format
     * @param string $country_code Default country code (e.g., '91' for India)
     * @return string Formatted mobile number with country code
     */
    public function format_mobile($mobile, $country_code = '91') {
        // Remove all non-digit characters.
        $cleaned = preg_replace('/[^0-9]/', '', $mobile);

        // If already has country code, return as-is.
        if (strlen($cleaned) > 10) {
            return '+' . $cleaned;
        }

        // Add country code.
        return '+' . $country_code . $cleaned;
    }

    /**
     * Log SMS sending attempt
     *
     * @param string $to Recipient
     * @param string $message Message content
     * @param bool $success Success status
     * @param string $message_id Message ID (if successful)
     * @param string $error Error message (if failed)
     */
    protected function log_sms_attempt($to, $message, $success, $message_id = '', $error = '') {
        $eventdata = array(
            'recipient' => $this->mask_mobile($to),
            'message_length' => strlen($message),
            'message_id' => $message_id,
            'error' => $error,
            'gateway' => get_class($this)
        );

        if ($success) {
            $this->audit_logger->log_event(
                'SMS_SENT',
                'SUCCESS',
                null,
                null,
                $eventdata
            );
        } else {
            $this->audit_logger->log_event(
                'SMS_FAILED',
                'FAILURE',
                null,
                null,
                $eventdata,
                'WARNING'
            );
        }
    }

    /**
     * Mask mobile number for logging (keep last 4 digits)
     *
     * @param string $mobile Mobile number
     * @return string Masked mobile number
     */
    protected function mask_mobile($mobile) {
        if (strlen($mobile) <= 4) {
            return '****';
        }
        return str_repeat('*', strlen($mobile) - 4) . substr($mobile, -4);
    }

    /**
     * Get configuration value
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if not set
     * @return mixed Configuration value
     */
    protected function get_config($key, $default = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }

    /**
     * Sanitize message content
     *
     * @param string $message Message content
     * @return string Sanitized message
     */
    protected function sanitize_message($message) {
        // Remove any control characters.
        $message = preg_replace('/[\x00-\x1F\x7F]/', '', $message);
        // Limit length (typical SMS limit is 160 chars for GSM, 70 for Unicode).
        return substr($message, 0, 160);
    }
}

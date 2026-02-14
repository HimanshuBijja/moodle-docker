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
 * Twilio SMS gateway implementation
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\messaging;

defined('MOODLE_INTERNAL') || die();

/**
 * Twilio SMS gateway implementation.
 *
 * Note: This class uses Moodle's \curl class from lib/filelib.php.
 * The require_once is done inside each method (not at file scope)
 * to avoid issues when the class is loaded via autoloader.
 */
class twilio_gateway extends sms_gateway {

    /**
     * Twilio API base URL
     */
    const API_BASE_URL = 'https://api.twilio.com/2010-04-01';

    /**
     * Send SMS via Twilio API
     *
     * @param string $to Recipient mobile number
     * @param string $message Message content
     * @param array $options Additional options
     * @return array Result
     */
    public function send_sms($to, $message, $options = array()) {
        // Validate configuration.
        $account_sid = $this->get_config('account_sid');
        $auth_token = $this->get_config('auth_token');
        $from_number = $this->get_config('from_number');

        if (empty($account_sid) || empty($auth_token) || empty($from_number)) {
            $error = 'Twilio configuration incomplete: missing ' .
                (empty($account_sid) ? 'Account SID, ' : '') .
                (empty($auth_token) ? 'Auth Token, ' : '') .
                (empty($from_number) ? 'From Number' : '');
            $error = rtrim($error, ', ');
            $this->log_sms_attempt($to, $message, false, '', $error);
            return array(
                'success' => false,
                'message_id' => '',
                'error' => $error
            );
        }

        // Validate and format mobile number.
        if (!$this->validate_mobile($to)) {
            $error = 'Invalid mobile number format: ' . $this->mask_mobile($to);
            $this->log_sms_attempt($to, $message, false, '', $error);
            return array(
                'success' => false,
                'message_id' => '',
                'error' => $error
            );
        }

        $to = $this->format_mobile($to);

        // Ensure from_number has + prefix (E.164).
        if (substr($from_number, 0, 1) !== '+') {
            $from_number = '+' . $from_number;
        }

        $message = $this->sanitize_message($message);

        // Prepare API request.
        $url = self::API_BASE_URL . "/Accounts/{$account_sid}/Messages.json";

        $postdata = array(
            'From' => $from_number,
            'To' => $to,
            'Body' => $message
        );

        // Add optional parameters.
        if (isset($options['validity_period'])) {
            $postdata['ValidityPeriod'] = $options['validity_period'];
        }
        if (isset($options['status_callback'])) {
            $postdata['StatusCallback'] = $options['status_callback'];
        }

        // Make HTTP request using Moodle's curl wrapper.
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setHeader(array(
            'Authorization: Basic ' . base64_encode($account_sid . ':' . $auth_token),
            'Content-Type: application/x-www-form-urlencoded'
        ));

        try {
            // Explicitly use '&' separator — PHP's http_build_query defaults to
            // ini_get('arg_separator.output') which many Moodle installs set to '&amp;',
            // corrupting the POST body so Twilio never sees the 'To' parameter.
            $response = $curl->post($url, http_build_query($postdata, '', '&'));
            $info = $curl->get_info();
            $httpcode = isset($info['http_code']) ? $info['http_code'] : 0;

            // Check for connection failure (no HTTP code means curl couldn't connect).
            if ($httpcode == 0) {
                $curl_error = $curl->error;
                $error = 'Cannot connect to Twilio API: ' . ($curl_error ? $curl_error : 'Connection failed');
                $this->log_sms_attempt($to, $message, false, '', $error);
                return array(
                    'success' => false,
                    'message_id' => '',
                    'error' => $error
                );
            }

            if ($httpcode >= 200 && $httpcode < 300) {
                $result = json_decode($response, true);

                if (isset($result['sid'])) {
                    $this->log_sms_attempt($to, $message, true, $result['sid']);
                    return array(
                        'success' => true,
                        'message_id' => $result['sid'],
                        'error' => ''
                    );
                }
            }

            // Handle error response.
            $result = json_decode($response, true);
            $error_msg = 'Twilio API error (HTTP ' . $httpcode . ')';
            if (isset($result['message'])) {
                $error_msg .= ': ' . $result['message'];
            }
            if (isset($result['code'])) {
                $error_msg .= ' [Twilio error ' . $result['code'] . ']';
            }

            $this->log_sms_attempt($to, $message, false, '', $error_msg);
            return array(
                'success' => false,
                'message_id' => '',
                'error' => $error_msg
            );

        } catch (\Exception $e) {
            $error = 'Twilio API exception: ' . $e->getMessage();
            $this->log_sms_attempt($to, $message, false, '', $error);
            return array(
                'success' => false,
                'message_id' => '',
                'error' => $error
            );
        }
    }

    /**
     * Check Twilio account balance
     *
     * @return array Result
     */
    public function check_balance() {
        $account_sid = $this->get_config('account_sid');
        $auth_token = $this->get_config('auth_token');

        if (empty($account_sid) || empty($auth_token)) {
            return array(
                'success' => false,
                'balance' => 0,
                'currency' => '',
                'error' => 'Twilio configuration incomplete'
            );
        }

        $url = self::API_BASE_URL . "/Accounts/{$account_sid}/Balance.json";

        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setHeader(array(
            'Authorization: Basic ' . base64_encode($account_sid . ':' . $auth_token)
        ));

        try {
            $response = $curl->get($url);
            $httpcode = $curl->get_info()['http_code'];

            if ($httpcode >= 200 && $httpcode < 300) {
                $result = json_decode($response, true);

                if (isset($result['balance'])) {
                    return array(
                        'success' => true,
                        'balance' => floatval($result['balance']),
                        'currency' => isset($result['currency']) ? $result['currency'] : 'USD',
                        'error' => ''
                    );
                }
            }

            return array(
                'success' => false,
                'balance' => 0,
                'currency' => '',
                'error' => 'Failed to retrieve balance'
            );

        } catch (\Exception $e) {
            return array(
                'success' => false,
                'balance' => 0,
                'currency' => '',
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Get delivery status of a message
     *
     * @param string $message_id Twilio message SID
     * @return array Result
     */
    public function get_delivery_status($message_id) {
        $account_sid = $this->get_config('account_sid');
        $auth_token = $this->get_config('auth_token');

        if (empty($account_sid) || empty($auth_token)) {
            return array(
                'success' => false,
                'status' => '',
                'delivered_at' => 0,
                'error' => 'Twilio configuration incomplete'
            );
        }

        $url = self::API_BASE_URL . "/Accounts/{$account_sid}/Messages/{$message_id}.json";

        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        $curl->setHeader(array(
            'Authorization: Basic ' . base64_encode($account_sid . ':' . $auth_token)
        ));

        try {
            $response = $curl->get($url);
            $httpcode = $curl->get_info()['http_code'];

            if ($httpcode >= 200 && $httpcode < 300) {
                $result = json_decode($response, true);

                if (isset($result['status'])) {
                    $delivered_at = 0;
                    if ($result['status'] === 'delivered' && isset($result['date_sent'])) {
                        $delivered_at = strtotime($result['date_sent']);
                    }

                    return array(
                        'success' => true,
                        'status' => $result['status'],
                        'delivered_at' => $delivered_at,
                        'error' => ''
                    );
                }
            }

            return array(
                'success' => false,
                'status' => '',
                'delivered_at' => 0,
                'error' => 'Failed to retrieve delivery status'
            );

        } catch (\Exception $e) {
            return array(
                'success' => false,
                'status' => '',
                'delivered_at' => 0,
                'error' => $e->getMessage()
            );
        }
    }

    /**
     * Test Twilio connection and credentials
     *
     * @return array Result with success and message
     */
    public function test_connection() {
        // Test by checking balance.
        $balance_result = $this->check_balance();

        if ($balance_result['success']) {
            return array(
                'success' => true,
                'message' => 'Twilio connection successful. Balance: ' .
                    $balance_result['balance'] . ' ' . $balance_result['currency']
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Twilio connection failed: ' . $balance_result['error']
            );
        }
    }
}

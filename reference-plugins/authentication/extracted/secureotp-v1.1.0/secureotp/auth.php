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
 * Secure OTP authentication plugin - Main authentication class
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/authlib.php');

/**
 * Secure OTP authentication plugin class
 */
class auth_plugin_secureotp extends auth_plugin_base {

    /**
     * Constructor
     */
    public function __construct() {
        $this->authtype = 'secureotp';
        $this->config = get_config('auth_secureotp');
    }

    /**
     * Initiate OTP-based login
     * This is NOT the traditional user_login() - we handle login differently
     *
     * @param string $identifier Employee ID, Mobile, or Email
     * @return array Result with success, message, and redirect_url
     */
    public function initiate_otp_login($identifier) {
        global $DB, $SESSION;

        // Sanitize identifier.
        require_once(__DIR__ . '/classes/security/input_sanitizer.php');
        $sanitizer = new \auth_secureotp\security\input_sanitizer();
        $identifier = $sanitizer->sanitize_identifier($identifier);

        // Check rate limiting.
        require_once(__DIR__ . '/classes/auth/rate_limiter.php');
        $rate_limiter = new \auth_secureotp\auth\rate_limiter();

        if (!$rate_limiter->check_rate_limit('OTP_REQUEST', $identifier)) {
            return array(
                'success' => false,
                'message' => get_string('error_rate_limit', 'auth_secureotp', 15),
                'error_code' => 'RATE_LIMIT'
            );
        }

        // Lookup user by identifier (employee_id, mobile, or email).
        $user = $this->find_user_by_identifier($identifier);

        if (!$user) {
            // Log failed lookup.
            $this->log_audit_event('LOGIN_FAILED', 'FAILURE', null, $identifier, array(
                'reason' => 'User not found',
                'identifier' => $identifier
            ));

            return array(
                'success' => false,
                'message' => get_string('error_user_not_found', 'auth_secureotp'),
                'error_code' => 'USER_NOT_FOUND'
            );
        }

        // Check user security status.
        $security = $DB->get_record('auth_secureotp_security', array('userid' => $user->id));

        if (!$security) {
            // Create security record if missing.
            $security = new \stdClass();
            $security->userid = $user->id;
            $security->status = 'PROVISIONED';
            $security->otp_enabled = 1;
            $security->timecreated = time();
            $security->timemodified = time();
            $DB->insert_record('auth_secureotp_security', $security);
            $security = $DB->get_record('auth_secureotp_security', array('userid' => $user->id));
        }

        // Check account status.
        if ($security->status === 'SUSPENDED') {
            return array(
                'success' => false,
                'message' => get_string('error_user_suspended', 'auth_secureotp'),
                'error_code' => 'ACCOUNT_SUSPENDED'
            );
        }

        if ($security->status === 'ARCHIVED') {
            return array(
                'success' => false,
                'message' => get_string('error_user_archived', 'auth_secureotp'),
                'error_code' => 'ACCOUNT_ARCHIVED'
            );
        }

        // Check if account is locked.
        if ($security->is_locked && $security->locked_until > time()) {
            $locked_until = userdate($security->locked_until);
            return array(
                'success' => false,
                'message' => get_string('error_account_locked', 'auth_secureotp', $locked_until),
                'error_code' => 'ACCOUNT_LOCKED'
            );
        }

        // Unlock account if lock period expired.
        if ($security->is_locked && $security->locked_until <= time()) {
            $DB->set_field('auth_secureotp_security', 'is_locked', 0, array('id' => $security->id));
            $DB->set_field('auth_secureotp_security', 'locked_until', null, array('id' => $security->id));
            $security->is_locked = 0;
        }

        // Generate OTP.
        require_once(__DIR__ . '/classes/auth/otp_manager.php');
        $otp_manager = new \auth_secureotp\auth\otp_manager();
        $otp_result = $otp_manager->generate_otp($user->id);

        if (!$otp_result['success']) {
            return array(
                'success' => false,
                'message' => 'Failed to generate OTP',
                'error_code' => 'OTP_GENERATION_FAILED'
            );
        }

        $otp = $otp_result['otp'];

        // Send OTP via SMS/Email.
        $send_result = $this->send_otp($user, $otp);

        if (!$send_result['success']) {
            return array(
                'success' => false,
                'message' => $send_result['error'],
                'error_code' => 'OTP_SEND_FAILED'
            );
        }

        // Store pending login in Moodle session.
        require_once(__DIR__ . '/classes/security/csrf_protection.php');
        $csrf = new \auth_secureotp\security\csrf_protection();
        $csrf_token = $csrf->generate_token();

        $SESSION->secureotp_pending_userid = $user->id;
        $SESSION->secureotp_pending_time = time();
        $SESSION->secureotp_csrf_token = $csrf_token;

        // Store device fingerprint.
        require_once(__DIR__ . '/classes/auth/device_fingerprint.php');
        $device_fp = new \auth_secureotp\auth\device_fingerprint();
        $SESSION->secureotp_device_fp = $device_fp->get_fingerprint();

        // Log OTP sent event.
        $this->log_audit_event('OTP_SENT', 'SUCCESS', $user->id, null, array(
            'delivery_method' => $send_result['method'],
            'recipient_masked' => $send_result['recipient_masked']
        ));

        // Record rate limit.
        $rate_limiter->record_attempt('OTP_REQUEST', $identifier);

        return array(
            'success' => true,
            'message' => get_string('otp_sent_success', 'auth_secureotp'),
            'csrf_token' => $csrf_token,
            'recipient_masked' => $send_result['recipient_masked'],
            'method' => $send_result['method']
        );
    }

    /**
     * Verify OTP and complete login
     *
     * @param string $otp OTP code entered by user
     * @param string $csrf_token CSRF token
     * @param bool $trust_device Whether to trust this device
     * @return array Result with success and redirect_url
     */
    public function verify_otp($otp, $csrf_token, $trust_device = false) {
        global $DB, $SESSION, $USER, $CFG;

        // Verify CSRF token.
        require_once(__DIR__ . '/classes/security/csrf_protection.php');
        $csrf = new \auth_secureotp\security\csrf_protection();

        if (!$csrf->verify_token($csrf_token)) {
            return array(
                'success' => false,
                'message' => get_string('error_csrf_token', 'auth_secureotp'),
                'error_code' => 'CSRF_INVALID'
            );
        }

        // Check session validity.
        if (!isset($SESSION->secureotp_pending_userid)) {
            return array(
                'success' => false,
                'message' => get_string('error_invalid_session', 'auth_secureotp'),
                'error_code' => 'SESSION_INVALID'
            );
        }

        $userid = $SESSION->secureotp_pending_userid;
        $user = $DB->get_record('user', array('id' => $userid));

        if (!$user) {
            return array(
                'success' => false,
                'message' => get_string('error_user_not_found', 'auth_secureotp'),
                'error_code' => 'USER_NOT_FOUND'
            );
        }

        // Verify OTP.
        require_once(__DIR__ . '/classes/auth/otp_manager.php');
        $otp_manager = new \auth_secureotp\auth\otp_manager();
        $verify_result = $otp_manager->validate_otp($userid, $otp);

        if (!$verify_result['success']) {
            // Record failed attempt.
            $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));
            if ($security) {
                $failed_attempts = $security->failed_attempts + 1;
                $DB->set_field('auth_secureotp_security', 'failed_attempts', $failed_attempts, array('id' => $security->id));
                $DB->set_field('auth_secureotp_security', 'last_failed_at', time(), array('id' => $security->id));

                // Lock account if too many failures.
                $max_attempts = isset($this->config->max_login_attempts) ? $this->config->max_login_attempts : 5;
                if ($failed_attempts >= $max_attempts) {
                    $lockout_duration = isset($this->config->lockout_duration) ? $this->config->lockout_duration : 15;
                    $locked_until = time() + ($lockout_duration * 60);

                    $DB->set_field('auth_secureotp_security', 'is_locked', 1, array('id' => $security->id));
                    $DB->set_field('auth_secureotp_security', 'locked_until', $locked_until, array('id' => $security->id));
                    $DB->set_field('auth_secureotp_security', 'locked_reason', 'Too many failed attempts', array('id' => $security->id));

                    $this->log_audit_event('ACCOUNT_LOCKED', 'WARNING', $userid, null, array(
                        'reason' => 'Too many failed OTP attempts',
                        'locked_until' => $locked_until
                    ), 'WARNING');

                    return array(
                        'success' => false,
                        'message' => get_string('error_too_many_attempts', 'auth_secureotp', $lockout_duration),
                        'error_code' => 'ACCOUNT_LOCKED'
                    );
                }
            }

            $this->log_audit_event('OTP_VERIFICATION_FAILED', 'FAILURE', $userid, null, array(
                'reason' => $verify_result['error']
            ));

            return array(
                'success' => false,
                'message' => get_string('error_invalid_otp', 'auth_secureotp'),
                'error_code' => 'OTP_INVALID'
            );
        }

        // OTP is valid - complete login.
        $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));

        // Reset failed attempts.
        $DB->set_field('auth_secureotp_security', 'failed_attempts', 0, array('id' => $security->id));

        // Update login statistics.
        $now = time();
        if (!$security->first_login_at) {
            $DB->set_field('auth_secureotp_security', 'first_login_at', $now, array('id' => $security->id));
        }
        $DB->set_field('auth_secureotp_security', 'last_login_at', $now, array('id' => $security->id));
        $DB->set_field('auth_secureotp_security', 'login_count', $security->login_count + 1, array('id' => $security->id));

        // Update status from PROVISIONED to ACTIVE on first login.
        if ($security->status === 'PROVISIONED') {
            $DB->set_field('auth_secureotp_security', 'status', 'ACTIVE', array('id' => $security->id));
        }

        // Store device fingerprint.
        require_once(__DIR__ . '/classes/auth/device_fingerprint.php');
        $device_fp = new \auth_secureotp\auth\device_fingerprint();
        $current_fp = $device_fp->get_fingerprint();
        $fp_hash = hash('sha256', $current_fp);

        // Check for device change.
        if ($security->device_fingerprint_hash && $security->device_fingerprint_hash !== $fp_hash) {
            $this->log_audit_event('DEVICE_CHANGE_DETECTED', 'WARNING', $userid, null, array(
                'old_fingerprint' => substr($security->device_fingerprint_hash, 0, 8),
                'new_fingerprint' => substr($fp_hash, 0, 8)
            ), 'WARNING');
            $DB->set_field('auth_secureotp_security', 'last_device_change', $now, array('id' => $security->id));
        }

        // Update device fingerprint.
        $DB->set_field('auth_secureotp_security', 'device_fingerprint_hash', $fp_hash, array('id' => $security->id));

        // Handle trusted devices.
        if ($trust_device) {
            $trusted_devices = $security->trusted_devices ? json_decode($security->trusted_devices, true) : array();
            $trusted_devices[] = array(
                'fingerprint' => $fp_hash,
                'added_at' => $now,
                'expires_at' => $now + (30 * 24 * 60 * 60) // 30 days
            );
            $DB->set_field('auth_secureotp_security', 'trusted_devices', json_encode($trusted_devices), array('id' => $security->id));
        }

        // Complete Moodle login using native session system.
        complete_user_login($user);

        // Store security metadata in session for additional verification.
        $SESSION->secureotp_device_fp = $current_fp;
        $SESSION->secureotp_login_ip = getremoteaddr();
        $SESSION->secureotp_login_time = $now;

        // Clear pending login data.
        unset($SESSION->secureotp_pending_userid);
        unset($SESSION->secureotp_pending_time);
        unset($SESSION->secureotp_csrf_token);

        // Log successful login.
        $this->log_audit_event('LOGIN_SUCCESS', 'SUCCESS', $userid, null, array(
            'login_count' => $security->login_count + 1
        ));

        return array(
            'success' => true,
            'message' => get_string('login_success', 'auth_secureotp'),
            'redirect_url' => $CFG->wwwroot . '/my/'
        );
    }

    /**
     * Find user by identifier (employee_id, mobile, or email)
     *
     * @param string $identifier Identifier string
     * @return object|false User object or false
     */
    private function find_user_by_identifier($identifier) {
        global $DB;

        // Try employee_id first.
        $userdata = $DB->get_record('auth_secureotp_userdata', array('employee_id' => $identifier));
        if ($userdata) {
            return $DB->get_record('user', array('id' => $userdata->userid));
        }

        // Try mobile number.
        $userdata = $DB->get_record('auth_secureotp_userdata', array('personal_mobile' => $identifier));
        if ($userdata) {
            return $DB->get_record('user', array('id' => $userdata->userid));
        }

        // Try email.
        $user = $DB->get_record('user', array('email' => $identifier));
        if ($user) {
            return $user;
        }

        // Try username.
        $user = $DB->get_record('user', array('username' => $identifier));
        if ($user) {
            return $user;
        }

        return false;
    }

    /**
     * Send OTP to user via SMS or Email
     *
     * @param object $user User object
     * @param string $otp OTP code
     * @return array Result
     */
    private function send_otp($user, $otp) {
        global $DB;

        // Get user's mobile number from extended profile.
        $userdata = $DB->get_record('auth_secureotp_userdata', array('userid' => $user->id));
        $sms_error = '';

        // Try SMS first if mobile available (SMS is primary delivery method).
        if ($userdata && !empty($userdata->personal_mobile)) {
            $sms_result = $this->send_otp_sms($userdata->personal_mobile, $otp);

            if ($sms_result['success']) {
                return array(
                    'success' => true,
                    'method' => 'SMS',
                    'recipient_masked' => substr($userdata->personal_mobile, -4)
                );
            }

            // SMS failed - log detailed error for admin diagnosis.
            $sms_error = isset($sms_result['error']) ? $sms_result['error'] : 'Unknown SMS error';
            $this->log_audit_event('OTP_SMS_FAILED', 'FAILURE', $user->id, null, array(
                'mobile_masked' => '****' . substr($userdata->personal_mobile, -4),
                'error' => $sms_error,
                'mobile_length' => strlen($userdata->personal_mobile)
            ), 'WARNING');

            // Also log to PHP error log so admin can see without checking audit table.
            debugging('SecureOTP SMS failed for user ' . $user->id . ': ' . $sms_error, DEBUG_NORMAL);
        }

        // Fallback to email only if user has an email address.
        if (!empty($user->email)) {
            require_once(__DIR__ . '/classes/messaging/email_gateway.php');
            $email_gateway = new \auth_secureotp\messaging\email_gateway();

            $result = $email_gateway->send_otp_email($user, $otp);

            if ($result['success']) {
                $email_parts = explode('@', $user->email);
                $masked_email = substr($email_parts[0], 0, 2) . '***@' . $email_parts[1];

                // Log that we fell back to email (admin should know SMS isn't working).
                if (!empty($sms_error)) {
                    $this->log_audit_event('OTP_EMAIL_FALLBACK', 'WARNING', $user->id, null, array(
                        'sms_error' => $sms_error,
                        'email_masked' => $masked_email
                    ), 'WARNING');
                }

                return array(
                    'success' => true,
                    'method' => 'Email',
                    'recipient_masked' => $masked_email
                );
            }

            // Both SMS and email failed.
            $email_error = isset($result['error']) ? $result['error'] : 'Unknown email error';

            if (!empty($sms_error)) {
                // Both channels failed - report the primary (SMS) error with detail.
                return array(
                    'success' => false,
                    'error' => get_string('error_sms_failed', 'auth_secureotp') . ' (' . $sms_error . ')'
                );
            }

            return array(
                'success' => false,
                'error' => $email_error
            );
        }

        // No email available.
        if (!empty($sms_error)) {
            return array(
                'success' => false,
                'error' => get_string('error_sms_failed', 'auth_secureotp') . ' (' . $sms_error . ')'
            );
        }

        return array(
            'success' => false,
            'error' => get_string('error_no_mobile', 'auth_secureotp')
        );
    }

    /**
     * Send OTP via SMS - synchronous Twilio gateway call
     *
     * OTP must be delivered synchronously because the user is waiting.
     * We do NOT use the message queue here - it would either:
     * (a) Queue but never send (no cron worker), or
     * (b) Send twice when Redis is down (queue fallback + direct).
     *
     * @param string $mobile Mobile number
     * @param string $otp OTP code
     * @return array Result with success key
     */
    private function send_otp_sms($mobile, $otp) {
        $config = get_config('auth_secureotp');

        // Check if SMS gateway credentials are configured.
        $account_sid = !empty($config->twilio_account_sid) ? trim($config->twilio_account_sid) : '';
        $auth_token = !empty($config->twilio_auth_token) ? trim($config->twilio_auth_token) : '';
        $from_number = !empty($config->twilio_from_number) ? trim($config->twilio_from_number) : '';

        if (empty($account_sid) || empty($auth_token) || empty($from_number)) {
            return array(
                'success' => false,
                'error' => 'SMS gateway not configured. Set Twilio Account SID, Auth Token, and From Number in plugin settings.'
            );
        }

        // Ensure from_number has + prefix (E.164 format required by Twilio).
        if (substr($from_number, 0, 1) !== '+') {
            $from_number = '+' . $from_number;
        }

        $message = $this->format_otp_sms($otp);

        $gateway_config = array(
            'account_sid' => $account_sid,
            'auth_token' => $auth_token,
            'from_number' => $from_number
        );

        // Send directly via Twilio (synchronous - user is waiting for OTP).
        require_once(__DIR__ . '/classes/messaging/sms_gateway.php');
        require_once(__DIR__ . '/classes/messaging/twilio_gateway.php');
        $gateway = new \auth_secureotp\messaging\twilio_gateway($gateway_config);

        return $gateway->send_sms($mobile, $message);
    }

    /**
     * Format OTP SMS message
     *
     * @param string $otp OTP code
     * @return string Formatted message
     */
    private function format_otp_sms($otp) {
        $site = get_site();

        $data = new \stdClass();
        $data->sitename = format_string($site->fullname);
        $data->otp = $otp;
        $data->validity = isset($this->config->otp_validity) ? $this->config->otp_validity : 5;

        return get_string('sms_otp_template', 'auth_secureotp', $data);
    }

    /**
     * Log audit event
     *
     * @param string $event_type Event type
     * @param string $event_status SUCCESS/FAILURE/WARNING
     * @param int $userid User ID (null if not applicable)
     * @param string $employee_id Employee ID (null if not applicable)
     * @param array $event_data Additional event data
     * @param string $severity INFO/WARNING/CRITICAL
     */
    private function log_audit_event($event_type, $event_status, $userid = null, $employee_id = null, $event_data = array(), $severity = 'INFO') {
        require_once(__DIR__ . '/classes/security/audit_logger.php');
        $logger = new \auth_secureotp\security\audit_logger();

        // Use employee_id as userid if userid is null but employee_id is set
        $user_identifier = $userid ?? $employee_id;

        // Let audit logger auto-detect IP address
        $logger->log_event($event_type, $event_status, $user_identifier, null, $event_data, $severity);
    }

    /**
     * Resend OTP to a user (public wrapper for send_otp)
     *
     * @param object $user User object
     * @param string $otp OTP code
     * @return array Result with success, method, recipient_masked
     */
    public function resend_otp($user, $otp) {
        return $this->send_otp($user, $otp);
    }

    /**
     * Standard Moodle auth plugin method - NOT USED (we use custom OTP flow)
     *
     * @param string $username Username
     * @param string $password Password
     * @return bool Always returns false
     */
    public function user_login($username, $password) {
        // This plugin does not use traditional username/password login.
        // All authentication is handled through initiate_otp_login() and verify_otp().
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's password
     *
     * @return bool
     */
    public function can_change_password() {
        return false;
    }

    /**
     * Returns true if plugin allows resetting of internal password
     *
     * @return bool
     */
    public function can_reset_password() {
        return false;
    }

    /**
     * Returns true if plugin allows signup and user creation
     *
     * @return bool
     */
    public function can_signup() {
        return false;
    }

    /**
     * Returns true if plugin allows confirming of new users
     *
     * @return bool
     */
    public function can_confirm() {
        return false;
    }

    /**
     * Hook for overriding behaviour before going to the login page
     *
     * NOTE: This only redirects if SecureOTP is the ONLY enabled auth method.
     * This prevents breaking other authentication methods.
     */
    public function pre_loginpage_hook() {
        global $CFG, $SESSION;

        // Only redirect if this is the primary/only auth method.
        // Check if user explicitly wants standard login.
        if (!empty($SESSION->wantsurl) || !empty($_GET['authldap_skipntlmsso'])) {
            // Allow standard login for special cases.
            return;
        }

        // Don't redirect if coming from logout or already on our login page.
        if (strpos($_SERVER['REQUEST_URI'], '/auth/secureotp/') !== false ||
            strpos($_SERVER['REQUEST_URI'], '/login/logout.php') !== false) {
            return;
        }

        // Get enabled auth plugins.
        $enabled_auths = get_enabled_auth_plugins();

        // Only redirect if SecureOTP is the first/primary auth method.
        if (!empty($enabled_auths) && $enabled_auths[0] === 'secureotp') {
            if (strpos($_SERVER['REQUEST_URI'], '/login/index.php') !== false) {
                redirect($CFG->wwwroot . '/auth/secureotp/login.php');
            }
        }
    }

    /**
     * Hook for overriding behaviour of logout page
     */
    public function logoutpage_hook() {
        global $SESSION;

        // Clear all secureotp session data.
        unset($SESSION->secureotp_pending_userid);
        unset($SESSION->secureotp_pending_time);
        unset($SESSION->secureotp_csrf_token);
        unset($SESSION->secureotp_device_fp);
        unset($SESSION->secureotp_login_ip);
        unset($SESSION->secureotp_login_time);
    }
}

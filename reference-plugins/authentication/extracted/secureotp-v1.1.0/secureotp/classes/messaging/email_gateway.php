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
 * Email gateway for OTP delivery (fallback method)
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\messaging;

defined('MOODLE_INTERNAL') || die();

/**
 * Email gateway for OTP delivery
 */
class email_gateway {

    /**
     * @var \auth_secureotp\security\audit_logger Audit logger instance
     */
    protected $audit_logger;

    /**
     * Constructor
     */
    public function __construct() {
        $this->audit_logger = new \auth_secureotp\security\audit_logger();
    }

    /**
     * Send OTP via email using Moodle's native email system
     *
     * @param object $user Moodle user object
     * @param string $otp OTP code
     * @param array $options Additional options (language, validity)
     * @return array Result with keys: success (bool), error (string)
     */
    public function send_otp_email($user, $otp, $options = array()) {
        global $CFG;

        if (empty($user->email)) {
            $error = 'User has no email address';
            $this->log_email_attempt($user->id, $user->email, false, $error);
            return array(
                'success' => false,
                'error' => $error
            );
        }

        // Get language preference.
        $lang = isset($options['language']) ? $options['language'] : $user->lang;
        if (empty($lang)) {
            $lang = $CFG->lang;
        }

        // Get validity period (in minutes).
        $validity = isset($options['validity']) ? $options['validity'] : 5;

        // Temporarily switch language for localised strings.
        $currentlang = current_language();
        if ($lang !== $currentlang) {
            force_current_language($lang);
        }

        $site = get_site();

        // Get email subject and body from language strings.
        $subject = get_string('otp_email_subject', 'auth_secureotp', format_string($site->fullname));

        $messagedata = new \stdClass();
        $messagedata->otp = $otp;
        $messagedata->validity = $validity;
        $messagedata->fullname = fullname($user);
        $messagedata->sitename = format_string($site->fullname);
        $messagedata->supportemail = !empty($CFG->supportemail) ? $CFG->supportemail : (isset($CFG->noreplyaddress) ? $CFG->noreplyaddress : '');

        $messagetext = get_string('otp_email_body', 'auth_secureotp', $messagedata);
        $messagehtml = $this->format_otp_email_html($messagedata);

        // Restore original language.
        if ($lang !== $currentlang) {
            force_current_language($currentlang);
        }

        // Create support user object.
        $supportuser = \core_user::get_support_user();

        try {
            // Use Moodle's native email function.
            $success = email_to_user(
                $user,
                $supportuser,
                $subject,
                $messagetext,
                $messagehtml
            );

            if ($success) {
                $this->log_email_attempt($user->id, $user->email, true);
                return array(
                    'success' => true,
                    'error' => ''
                );
            } else {
                $error = 'Failed to send email';
                $this->log_email_attempt($user->id, $user->email, false, $error);
                return array(
                    'success' => false,
                    'error' => $error
                );
            }

        } catch (\Exception $e) {
            $error = 'Email exception: ' . $e->getMessage();
            $this->log_email_attempt($user->id, $user->email, false, $error);
            return array(
                'success' => false,
                'error' => $error
            );
        }
    }

    /**
     * Format OTP email as HTML
     *
     * @param object $data Email data
     * @param string $lang Language code
     * @return string HTML content
     */
    protected function format_otp_email_html($data) {
        $lang = current_language();

        $html = '<!DOCTYPE html>
<html lang="' . s($lang) . '">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . get_string('otp_email_subject', 'auth_secureotp') . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #0066cc;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .otp-box {
            background-color: #fff;
            border: 2px dashed #0066cc;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border-radius: 5px;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #0066cc;
            letter-spacing: 5px;
            font-family: "Courier New", monospace;
        }
        .validity {
            color: #e74c3c;
            font-weight: bold;
            margin-top: 10px;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . s($data->sitename) . '</h1>
        <p>' . get_string('otp_email_header', 'auth_secureotp') . '</p>
    </div>
    <div class="content">
        <p>' . get_string('otp_email_greeting', 'auth_secureotp', $data) . '</p>

        <div class="otp-box">
            <p>' . get_string('otp_email_code_label', 'auth_secureotp') . '</p>
            <div class="otp-code">' . htmlspecialchars($data->otp) . '</div>
            <p class="validity">' . get_string('otp_email_validity', 'auth_secureotp', $data) . '</p>
        </div>

        <div class="warning">
            <strong>' . get_string('otp_email_security_warning_title', 'auth_secureotp') . '</strong><br>
            ' . get_string('otp_email_security_warning', 'auth_secureotp') . '
        </div>

        <p>' . get_string('otp_email_footer_text', 'auth_secureotp') . '</p>
    </div>
    <div class="footer">
        <p>' . get_string('otp_email_auto_message', 'auth_secureotp') . '</p>
        <p>' . get_string('otp_email_support', 'auth_secureotp', $data) . '</p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Log email sending attempt
     *
     * @param int $userid User ID
     * @param string $email Email address
     * @param bool $success Success status
     * @param string $error Error message (if failed)
     */
    protected function log_email_attempt($userid, $email, $success, $error = '') {
        $eventdata = array(
            'recipient' => $this->mask_email($email),
            'error' => $error
        );

        if ($success) {
            $this->audit_logger->log_event(
                'OTP_EMAIL_SENT',
                'SUCCESS',
                $userid,
                null,
                $eventdata
            );
        } else {
            $this->audit_logger->log_event(
                'OTP_EMAIL_FAILED',
                'FAILURE',
                $userid,
                null,
                $eventdata,
                'WARNING'
            );
        }
    }

    /**
     * Mask email address for logging
     *
     * @param string $email Email address
     * @return string Masked email
     */
    protected function mask_email($email) {
        if (strpos($email, '@') === false) {
            return '****';
        }

        list($local, $domain) = explode('@', $email);

        if (strlen($local) <= 2) {
            $masked_local = str_repeat('*', strlen($local));
        } else {
            $masked_local = substr($local, 0, 1) . str_repeat('*', strlen($local) - 2) . substr($local, -1);
        }

        return $masked_local . '@' . $domain;
    }

    /**
     * Send password reset notification (optional feature)
     *
     * @param object $user Moodle user object
     * @param string $reset_url Password reset URL
     * @return array Result
     */
    public function send_password_reset_email($user, $reset_url) {
        global $CFG;

        if (empty($user->email)) {
            return array(
                'success' => false,
                'error' => 'User has no email address'
            );
        }

        $supportuser = \core_user::get_support_user();

        $subject = get_string('password_reset_subject', 'auth_secureotp');

        $messagedata = new \stdClass();
        $messagedata->fullname = fullname($user);
        $messagedata->reset_url = $reset_url;
        $messagedata->sitename = format_string(get_site()->fullname);
        $messagedata->supportemail = !empty($CFG->supportemail) ? $CFG->supportemail : '';

        $messagetext = get_string('password_reset_body', 'auth_secureotp', $messagedata);

        try {
            $success = email_to_user($user, $supportuser, $subject, $messagetext);

            return array(
                'success' => $success,
                'error' => $success ? '' : 'Failed to send email'
            );

        } catch (\Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
}

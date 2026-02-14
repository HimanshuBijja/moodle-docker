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
 * OTP Verification Page
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/authlib.php');
require_once('auth.php');

// Declare global variables.
global $CFG, $PAGE, $OUTPUT, $SESSION, $SITE, $DB;

// This page should not require login.
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/auth/secureotp/verify_otp.php'));
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('otp_title', 'auth_secureotp'));
$PAGE->set_heading(get_string('otp_title', 'auth_secureotp'));

// Check if there's a pending login.
if (!isset($SESSION->secureotp_pending_userid)) {
    // No pending login, redirect to login page.
    redirect(new moodle_url('/auth/secureotp/login.php'), get_string('error_invalid_session', 'auth_secureotp'), null, \core\output\notification::NOTIFY_ERROR);
}

$error = '';
$success = '';

// Handle OTP submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $otp = required_param('otp', PARAM_RAW);
    $csrf_token = required_param('csrf_token', PARAM_RAW);
    $trust_device = optional_param('trust_device', 0, PARAM_INT);

    $auth = new auth_plugin_secureotp();
    $result = $auth->verify_otp($otp, $csrf_token, (bool)$trust_device);

    if ($result['success']) {
        // Login successful, redirect to dashboard.
        redirect(new moodle_url('/my/'), $result['message'], null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        $error = $result['message'];
    }
}

// Handle OTP resend request (via GET link or AJAX POST).
if (optional_param('resend', 0, PARAM_INT) && confirm_sesskey()) {
    $userid = $SESSION->secureotp_pending_userid;
    $user = $DB->get_record('user', array('id' => $userid));

    $resend_result = array('success' => false, 'error' => 'User not found');

    if ($user) {
        // Generate new OTP.
        require_once(__DIR__ . '/classes/auth/otp_manager.php');
        $otp_manager = new \auth_secureotp\auth\otp_manager();
        $otp_result = $otp_manager->generate_otp($userid);

        if ($otp_result['success']) {
            $auth = new auth_plugin_secureotp();
            $send_result = $auth->resend_otp($user, $otp_result['otp']);

            if ($send_result['success']) {
                $success = get_string('otp_sent_success', 'auth_secureotp');
                // Reset the pending time so timer and resend countdown refresh.
                $SESSION->secureotp_pending_time = time();
                $resend_result = array('success' => true);
            } else {
                $error = isset($send_result['error']) ? $send_result['error'] : 'Failed to resend OTP';
                $resend_result = array('success' => false, 'error' => $error);
            }
        } else {
            $error = 'Failed to generate new OTP';
            $resend_result = array('success' => false, 'error' => $error);
        }
    }

    // If this is an AJAX request, return JSON response.
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($resend_result);
        exit;
    }
}

// Get user details for display.
$userid = $SESSION->secureotp_pending_userid;
$user = $DB->get_record('user', array('id' => $userid));
$userdata = $DB->get_record('auth_secureotp_userdata', array('userid' => $userid));

// Prepare template context.
$config = get_config('auth_secureotp');

// Get site information.
$sitename = format_string($SITE->fullname);

// Get logo URL (handle case when logo doesn't exist).
$logourl_obj = $OUTPUT->get_logo_url(null, 200);
$logourl = $logourl_obj ? $logourl_obj->out() : '';

$context = array(
    'sitename' => $sitename,
    'logourl' => $logourl,
    'csrf_token' => $SESSION->secureotp_csrf_token,
    'error' => $error,
    'success' => $success,
    'masked_mobile' => '',
    'masked_email' => '',
    'otp_length' => isset($config->otp_length) ? $config->otp_length : 6,
    'validity_minutes' => isset($config->otp_validity) ? $config->otp_validity : 5,
    'can_resend' => false,
    'resend_countdown' => 60,
    'action_url' => new moodle_url('/auth/secureotp/verify_otp.php'),
    'resend_url' => new moodle_url('/auth/secureotp/verify_otp.php', array('resend' => 1, 'sesskey' => sesskey())),
    'login_url' => new moodle_url('/auth/secureotp/login.php'),
    'sesskey' => sesskey(),
    'year' => date('Y')
);

// Get masked mobile or email for display.
if ($userdata && !empty($userdata->personal_mobile)) {
    $context['masked_mobile'] = substr($userdata->personal_mobile, -4);
} else if (!empty($user->email)) {
    $email_parts = explode('@', $user->email);
    $context['masked_email'] = substr($email_parts[0], 0, 2) . '***@' . $email_parts[1];
}

// Check if OTP was sent recently (60 seconds cooldown for resend).
if (isset($SESSION->secureotp_pending_time)) {
    $time_since_otp = time() - $SESSION->secureotp_pending_time;
    if ($time_since_otp >= 60) {
        $context['can_resend'] = true;
        $context['resend_countdown'] = 0;
    } else {
        $context['resend_countdown'] = 60 - $time_since_otp;
    }
}

echo $OUTPUT->header();

// Render template.
echo $OUTPUT->render_from_template('auth_secureotp/otp_verify', $context);

echo $OUTPUT->footer();

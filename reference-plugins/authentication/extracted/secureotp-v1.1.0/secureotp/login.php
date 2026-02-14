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
 * Secure OTP Login Page
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
$PAGE->set_url(new moodle_url('/auth/secureotp/login.php'));
$PAGE->set_pagelayout('login');
$PAGE->set_title(get_string('login_title', 'auth_secureotp'));
$PAGE->set_heading(get_string('login_title', 'auth_secureotp'));

// If already logged in, redirect to dashboard.
if (isloggedin() && !isguestuser()) {
    redirect(new moodle_url('/my/'));
}

$error = '';
$rate_limit_message = '';

// Handle form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $identifier = required_param('identifier', PARAM_RAW);

    $auth = new auth_plugin_secureotp();
    $result = $auth->initiate_otp_login($identifier);

    if ($result['success']) {
        // OTP sent successfully, redirect to verification page.
        redirect(new moodle_url('/auth/secureotp/verify_otp.php'), $result['message'], null, \core\output\notification::NOTIFY_SUCCESS);
    } else {
        // Show error.
        if ($result['error_code'] === 'RATE_LIMIT') {
            $rate_limit_message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Prepare template context.
require_once($CFG->libdir . '/outputlib.php');

// Get available languages.
$languages = array();
$installed_langs = get_string_manager()->get_list_of_translations();
$priority_langs = array('en' => 'English', 'hi' => 'हिन्दी', 'te' => 'తెలుగు');

foreach ($priority_langs as $code => $name) {
    if (isset($installed_langs[$code])) {
        $languages[] = array(
            'code' => $code,
            'name' => $name,
            'selected' => ($code === current_language())
        );
    }
}

// Generate CSRF token.
require_once(__DIR__ . '/classes/security/csrf_protection.php');
$csrf = new \auth_secureotp\security\csrf_protection();
$csrf_token = $csrf->generate_token();
$SESSION->secureotp_csrf_token = $csrf_token;

// Get site information.
$sitename = format_string($SITE->fullname);

// Get logo URL (handle case when logo doesn't exist).
$logourl_obj = $OUTPUT->get_logo_url(null, 200);
$logourl = $logourl_obj ? $logourl_obj->out() : '';

$context = array(
    'sitename' => $sitename,
    'logourl' => $logourl,
    'error' => $error,
    'rate_limit_message' => $rate_limit_message,
    'csrf_token' => $csrf_token,
    'action_url' => new moodle_url('/auth/secureotp/login.php'),
    'sesskey' => sesskey(),
    'languages' => $languages,
    'year' => date('Y')
);

echo $OUTPUT->header();

// Render template.
echo $OUTPUT->render_from_template('auth_secureotp/login', $context);

echo $OUTPUT->footer();

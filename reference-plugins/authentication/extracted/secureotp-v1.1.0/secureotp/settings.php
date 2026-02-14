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
 * Settings for the Secure OTP authentication plugin
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Heading: General Settings.
    $settings->add(new admin_setting_heading(
        'auth_secureotp/settings_general',
        get_string('settings_general', 'auth_secureotp'),
        ''
    ));

    // Heading: OTP Configuration.
    $settings->add(new admin_setting_heading(
        'auth_secureotp/settings_otp',
        get_string('settings_otp', 'auth_secureotp'),
        ''
    ));

    // OTP Length.
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/otp_length',
        get_string('otp_length', 'auth_secureotp'),
        get_string('otp_length_desc', 'auth_secureotp'),
        6,
        PARAM_INT
    ));

    // OTP Validity (minutes).
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/otp_validity',
        get_string('otp_validity', 'auth_secureotp'),
        get_string('otp_validity_desc', 'auth_secureotp'),
        5,
        PARAM_INT
    ));

    // Heading: SMS Gateway Settings.
    $settings->add(new admin_setting_heading(
        'auth_secureotp/settings_sms',
        get_string('settings_sms', 'auth_secureotp'),
        ''
    ));

    // Twilio Account SID.
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/twilio_account_sid',
        get_string('twilio_account_sid', 'auth_secureotp'),
        get_string('twilio_account_sid_desc', 'auth_secureotp'),
        '',
        PARAM_TEXT
    ));

    // Twilio Auth Token.
    $settings->add(new admin_setting_configpasswordunmask(
        'auth_secureotp/twilio_auth_token',
        get_string('twilio_auth_token', 'auth_secureotp'),
        get_string('twilio_auth_token_desc', 'auth_secureotp'),
        ''
    ));

    // Twilio From Number.
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/twilio_from_number',
        get_string('twilio_from_number', 'auth_secureotp'),
        get_string('twilio_from_number_desc', 'auth_secureotp'),
        '',
        PARAM_TEXT
    ));

    // Test OTP Delivery link.
    $test_url = new moodle_url('/auth/secureotp/test_otp.php');
    $settings->add(new admin_setting_heading(
        'auth_secureotp/test_otp_link',
        get_string('test_otp_settings_link', 'auth_secureotp'),
        get_string('test_otp_settings_link_desc', 'auth_secureotp', $test_url->out())
    ));

    // Heading: Security Settings.
    $settings->add(new admin_setting_heading(
        'auth_secureotp/settings_security',
        get_string('settings_security', 'auth_secureotp'),
        ''
    ));

    // Max Login Attempts.
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/max_login_attempts',
        get_string('max_login_attempts', 'auth_secureotp'),
        get_string('max_login_attempts_desc', 'auth_secureotp'),
        5,
        PARAM_INT
    ));

    // Lockout Duration (minutes).
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/lockout_duration',
        get_string('lockout_duration', 'auth_secureotp'),
        get_string('lockout_duration_desc', 'auth_secureotp'),
        15,
        PARAM_INT
    ));

    // Rate Limit - OTP Requests per hour.
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/rate_limit_otp',
        get_string('rate_limit_otp', 'auth_secureotp'),
        get_string('rate_limit_otp_desc', 'auth_secureotp'),
        3,
        PARAM_INT
    ));

    // Enable Device Fingerprinting.
    $settings->add(new admin_setting_configcheckbox(
        'auth_secureotp/enable_device_fingerprint',
        get_string('enable_device_fingerprint', 'auth_secureotp'),
        get_string('enable_device_fingerprint_desc', 'auth_secureotp'),
        1
    ));

    // Require Trusted Device.
    $settings->add(new admin_setting_configcheckbox(
        'auth_secureotp/require_trusted_device',
        get_string('require_trusted_device', 'auth_secureotp'),
        get_string('require_trusted_device_desc', 'auth_secureotp'),
        0
    ));

    // Heading: Redis Configuration.
    $settings->add(new admin_setting_heading(
        'auth_secureotp/redis_settings',
        'Redis Configuration',
        'Configure Redis for OTP storage and message queue'
    ));

    // Redis Host.
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/redis_host',
        get_string('redis_host', 'auth_secureotp'),
        get_string('redis_host_desc', 'auth_secureotp'),
        '127.0.0.1',
        PARAM_HOST
    ));

    // Redis Port.
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/redis_port',
        get_string('redis_port', 'auth_secureotp'),
        get_string('redis_port_desc', 'auth_secureotp'),
        6379,
        PARAM_INT
    ));

    // Redis Password.
    $settings->add(new admin_setting_configpasswordunmask(
        'auth_secureotp/redis_password',
        get_string('redis_password', 'auth_secureotp'),
        get_string('redis_password_desc', 'auth_secureotp'),
        ''
    ));

    // Redis Database.
    $settings->add(new admin_setting_configtext(
        'auth_secureotp/redis_db',
        get_string('redis_db', 'auth_secureotp'),
        get_string('redis_db_desc', 'auth_secureotp'),
        0,
        PARAM_INT
    ));
}

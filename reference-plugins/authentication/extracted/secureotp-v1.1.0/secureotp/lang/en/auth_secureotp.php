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
 * English language strings for auth_secureotp
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Secure OTP Authentication';
$string['auth_secureotpdescription'] = 'Government-certified OTP authentication for Moodle with multi-layer security';

// Login page strings.
$string['login_title'] = 'Login with OTP';
$string['login_subtitle'] = 'Enter your Employee ID or Mobile Number to receive OTP';
$string['employee_id'] = 'Employee ID';
$string['mobile_number'] = 'Mobile Number';
$string['email_address'] = 'Email Address';
$string['send_otp'] = 'Send OTP';
$string['identifier_placeholder'] = 'Employee ID / Mobile / Email';
$string['identifier_required'] = 'Please enter your Employee ID, Mobile Number, or Email';

// OTP verification page strings.
$string['otp_title'] = 'Enter OTP';
$string['otp_subtitle'] = 'Enter the 6-digit OTP sent to your mobile';
$string['otp_code'] = 'OTP Code';
$string['otp_placeholder'] = 'Enter 6-digit OTP';
$string['verify_otp'] = 'Verify OTP';
$string['resend_otp'] = 'Resend OTP';
$string['otp_sent_to'] = 'OTP sent to mobile ending in **{$a}';
$string['otp_sent_to_email'] = 'OTP sent to email {$a}';
$string['otp_expires_in'] = 'OTP expires in {$a} minutes';
$string['otp_timer'] = 'Time remaining: {$a}';

// Success messages.
$string['otp_sent_success'] = 'OTP sent successfully';
$string['login_success'] = 'Login successful. Welcome!';
$string['otp_verified'] = 'OTP verified successfully';

// Error messages.
$string['error_invalid_identifier'] = 'Invalid Employee ID, Mobile Number, or Email';
$string['error_user_not_found'] = 'User not found in system';
$string['error_user_suspended'] = 'Your account has been suspended. Please contact administrator.';
$string['error_user_archived'] = 'Your account has been archived. Please contact administrator.';
$string['error_account_locked'] = 'Your account is locked until {$a}. Please try again later.';
$string['error_invalid_otp'] = 'Invalid OTP code. Please try again.';
$string['error_otp_expired'] = 'OTP has expired. Please request a new one.';
$string['error_otp_already_used'] = 'This OTP has already been used. Please request a new one.';
$string['error_too_many_attempts'] = 'Too many failed attempts. Account locked for {$a} minutes.';
$string['error_rate_limit'] = 'Too many requests. Please try again in {$a} minutes.';
$string['error_sms_failed'] = 'Failed to send SMS. Please try again or contact support.';
$string['error_email_failed'] = 'Failed to send email. Please contact support.';
$string['error_no_mobile'] = 'No mobile number found for this account';
$string['error_no_email'] = 'No email address found for this account';
$string['error_invalid_session'] = 'Invalid or expired session. Please start again.';
$string['error_csrf_token'] = 'Security token mismatch. Please try again.';
$string['error_device_changed'] = 'Device change detected. Additional verification required.';

// Admin settings strings.
$string['settings_header'] = 'Secure OTP Settings';
$string['settings_general'] = 'General Settings';
$string['settings_otp'] = 'OTP Configuration';
$string['settings_sms'] = 'SMS Gateway Settings';
$string['settings_security'] = 'Security Settings';
$string['settings_rate_limit'] = 'Rate Limiting';

$string['otp_length'] = 'OTP Length';
$string['otp_length_desc'] = 'Number of digits in OTP code (4-8)';
$string['otp_validity'] = 'OTP Validity Period';
$string['otp_validity_desc'] = 'OTP validity in minutes (default: 5)';
$string['otp_algorithm'] = 'OTP Algorithm';
$string['otp_algorithm_desc'] = 'Algorithm for OTP generation';

$string['sms_provider'] = 'SMS Provider';
$string['sms_provider_desc'] = 'Select SMS gateway provider';
$string['twilio_account_sid'] = 'Twilio Account SID';
$string['twilio_account_sid_desc'] = 'Your Twilio Account SID';
$string['twilio_auth_token'] = 'Twilio Auth Token';
$string['twilio_auth_token_desc'] = 'Your Twilio Authentication Token';
$string['twilio_from_number'] = 'Twilio From Number';
$string['twilio_from_number_desc'] = 'Your Twilio phone number (E.164 format)';

$string['max_login_attempts'] = 'Maximum Login Attempts';
$string['max_login_attempts_desc'] = 'Maximum failed login attempts before account lock';
$string['lockout_duration'] = 'Lockout Duration';
$string['lockout_duration_desc'] = 'Account lockout duration in minutes';
$string['rate_limit_otp'] = 'OTP Request Rate Limit';
$string['rate_limit_otp_desc'] = 'Maximum OTP requests per IP per hour';
$string['enable_device_fingerprint'] = 'Enable Device Fingerprinting';
$string['enable_device_fingerprint_desc'] = 'Track and verify device fingerprints';
$string['require_trusted_device'] = 'Require Trusted Device';
$string['require_trusted_device_desc'] = 'Only allow logins from trusted devices';

$string['redis_host'] = 'Redis Host';
$string['redis_host_desc'] = 'Redis server hostname (default: 127.0.0.1)';
$string['redis_port'] = 'Redis Port';
$string['redis_port_desc'] = 'Redis server port (default: 6379)';
$string['redis_password'] = 'Redis Password';
$string['redis_password_desc'] = 'Redis authentication password (if required)';
$string['redis_db'] = 'Redis Database';
$string['redis_db_desc'] = 'Redis database number (default: 0)';

// SMS templates.
$string['sms_otp_template'] = 'Your OTP for {$a->sitename} is: {$a->otp}. Valid for {$a->validity} minutes. Do not share.';
$string['sms_otp_template_hi'] = 'आपका OTP {$a->sitename} के लिए: {$a->otp}। {$a->validity} मिनट के लिए मान्य। साझा न करें।';
$string['sms_otp_template_te'] = 'మీ OTP {$a->sitename} కోసం: {$a->otp}. {$a->validity} నిమిషాలు చెల్లుతుంది. షేర్ చేయవద్దు.';

// Email templates.
$string['otp_email_subject'] = 'Your OTP Code - {$a}';
$string['otp_email_header'] = 'One-Time Password';
$string['otp_email_greeting'] = 'Hello {$a->fullname},';
$string['otp_email_body'] = 'Your OTP code for login is: {$a->otp}

This code is valid for {$a->validity} minutes.

If you did not request this code, please ignore this email.

Thank you,
{$a->sitename}';
$string['otp_email_code_label'] = 'Your One-Time Password:';
$string['otp_email_validity'] = 'Valid for {$a->validity} minutes';
$string['otp_email_security_warning_title'] = 'Security Warning';
$string['otp_email_security_warning'] = 'Never share this OTP with anyone. Our staff will never ask for your OTP.';
$string['otp_email_footer_text'] = 'If you did not request this OTP, please ignore this email or contact support if you have concerns.';
$string['otp_email_auto_message'] = 'This is an automated message. Please do not reply.';
$string['otp_email_support'] = 'Need help? Contact us at {$a->supportemail}';

// Password reset.
$string['password_reset_subject'] = 'Password Reset Request';
$string['password_reset_body'] = 'Hello {$a->fullname},

A password reset was requested for your account.

To reset your password, please visit:
{$a->reset_url}

If you did not request this, please ignore this email.

Thank you,
{$a->sitename}';

// Audit log event types.
$string['event_otp_sent'] = 'OTP Sent';
$string['event_otp_verified'] = 'OTP Verified';
$string['event_otp_failed'] = 'OTP Verification Failed';
$string['event_login_success'] = 'Login Successful';
$string['event_login_failed'] = 'Login Failed';
$string['event_account_locked'] = 'Account Locked';
$string['event_account_unlocked'] = 'Account Unlocked';
$string['event_device_change'] = 'Device Change Detected';
$string['event_rate_limit'] = 'Rate Limit Exceeded';
$string['event_user_created'] = 'User Created';
$string['event_user_updated'] = 'User Updated';
$string['event_bulk_import'] = 'Bulk User Import';

// Admin dashboard.
$string['dashboard_title'] = 'Security Dashboard';
$string['dashboard_total_users'] = 'Total Users';
$string['dashboard_active_users'] = 'Active Users';
$string['dashboard_logins_today'] = 'Logins Today';
$string['dashboard_failed_attempts'] = 'Failed Attempts Today';
$string['dashboard_locked_accounts'] = 'Locked Accounts';
$string['dashboard_success_rate'] = 'Login Success Rate';
$string['dashboard_recent_logins'] = 'Recent Logins';
$string['dashboard_security_alerts'] = 'Security Alerts';
$string['dashboard_quick_actions'] = 'Quick Actions';

// User management.
$string['user_search'] = 'Search Users';
$string['user_status'] = 'Status';
$string['user_employee_id'] = 'Employee ID';
$string['user_last_login'] = 'Last Login';
$string['user_login_count'] = 'Login Count';
$string['user_actions'] = 'Actions';
$string['action_unlock'] = 'Unlock Account';
$string['action_suspend'] = 'Suspend';
$string['action_activate'] = 'Activate';
$string['action_view_audit'] = 'View Audit Log';
$string['action_send_test_otp'] = 'Send Test OTP';

// Bulk import.
$string['import_title'] = 'Bulk User Import';
$string['import_upload_file'] = 'Upload CSV File';
$string['import_source_system'] = 'Source System';
$string['import_dry_run'] = 'Dry Run (Validate Only)';
$string['import_start'] = 'Start Import';
$string['import_batch_id'] = 'Batch ID';
$string['import_total_records'] = 'Total Records';
$string['import_created'] = 'Created';
$string['import_updated'] = 'Updated';
$string['import_failed'] = 'Failed';
$string['import_duration'] = 'Duration';
$string['import_status'] = 'Status';
$string['import_view_log'] = 'View Import Log';
$string['import_download_errors'] = 'Download Errors';

// Status values.
$string['status_provisioned'] = 'Provisioned';
$string['status_active'] = 'Active';
$string['status_suspended'] = 'Suspended';
$string['status_archived'] = 'Archived';

// Capabilities.
$string['secureotp:manage'] = 'Manage OTP authentication settings';
$string['secureotp:viewaudit'] = 'View audit logs';
$string['secureotp:manageusers'] = 'Manage user security status';
$string['secureotp:bulkimport'] = 'Perform bulk user imports';
$string['secureotp:viewreports'] = 'View security reports';

// Privacy API.
$string['privacy:metadata:auth_secureotp_userdata'] = 'Extended user profile data';
$string['privacy:metadata:auth_secureotp_security'] = 'User authentication and security metadata';
$string['privacy:metadata:auth_secureotp_audit'] = 'Immutable audit trail of authentication events';
$string['privacy:metadata:userid'] = 'User ID';
$string['privacy:metadata:employee_id'] = 'Employee ID';
$string['privacy:metadata:personal_mobile'] = 'Personal mobile number';
$string['privacy:metadata:ip_address'] = 'IP address';
$string['privacy:metadata:device_fingerprint'] = 'Device fingerprint hash';
$string['privacy:metadata:login_count'] = 'Number of successful logins';
$string['privacy:metadata:last_login_at'] = 'Last login timestamp';

// Miscellaneous.
$string['back_to_login'] = 'Back to Login';
$string['change_identifier'] = 'Use Different ID';
$string['trust_this_device'] = 'Trust this device (30 days)';
$string['logout'] = 'Logout';
$string['contact_support'] = 'Contact Support';
$string['sending'] = 'Sending';
$string['language'] = 'Language';

// Error messages for import/validation.
$string['missingrequiredfield'] = 'Missing required CSV field: {$a}';
$string['emptyemployeeid'] = 'Empty employee_id in row {$a}';
$string['emptyfirstname'] = 'Empty firstname in row {$a}';
$string['emptylastname'] = 'Empty lastname in row {$a}';
$string['invalidmobile'] = 'Invalid mobile number in row {$a}';
$string['invalidemail'] = 'Invalid email in row {$a}';
$string['csvemptyfile'] = 'CSV file is empty';
$string['filenotfound'] = 'File not found: {$a}';
$string['filenotreadable'] = 'File is not readable: {$a}';
$string['cannotreadfile'] = 'Cannot read file: {$a}';

// Scheduled tasks.
$string['task_cleanup_otps'] = 'Cleanup expired OTPs';

// Event strings.
$string['event_login_attempted'] = 'SecureOTP login attempted';

// CSV import validation.
$string['csvcolumnmismatch'] = 'CSV column count mismatch: {$a}';

// Test OTP page strings.
$string['test_otp_title'] = 'Test OTP Delivery';
$string['test_otp_desc'] = 'Test SMS and Email OTP delivery to verify your configuration is working correctly.';
$string['test_otp_back_settings'] = 'Back to Plugin Settings';
$string['test_otp_config_status'] = 'Configuration Status';
$string['test_otp_twilio_conn'] = 'Test Twilio Connection';
$string['test_otp_twilio_conn_desc'] = 'Verify your Twilio Account SID and Auth Token are valid by checking account balance.';
$string['test_otp_twilio_conn_btn'] = 'Test Connection';
$string['test_otp_redis_conn'] = 'Test Redis Connection';
$string['test_otp_redis_conn_desc'] = 'Test Redis server connectivity and read/write operations.';
$string['test_otp_redis_conn_btn'] = 'Test Redis';
$string['test_otp_sms'] = 'Test SMS Delivery';
$string['test_otp_sms_desc'] = 'Send a test OTP to a mobile number via Twilio to verify SMS delivery is working.';
$string['test_otp_mobile_label'] = 'Mobile Number';
$string['test_otp_mobile_help'] = 'Enter mobile number with country code (e.g., +919876543210)';
$string['test_otp_send_sms'] = 'Send Test SMS';
$string['test_otp_sms_not_configured'] = 'Twilio credentials not configured. Please set them in plugin settings first.';
$string['test_otp_email'] = 'Test Email Delivery';
$string['test_otp_email_desc'] = 'Send a test OTP to an email address to verify email delivery is working.';
$string['test_otp_email_label'] = 'Email Address';
$string['test_otp_email_help'] = 'Enter an email address to send test OTP';
$string['test_otp_send_email'] = 'Send Test Email';
$string['test_otp_settings_link'] = 'Test OTP Delivery';
$string['test_otp_settings_link_desc'] = '<a href="{$a}">Click here to test SMS and Email OTP delivery</a>. Verify your Twilio and email configuration before going live.';

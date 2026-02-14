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
 * Telugu language strings for auth_secureotp
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'సురక్షిత OTP ప్రమాణీకరణ';
$string['auth_secureotpdescription'] = 'బహుళ-స్థాయి భద్రతతో Moodle కోసం ప్రభుత్వ-ధృవీకరించబడిన OTP ప్రమాణీకరణ';

// Login page strings.
$string['login_title'] = 'OTP తో లాగిన్ చేయండి';
$string['login_subtitle'] = 'OTP అందుకోవడానికి మీ ఉద్యోగి ID లేదా మొబైల్ నంబర్ నమోదు చేయండి';
$string['employee_id'] = 'ఉద్యోగి ID';
$string['mobile_number'] = 'మొబైల్ నంబర్';
$string['email_address'] = 'ఇమెయిల్ చిరునామా';
$string['send_otp'] = 'OTP పంపండి';
$string['identifier_placeholder'] = 'ఉద్యోగి ID / మొబైల్ / ఇమెయిల్';
$string['identifier_required'] = 'దయచేసి మీ ఉద్యోగి ID, మొబైల్ నంబర్ లేదా ఇమెయిల్ నమోదు చేయండి';

// OTP verification page strings.
$string['otp_title'] = 'OTP నమోదు చేయండి';
$string['otp_subtitle'] = 'మీ మొబైల్‌కు పంపబడిన 6 అంకెల OTP నమోదు చేయండి';
$string['otp_code'] = 'OTP కోడ్';
$string['otp_placeholder'] = '6 అంకెల OTP నమోదు చేయండి';
$string['verify_otp'] = 'OTP ధృవీకరించండి';
$string['resend_otp'] = 'OTP మళ్లీ పంపండి';
$string['otp_sent_to'] = '**{$a}తో ముగిసే మొబైల్‌కు OTP పంపబడింది';
$string['otp_sent_to_email'] = '{$a} ఇమెయిల్‌కు OTP పంపబడింది';
$string['otp_expires_in'] = 'OTP {$a} నిమిషాల్లో గడువు ముగుస్తుంది';
$string['otp_timer'] = 'మిగిలిన సమయం: {$a}';

// Success messages.
$string['otp_sent_success'] = 'OTP విజయవంతంగా పంపబడింది';
$string['login_success'] = 'లాగిన్ విజయవంతం. స్వాగతం!';
$string['otp_verified'] = 'OTP విజయవంతంగా ధృవీకరించబడింది';

// Error messages.
$string['error_invalid_identifier'] = 'చెల్లని ఉద్యోగి ID, మొబైల్ నంబర్ లేదా ఇమెయిల్';
$string['error_user_not_found'] = 'సిస్టమ్‌లో వినియోగదారు కనుగొనబడలేదు';
$string['error_user_suspended'] = 'మీ ఖాతా తాత్కాలికంగా నిలిపివేయబడింది. దయచేసి నిర్వాహకుడిని సంప్రదించండి.';
$string['error_user_archived'] = 'మీ ఖాతా ఆర్కైవ్ చేయబడింది. దయచేసి నిర్వాహకుడిని సంప్రదించండి.';
$string['error_account_locked'] = 'మీ ఖాతా {$a} వరకు లాక్ చేయబడింది. దయచేసి తర్వాత మళ్లీ ప్రయత్నించండి.';
$string['error_invalid_otp'] = 'చెల్లని OTP కోడ్. దయచేసి మళ్లీ ప్రయత్నించండి.';
$string['error_otp_expired'] = 'OTP గడువు ముగిసింది. దయచేసి కొత్తదాన్ని అభ్యర్థించండి.';
$string['error_otp_already_used'] = 'ఈ OTP ఇప్పటికే ఉపయోగించబడింది. దయచేసి కొత్తదాన్ని అభ్యర్థించండి.';
$string['error_too_many_attempts'] = 'చాలా ఎక్కువ విఫల ప్రయత్నాలు. ఖాతా {$a} నిమిషాలకు లాక్ చేయబడింది.';
$string['error_rate_limit'] = 'చాలా ఎక్కువ అభ్యర్థనలు. దయచేసి {$a} నిమిషాల్లో మళ్లీ ప్రయత్నించండి.';
$string['error_sms_failed'] = 'SMS పంపడం విఫలమైంది. దయచేసి మళ్లీ ప్రయత్నించండి లేదా మద్దతును సంప్రదించండి.';
$string['error_email_failed'] = 'ఇమెయిల్ పంపడం విఫలమైంది. దయచేసి మద్దతును సంప్రదించండి.';
$string['error_no_mobile'] = 'ఈ ఖాతాకు మొబైల్ నంబర్ కనుగొనబడలేదు';
$string['error_no_email'] = 'ఈ ఖాతాకు ఇమెయిల్ చిరునామా కనుగొనబడలేదు';
$string['error_invalid_session'] = 'చెల్లని లేదా గడువు ముగిసిన సెషన్. దయచేసి మళ్లీ ప్రారంభించండి.';
$string['error_csrf_token'] = 'భద్రతా టోకెన్ సరిపోలలేదు. దయచేసి మళ్లీ ప్రయత్నించండి.';
$string['error_device_changed'] = 'పరికరం మార్పు గుర్తించబడింది. అదనపు ధృవీకరణ అవసరం.';

// Admin settings strings.
$string['settings_header'] = 'సురక్షిత OTP సెట్టింగ్‌లు';
$string['settings_general'] = 'సాధారణ సెట్టింగ్‌లు';
$string['settings_otp'] = 'OTP కాన్ఫిగరేషన్';
$string['settings_sms'] = 'SMS గేట్‌వే సెట్టింగ్‌లు';
$string['settings_security'] = 'భద్రతా సెట్టింగ్‌లు';
$string['settings_rate_limit'] = 'రేటు పరిమితి';

$string['otp_length'] = 'OTP పొడవు';
$string['otp_length_desc'] = 'OTP కోడ్‌లో అంకెల సంఖ్య (4-8)';
$string['otp_validity'] = 'OTP చెల్లుబాటు కాలం';
$string['otp_validity_desc'] = 'నిమిషాల్లో OTP చెల్లుబాటు (డిఫాల్ట్: 5)';
$string['otp_algorithm'] = 'OTP అల్గోరిథం';
$string['otp_algorithm_desc'] = 'OTP జనరేషన్ కోసం అల్గోరిథం';

$string['sms_provider'] = 'SMS ప్రొవైడర్';
$string['sms_provider_desc'] = 'SMS గేట్‌వే ప్రొవైడర్‌ను ఎంచుకోండి';
$string['twilio_account_sid'] = 'Twilio ఖాతా SID';
$string['twilio_account_sid_desc'] = 'మీ Twilio ఖాతా SID';
$string['twilio_auth_token'] = 'Twilio ప్రమాణీకరణ టోకెన్';
$string['twilio_auth_token_desc'] = 'మీ Twilio ప్రమాణీకరణ టోకెన్';
$string['twilio_from_number'] = 'Twilio నుండి నంబర్';
$string['twilio_from_number_desc'] = 'మీ Twilio ఫోన్ నంబర్ (E.164 ఫార్మాట్)';

$string['max_login_attempts'] = 'గరిష్ట లాగిన్ ప్రయత్నాలు';
$string['max_login_attempts_desc'] = 'ఖాతా లాక్ కాకముందు గరిష్ట విఫల లాగిన్ ప్రయత్నాలు';
$string['lockout_duration'] = 'లాకౌట్ వ్యవధి';
$string['lockout_duration_desc'] = 'నిమిషాల్లో ఖాతా లాకౌట్ వ్యవధి';
$string['rate_limit_otp'] = 'OTP అభ్యర్థన రేటు పరిమితి';
$string['rate_limit_otp_desc'] = 'గంటకు ప్రతి IP కు గరిష్ట OTP అభ్యర్థనలు';
$string['enable_device_fingerprint'] = 'పరికర వేలిముద్ర ప్రారంభించండి';
$string['enable_device_fingerprint_desc'] = 'పరికర వేలిముద్రలను ట్రాక్ మరియు ధృవీకరించండి';
$string['require_trusted_device'] = 'విశ్వసనీయ పరికరం అవసరం';
$string['require_trusted_device_desc'] = 'విశ్వసనీయ పరికరాల నుండి మాత్రమే లాగిన్‌లను అనుమతించండి';

$string['redis_host'] = 'Redis హోస్ట్';
$string['redis_host_desc'] = 'Redis సర్వర్ హోస్ట్‌నేమ్ (డిఫాల్ట్: 127.0.0.1)';
$string['redis_port'] = 'Redis పోర్ట్';
$string['redis_port_desc'] = 'Redis సర్వర్ పోర్ట్ (డిఫాల్ట్: 6379)';
$string['redis_password'] = 'Redis పాస్‌వర్డ్';
$string['redis_password_desc'] = 'Redis ప్రమాణీకరణ పాస్‌వర్డ్ (అవసరమైతే)';
$string['redis_db'] = 'Redis డేటాబేస్';
$string['redis_db_desc'] = 'Redis డేటాబేస్ నంబర్ (డిఫాల్ట్: 0)';

// SMS templates.
$string['sms_otp_template'] = '{$a->sitename} కోసం మీ OTP: {$a->otp}. {$a->validity} నిమిషాలు చెల్లుతుంది. షేర్ చేయవద్దు.';
$string['sms_otp_template_hi'] = 'आपका OTP {$a->sitename} के लिए: {$a->otp}। {$a->validity} मिनट के लिए मान्य। साझा न करें।';
$string['sms_otp_template_te'] = '{$a->sitename} కోసం మీ OTP: {$a->otp}. {$a->validity} నిమిషాలు చెల్లుతుంది. షేర్ చేయవద్దు.';

// Email templates.
$string['otp_email_subject'] = 'మీ OTP కోడ్ - {$a}';
$string['otp_email_header'] = 'ఒకసారి పాస్‌వర్డ్';
$string['otp_email_greeting'] = 'నమస్కారం {$a->fullname},';
$string['otp_email_body'] = 'లాగిన్ కోసం మీ OTP కోడ్: {$a->otp}

ఈ కోడ్ {$a->validity} నిమిషాలకు చెల్లుతుంది.

మీరు ఈ కోడ్‌ను అభ్యర్థించకపోతే, దయచేసి ఈ ఇమెయిల్‌ను విస్మరించండి.

ధన్యవాదాలు,
{$a->sitename}';
$string['otp_email_code_label'] = 'మీ ఒకసారి పాస్‌వర్డ్:';
$string['otp_email_validity'] = '{$a->validity} నిమిషాలకు చెల్లుతుంది';
$string['otp_email_security_warning_title'] = 'భద్రతా హెచ్చరిక';
$string['otp_email_security_warning'] = 'ఈ OTPని ఎవరితోనూ షేర్ చేయవద్దు. మా సిబ్బంది మీ OTPని ఎప్పుడూ అడగరు.';
$string['otp_email_footer_text'] = 'మీరు ఈ OTPని అభ్యర్థించకపోతే, దయచేసి ఈ ఇమెయిల్‌ను విస్మరించండి లేదా మీకు ఆందోళనలు ఉంటే మద్దతును సంప్రదించండి.';
$string['otp_email_auto_message'] = 'ఇది స్వయంచాలక సందేశం. దయచేసి ప్రత్యుత్తరం ఇవ్వవద్దు.';
$string['otp_email_support'] = 'సహాయం కావాలా? మమ్మల్ని సంప్రదించండి: {$a->supportemail}';

// Password reset.
$string['password_reset_subject'] = 'పాస్‌వర్డ్ రీసెట్ అభ్యర్థన';
$string['password_reset_body'] = 'నమస్కారం {$a->fullname},

మీ ఖాతా కోసం పాస్‌వర్డ్ రీసెట్ అభ్యర్థించబడింది.

మీ పాస్‌వర్డ్‌ను రీసెట్ చేయడానికి, దయచేసి ఇక్కడ సందర్శించండి:
{$a->reset_url}

మీరు దీన్ని అభ్యర్థించకపోతే, దయచేసి ఈ ఇమెయిల్‌ను విస్మరించండి.

ధన్యవాదాలు,
{$a->sitename}';

// Audit log event types.
$string['event_otp_sent'] = 'OTP పంపబడింది';
$string['event_otp_verified'] = 'OTP ధృవీకరించబడింది';
$string['event_otp_failed'] = 'OTP ధృవీకరణ విఫలమైంది';
$string['event_login_success'] = 'లాగిన్ విజయవంతం';
$string['event_login_failed'] = 'లాగిన్ విఫలమైంది';
$string['event_account_locked'] = 'ఖాతా లాక్ చేయబడింది';
$string['event_account_unlocked'] = 'ఖాతా అన్‌లాక్ చేయబడింది';
$string['event_device_change'] = 'పరికరం మార్పు గుర్తించబడింది';
$string['event_rate_limit'] = 'రేటు పరిమితి మించిపోయింది';
$string['event_user_created'] = 'వినియోగదారు సృష్టించబడింది';
$string['event_user_updated'] = 'వినియోగదారు నవీకరించబడింది';
$string['event_bulk_import'] = 'బల్క్ వినియోగదారు దిగుమతి';

// Admin dashboard.
$string['dashboard_title'] = 'భద్రతా డాష్‌బోర్డ్';
$string['dashboard_total_users'] = 'మొత్తం వినియోగదారులు';
$string['dashboard_active_users'] = 'చురుకైన వినియోగదారులు';
$string['dashboard_logins_today'] = 'నేడు లాగిన్‌లు';
$string['dashboard_failed_attempts'] = 'నేడు విఫల ప్రయత్నాలు';
$string['dashboard_locked_accounts'] = 'లాక్ చేయబడిన ఖాతాలు';
$string['dashboard_success_rate'] = 'లాగిన్ విజయ రేటు';
$string['dashboard_recent_logins'] = 'ఇటీవలి లాగిన్‌లు';
$string['dashboard_security_alerts'] = 'భద్రతా హెచ్చరికలు';
$string['dashboard_quick_actions'] = 'త్వరిత చర్యలు';

// User management.
$string['user_search'] = 'వినియోగదారులను శోధించండి';
$string['user_status'] = 'స్థితి';
$string['user_employee_id'] = 'ఉద్యోగి ID';
$string['user_last_login'] = 'చివరి లాగిన్';
$string['user_login_count'] = 'లాగిన్ గణన';
$string['user_actions'] = 'చర్యలు';
$string['action_unlock'] = 'ఖాతాను అన్‌లాక్ చేయండి';
$string['action_suspend'] = 'నిలిపివేయండి';
$string['action_activate'] = 'సక్రియం చేయండి';
$string['action_view_audit'] = 'ఆడిట్ లాగ్ చూడండి';
$string['action_send_test_otp'] = 'పరీక్ష OTP పంపండి';

// Bulk import.
$string['import_title'] = 'బల్క్ వినియోగదారు దిగుమతి';
$string['import_upload_file'] = 'CSV ఫైల్ అప్‌లోడ్ చేయండి';
$string['import_source_system'] = 'మూల వ్యవస్థ';
$string['import_dry_run'] = 'డ్రై రన్ (ధృవీకరణ మాత్రమే)';
$string['import_start'] = 'దిగుమతి ప్రారంభించండి';
$string['import_batch_id'] = 'బ్యాచ్ ID';
$string['import_total_records'] = 'మొత్తం రికార్డులు';
$string['import_created'] = 'సృష్టించబడింది';
$string['import_updated'] = 'నవీకరించబడింది';
$string['import_failed'] = 'విఫలమైంది';
$string['import_duration'] = 'వ్యవధి';
$string['import_status'] = 'స్థితి';
$string['import_view_log'] = 'దిగుమతి లాగ్ చూడండి';
$string['import_download_errors'] = 'లోపాలను డౌన్‌లోడ్ చేయండి';

// Status values.
$string['status_provisioned'] = 'ప్రొవిజన్ చేయబడింది';
$string['status_active'] = 'చురుకైన';
$string['status_suspended'] = 'నిలిపివేయబడింది';
$string['status_archived'] = 'ఆర్కైవ్ చేయబడింది';

// Capabilities.
$string['secureotp:manage'] = 'OTP ప్రమాణీకరణ సెట్టింగ్‌లను నిర్వహించండి';
$string['secureotp:viewaudit'] = 'ఆడిట్ లాగ్‌లను చూడండి';
$string['secureotp:manageusers'] = 'వినియోగదారు భద్రతా స్థితిని నిర్వహించండి';
$string['secureotp:bulkimport'] = 'బల్క్ వినియోగదారు దిగుమతులు చేయండి';
$string['secureotp:viewreports'] = 'భద్రతా నివేదికలను చూడండి';

// Privacy API.
$string['privacy:metadata:auth_secureotp_userdata'] = 'విస్తరించిన వినియోగదారు ప్రొఫైల్ డేటా';
$string['privacy:metadata:auth_secureotp_security'] = 'వినియోగదారు ప్రమాణీకరణ మరియు భద్రతా మెటాడేటా';
$string['privacy:metadata:auth_secureotp_audit'] = 'ప్రమాణీకరణ ఈవెంట్‌ల మార్చలేని ఆడిట్ ట్రైల్';
$string['privacy:metadata:userid'] = 'వినియోగదారు ID';
$string['privacy:metadata:employee_id'] = 'ఉద్యోగి ID';
$string['privacy:metadata:personal_mobile'] = 'వ్యక్తిగత మొబైల్ నంబర్';
$string['privacy:metadata:ip_address'] = 'IP చిరునామా';
$string['privacy:metadata:device_fingerprint'] = 'పరికర వేలిముద్ర హాష్';
$string['privacy:metadata:login_count'] = 'విజయవంతమైన లాగిన్‌ల సంఖ్య';
$string['privacy:metadata:last_login_at'] = 'చివరి లాగిన్ టైమ్‌స్టాంప్';

// Miscellaneous.
$string['back_to_login'] = 'లాగిన్‌కు తిరిగి వెళ్ళండి';
$string['change_identifier'] = 'వేరే ID ఉపయోగించండి';
$string['trust_this_device'] = 'ఈ పరికరాన్ని విశ్వసించండి (30 రోజులు)';
$string['logout'] = 'లాగౌట్';
$string['contact_support'] = 'మద్దతును సంప్రదించండి';

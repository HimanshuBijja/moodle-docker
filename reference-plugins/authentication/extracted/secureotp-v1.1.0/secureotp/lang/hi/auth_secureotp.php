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
 * Hindi language strings for auth_secureotp
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'सुरक्षित OTP प्रमाणीकरण';
$string['auth_secureotpdescription'] = 'बहु-स्तरीय सुरक्षा के साथ Moodle के लिए सरकारी-प्रमाणित OTP प्रमाणीकरण';

// Login page strings.
$string['login_title'] = 'OTP से लॉगिन करें';
$string['login_subtitle'] = 'OTP प्राप्त करने के लिए अपनी कर्मचारी ID या मोबाइल नंबर दर्ज करें';
$string['employee_id'] = 'कर्मचारी ID';
$string['mobile_number'] = 'मोबाइल नंबर';
$string['email_address'] = 'ईमेल पता';
$string['send_otp'] = 'OTP भेजें';
$string['identifier_placeholder'] = 'कर्मचारी ID / मोबाइल / ईमेल';
$string['identifier_required'] = 'कृपया अपनी कर्मचारी ID, मोबाइल नंबर या ईमेल दर्ज करें';

// OTP verification page strings.
$string['otp_title'] = 'OTP दर्ज करें';
$string['otp_subtitle'] = 'आपके मोबाइल पर भेजा गया 6 अंकों का OTP दर्ज करें';
$string['otp_code'] = 'OTP कोड';
$string['otp_placeholder'] = '6 अंकों का OTP दर्ज करें';
$string['verify_otp'] = 'OTP सत्यापित करें';
$string['resend_otp'] = 'OTP पुनः भेजें';
$string['otp_sent_to'] = '**{$a} पर समाप्त होने वाले मोबाइल पर OTP भेजा गया';
$string['otp_sent_to_email'] = '{$a} ईमेल पर OTP भेजा गया';
$string['otp_expires_in'] = 'OTP {$a} मिनट में समाप्त हो जाएगा';
$string['otp_timer'] = 'शेष समय: {$a}';

// Success messages.
$string['otp_sent_success'] = 'OTP सफलतापूर्वक भेजा गया';
$string['login_success'] = 'लॉगिन सफल। स्वागत है!';
$string['otp_verified'] = 'OTP सफलतापूर्वक सत्यापित';

// Error messages.
$string['error_invalid_identifier'] = 'अमान्य कर्मचारी ID, मोबाइल नंबर या ईमेल';
$string['error_user_not_found'] = 'सिस्टम में उपयोगकर्ता नहीं मिला';
$string['error_user_suspended'] = 'आपका खाता निलंबित कर दिया गया है। कृपया व्यवस्थापक से संपर्क करें।';
$string['error_user_archived'] = 'आपका खाता संग्रहीत कर दिया गया है। कृपया व्यवस्थापक से संपर्क करें।';
$string['error_account_locked'] = 'आपका खाता {$a} तक लॉक है। कृपया बाद में पुनः प्रयास करें।';
$string['error_invalid_otp'] = 'अमान्य OTP कोड। कृपया पुनः प्रयास करें।';
$string['error_otp_expired'] = 'OTP समाप्त हो गया है। कृपया नया अनुरोध करें।';
$string['error_otp_already_used'] = 'यह OTP पहले ही उपयोग किया जा चुका है। कृपया नया अनुरोध करें।';
$string['error_too_many_attempts'] = 'बहुत अधिक असफल प्रयास। खाता {$a} मिनट के लिए लॉक कर दिया गया।';
$string['error_rate_limit'] = 'बहुत अधिक अनुरोध। कृपया {$a} मिनट में पुनः प्रयास करें।';
$string['error_sms_failed'] = 'SMS भेजने में विफल। कृपया पुनः प्रयास करें या समर्थन से संपर्क करें।';
$string['error_email_failed'] = 'ईमेल भेजने में विफल। कृपया समर्थन से संपर्क करें।';
$string['error_no_mobile'] = 'इस खाते के लिए कोई मोबाइल नंबर नहीं मिला';
$string['error_no_email'] = 'इस खाते के लिए कोई ईमेल पता नहीं मिला';
$string['error_invalid_session'] = 'अमान्य या समाप्त सत्र। कृपया फिर से शुरू करें।';
$string['error_csrf_token'] = 'सुरक्षा टोकन बेमेल। कृपया पुनः प्रयास करें।';
$string['error_device_changed'] = 'डिवाइस परिवर्तन का पता चला। अतिरिक्त सत्यापन आवश्यक।';

// Admin settings strings.
$string['settings_header'] = 'सुरक्षित OTP सेटिंग्स';
$string['settings_general'] = 'सामान्य सेटिंग्स';
$string['settings_otp'] = 'OTP कॉन्फ़िगरेशन';
$string['settings_sms'] = 'SMS गेटवे सेटिंग्स';
$string['settings_security'] = 'सुरक्षा सेटिंग्स';
$string['settings_rate_limit'] = 'दर सीमा';

$string['otp_length'] = 'OTP लंबाई';
$string['otp_length_desc'] = 'OTP कोड में अंकों की संख्या (4-8)';
$string['otp_validity'] = 'OTP वैधता अवधि';
$string['otp_validity_desc'] = 'मिनटों में OTP वैधता (डिफ़ॉल्ट: 5)';
$string['otp_algorithm'] = 'OTP एल्गोरिदम';
$string['otp_algorithm_desc'] = 'OTP जनरेशन के लिए एल्गोरिदम';

$string['sms_provider'] = 'SMS प्रदाता';
$string['sms_provider_desc'] = 'SMS गेटवे प्रदाता चुनें';
$string['twilio_account_sid'] = 'Twilio खाता SID';
$string['twilio_account_sid_desc'] = 'आपका Twilio खाता SID';
$string['twilio_auth_token'] = 'Twilio प्रमाणीकरण टोकन';
$string['twilio_auth_token_desc'] = 'आपका Twilio प्रमाणीकरण टोकन';
$string['twilio_from_number'] = 'Twilio से नंबर';
$string['twilio_from_number_desc'] = 'आपका Twilio फोन नंबर (E.164 प्रारूप)';

$string['max_login_attempts'] = 'अधिकतम लॉगिन प्रयास';
$string['max_login_attempts_desc'] = 'खाता लॉक होने से पहले अधिकतम असफल लॉगिन प्रयास';
$string['lockout_duration'] = 'लॉकआउट अवधि';
$string['lockout_duration_desc'] = 'मिनटों में खाता लॉकआउट अवधि';
$string['rate_limit_otp'] = 'OTP अनुरोध दर सीमा';
$string['rate_limit_otp_desc'] = 'प्रति घंटे प्रति IP अधिकतम OTP अनुरोध';
$string['enable_device_fingerprint'] = 'डिवाइस फिंगरप्रिंटिंग सक्षम करें';
$string['enable_device_fingerprint_desc'] = 'डिवाइस फिंगरप्रिंट ट्रैक और सत्यापित करें';
$string['require_trusted_device'] = 'विश्वसनीय डिवाइस की आवश्यकता';
$string['require_trusted_device_desc'] = 'केवल विश्वसनीय उपकरणों से लॉगिन की अनुमति दें';

$string['redis_host'] = 'Redis होस्ट';
$string['redis_host_desc'] = 'Redis सर्वर होस्टनाम (डिफ़ॉल्ट: 127.0.0.1)';
$string['redis_port'] = 'Redis पोर्ट';
$string['redis_port_desc'] = 'Redis सर्वर पोर्ट (डिफ़ॉल्ट: 6379)';
$string['redis_password'] = 'Redis पासवर्ड';
$string['redis_password_desc'] = 'Redis प्रमाणीकरण पासवर्ड (यदि आवश्यक हो)';
$string['redis_db'] = 'Redis डेटाबेस';
$string['redis_db_desc'] = 'Redis डेटाबेस नंबर (डिफ़ॉल्ट: 0)';

// SMS templates.
$string['sms_otp_template'] = '{$a->sitename} के लिए आपका OTP: {$a->otp}। {$a->validity} मिनट के लिए मान्य। साझा न करें।';
$string['sms_otp_template_hi'] = '{$a->sitename} के लिए आपका OTP: {$a->otp}। {$a->validity} मिनट के लिए मान्य। साझा न करें।';
$string['sms_otp_template_te'] = 'మీ OTP {$a->sitename} కోసం: {$a->otp}. {$a->validity} నిమిషాలు చెల్లుతుంది. షేర్ చేయవద్దు.';

// Email templates.
$string['otp_email_subject'] = 'आपका OTP कोड - {$a}';
$string['otp_email_header'] = 'एक बार का पासवर्ड';
$string['otp_email_greeting'] = 'नमस्ते {$a->fullname},';
$string['otp_email_body'] = 'लॉगिन के लिए आपका OTP कोड है: {$a->otp}

यह कोड {$a->validity} मिनट के लिए मान्य है।

यदि आपने इस कोड का अनुरोध नहीं किया है, तो कृपया इस ईमेल को अनदेखा करें।

धन्यवाद,
{$a->sitename}';
$string['otp_email_code_label'] = 'आपका एक बार का पासवर्ड:';
$string['otp_email_validity'] = '{$a->validity} मिनट के लिए मान्य';
$string['otp_email_security_warning_title'] = 'सुरक्षा चेतावनी';
$string['otp_email_security_warning'] = 'इस OTP को किसी के साथ साझा न करें। हमारे कर्मचारी कभी भी आपका OTP नहीं मांगेंगे।';
$string['otp_email_footer_text'] = 'यदि आपने इस OTP का अनुरोध नहीं किया है, तो कृपया इस ईमेल को अनदेखा करें या यदि आपको चिंता है तो समर्थन से संपर्क करें।';
$string['otp_email_auto_message'] = 'यह एक स्वचालित संदेश है। कृपया जवाब न दें।';
$string['otp_email_support'] = 'सहायता चाहिए? हमसे संपर्क करें: {$a->supportemail}';

// Password reset.
$string['password_reset_subject'] = 'पासवर्ड रीसेट अनुरोध';
$string['password_reset_body'] = 'नमस्ते {$a->fullname},

आपके खाते के लिए पासवर्ड रीसेट का अनुरोध किया गया था।

अपना पासवर्ड रीसेट करने के लिए, कृपया यहां जाएं:
{$a->reset_url}

यदि आपने इसका अनुरोध नहीं किया है, तो कृपया इस ईमेल को अनदेखा करें।

धन्यवाद,
{$a->sitename}';

// Audit log event types.
$string['event_otp_sent'] = 'OTP भेजा गया';
$string['event_otp_verified'] = 'OTP सत्यापित';
$string['event_otp_failed'] = 'OTP सत्यापन विफल';
$string['event_login_success'] = 'लॉगिन सफल';
$string['event_login_failed'] = 'लॉगिन विफल';
$string['event_account_locked'] = 'खाता लॉक किया गया';
$string['event_account_unlocked'] = 'खाता अनलॉक किया गया';
$string['event_device_change'] = 'डिवाइस परिवर्तन का पता चला';
$string['event_rate_limit'] = 'दर सीमा पार हो गई';
$string['event_user_created'] = 'उपयोगकर्ता बनाया गया';
$string['event_user_updated'] = 'उपयोगकर्ता अपडेट किया गया';
$string['event_bulk_import'] = 'बल्क उपयोगकर्ता आयात';

// Admin dashboard.
$string['dashboard_title'] = 'सुरक्षा डैशबोर्ड';
$string['dashboard_total_users'] = 'कुल उपयोगकर्ता';
$string['dashboard_active_users'] = 'सक्रिय उपयोगकर्ता';
$string['dashboard_logins_today'] = 'आज लॉगिन';
$string['dashboard_failed_attempts'] = 'आज असफल प्रयास';
$string['dashboard_locked_accounts'] = 'लॉक खाते';
$string['dashboard_success_rate'] = 'लॉगिन सफलता दर';
$string['dashboard_recent_logins'] = 'हाल के लॉगिन';
$string['dashboard_security_alerts'] = 'सुरक्षा अलर्ट';
$string['dashboard_quick_actions'] = 'त्वरित क्रियाएं';

// User management.
$string['user_search'] = 'उपयोगकर्ता खोजें';
$string['user_status'] = 'स्थिति';
$string['user_employee_id'] = 'कर्मचारी ID';
$string['user_last_login'] = 'अंतिम लॉगिन';
$string['user_login_count'] = 'लॉगिन गिनती';
$string['user_actions'] = 'क्रियाएं';
$string['action_unlock'] = 'खाता अनलॉक करें';
$string['action_suspend'] = 'निलंबित करें';
$string['action_activate'] = 'सक्रिय करें';
$string['action_view_audit'] = 'ऑडिट लॉग देखें';
$string['action_send_test_otp'] = 'परीक्षण OTP भेजें';

// Bulk import.
$string['import_title'] = 'बल्क उपयोगकर्ता आयात';
$string['import_upload_file'] = 'CSV फ़ाइल अपलोड करें';
$string['import_source_system'] = 'स्रोत प्रणाली';
$string['import_dry_run'] = 'ड्राई रन (केवल सत्यापित करें)';
$string['import_start'] = 'आयात प्रारंभ करें';
$string['import_batch_id'] = 'बैच ID';
$string['import_total_records'] = 'कुल रिकॉर्ड';
$string['import_created'] = 'बनाया गया';
$string['import_updated'] = 'अपडेट किया गया';
$string['import_failed'] = 'विफल';
$string['import_duration'] = 'अवधि';
$string['import_status'] = 'स्थिति';
$string['import_view_log'] = 'आयात लॉग देखें';
$string['import_download_errors'] = 'त्रुटियां डाउनलोड करें';

// Status values.
$string['status_provisioned'] = 'प्रावधानित';
$string['status_active'] = 'सक्रिय';
$string['status_suspended'] = 'निलंबित';
$string['status_archived'] = 'संग्रहीत';

// Capabilities.
$string['secureotp:manage'] = 'OTP प्रमाणीकरण सेटिंग्स प्रबंधित करें';
$string['secureotp:viewaudit'] = 'ऑडिट लॉग देखें';
$string['secureotp:manageusers'] = 'उपयोगकर्ता सुरक्षा स्थिति प्रबंधित करें';
$string['secureotp:bulkimport'] = 'बल्क उपयोगकर्ता आयात करें';
$string['secureotp:viewreports'] = 'सुरक्षा रिपोर्ट देखें';

// Privacy API.
$string['privacy:metadata:auth_secureotp_userdata'] = 'विस्तारित उपयोगकर्ता प्रोफ़ाइल डेटा';
$string['privacy:metadata:auth_secureotp_security'] = 'उपयोगकर्ता प्रमाणीकरण और सुरक्षा मेटाडेटा';
$string['privacy:metadata:auth_secureotp_audit'] = 'प्रमाणीकरण घटनाओं का अपरिवर्तनीय ऑडिट ट्रेल';
$string['privacy:metadata:userid'] = 'उपयोगकर्ता ID';
$string['privacy:metadata:employee_id'] = 'कर्मचारी ID';
$string['privacy:metadata:personal_mobile'] = 'व्यक्तिगत मोबाइल नंबर';
$string['privacy:metadata:ip_address'] = 'IP पता';
$string['privacy:metadata:device_fingerprint'] = 'डिवाइस फ़िंगरप्रिंट हैश';
$string['privacy:metadata:login_count'] = 'सफल लॉगिन की संख्या';
$string['privacy:metadata:last_login_at'] = 'अंतिम लॉगिन टाइमस्टैम्प';

// Miscellaneous.
$string['back_to_login'] = 'लॉगिन पर वापस जाएं';
$string['change_identifier'] = 'अलग ID उपयोग करें';
$string['trust_this_device'] = 'इस डिवाइस पर भरोसा करें (30 दिन)';
$string['logout'] = 'लॉगआउट';
$string['contact_support'] = 'समर्थन से संपर्क करें';

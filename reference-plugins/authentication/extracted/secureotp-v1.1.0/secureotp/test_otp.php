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
 * Admin page for testing OTP delivery via SMS and Email.
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require site admin.
require_login();
$context = context_system::instance();
require_capability('auth/secureotp:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/auth/secureotp/test_otp.php'));
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('test_otp_title', 'auth_secureotp'));
$PAGE->set_heading(get_string('test_otp_title', 'auth_secureotp'));

// Navigation.
$PAGE->navbar->add(get_string('pluginname', 'auth_secureotp'),
    new moodle_url('/admin/settings.php', array('section' => 'authsettingsecureotp')));
$PAGE->navbar->add(get_string('test_otp_title', 'auth_secureotp'));

$config = get_config('auth_secureotp');
$results = array();

// Handle form submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    $action = required_param('action', PARAM_ALPHA);

    if ($action === 'testsms') {
        $mobile = required_param('test_mobile', PARAM_RAW);
        // Sanitize - digits and + only.
        $mobile = preg_replace('/[^0-9+]/', '', trim($mobile));

        if (empty($mobile) || strlen($mobile) < 10) {
            $results[] = array('type' => 'danger', 'title' => 'SMS Test Failed',
                'message' => 'Please enter a valid mobile number (at least 10 digits).');
        } else {
            // Build gateway config from plugin settings.
            $account_sid = !empty($config->twilio_account_sid) ? trim($config->twilio_account_sid) : '';
            $auth_token = !empty($config->twilio_auth_token) ? trim($config->twilio_auth_token) : '';
            $from_number = !empty($config->twilio_from_number) ? trim($config->twilio_from_number) : '';

            if (empty($account_sid) || empty($auth_token) || empty($from_number)) {
                $missing = array();
                if (empty($account_sid)) {
                    $missing[] = 'Account SID';
                }
                if (empty($auth_token)) {
                    $missing[] = 'Auth Token';
                }
                if (empty($from_number)) {
                    $missing[] = 'From Number';
                }
                $results[] = array('type' => 'danger', 'title' => 'SMS Configuration Missing',
                    'message' => 'The following Twilio settings are not configured: ' . implode(', ', $missing) .
                    '. Please configure them in the plugin settings before testing.');
            } else {
                // Ensure from_number has + prefix.
                if (substr($from_number, 0, 1) !== '+') {
                    $from_number = '+' . $from_number;
                }

                $gateway_config = array(
                    'account_sid' => $account_sid,
                    'auth_token' => $auth_token,
                    'from_number' => $from_number
                );

                // Generate a test OTP.
                $test_otp = '';
                $otp_length = !empty($config->otp_length) ? (int)$config->otp_length : 6;
                for ($i = 0; $i < $otp_length; $i++) {
                    $test_otp .= random_int(0, 9);
                }

                $site = get_site();
                $message = 'TEST OTP for ' . format_string($site->fullname) . ': ' . $test_otp .
                    '. This is a test message from admin. Do not share.';

                require_once(__DIR__ . '/classes/messaging/sms_gateway.php');
                require_once(__DIR__ . '/classes/messaging/twilio_gateway.php');
                $gateway = new \auth_secureotp\messaging\twilio_gateway($gateway_config);

                $result = $gateway->send_sms($mobile, $message);

                if ($result['success']) {
                    $results[] = array('type' => 'success', 'title' => 'SMS Sent Successfully!',
                        'message' => 'Test OTP <strong>' . $test_otp . '</strong> sent to <strong>' . $mobile .
                        '</strong>.<br>Message ID: ' . $result['message_id'] .
                        '<br><br>Please check the phone for the SMS.');
                } else {
                    $results[] = array('type' => 'danger', 'title' => 'SMS Send Failed',
                        'message' => 'Error: <strong>' . s($result['error']) . '</strong>' .
                        '<br><br><strong>Troubleshooting:</strong><ul>' .
                        '<li>Verify Twilio Account SID and Auth Token are correct</li>' .
                        '<li>Ensure the From Number is a valid Twilio phone number with SMS capability</li>' .
                        '<li>Check if India is enabled in Twilio Geo Permissions</li>' .
                        '<li>For trial accounts, verify the recipient number is verified in Twilio</li>' .
                        '<li>Ensure the server can reach api.twilio.com (no firewall blocking)</li></ul>');
                }
            }
        }
    } else if ($action === 'testemail') {
        $email = trim(required_param('test_email', PARAM_RAW));

        if (empty($email) || !validate_email($email)) {
            $results[] = array('type' => 'danger', 'title' => 'Email Test Failed',
                'message' => 'Please enter a valid email address.');
        } else {
            // Generate a test OTP.
            $otp_length = !empty($config->otp_length) ? (int)$config->otp_length : 6;
            $test_otp = '';
            for ($i = 0; $i < $otp_length; $i++) {
                $test_otp .= random_int(0, 9);
            }

            // Create a fake user object for the email gateway.
            $testuser = new stdClass();
            $testuser->id = $USER->id;
            $testuser->email = $email;
            $testuser->firstname = $USER->firstname;
            $testuser->lastname = $USER->lastname;
            $testuser->mailformat = 1; // HTML.
            $testuser->auth = 'secureotp';

            require_once(__DIR__ . '/classes/messaging/email_gateway.php');
            $email_gateway = new \auth_secureotp\messaging\email_gateway();
            $result = $email_gateway->send_otp_email($testuser, $test_otp);

            if ($result['success']) {
                $results[] = array('type' => 'success', 'title' => 'Email Sent Successfully!',
                    'message' => 'Test OTP <strong>' . $test_otp . '</strong> sent to <strong>' .
                    s($email) . '</strong>.<br><br>Please check the inbox (and spam folder) for the OTP email.');
            } else {
                $error_detail = isset($result['error']) ? $result['error'] : 'Unknown error';
                $results[] = array('type' => 'danger', 'title' => 'Email Send Failed',
                    'message' => 'Error: <strong>' . s($error_detail) . '</strong>' .
                    '<br><br><strong>Troubleshooting:</strong><ul>' .
                    '<li>Check Moodle email settings (Site admin > Server > Outgoing mail)</li>' .
                    '<li>Verify SMTP server is configured and reachable</li>' .
                    '<li>Check Moodle cron is running for queued emails</li>' .
                    '<li>Review Moodle logs for email sending errors</li></ul>');
            }
        }
    } else if ($action === 'testtwilio') {
        // Test Twilio connection (check balance).
        $account_sid = !empty($config->twilio_account_sid) ? trim($config->twilio_account_sid) : '';
        $auth_token = !empty($config->twilio_auth_token) ? trim($config->twilio_auth_token) : '';
        $from_number = !empty($config->twilio_from_number) ? trim($config->twilio_from_number) : '';

        if (empty($account_sid) || empty($auth_token)) {
            $results[] = array('type' => 'danger', 'title' => 'Connection Test Failed',
                'message' => 'Twilio Account SID and Auth Token must be configured first.');
        } else {
            $gateway_config = array(
                'account_sid' => $account_sid,
                'auth_token' => $auth_token,
                'from_number' => $from_number
            );

            require_once(__DIR__ . '/classes/messaging/sms_gateway.php');
            require_once(__DIR__ . '/classes/messaging/twilio_gateway.php');
            $gateway = new \auth_secureotp\messaging\twilio_gateway($gateway_config);
            $result = $gateway->test_connection();

            if ($result['success']) {
                $results[] = array('type' => 'success', 'title' => 'Twilio Connection Successful!',
                    'message' => s($result['message']) .
                    '<br>From Number: <strong>' . s($from_number) . '</strong>' .
                    (empty($from_number) ? '<br><span class="text-warning">Warning: From Number is not set!</span>' : ''));
            } else {
                $results[] = array('type' => 'danger', 'title' => 'Twilio Connection Failed',
                    'message' => 'Error: <strong>' . s($result['message']) . '</strong>' .
                    '<br><br>Please verify your Account SID and Auth Token are correct.');
            }
        }
    } else if ($action === 'testredis') {
        // Test Redis connection.
        if (!class_exists('Redis')) {
            $results[] = array('type' => 'warning', 'title' => 'Redis Not Available',
                'message' => 'PHP Redis extension is not installed. The plugin will use database storage instead. ' .
                'Redis is optional but recommended for high-traffic deployments.');
        } else {
            $redis_host = !empty($config->redis_host) ? $config->redis_host : '127.0.0.1';
            $redis_port = !empty($config->redis_port) ? (int)$config->redis_port : 6379;

            try {
                $redis = new Redis();
                $connected = @$redis->connect($redis_host, $redis_port, 2.0);

                if (!$connected) {
                    $results[] = array('type' => 'danger', 'title' => 'Redis Connection Failed',
                        'message' => 'Cannot connect to Redis at <strong>' . s($redis_host) . ':' . $redis_port .
                        '</strong>.<br>The plugin will use database fallback (this is OK for moderate traffic).');
                } else {
                    if (!empty($config->redis_password)) {
                        $redis->auth($config->redis_password);
                    }
                    $redis->select(!empty($config->redis_db) ? (int)$config->redis_db : 0);

                    // Test read/write.
                    $test_key = 'auth_secureotp:test:' . time();
                    $redis->setex($test_key, 10, 'test_value');
                    $value = $redis->get($test_key);
                    $redis->del($test_key);

                    if ($value === 'test_value') {
                        $info = $redis->info('server');
                        $version = isset($info['redis_version']) ? $info['redis_version'] : 'unknown';
                        $results[] = array('type' => 'success', 'title' => 'Redis Connection Successful!',
                            'message' => 'Connected to Redis at <strong>' . s($redis_host) . ':' . $redis_port .
                            '</strong><br>Redis version: ' . s($version) . '<br>Read/Write test: passed');
                    } else {
                        $results[] = array('type' => 'warning', 'title' => 'Redis Partial Success',
                            'message' => 'Connected to Redis but read/write test failed.');
                    }
                }
            } catch (Exception $e) {
                $results[] = array('type' => 'danger', 'title' => 'Redis Connection Error',
                    'message' => 'Error: <strong>' . s($e->getMessage()) . '</strong>' .
                    '<br>The plugin will use database fallback.');
            }
        }
    }
}

// Check current configuration status.
$sms_configured = !empty($config->twilio_account_sid) && !empty($config->twilio_auth_token) && !empty($config->twilio_from_number);
$redis_available = class_exists('Redis');

echo $OUTPUT->header();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2><?php echo get_string('test_otp_title', 'auth_secureotp'); ?></h2>
            <p class="text-muted"><?php echo get_string('test_otp_desc', 'auth_secureotp'); ?></p>
            <a href="<?php echo new moodle_url('/admin/settings.php', array('section' => 'authsettingsecureotp')); ?>"
               class="btn btn-outline-secondary btn-sm mb-3">
                <i class="fa fa-arrow-left"></i> <?php echo get_string('test_otp_back_settings', 'auth_secureotp'); ?>
            </a>
        </div>
    </div>

    <?php foreach ($results as $r): ?>
    <div class="alert alert-<?php echo $r['type']; ?> alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><?php echo $r['title']; ?></h5>
        <p><?php echo $r['message']; ?></p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php endforeach; ?>

    <!-- Configuration Status -->
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0"><i class="fa fa-info-circle"></i> <?php echo get_string('test_otp_config_status', 'auth_secureotp'); ?></h5></div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr>
                    <td><strong>Twilio SMS Gateway</strong></td>
                    <td>
                        <?php if ($sms_configured): ?>
                            <span class="badge badge-success">Configured</span>
                            <small class="text-muted ml-2">SID: <?php echo s(substr($config->twilio_account_sid, 0, 8)); ?>...
                            | From: <?php echo s($config->twilio_from_number); ?></small>
                        <?php else: ?>
                            <span class="badge badge-danger">Not Configured</span>
                            <small class="text-muted ml-2">Set credentials in
                                <a href="<?php echo new moodle_url('/admin/settings.php', array('section' => 'authsettingsecureotp')); ?>">plugin settings</a></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Redis Cache</strong></td>
                    <td>
                        <?php if (!$redis_available): ?>
                            <span class="badge badge-warning">PHP Extension Not Installed</span>
                            <small class="text-muted ml-2">Using database fallback (OK for moderate traffic)</small>
                        <?php else: ?>
                            <span class="badge badge-info">Extension Available</span>
                            <small class="text-muted ml-2">Host: <?php echo s($config->redis_host ?? '127.0.0.1'); ?>:<?php echo s($config->redis_port ?? '6379'); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>OTP Settings</strong></td>
                    <td>
                        <span class="badge badge-info">Length: <?php echo (int)($config->otp_length ?? 6); ?> digits</span>
                        <span class="badge badge-info ml-1">Validity: <?php echo (int)($config->otp_validity ?? 5); ?> minutes</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Moodle Email</strong></td>
                    <td>
                        <?php
                        $smtphost = get_config('', 'smtphosts');
                        if (!empty($smtphost)): ?>
                            <span class="badge badge-success">SMTP Configured</span>
                            <small class="text-muted ml-2"><?php echo s($smtphost); ?></small>
                        <?php else: ?>
                            <span class="badge badge-info">Using PHP mail()</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        <!-- Test Twilio Connection -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fa fa-plug"></i> <?php echo get_string('test_otp_twilio_conn', 'auth_secureotp'); ?></h5></div>
                <div class="card-body">
                    <p><?php echo get_string('test_otp_twilio_conn_desc', 'auth_secureotp'); ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                        <input type="hidden" name="action" value="testtwilio">
                        <button type="submit" class="btn btn-info" <?php echo $sms_configured ? '' : 'disabled'; ?>>
                            <i class="fa fa-plug"></i> <?php echo get_string('test_otp_twilio_conn_btn', 'auth_secureotp'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Test Redis -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-secondary text-white"><h5 class="mb-0"><i class="fa fa-database"></i> <?php echo get_string('test_otp_redis_conn', 'auth_secureotp'); ?></h5></div>
                <div class="card-body">
                    <p><?php echo get_string('test_otp_redis_conn_desc', 'auth_secureotp'); ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                        <input type="hidden" name="action" value="testredis">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fa fa-database"></i> <?php echo get_string('test_otp_redis_conn_btn', 'auth_secureotp'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Test SMS -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fa fa-mobile"></i> <?php echo get_string('test_otp_sms', 'auth_secureotp'); ?></h5></div>
                <div class="card-body">
                    <p><?php echo get_string('test_otp_sms_desc', 'auth_secureotp'); ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                        <input type="hidden" name="action" value="testsms">
                        <div class="form-group">
                            <label for="test_mobile"><?php echo get_string('test_otp_mobile_label', 'auth_secureotp'); ?></label>
                            <input type="text" class="form-control" id="test_mobile" name="test_mobile"
                                   placeholder="+919876543210" required
                                   value="<?php echo s(optional_param('test_mobile', '', PARAM_RAW)); ?>">
                            <small class="form-text text-muted"><?php echo get_string('test_otp_mobile_help', 'auth_secureotp'); ?></small>
                        </div>
                        <button type="submit" class="btn btn-primary" <?php echo $sms_configured ? '' : 'disabled'; ?>>
                            <i class="fa fa-paper-plane"></i> <?php echo get_string('test_otp_send_sms', 'auth_secureotp'); ?>
                        </button>
                        <?php if (!$sms_configured): ?>
                            <small class="text-danger d-block mt-2"><?php echo get_string('test_otp_sms_not_configured', 'auth_secureotp'); ?></small>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Test Email -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fa fa-envelope"></i> <?php echo get_string('test_otp_email', 'auth_secureotp'); ?></h5></div>
                <div class="card-body">
                    <p><?php echo get_string('test_otp_email_desc', 'auth_secureotp'); ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
                        <input type="hidden" name="action" value="testemail">
                        <div class="form-group">
                            <label for="test_email"><?php echo get_string('test_otp_email_label', 'auth_secureotp'); ?></label>
                            <input type="email" class="form-control" id="test_email" name="test_email"
                                   placeholder="admin@example.com" required
                                   value="<?php echo s(optional_param('test_email', $USER->email, PARAM_RAW)); ?>">
                            <small class="form-text text-muted"><?php echo get_string('test_otp_email_help', 'auth_secureotp'); ?></small>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-paper-plane"></i> <?php echo get_string('test_otp_send_email', 'auth_secureotp'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();

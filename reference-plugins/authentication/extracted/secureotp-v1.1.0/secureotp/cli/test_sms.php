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
 * CLI script to test SMS gateway
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once(__DIR__ . '/../classes/messaging/sms_gateway.php');
require_once(__DIR__ . '/../classes/messaging/twilio_gateway.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'mobile' => '',
        'message' => 'Test message from SecureOTP',
        'check-balance' => false
    ),
    array('h' => 'help', 'm' => 'mobile', 'msg' => 'message', 'b' => 'check-balance')
);

if ($options['help']) {
    echo "Test SMS Gateway (Twilio)\n\n";
    echo "Usage:\n";
    echo "  php test_sms.php --mobile=9876543210 [--message=\"Test message\"]\n";
    echo "  php test_sms.php --check-balance\n\n";
    echo "Options:\n";
    echo "  -m, --mobile=<number>     Mobile number to send test SMS\n";
    echo "  --message=<text>          Custom message (default: \"Test message from SecureOTP\")\n";
    echo "  -b, --check-balance       Check Twilio account balance\n";
    exit(0);
}

cli_heading('SMS Gateway Test');

$config = get_config('auth_secureotp');

$gateway_config = array(
    'account_sid' => $config->twilio_account_sid ?? '',
    'auth_token' => $config->twilio_auth_token ?? '',
    'from_number' => $config->twilio_from_number ?? ''
);

$gateway = new \auth_secureotp\messaging\twilio_gateway($gateway_config);

// Check balance.
if ($options['check-balance']) {
    echo "Checking Twilio account balance...\n\n";

    $result = $gateway->check_balance();

    if ($result['success']) {
        echo "✓ Balance check successful\n";
        echo "  Balance: {$result['balance']} {$result['currency']}\n";
    } else {
        cli_error("✗ Balance check failed: " . $result['error']);
    }

    exit(0);
}

// Send test SMS.
if (empty($options['mobile'])) {
    cli_error("Error: --mobile parameter is required. Use --help for usage.");
}

$mobile = $options['mobile'];
$message = $options['message'];

echo "Testing SMS delivery...\n";
echo "Mobile: $mobile\n";
echo "Message: $message\n\n";

$result = $gateway->send_sms($mobile, $message);

if ($result['success']) {
    echo "✓ SMS sent successfully!\n";
    echo "  Message ID: {$result['message_id']}\n\n";

    // Check delivery status.
    echo "Checking delivery status...\n";
    sleep(2);

    $status_result = $gateway->get_delivery_status($result['message_id']);

    if ($status_result['success']) {
        echo "  Status: {$status_result['status']}\n";
        if ($status_result['delivered_at']) {
            echo "  Delivered at: " . userdate($status_result['delivered_at']) . "\n";
        }
    }
} else {
    cli_error("✗ SMS send failed: " . $result['error']);
}

echo "\nTest completed successfully!\n";

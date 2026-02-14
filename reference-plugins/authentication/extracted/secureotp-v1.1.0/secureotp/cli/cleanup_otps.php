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
 * CLI script to cleanup expired OTPs
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

list($options, $unrecognized) = cli_get_params(
    array('help' => false),
    array('h' => 'help')
);

if ($options['help']) {
    echo "Cleanup expired OTPs from Redis and database\n\n";
    echo "Usage: php cleanup_otps.php\n";
    echo "\nThis script should be run via cron every hour.\n";
    exit(0);
}

cli_heading('OTP Cleanup');

$cleaned = 0;

// Clean up Redis OTPs.
if (class_exists('Redis')) {
    try {
        $config = get_config('auth_secureotp');
        $redis = new Redis();
        $redis->connect(
            $config->redis_host ?: '127.0.0.1',
            $config->redis_port ?: 6379
        );

        if (!empty($config->redis_password)) {
            $redis->auth($config->redis_password);
        }

        $redis->select($config->redis_db ?: 0);

        // Get all OTP keys.
        $keys = $redis->keys('auth_secureotp:otp:*');

        foreach ($keys as $key) {
            $ttl = $redis->ttl($key);
            if ($ttl < 0) {
                $redis->del($key);
                $cleaned++;
            }
        }

        echo "Cleaned $cleaned expired OTPs from Redis\n";
    } catch (Exception $e) {
        echo "Redis cleanup failed: " . $e->getMessage() . "\n";
    }
}

// Clean up database fallback OTPs.
$now = time();
$DB->delete_records_select('auth_secureotp_security', 'otp_expires_at < ? AND otp_expires_at IS NOT NULL', array($now));

echo "Cleanup completed\n";

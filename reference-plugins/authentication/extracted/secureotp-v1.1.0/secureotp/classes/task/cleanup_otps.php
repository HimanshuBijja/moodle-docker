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
 * Scheduled task to cleanup expired OTPs
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Cleanup expired OTPs task
 */
class cleanup_otps extends \core\task\scheduled_task {

    /**
     * Get task name
     *
     * @return string Task name
     */
    public function get_name() {
        return get_string('task_cleanup_otps', 'auth_secureotp');
    }

    /**
     * Execute the task
     */
    public function execute() {
        global $DB;

        mtrace('Starting OTP cleanup...');

        $cleaned = 0;

        // Clean up Redis OTPs.
        if (class_exists('Redis')) {
            try {
                $config = get_config('auth_secureotp');
                $redis = new \Redis();

                $connected = $redis->connect(
                    $config->redis_host ?: '127.0.0.1',
                    $config->redis_port ?: 6379,
                    2.0
                );

                if ($connected) {
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

                    mtrace("Cleaned $cleaned expired OTPs from Redis");
                }
            } catch (\Exception $e) {
                mtrace('Redis cleanup error: ' . $e->getMessage());
            }
        }

        // Clean up database fallback OTPs.
        $now = time();
        $deleted = $DB->delete_records_select(
            'auth_secureotp_security',
            'otp_expires_at < ? AND otp_expires_at IS NOT NULL',
            array($now)
        );

        if ($deleted) {
            mtrace("Cleaned $deleted expired OTPs from database");
        }

        mtrace('OTP cleanup completed');
    }
}

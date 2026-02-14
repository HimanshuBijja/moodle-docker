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
 * OTP Manager for SecureOTP authentication plugin.
 *
 * @package   auth_secureotp
 * @copyright 2023 Your Name <your.email@organization.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\auth;

defined('MOODLE_INTERNAL') || die();

/**
 * Class otp_manager
 */
class otp_manager {
    
    /**
     * Generate a new OTP for the given user
     *
     * @param int $userid The user ID
     * @param string $deliverymethod The delivery method ('sms', 'email', 'totp')
     * @return array Array with 'success', 'otp', 'expires_at'
     */
    public function generate_otp($userid, $deliverymethod = 'sms') {
        global $DB;

        // Generate a random OTP based on configuration
        $otpconfig = get_config('auth_secureotp');
        $length = !empty($otpconfig->otp_length) ? (int)$otpconfig->otp_length : 6;

        // Generate random OTP
        $otp = $this->generate_random_otp($length);

        // Calculate expiry time
        $expiryminutes = !empty($otpconfig->otp_validity) ? (int)$otpconfig->otp_validity : 5;
        $expirytime = time() + ($expiryminutes * 60);

        // Store OTP in Redis (primary) or database (fallback)
        if (class_exists('Redis')) {
            $stored = $this->store_otp_redis($userid, $otp, $expirytime);
        } else {
            $stored = $this->store_otp_db($userid, $otp, $expirytime);
        }

        if (!$stored) {
            // Fallback to database if Redis fails
            $stored = $this->store_otp_db($userid, $otp, $expirytime);
        }

        return array(
            'success' => $stored,
            'otp' => $otp,
            'expires_at' => $expirytime
        );
    }

    /**
     * Store OTP in Redis
     *
     * @param int $userid User ID
     * @param string $otp OTP code
     * @param int $expirytime Expiry timestamp
     * @return bool Success
     */
    private function store_otp_redis($userid, $otp, $expirytime) {
        try {
            $config = get_config('auth_secureotp');
            $redis = new \Redis();

            $redis_host = !empty($config->redis_host) ? $config->redis_host : '127.0.0.1';
            $redis_port = !empty($config->redis_port) ? (int)$config->redis_port : 6379;

            // Short timeout to avoid blocking when Redis is down.
            $connected = @$redis->connect($redis_host, $redis_port, 0.5);

            if (!$connected) {
                return false;
            }

            if (!empty($config->redis_password)) {
                $redis->auth($config->redis_password);
            }

            $redis->select(!empty($config->redis_db) ? (int)$config->redis_db : 0);

            // Store OTP with auto-expiry.
            $key = "auth_secureotp:otp:{$userid}";
            $ttl = $expirytime - time();
            $redis->setex($key, $ttl, $otp);

            return true;

        } catch (\Exception $e) {
            // Redis unavailable - caller will use DB fallback.
            return false;
        }
    }

    /**
     * Store OTP in database (fallback)
     *
     * @param int $userid User ID
     * @param string $otp OTP code
     * @param int $expirytime Expiry timestamp
     * @return bool Success
     */
    private function store_otp_db($userid, $otp, $expirytime) {
        global $DB;

        try {
            // Store in auth_secureotp_security table
            $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));

            if ($security) {
                // Update existing record
                $DB->set_field('auth_secureotp_security', 'current_otp_hash', password_hash($otp, PASSWORD_DEFAULT), array('id' => $security->id));
                $DB->set_field('auth_secureotp_security', 'otp_expires_at', $expirytime, array('id' => $security->id));
            } else {
                // Create new record
                $record = new \stdClass();
                $record->userid = $userid;
                $record->status = 'PROVISIONED';
                $record->current_otp_hash = password_hash($otp, PASSWORD_DEFAULT);
                $record->otp_expires_at = $expirytime;
                $record->otp_enabled = 1;
                $record->password_enabled = 0;
                $record->is_locked = 0;
                $record->failed_attempts = 0;
                $record->login_count = 0;
                $record->timecreated = time();
                $record->timemodified = time();

                $DB->insert_record('auth_secureotp_security', $record);
            }

            return true;

        } catch (\Exception $e) {
            debugging('Database OTP storage failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }
    
    /**
     * Generate a random OTP string of specified length
     *
     * @param int $length The length of the OTP
     * @return string The random OTP
     */
    private function generate_random_otp($length) {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }
    
    /**
     * Validate the OTP for the given user
     *
     * @param int $userid The user ID
     * @param string $otp The OTP to validate
     * @return array Array with 'success', 'error', 'error_code'
     */
    public function validate_otp($userid, $otp) {
        // Try Redis first
        if (class_exists('Redis')) {
            $result = $this->validate_otp_redis($userid, $otp);
            if ($result['success'] || $result['error_code'] !== 'REDIS_UNAVAILABLE') {
                return $result;
            }
        }

        // Fallback to database
        return $this->validate_otp_db($userid, $otp);
    }

    /**
     * Validate OTP from Redis
     *
     * @param int $userid User ID
     * @param string $otp OTP code
     * @return array Result
     */
    private function validate_otp_redis($userid, $otp) {
        try {
            $config = get_config('auth_secureotp');
            $redis = new \Redis();

            $redis_host = !empty($config->redis_host) ? $config->redis_host : '127.0.0.1';
            $redis_port = !empty($config->redis_port) ? (int)$config->redis_port : 6379;

            $connected = @$redis->connect($redis_host, $redis_port, 0.5);

            if (!$connected) {
                return array('success' => false, 'error' => 'Redis unavailable', 'error_code' => 'REDIS_UNAVAILABLE');
            }

            if (!empty($config->redis_password)) {
                $redis->auth($config->redis_password);
            }

            $redis->select(!empty($config->redis_db) ? (int)$config->redis_db : 0);

            // Get stored OTP.
            $key = "auth_secureotp:otp:{$userid}";
            $stored_otp = $redis->get($key);

            if ($stored_otp === false) {
                return array('success' => false, 'error' => 'OTP not found or expired', 'error_code' => 'OTP_NOT_FOUND');
            }

            if ($stored_otp !== $otp) {
                return array('success' => false, 'error' => 'Invalid OTP', 'error_code' => 'INVALID_OTP');
            }

            // Delete OTP after successful validation (single use).
            $redis->del($key);

            return array('success' => true);

        } catch (\Exception $e) {
            // Redis unavailable - caller will use DB fallback.
            return array('success' => false, 'error' => 'Redis unavailable', 'error_code' => 'REDIS_UNAVAILABLE');
        }
    }

    /**
     * Validate OTP from database
     *
     * @param int $userid User ID
     * @param string $otp OTP code
     * @return array Result
     */
    private function validate_otp_db($userid, $otp) {
        global $DB;

        try {
            $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));

            if (!$security) {
                return array('success' => false, 'error' => 'User not found', 'error_code' => 'USER_NOT_FOUND');
            }

            if (empty($security->current_otp_hash)) {
                return array('success' => false, 'error' => 'No OTP generated', 'error_code' => 'OTP_NOT_FOUND');
            }

            // Check expiry
            if ($security->otp_expires_at && $security->otp_expires_at < time()) {
                // Clear expired OTP
                $DB->set_field('auth_secureotp_security', 'current_otp_hash', null, array('id' => $security->id));
                $DB->set_field('auth_secureotp_security', 'otp_expires_at', null, array('id' => $security->id));

                return array('success' => false, 'error' => 'OTP expired', 'error_code' => 'OTP_EXPIRED');
            }

            // Verify OTP
            if (!password_verify($otp, $security->current_otp_hash)) {
                return array('success' => false, 'error' => 'Invalid OTP', 'error_code' => 'INVALID_OTP');
            }

            // Clear OTP after successful validation (single use)
            $DB->set_field('auth_secureotp_security', 'current_otp_hash', null, array('id' => $security->id));
            $DB->set_field('auth_secureotp_security', 'otp_expires_at', null, array('id' => $security->id));

            return array('success' => true);

        } catch (\Exception $e) {
            debugging('Database OTP validation failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return array('success' => false, 'error' => 'Database error', 'error_code' => 'DB_ERROR');
        }
    }

    /**
     * Clean up expired OTPs from the database
     */
    public function cleanup_expired_otps() {
        global $DB;

        // Clean up expired OTPs from security table
        $now = time();
        $DB->execute(
            "UPDATE {auth_secureotp_security} SET current_otp_hash = NULL, otp_expires_at = NULL WHERE otp_expires_at < ?",
            array($now)
        );

        // Clean up from Redis if available
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

                    // Get all OTP keys and check expiry
                    $keys = $redis->keys('auth_secureotp:otp:*');
                    foreach ($keys as $key) {
                        if ($redis->ttl($key) < 0) {
                            $redis->del($key);
                        }
                    }
                }
            } catch (\Exception $e) {
                debugging('Redis cleanup failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
    }

    /**
     * Check how many unused OTPs a user has
     *
     * @param int $userid The user ID
     * @return int Number of unused OTPs (0 or 1)
     */
    public function count_unused_otps($userid) {
        global $DB;

        // Check database
        $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));

        if ($security && !empty($security->current_otp_hash) && $security->otp_expires_at > time()) {
            return 1;
        }

        // Check Redis
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

                    $key = "auth_secureotp:otp:{$userid}";
                    if ($redis->exists($key)) {
                        return 1;
                    }
                }
            } catch (\Exception $e) {
                // Ignore Redis errors
            }
        }

        return 0;
    }
}
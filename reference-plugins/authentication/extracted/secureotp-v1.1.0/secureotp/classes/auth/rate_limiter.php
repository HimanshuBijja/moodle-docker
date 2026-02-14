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
 * Rate Limiter for SecureOTP authentication plugin.
 *
 * @package   auth_secureotp
 * @copyright 2023 Your Name <your.email@organization.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\auth;

defined('MOODLE_INTERNAL') || die();

/**
 * Class rate_limiter
 */
class rate_limiter {
    
    /**
     * Check if a rate limit has been exceeded
     *
     * @param string $limit_type Type of limit (OTP_REQUEST, OTP_VERIFY, etc.)
     * @param string $identifier Identifier (IP address, employee_id, etc.)
     * @return bool True if allowed (not rate limited), false if exceeded
     */
    public function check_rate_limit($limit_type, $identifier) {
        // Try Redis first, fall back to database silently.
        if (class_exists('Redis')) {
            $result = $this->check_rate_limit_redis($limit_type, $identifier);
            if ($result !== null) {
                return $result;
            }
        }

        return $this->check_rate_limit_db($limit_type, $identifier);
    }

    /**
     * Check rate limit using Redis
     *
     * @param string $limit_type Type of limit
     * @param string $identifier Identifier
     * @return bool|null True if allowed, false if exceeded, null if Redis unavailable
     */
    private function check_rate_limit_redis($limit_type, $identifier) {
        try {
            $config = get_config('auth_secureotp');
            $redis = new \Redis();

            $redis_host = !empty($config->redis_host) ? $config->redis_host : '127.0.0.1';
            $redis_port = !empty($config->redis_port) ? (int)$config->redis_port : 6379;

            // Short timeout to avoid blocking when Redis is down.
            $connected = @$redis->connect($redis_host, $redis_port, 0.5);

            if (!$connected) {
                return null; // Signal caller to use DB fallback.
            }

            if (!empty($config->redis_password)) {
                $redis->auth($config->redis_password);
            }

            $redis->select(!empty($config->redis_db) ? (int)$config->redis_db : 0);

            // Rate limit: 3 OTP requests per 15 minutes.
            $key = "auth_secureotp:ratelimit:{$limit_type}:{$identifier}";
            $limit = 3;
            $window = 900; // 15 minutes.

            $current = $redis->get($key);
            if ($current === false) {
                $redis->setex($key, $window, 1);
                return true;
            }

            if ((int)$current >= $limit) {
                return false;
            }

            $redis->incr($key);
            return true;

        } catch (\Exception $e) {
            // Redis unavailable - signal caller to use DB fallback.
            return null;
        }
    }

    /**
     * Check rate limit using database
     *
     * @param string $limit_type Type of limit
     * @param string $identifier Identifier
     * @return bool True if allowed
     */
    private function check_rate_limit_db($limit_type, $identifier) {
        global $DB;

        $now = time();
        $window_start = $now - 900; // 15 minutes
        $limit = 3;

        // Get or create record
        $record = $DB->get_record('auth_secureotp_rate_limit', array(
            'identifier' => $identifier,
            'limit_type' => $limit_type
        ));

        if (!$record) {
            // Create new record
            $record = new \stdClass();
            $record->identifier = $identifier;
            $record->limit_type = $limit_type;
            $record->attempt_count = 1;
            $record->window_start = $now;
            $record->window_duration = 900;
            $record->locked_until = null;
            $record->timecreated = $now;
            $record->timemodified = $now;

            $DB->insert_record('auth_secureotp_rate_limit', $record);
            return true;
        }

        // Check if window has expired
        if ($record->window_start < $window_start) {
            // Reset window
            $record->attempt_count = 1;
            $record->window_start = $now;
            $record->locked_until = null;
            $record->timemodified = $now;

            $DB->update_record('auth_secureotp_rate_limit', $record);
            return true;
        }

        // Check if locked
        if ($record->locked_until && $record->locked_until > $now) {
            return false;
        }

        // Check if limit exceeded
        if ($record->attempt_count >= $limit) {
            // Lock for remaining window time
            $record->locked_until = $record->window_start + 900;
            $record->timemodified = $now;
            $DB->update_record('auth_secureotp_rate_limit', $record);
            return false;
        }

        // Increment counter
        $record->attempt_count++;
        $record->timemodified = $now;
        $DB->update_record('auth_secureotp_rate_limit', $record);

        return true;
    }

    /**
     * Record an attempt for rate limiting
     *
     * @param string $limit_type Type of limit (OTP_REQUEST, OTP_VERIFY, etc.)
     * @param string $identifier Identifier (IP address, employee_id, etc.)
     */
    public function record_attempt($limit_type, $identifier) {
        // The check_rate_limit method already records the attempt
        // This method exists for compatibility
        return true;
    }

    /**
     * Record a failed OTP attempt for a user
     *
     * @param int $userid The user ID
     * @return bool True if account should be locked
     */
    public function record_failed_attempt($userid) {
        global $DB;

        $config = get_config('auth_secureotp');
        $max_attempts = !empty($config->max_attempts) ? (int)$config->max_attempts : 3;
        $lockout_duration = !empty($config->lockout_duration_minutes) ? (int)$config->lockout_duration_minutes : 15;
        $now = time();
        $identifier = (string)$userid;
        $limit_type = 'OTP_VERIFY';
        $window_duration = 3600; // 1 hour window

        $record = $DB->get_record('auth_secureotp_rate_limit', array(
            'identifier' => $identifier,
            'limit_type' => $limit_type
        ));

        if (!$record) {
            $record = new \stdClass();
            $record->identifier = $identifier;
            $record->limit_type = $limit_type;
            $record->attempt_count = 1;
            $record->window_start = $now;
            $record->window_duration = $window_duration;
            $record->locked_until = null;
            $record->timecreated = $now;
            $record->timemodified = $now;

            $DB->insert_record('auth_secureotp_rate_limit', $record);
            return false;
        }

        // Already locked.
        if ($record->locked_until && $record->locked_until > $now) {
            return true;
        }

        // Reset window if expired.
        if (($record->window_start + $record->window_duration) < $now) {
            $record->attempt_count = 1;
            $record->window_start = $now;
            $record->locked_until = null;
        } else {
            $record->attempt_count++;
        }

        // Lock if exceeded.
        if ($record->attempt_count >= $max_attempts) {
            $record->locked_until = $now + ($lockout_duration * 60);
        }

        $record->timemodified = $now;
        $DB->update_record('auth_secureotp_rate_limit', $record);

        return ($record->locked_until && $record->locked_until > $now);
    }
    
    /**
     * Check if a user is currently rate limited
     *
     * @param int $userid The user ID
     * @return bool True if rate limited, false otherwise
     */
    public function is_rate_limited($userid) {
        global $DB;

        $identifier = (string)$userid;
        $record = $DB->get_record('auth_secureotp_rate_limit', array(
            'identifier' => $identifier,
            'limit_type' => 'OTP_VERIFY'
        ));

        if (!$record) {
            return false;
        }

        $now = time();

        // Check if currently locked.
        if ($record->locked_until && $record->locked_until > $now) {
            return true;
        }

        // Reset if window expired.
        if (($record->window_start + $record->window_duration) < $now) {
            $record->attempt_count = 0;
            $record->window_start = $now;
            $record->locked_until = null;
            $record->timemodified = $now;
            $DB->update_record('auth_secureotp_rate_limit', $record);
            return false;
        }

        return false;
    }
    
    /**
     * Get remaining attempts for a user
     *
     * @param int $userid The user ID
     * @return int Number of remaining attempts
     */
    public function get_remaining_attempts($userid) {
        global $DB;

        $config = get_config('auth_secureotp');
        $max_attempts = !empty($config->max_attempts) ? (int)$config->max_attempts : 3;
        $identifier = (string)$userid;

        $record = $DB->get_record('auth_secureotp_rate_limit', array(
            'identifier' => $identifier,
            'limit_type' => 'OTP_VERIFY'
        ));

        if (!$record) {
            return $max_attempts;
        }

        $now = time();

        // If locked, return 0.
        if ($record->locked_until && $record->locked_until > $now) {
            return 0;
        }

        // Reset if window expired.
        if (($record->window_start + $record->window_duration) < $now) {
            return $max_attempts;
        }

        return max(0, $max_attempts - $record->attempt_count);
    }
    
    /**
     * Get time until lockout resets for a user
     *
     * @param int $userid The user ID
     * @return int Seconds until reset, or 0 if not locked
     */
    public function get_lockout_reset_time($userid) {
        global $DB;

        $identifier = (string)$userid;
        $record = $DB->get_record('auth_secureotp_rate_limit', array(
            'identifier' => $identifier,
            'limit_type' => 'OTP_VERIFY'
        ));

        if (!$record || !$record->locked_until || $record->locked_until <= time()) {
            return 0;
        }

        return $record->locked_until - time();
    }
    
    /**
     * Record an OTP request to prevent flooding
     *
     * @param string $identifier An identifier (IP address, user ID, etc.)
     * @return bool True if request is allowed, false if rate limited
     */
    public function record_request($identifier) {
        global $DB;

        $config = get_config('auth_secureotp');
        $requests_per_minute = !empty($config->rate_limit_requests_per_minute) ? (int)$config->rate_limit_requests_per_minute : 5;

        $now = time();
        $limit_type = 'REQUEST_FLOOD';
        $window_duration = 60; // 1 minute window

        $record = $DB->get_record('auth_secureotp_rate_limit', array(
            'identifier' => $identifier,
            'limit_type' => $limit_type
        ));

        if (!$record) {
            $record = new \stdClass();
            $record->identifier = $identifier;
            $record->limit_type = $limit_type;
            $record->attempt_count = 1;
            $record->window_start = $now;
            $record->window_duration = $window_duration;
            $record->locked_until = null;
            $record->timecreated = $now;
            $record->timemodified = $now;

            $DB->insert_record('auth_secureotp_rate_limit', $record);
            return true;
        }

        // Check if window has expired.
        if (($record->window_start + $record->window_duration) < $now) {
            $record->attempt_count = 1;
            $record->window_start = $now;
            $record->timemodified = $now;
            $DB->update_record('auth_secureotp_rate_limit', $record);
            return true;
        }

        // Check if limit exceeded.
        if ($record->attempt_count >= $requests_per_minute) {
            return false;
        }

        $record->attempt_count++;
        $record->timemodified = $now;
        $DB->update_record('auth_secureotp_rate_limit', $record);

        return true;
    }
    
    /**
     * Clean up expired rate limit records
     */
    public function cleanup_expired_records() {
        global $DB;

        $now = time();

        // Remove records where the window has expired and they are not locked.
        $DB->delete_records_select(
            'auth_secureotp_rate_limit',
            '(window_start + window_duration) < ? AND (locked_until IS NULL OR locked_until < ?)',
            array($now, $now)
        );
    }
}
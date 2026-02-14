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
 * Device Fingerprint for SecureOTP authentication plugin.
 *
 * @package   auth_secureotp
 * @copyright 2023 Your Name <your.email@organization.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\auth;

defined('MOODLE_INTERNAL') || die();

/**
 * Class device_fingerprint
 */
class device_fingerprint {
    
    /**
     * Get the fingerprint for the current device (alias for generate_fingerprint)
     *
     * @return string The device fingerprint
     */
    public function get_fingerprint() {
        return $this->generate_fingerprint();
    }

    /**
     * Generate a unique fingerprint for the current device/browser
     *
     * @return string The device fingerprint
     */
    public function generate_fingerprint() {
        // Collect various browser/device characteristics
        $components = array(
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'accept_language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            'screen_resolution' => $_POST['screen_resolution'] ?? '', // Would need JS to collect this
            'timezone_offset' => $_POST['timezone_offset'] ?? '', // Would need JS to collect this
            'platform' => $_POST['platform'] ?? '', // Would need JS to collect this
        );

        // Create a hash from the collected data
        $fingerprint = hash('sha256', serialize($components));

        return $fingerprint;
    }
    
    /**
     * Check if the current device is trusted for the given user
     *
     * @param int $userid The user ID
     * @param string $fingerprint The device fingerprint
     * @return bool True if device is trusted, false otherwise
     */
    public function is_trusted_device($userid, $fingerprint) {
        global $DB;
        
        // Check if trusted devices are enabled in config
        $config = get_config('auth_secureotp');
        if (empty($config->enable_trusted_devices)) {
            return false;
        }
        
        // Look up the device in the trusted devices table
        $record = $DB->get_record('auth_secureotp_trusted_devices', array(
            'userid' => $userid,
            'fingerprint' => $fingerprint,
            'active' => 1
        ));
        
        if (!$record) {
            return false;
        }
        
        // Check if the device trust has expired
        $duration_days = !empty($config->trusted_device_duration_days) ? (int)$config->trusted_device_duration_days : 30;
        $expiry_time = $record->added_on + ($duration_days * 24 * 60 * 60); // Convert days to seconds
        
        if (time() > $expiry_time) {
            // Mark the device as expired
            $update = new \stdClass();
            $update->id = $record->id;
            $update->active = 0;
            $DB->update_record('auth_secureotp_trusted_devices', $update);
            
            return false;
        }
        
        // Update the last used timestamp
        $update = new \stdClass();
        $update->id = $record->id;
        $update->last_used = time();
        $DB->update_record('auth_secureotp_trusted_devices', $update);
        
        return true;
    }
    
    /**
     * Trust the current device for the given user
     *
     * @param int $userid The user ID
     * @param string $fingerprint The device fingerprint
     * @return bool True if successful
     */
    public function trust_device($userid, $fingerprint) {
        global $DB;
        
        // Check if device is already trusted
        $existing = $DB->get_record('auth_secureotp_trusted_devices', array(
            'userid' => $userid,
            'fingerprint' => $fingerprint
        ));
        
        if ($existing) {
            // Update the existing record
            $update = new \stdClass();
            $update->id = $existing->id;
            $update->active = 1;
            $update->added_on = time();
            $update->last_used = time();
            
            return $DB->update_record('auth_secureotp_trusted_devices', $update);
        } else {
            // Create a new record
            $record = new \stdClass();
            $record->userid = $userid;
            $record->fingerprint = $fingerprint;
            $record->added_on = time();
            $record->last_used = time();
            $record->active = 1;
            
            return $DB->insert_record('auth_secureotp_trusted_devices', $record) !== false;
        }
    }
    
    /**
     * Remove a device from the trusted list
     *
     * @param int $userid The user ID
     * @param string $fingerprint The device fingerprint
     * @return bool True if successful
     */
    public function remove_trusted_device($userid, $fingerprint) {
        global $DB;
        
        return $DB->delete_records('auth_secureotp_trusted_devices', array(
            'userid' => $userid,
            'fingerprint' => $fingerprint
        ));
    }
    
    /**
     * Get all trusted devices for a user
     *
     * @param int $userid The user ID
     * @return array Array of trusted devices
     */
    public function get_trusted_devices($userid) {
        global $DB;
        
        $records = $DB->get_records('auth_secureotp_trusted_devices', array(
            'userid' => $userid,
            'active' => 1
        ), 'added_on DESC');
        
        return $records;
    }
    
    /**
     * Clean up expired trusted devices
     */
    public function cleanup_expired_devices() {
        global $DB;
        
        $config = get_config('auth_secureotp');
        $duration_days = !empty($config->trusted_device_duration_days) ? (int)$config->trusted_device_duration_days : 30;
        $expiry_time = time() - ($duration_days * 24 * 60 * 60);
        
        $DB->execute("
            UPDATE {auth_secureotp_trusted_devices}
            SET active = 0
            WHERE active = 1 AND added_on < ?
        ", array($expiry_time));
    }
}
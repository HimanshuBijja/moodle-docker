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
 * Audit Logger for SecureOTP authentication plugin.
 *
 * @package   auth_secureotp
 * @copyright 2023 Your Name <your.email@organization.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\security;

defined('MOODLE_INTERNAL') || die();

/**
 * Class audit_logger
 */
class audit_logger {
    
    /**
     * Log an authentication event
     *
     * @param string $eventtype The event type (OTP_SENT, LOGIN_SUCCESS, BULK_IMPORT_COMPLETED, etc.)
     * @param string $eventstatus Event status (SUCCESS, FAILURE, WARNING, ERROR)
     * @param int|string $userid The user ID or employee ID
     * @param string $ipaddress IP address of the user
     * @param array $eventdata Additional event data as array (will be JSON encoded)
     * @param string $severity Severity level (INFO, WARNING, CRITICAL)
     * @return bool True if logged successfully
     */
    public function log_event($eventtype, $eventstatus, $userid = null, $ipaddress = null, $eventdata = array(), $severity = 'INFO') {
        global $DB, $CFG;

        if (is_null($ipaddress)) {
            $ipaddress = $this->get_client_ip();
        }

        $record = new \stdClass();
        $record->event_type = $eventtype;
        $record->event_status = $eventstatus;
        $record->severity = $severity;

        // Handle userid - can be integer (Moodle user ID) or string (employee_id) or null
        if (is_numeric($userid) && $userid > 0) {
            $record->userid = (int)$userid;
        } else if (is_string($userid)) {
            $record->employee_id = $userid;
            $record->userid = null;
        } else {
            $record->userid = null;
        }

        $record->ip_address = $ipaddress;
        $record->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $record->device_fingerprint = null; // Can be populated by caller if needed
        $record->event_data = !empty($eventdata) ? json_encode($eventdata) : null;
        $record->timecreated = time();

        // Generate HMAC signature for tamper detection
        $record->signature = $this->generate_signature($record);

        // Insert the log record
        try {
            $id = $DB->insert_record('auth_secureotp_audit', $record);
            return $id !== false;
        } catch (\Exception $e) {
            // Fallback: log to Moodle debugging if audit insert fails
            debugging('Audit log failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Generate HMAC signature for audit record
     *
     * @param object $record The audit record
     * @return string HMAC-SHA256 signature
     */
    private function generate_signature($record) {
        global $CFG;

        // Use site identifier as secret key
        $secret = isset($CFG->auth_secureotp_audit_secret) ? $CFG->auth_secureotp_audit_secret : $CFG->siteidentifier;

        // Create signature payload
        $payload = implode('|', array(
            $record->event_type,
            $record->event_status,
            $record->severity,
            $record->userid ?? '',
            $record->employee_id ?? '',
            $record->ip_address ?? '',
            $record->timecreated
        ));

        return hash_hmac('sha256', $payload, $secret);
    }
    
    /**
     * Get the client's IP address using Moodle's secure method.
     *
     * @return string The client IP address
     */
    private function get_client_ip() {
        // Use Moodle's getremoteaddr() which respects $CFG->getremoteaddrconf
        // and handles proxy headers securely based on admin configuration.
        if (function_exists('getremoteaddr')) {
            return getremoteaddr();
        }

        // Fallback for CLI or early bootstrap.
        return $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    }
    
    /**
     * Log event to Moodle's standard log system
     *
     * @param int $userid The user ID
     * @param string $eventtype The event type
     * @param string $details Additional details
     * @param string $ipaddress IP address
     */
    private function log_to_moodle($userid, $eventtype, $details, $ipaddress) {
        $eventdata = array(
            'objectid' => $userid,
            'context' => \context_system::instance(),
            'other' => array(
                'component' => 'auth_secureotp',
                'event_type' => $eventtype,
                'details' => $details,
                'ip_address' => $ipaddress
            )
        );
        
        $event = \auth_secureotp\event\login_attempted::create($eventdata);
        $event->trigger();
    }
    
    /**
     * Get audit logs for a specific user
     *
     * @param int $userid The user ID
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Array of audit log records
     */
    public function get_user_logs($userid, $limit = 50, $offset = 0) {
        global $DB;
        
        $records = $DB->get_records(
            'auth_secureotp_audit',
            array('userid' => $userid),
            'timecreated DESC',
            '*', $offset, $limit
        );
        
        return $records;
    }
    
    /**
     * Get audit logs for a specific time period
     *
     * @param int $starttime Start time (timestamp)
     * @param int $endtime End time (timestamp)
     * @param string $eventtype Optional event type filter
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Array of audit log records
     */
    public function get_logs_by_time($starttime, $endtime, $eventtype = null, $limit = 50, $offset = 0) {
        global $DB;
        
        $params = array($starttime, $endtime);
        $sql = "timecreated >= ? AND timecreated <= ?";
        
        if ($eventtype) {
            $sql .= " AND event_type = ?";
            $params[] = $eventtype;
        }
        
        $records = $DB->get_records_select(
            'auth_secureotp_audit',
            $sql,
            $params,
            'timecreated DESC',
            '*', $offset, $limit
        );
        
        return $records;
    }
    
    /**
     * Count audit logs for a specific user and event type
     *
     * @param int $userid The user ID
     * @param string $eventtype The event type
     * @param int $since Only count logs since this time (timestamp)
     * @return int Number of matching logs
     */
    public function count_user_events($userid, $eventtype, $since = 0) {
        global $DB;

        $params = array($userid, $eventtype);
        $sql = 'userid = ? AND event_type = ?';

        if ($since > 0) {
            $sql .= ' AND timecreated >= ?';
            $params[] = $since;
        }

        return $DB->count_records_select('auth_secureotp_audit', $sql, $params);
    }
    
    /**
     * Clean up old audit logs
     *
     * @param int $days Number of days to keep logs (default 90)
     * @return int Number of records deleted
     */
    public function cleanup_old_logs($days = 90) {
        global $DB;
        
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        return $DB->delete_records_select(
            'auth_secureotp_audit',
            'timecreated < ?',
            array($cutoff)
        );
    }
    
    /**
     * Generate a security report for administrators
     *
     * @param int $days Number of days to include in the report (default 30)
     * @return array Security report data
     */
    public function generate_security_report($days = 30) {
        global $DB;
        
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        // Total authentication attempts
        $total_attempts = $DB->count_records_select(
            'auth_secureotp_audit',
            'timecreated >= ? AND event_type IN (?, ?, ?)',
            array($cutoff, 'LOGIN_SUCCESS', 'LOGIN_FAILED', 'OTP_SENT')
        );

        // Successful authentications
        $successful_auths = $DB->count_records_select(
            'auth_secureotp_audit',
            'timecreated >= ? AND event_type = ?',
            array($cutoff, 'LOGIN_SUCCESS')
        );

        // Failed authentications
        $failed_auths = $DB->count_records_select(
            'auth_secureotp_audit',
            'timecreated >= ? AND event_type = ?',
            array($cutoff, 'LOGIN_FAILED')
        );

        // OTP sent events
        $otp_sent = $DB->count_records_select(
            'auth_secureotp_audit',
            'timecreated >= ? AND event_type = ?',
            array($cutoff, 'OTP_SENT')
        );

        // Suspicious activities (account locks and device changes)
        $suspicious_activities = $DB->count_records_select(
            'auth_secureotp_audit',
            'timecreated >= ? AND event_type IN (?, ?)',
            array($cutoff, 'ACCOUNT_LOCKED', 'DEVICE_CHANGE_DETECTED')
        );

        // Top IP addresses for failed attempts
        $top_ips = $DB->get_records_sql(
            "SELECT ip_address, COUNT(*) as count
             FROM {auth_secureotp_audit}
             WHERE timecreated >= ? AND event_type = ?
             GROUP BY ip_address
             ORDER BY count DESC
             LIMIT 10",
            array($cutoff, 'LOGIN_FAILED')
        );
        
        return array(
            'period_days' => $days,
            'total_attempts' => $total_attempts,
            'successful_authentications' => $successful_auths,
            'failed_authentications' => $failed_auths,
            'otp_sent_count' => $otp_sent,
            'suspicious_activities' => $suspicious_activities,
            'success_rate' => $total_attempts > 0 ? round(($successful_auths / $total_attempts) * 100, 2) : 0,
            'top_failed_ip_addresses' => $top_ips
        );
    }
}
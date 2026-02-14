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
 * Security Dashboard data aggregation
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Security Dashboard class
 */
class security_dashboard {

    /**
     * Get dashboard metrics
     *
     * @return array Dashboard data
     */
    public function get_dashboard_data() {
        global $DB;

        $data = array();

        // Total users.
        $data['total_users'] = $DB->count_records_select('user', "auth = 'secureotp' AND deleted = 0");

        // Active users.
        $data['active_users'] = $DB->count_records('auth_secureotp_security', array('status' => 'ACTIVE'));

        // Logins today.
        $today_start = strtotime('today');
        $data['logins_today'] = $DB->count_records_select(
            'auth_secureotp_audit',
            "event_type = 'LOGIN_SUCCESS' AND timecreated >= ?",
            array($today_start)
        );

        // Failed attempts today.
        $data['failed_attempts'] = $DB->count_records_select(
            'auth_secureotp_audit',
            "event_type IN ('OTP_VERIFICATION_FAILED', 'LOGIN_FAILED') AND timecreated >= ?",
            array($today_start)
        );

        // Locked accounts.
        $data['locked_accounts'] = $DB->count_records_select(
            'auth_secureotp_security',
            "is_locked = 1 AND locked_until > ?",
            array(time())
        );

        // Success rate (last 24 hours).
        $yesterday = time() - 86400;
        $total_attempts = $DB->count_records_select(
            'auth_secureotp_audit',
            "event_type IN ('LOGIN_SUCCESS', 'LOGIN_FAILED') AND timecreated >= ?",
            array($yesterday)
        );
        $successful = $DB->count_records_select(
            'auth_secureotp_audit',
            "event_type = 'LOGIN_SUCCESS' AND timecreated >= ?",
            array($yesterday)
        );
        $data['success_rate'] = $total_attempts > 0 ? round(($successful / $total_attempts) * 100, 1) : 0;

        // Recent logins.
        $data['recent_logins'] = $this->get_recent_logins(10);

        // Security alerts.
        $data['security_alerts'] = $this->get_security_alerts(10);

        return $data;
    }

    /**
     * Get recent logins
     *
     * @param int $limit Number of records
     * @return array Recent login events
     */
    private function get_recent_logins($limit = 10) {
        global $DB;

        $sql = "SELECT a.*, u.username, u.firstname, u.lastname
                FROM {auth_secureotp_audit} a
                LEFT JOIN {user} u ON a.userid = u.id
                WHERE a.event_type IN ('LOGIN_SUCCESS', 'LOGIN_FAILED')
                ORDER BY a.timecreated DESC";

        $records = $DB->get_records_sql($sql, array(), 0, $limit);

        $logins = array();
        foreach ($records as $record) {
            $logins[] = array(
                'username' => $record->username,
                'fullname' => $record->firstname . ' ' . $record->lastname,
                'success' => $record->event_type === 'LOGIN_SUCCESS',
                'timeago' => $this->time_ago($record->timecreated),
                'ip_address' => $record->ip_address
            );
        }

        return $logins;
    }

    /**
     * Get security alerts
     *
     * @param int $limit Number of records
     * @return array Security alert events
     */
    private function get_security_alerts($limit = 10) {
        global $DB;

        $sql = "SELECT *
                FROM {auth_secureotp_audit}
                WHERE severity IN ('WARNING', 'CRITICAL')
                ORDER BY timecreated DESC";

        $records = $DB->get_records_sql($sql, array(), 0, $limit);

        $alerts = array();
        foreach ($records as $record) {
            $alerts[] = array(
                'event_type' => $record->event_type,
                'message' => $this->format_alert_message($record),
                'severity' => strtolower($record->severity),
                'timeago' => $this->time_ago($record->timecreated)
            );
        }

        return $alerts;
    }

    /**
     * Format alert message
     *
     * @param object $record Audit record
     * @return string Formatted message
     */
    private function format_alert_message($record) {
        $messages = array(
            'ACCOUNT_LOCKED' => 'Account locked due to failed attempts',
            'DEVICE_CHANGE_DETECTED' => 'Device change detected',
            'RATE_LIMIT_EXCEEDED' => 'Rate limit exceeded',
            'ACCOUNT_SUSPENDED' => 'Account suspended by admin'
        );

        $base_message = isset($messages[$record->event_type]) ? $messages[$record->event_type] : $record->event_type;

        if ($record->employee_id) {
            $base_message .= ' (Employee: ' . $record->employee_id . ')';
        }

        return $base_message;
    }

    /**
     * Format time ago
     *
     * @param int $timestamp Unix timestamp
     * @return string Time ago string
     */
    private function time_ago($timestamp) {
        $diff = time() - $timestamp;

        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        return floor($diff / 86400) . 'd ago';
    }

    /**
     * Get login chart data (last 7 days)
     *
     * @return array Chart data
     */
    public function get_login_chart_data() {
        global $DB;

        $data = array('labels' => array(), 'success' => array(), 'failed' => array());

        for ($i = 6; $i >= 0; $i--) {
            $day_start = strtotime("-$i days", strtotime('today'));
            $day_end = $day_start + 86400;

            $data['labels'][] = userdate($day_start, '%d %b');

            // Count successful logins.
            $success = $DB->count_records_select(
                'auth_secureotp_audit',
                "event_type = 'LOGIN_SUCCESS' AND timecreated >= ? AND timecreated < ?",
                array($day_start, $day_end)
            );
            $data['success'][] = $success;

            // Count failed logins.
            $failed = $DB->count_records_select(
                'auth_secureotp_audit',
                "event_type = 'LOGIN_FAILED' AND timecreated >= ? AND timecreated < ?",
                array($day_start, $day_end)
            );
            $data['failed'][] = $failed;
        }

        return $data;
    }
}

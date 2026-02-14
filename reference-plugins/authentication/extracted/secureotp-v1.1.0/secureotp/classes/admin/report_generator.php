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
 * Report Generator for compliance and security reports
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Report Generator class
 */
class report_generator {

    /**
     * Generate security audit report
     *
     * @param int $from_date Start date (Unix timestamp)
     * @param int $to_date End date (Unix timestamp)
     * @param string $format Format (csv/pdf/html)
     * @return string Report content
     */
    public function generate_audit_report($from_date, $to_date, $format = 'csv') {
        global $DB;

        $records = $DB->get_records_select(
            'auth_secureotp_audit',
            'timecreated >= ? AND timecreated <= ?',
            array($from_date, $to_date),
            'timecreated DESC'
        );

        if ($format === 'csv') {
            return $this->generate_audit_csv($records, $from_date, $to_date);
        } else if ($format === 'html') {
            return $this->generate_audit_html($records, $from_date, $to_date);
        }

        return '';
    }

    /**
     * Generate audit CSV
     *
     * @param array $records Audit records
     * @param int $from_date Start date
     * @param int $to_date End date
     * @return string CSV content
     */
    private function generate_audit_csv($records, $from_date, $to_date) {
        $csv = "Security Audit Report\n";
        $csv .= "Period: " . userdate($from_date) . " to " . userdate($to_date) . "\n";
        $csv .= "Generated: " . userdate(time()) . "\n\n";

        $csv .= "Timestamp,Event Type,Status,Severity,User ID,Employee ID,IP Address,Device Fingerprint,Event Data\n";

        foreach ($records as $record) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                userdate($record->timecreated),
                $record->event_type,
                $record->event_status,
                $record->severity,
                $record->userid ?: 'N/A',
                $record->employee_id ?: 'N/A',
                $record->ip_address ?: 'N/A',
                $record->device_fingerprint ? substr($record->device_fingerprint, 0, 8) . '...' : 'N/A',
                $record->event_data ?: '{}'
            );
        }

        return $csv;
    }

    /**
     * Generate audit HTML report
     *
     * @param array $records Audit records
     * @param int $from_date Start date
     * @param int $to_date End date
     * @return string HTML content
     */
    private function generate_audit_html($records, $from_date, $to_date) {
        $html = '<html><head><title>Security Audit Report</title></head><body>';
        $html .= '<h1>Security Audit Report</h1>';
        $html .= '<p>Period: ' . userdate($from_date) . ' to ' . userdate($to_date) . '</p>';
        $html .= '<p>Generated: ' . userdate(time()) . '</p>';

        $html .= '<table border="1" cellpadding="5"><thead><tr>';
        $html .= '<th>Timestamp</th><th>Event</th><th>Status</th><th>Severity</th>';
        $html .= '<th>User</th><th>Employee ID</th><th>IP Address</th></tr></thead><tbody>';

        foreach ($records as $record) {
            $html .= '<tr>';
            $html .= '<td>' . userdate($record->timecreated) . '</td>';
            $html .= '<td>' . htmlspecialchars($record->event_type) . '</td>';
            $html .= '<td>' . htmlspecialchars($record->event_status) . '</td>';
            $html .= '<td>' . htmlspecialchars($record->severity) . '</td>';
            $html .= '<td>' . ($record->userid ?: 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($record->employee_id ?: 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($record->ip_address ?: 'N/A') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return $html;
    }

    /**
     * Generate compliance report
     *
     * @return array Compliance report data
     */
    public function generate_compliance_report() {
        global $DB;

        $report = array();

        // User statistics.
        $report['total_users'] = $DB->count_records_select('user', "auth = 'secureotp' AND deleted = 0");
        $report['active_users'] = $DB->count_records('auth_secureotp_security', array('status' => 'ACTIVE'));
        $report['suspended_users'] = $DB->count_records('auth_secureotp_security', array('status' => 'SUSPENDED'));

        // Authentication statistics (last 30 days).
        $thirty_days_ago = time() - (30 * 86400);
        $report['total_logins_30d'] = $DB->count_records_select(
            'auth_secureotp_audit',
            "event_type = 'LOGIN_SUCCESS' AND timecreated >= ?",
            array($thirty_days_ago)
        );
        $report['failed_attempts_30d'] = $DB->count_records_select(
            'auth_secureotp_audit',
            "event_type IN ('LOGIN_FAILED', 'OTP_VERIFICATION_FAILED') AND timecreated >= ?",
            array($thirty_days_ago)
        );

        // Security incidents (last 30 days).
        $report['accounts_locked_30d'] = $DB->count_records_select(
            'auth_secureotp_audit',
            "event_type = 'ACCOUNT_LOCKED' AND timecreated >= ?",
            array($thirty_days_ago)
        );
        $report['device_changes_30d'] = $DB->count_records_select(
            'auth_secureotp_audit',
            "event_type = 'DEVICE_CHANGE_DETECTED' AND timecreated >= ?",
            array($thirty_days_ago)
        );

        // Audit log retention compliance.
        $oldest_log = $DB->get_record_sql(
            "SELECT MIN(timecreated) as oldest FROM {auth_secureotp_audit}"
        );
        $report['oldest_audit_log'] = $oldest_log->oldest;
        $report['audit_retention_days'] = $oldest_log->oldest ? floor((time() - $oldest_log->oldest) / 86400) : 0;
        $report['audit_retention_compliant'] = $report['audit_retention_days'] <= (7 * 365); // 7 years.

        // Generate digital signature.
        $report['signature'] = $this->generate_report_signature($report);
        $report['generated_at'] = time();

        return $report;
    }

    /**
     * Generate digital signature for report
     *
     * @param array $report Report data
     * @return string HMAC signature
     */
    private function generate_report_signature($report) {
        global $CFG;

        $secret = isset($CFG->auth_secureotp_signing_secret)
            ? $CFG->auth_secureotp_signing_secret
            : $CFG->passwordsaltmain;

        $data = json_encode($report);
        return hash_hmac('sha256', $data, $secret);
    }
}

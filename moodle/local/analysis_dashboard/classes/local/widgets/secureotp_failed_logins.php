<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_failed_logins extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_failed_logins'; }
    public function get_type(): string { return 'table'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $sevendays = time() - (7 * DAYSECS);
        $sql = "SELECT a.id, a.userid, a.ip_address, a.user_agent, a.timecreated, a.event_status,
                       ud.employee_id
                  FROM {auth_secureotp_audit} a
             LEFT JOIN {auth_secureotp_userdata} ud ON ud.userid = a.userid
                 WHERE a.event_type IN ('LOGIN_FAILED', 'ACCOUNT_LOCKED')
                   AND a.timecreated >= :starttime
              ORDER BY a.timecreated DESC
                 LIMIT 50";
        $records = $DB->get_records_sql($sql, ['starttime' => $sevendays]);

        $rows = [];
        foreach ($records as $record) {
            $rows[] = [
                $record->employee_id ?: 'N/A',
                $record->ip_address ?: '-',
                \core_text::substr($record->user_agent ?: '-', 0, 40),
                userdate($record->timecreated),
                $record->event_status,
            ];
        }

        return [
            'headers' => [
                get_string('employee_id', 'local_analysis_dashboard'),
                get_string('ip_address', 'local_analysis_dashboard'),
                get_string('user_agent', 'local_analysis_dashboard'),
                get_string('time'),
                get_string('status'),
            ],
            'rows' => $rows,
        ];
    }
}

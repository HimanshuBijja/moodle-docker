<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_failed_by_location extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_failed_by_location'; }
    public function get_type(): string { return 'bar'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $thirtydays = time() - (30 * DAYSECS);
        $sql = "SELECT COALESCE(ud.working_location, 'Unknown') as location, COUNT(*) as cnt
                  FROM {auth_secureotp_audit} a
             LEFT JOIN {auth_secureotp_userdata} ud ON ud.userid = a.userid
                 WHERE a.event_type = 'LOGIN_FAILED'
                   AND a.timecreated >= :starttime
              GROUP BY ud.working_location
              ORDER BY cnt DESC
                 LIMIT 15";
        $records = $DB->get_records_sql($sql, ['starttime' => $thirtydays]);

        $labels = [];
        $data = [];

        foreach ($records as $record) {
            $labels[] = $record->location;
            $data[] = (int) $record->cnt;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => get_string('failed_logins', 'local_analysis_dashboard'),
                'data' => $data,
                'backgroundColor' => 'rgba(231, 76, 60, 0.8)',
            ]],
        ];
    }
}

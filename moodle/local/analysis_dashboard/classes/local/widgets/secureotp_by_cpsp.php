<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_by_cpsp extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_by_cpsp'; }
    public function get_type(): string { return 'bar'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;
        $sql = "SELECT COALESCE(cp_sp_office, 'Unknown') as label, COUNT(*) as cnt
                  FROM {auth_secureotp_userdata}
              GROUP BY cp_sp_office
              ORDER BY cnt DESC
                 LIMIT 15";
        $records = $DB->get_records_sql($sql);
        $labels = [];
        $data = [];
        foreach ($records as $r) {
            $labels[] = $r->label;
            $data[] = (int) $r->cnt;
        }
        return ['labels' => $labels, 'datasets' => [['label' => get_string('user_count', 'local_analysis_dashboard'), 'data' => $data, 'backgroundColor' => 'rgba(26, 188, 156, 0.8)']]];
    }
}

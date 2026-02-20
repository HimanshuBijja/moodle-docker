<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_by_unit extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_by_unit'; }
    public function get_type(): string { return 'bar'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;
        $sql = "SELECT COALESCE(unit_name, 'Unknown') as label, COUNT(*) as cnt
                  FROM {auth_secureotp_userdata}
              GROUP BY unit_name
              ORDER BY cnt DESC
                 LIMIT 15";
        $records = $DB->get_records_sql($sql);
        $labels = [];
        $data = [];
        foreach ($records as $r) {
            $labels[] = $r->label;
            $data[] = (int) $r->cnt;
        }
        return ['labels' => $labels, 'datasets' => [['label' => get_string('user_count', 'local_analysis_dashboard'), 'data' => $data, 'backgroundColor' => 'rgba(46, 204, 113, 0.8)']]];
    }
}

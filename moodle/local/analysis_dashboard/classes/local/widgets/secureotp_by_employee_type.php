<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_by_employee_type extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_by_employee_type'; }
    public function get_type(): string { return 'pie'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;
        $sql = "SELECT COALESCE(employee_type, 'Unknown') as label, COUNT(*) as cnt
                  FROM {auth_secureotp_userdata}
              GROUP BY employee_type
              ORDER BY cnt DESC";
        $records = $DB->get_records_sql($sql);
        $labels = [];
        $data = [];
        $palette = ['rgba(52,152,219,0.8)', 'rgba(46,204,113,0.8)', 'rgba(155,89,182,0.8)', 'rgba(241,196,15,0.8)', 'rgba(231,76,60,0.8)', 'rgba(26,188,156,0.8)'];
        $bgcolors = [];
        $i = 0;
        foreach ($records as $r) {
            $labels[] = $r->label;
            $data[] = (int) $r->cnt;
            $bgcolors[] = $palette[$i % count($palette)];
            $i++;
        }
        return ['labels' => $labels, 'datasets' => [['data' => $data, 'backgroundColor' => $bgcolors]]];
    }
}

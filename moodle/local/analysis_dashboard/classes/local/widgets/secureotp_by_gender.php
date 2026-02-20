<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_by_gender extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_by_gender'; }
    public function get_type(): string { return 'pie'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;
        $sql = "SELECT COALESCE(gender, 'Unknown') as label, COUNT(*) as cnt
                  FROM {auth_secureotp_userdata}
              GROUP BY gender
              ORDER BY cnt DESC";
        $records = $DB->get_records_sql($sql);
        $labels = [];
        $data = [];
        $colors = ['Male' => 'rgba(52,152,219,0.8)', 'Female' => 'rgba(231,76,60,0.8)', 'Other' => 'rgba(155,89,182,0.8)', 'Unknown' => 'rgba(189,195,199,0.8)'];
        $bgcolors = [];
        foreach ($records as $r) {
            $labels[] = $r->label;
            $data[] = (int) $r->cnt;
            $bgcolors[] = $colors[$r->label] ?? 'rgba(189,195,199,0.8)';
        }
        return ['labels' => $labels, 'datasets' => [['data' => $data, 'backgroundColor' => $bgcolors]]];
    }
}

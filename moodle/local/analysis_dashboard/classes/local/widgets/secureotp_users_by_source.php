<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_users_by_source extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_users_by_source'; }
    public function get_type(): string { return 'pie'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $sql = "SELECT COALESCE(source_system, 'Unknown') as source, COUNT(*) as cnt
                  FROM {auth_secureotp_userdata}
              GROUP BY source_system
              ORDER BY cnt DESC";
        $records = $DB->get_records_sql($sql);

        $labels = [];
        $data = [];
        $bgcolors = [];
        $palette = [
            'rgba(52, 152, 219, 0.8)',
            'rgba(46, 204, 113, 0.8)',
            'rgba(155, 89, 182, 0.8)',
            'rgba(241, 196, 15, 0.8)',
            'rgba(231, 76, 60, 0.8)',
            'rgba(26, 188, 156, 0.8)',
        ];

        $i = 0;
        foreach ($records as $record) {
            $labels[] = $record->source;
            $data[] = (int) $record->cnt;
            $bgcolors[] = $palette[$i % count($palette)];
            $i++;
        }

        return [
            'labels' => $labels,
            'datasets' => [['data' => $data, 'backgroundColor' => $bgcolors]],
        ];
    }
}

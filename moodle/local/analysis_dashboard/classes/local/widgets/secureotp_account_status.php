<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_account_status extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_account_status'; }
    public function get_type(): string { return 'pie'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $sql = "SELECT status, COUNT(*) as cnt
                  FROM {auth_secureotp_security}
              GROUP BY status
              ORDER BY cnt DESC";
        $records = $DB->get_records_sql($sql);

        $labels = [];
        $data = [];
        $colors = [
            'PROVISIONED' => 'rgba(52, 152, 219, 0.8)',
            'ACTIVE' => 'rgba(46, 204, 113, 0.8)',
            'SUSPENDED' => 'rgba(241, 196, 15, 0.8)',
            'ARCHIVED' => 'rgba(149, 165, 166, 0.8)',
        ];
        $bgcolors = [];

        foreach ($records as $record) {
            $labels[] = $record->status ?: 'UNKNOWN';
            $data[] = (int) $record->cnt;
            $bgcolors[] = $colors[$record->status] ?? 'rgba(189, 195, 199, 0.8)';
        }

        return [
            'labels' => $labels,
            'datasets' => [['data' => $data, 'backgroundColor' => $bgcolors]],
        ];
    }
}

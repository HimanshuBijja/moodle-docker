<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_rate_limits extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_rate_limits'; }
    public function get_type(): string { return 'bar'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $sql = "SELECT limit_type, SUM(attempt_count) as total_attempts, COUNT(*) as affected_users
                  FROM {auth_secureotp_rate_limit}
              GROUP BY limit_type
              ORDER BY total_attempts DESC";
        $records = $DB->get_records_sql($sql);

        $labels = [];
        $attempts = [];
        $users = [];

        foreach ($records as $record) {
            $labels[] = str_replace('_', ' ', $record->limit_type);
            $attempts[] = (int) $record->total_attempts;
            $users[] = (int) $record->affected_users;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => get_string('total_attempts', 'local_analysis_dashboard'),
                    'data' => $attempts,
                    'backgroundColor' => 'rgba(231, 76, 60, 0.8)',
                ],
                [
                    'label' => get_string('affected_users', 'local_analysis_dashboard'),
                    'data' => $users,
                    'backgroundColor' => 'rgba(52, 152, 219, 0.8)',
                ],
            ],
        ];
    }
}

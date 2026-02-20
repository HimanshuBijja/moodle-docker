<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_audit_severity extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_audit_severity'; }
    public function get_type(): string { return 'bar'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $sql = "SELECT severity, COUNT(*) as cnt
                  FROM {auth_secureotp_audit}
              GROUP BY severity
              ORDER BY cnt DESC";
        $records = $DB->get_records_sql($sql);

        $labels = [];
        $data = [];
        $colors = [
            'INFO' => 'rgba(52, 152, 219, 0.8)',
            'WARNING' => 'rgba(241, 196, 15, 0.8)',
            'CRITICAL' => 'rgba(231, 76, 60, 0.8)',
        ];
        $bgcolors = [];

        foreach ($records as $record) {
            $labels[] = $record->severity ?: 'UNKNOWN';
            $data[] = (int) $record->cnt;
            $bgcolors[] = $colors[$record->severity] ?? 'rgba(189, 195, 199, 0.8)';
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => get_string('event_count', 'local_analysis_dashboard'),
                'data' => $data,
                'backgroundColor' => $bgcolors,
            ]],
        ];
    }
}

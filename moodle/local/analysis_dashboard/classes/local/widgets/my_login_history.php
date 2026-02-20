<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\base_widget;

class my_login_history extends base_widget {
    public function get_name(): string { return 'widget_my_login_history'; }
    public function get_type(): string { return 'line'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:viewown'; }
    public function get_supported_context_levels(): array { return [CONTEXT_USER]; }

    public function get_data(array $params = []): array {
        global $DB;
        $userid = (int) ($params['userid'] ?? 0);
        if (!$userid) {
            return ['labels' => [], 'datasets' => []];
        }

        $starttime = time() - (30 * DAYSECS);
        $sql = "SELECT id, timecreated
                  FROM {logstore_standard_log}
                 WHERE userid = :userid
                   AND action = 'loggedin'
                   AND target = 'user'
                   AND timecreated >= :starttime
              ORDER BY timecreated ASC";
        $records = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'starttime' => $starttime,
        ]);

        // Build daily counts.
        $dailycounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $dailycounts[$day] = 0;
        }

        foreach ($records as $record) {
            $day = date('Y-m-d', $record->timecreated);
            if (isset($dailycounts[$day])) {
                $dailycounts[$day]++;
            }
        }

        return [
            'labels' => array_keys($dailycounts),
            'datasets' => [[
                'label' => get_string('logins', 'local_analysis_dashboard'),
                'data' => array_values($dailycounts),
            ]],
        ];
    }
}

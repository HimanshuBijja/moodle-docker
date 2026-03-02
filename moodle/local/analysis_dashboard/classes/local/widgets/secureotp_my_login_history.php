<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_my_login_history extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_my_login_history'; }
    public function get_type(): string { return 'line'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:widget_secureotp_my_login_history'; }
    public function get_supported_context_levels(): array { return [CONTEXT_USER]; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;
        $userid = (int) ($params['userid'] ?? 0);
        if (!$userid) {
            return ['labels' => [], 'datasets' => []];
        }

        $starttime = time() - (30 * DAYSECS);
        $sql = "SELECT id, event_type, timecreated
                  FROM {auth_secureotp_audit}
                 WHERE userid = :userid
                   AND event_type IN ('LOGIN_SUCCESS', 'OTP_VERIFIED')
                   AND timecreated >= :starttime
              ORDER BY timecreated ASC";
        $records = $DB->get_records_sql($sql, ['userid' => $userid, 'starttime' => $starttime]);

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
                'label' => get_string('otp_logins', 'local_analysis_dashboard'),
                'data' => array_values($dailycounts),
            ]],
        ];
    }
}

<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_security_summary extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_security_summary'; }
    public function get_type(): string { return 'counter'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $locked = $DB->count_records('auth_secureotp_security', ['is_locked' => 1]);
        $otp = $DB->count_records('auth_secureotp_security', ['otp_enabled' => 1]);
        $pwdenabled = $DB->count_records('auth_secureotp_security', ['password_enabled' => 1]);

        // Failed today.
        $todaystart = strtotime('today midnight');
        $sql = "SELECT COALESCE(SUM(failed_attempts), 0)
                  FROM {auth_secureotp_security}
                 WHERE last_failed_at >= :todaystart";
        $failedtoday = $DB->count_records_sql($sql, ['todaystart' => $todaystart]);

        return [
            'items' => [
                ['label' => get_string('locked_accounts', 'local_analysis_dashboard'), 'value' => $locked],
                ['label' => get_string('otp_enabled', 'local_analysis_dashboard'), 'value' => $otp],
                ['label' => get_string('password_enabled', 'local_analysis_dashboard'), 'value' => $pwdenabled],
                ['label' => get_string('failed_today', 'local_analysis_dashboard'), 'value' => $failedtoday],
            ],
        ];
    }
}

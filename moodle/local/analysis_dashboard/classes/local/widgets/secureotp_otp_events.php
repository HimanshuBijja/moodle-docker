<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_otp_events extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_otp_events'; }
    public function get_type(): string { return 'line'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $starttime = time() - (30 * DAYSECS);
        $eventypes = ['OTP_SENT', 'OTP_VERIFIED', 'LOGIN_SUCCESS', 'LOGIN_FAILED'];

        // Build daily counts per event type.
        $dailydata = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $dailydata[$day] = array_fill_keys($eventypes, 0);
        }

        $sql = "SELECT id, event_type, timecreated
                  FROM {auth_secureotp_audit}
                 WHERE timecreated >= :starttime
              ORDER BY timecreated";
        $records = $DB->get_records_sql($sql, ['starttime' => $starttime]);

        foreach ($records as $record) {
            $day = date('Y-m-d', $record->timecreated);
            if (isset($dailydata[$day]) && isset($dailydata[$day][$record->event_type])) {
                $dailydata[$day][$record->event_type]++;
            }
        }

        $labels = array_keys($dailydata);
        $datasets = [];
        $colors = [
            'OTP_SENT' => 'rgba(52, 152, 219, 1)',
            'OTP_VERIFIED' => 'rgba(46, 204, 113, 1)',
            'LOGIN_SUCCESS' => 'rgba(155, 89, 182, 1)',
            'LOGIN_FAILED' => 'rgba(231, 76, 60, 1)',
        ];

        foreach ($eventypes as $type) {
            $data = [];
            foreach ($dailydata as $daydata) {
                $data[] = $daydata[$type];
            }
            $datasets[] = [
                'label' => $type,
                'data' => $data,
                'borderColor' => $colors[$type],
                'fill' => false,
            ];
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }
}

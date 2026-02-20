<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class course_students_by_rank extends secureotp_base {
    public function get_name(): string { return 'widget_course_students_by_rank'; }
    public function get_type(): string { return 'bar'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:viewcourse'; }
    public function get_supported_context_levels(): array { return [CONTEXT_COURSE]; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;
        $courseid = (int) ($params['courseid'] ?? 0);
        if (!$courseid) {
            return ['labels' => [], 'datasets' => []];
        }

        $sql = "SELECT COALESCE(ud.current_rank, 'Unknown') as rankname, COUNT(*) as cnt
                  FROM {auth_secureotp_userdata} ud
                  JOIN {user_enrolments} ue ON ue.userid = ud.userid
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid AND ue.status = 0
              GROUP BY ud.current_rank
              ORDER BY cnt DESC
                 LIMIT 15";
        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        $labels = [];
        $data = [];
        foreach ($records as $r) {
            $labels[] = $r->rankname;
            $data[] = (int) $r->cnt;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => get_string('students', 'local_analysis_dashboard'),
                'data' => $data,
                'backgroundColor' => 'rgba(155, 89, 182, 0.8)',
            ]],
        ];
    }
}

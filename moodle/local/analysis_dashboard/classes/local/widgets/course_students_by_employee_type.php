<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class course_students_by_employee_type extends secureotp_base {
    public function get_name(): string { return 'widget_course_students_by_employee_type'; }
    public function get_type(): string { return 'pie'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:widget_course_students_by_employee_type'; }
    public function get_supported_context_levels(): array { return [CONTEXT_COURSE]; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;
        $courseid = (int) ($params['courseid'] ?? 0);
        if (!$courseid) {
            return ['labels' => [], 'datasets' => []];
        }

        $sql = "SELECT COALESCE(ud.employee_type, 'Unknown') as etype, COUNT(*) as cnt
                  FROM {auth_secureotp_userdata} ud
                  JOIN {user_enrolments} ue ON ue.userid = ud.userid
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid AND ue.status = 0
              GROUP BY ud.employee_type
              ORDER BY cnt DESC";
        $records = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        $labels = [];
        $data = [];
        $palette = ['rgba(52,152,219,0.8)', 'rgba(46,204,113,0.8)', 'rgba(155,89,182,0.8)', 'rgba(241,196,15,0.8)', 'rgba(231,76,60,0.8)'];
        $bgcolors = [];
        $i = 0;
        foreach ($records as $r) {
            $labels[] = $r->etype;
            $data[] = (int) $r->cnt;
            $bgcolors[] = $palette[$i % count($palette)];
            $i++;
        }

        return ['labels' => $labels, 'datasets' => [['data' => $data, 'backgroundColor' => $bgcolors]]];
    }
}

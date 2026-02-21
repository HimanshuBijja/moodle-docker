<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\base_widget;

class my_course_progress extends base_widget {
    public function get_name(): string { return 'widget_my_course_progress'; }
    public function get_type(): string { return 'bar'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:viewown'; }
    public function get_supported_context_levels(): array { return [CONTEXT_USER]; }

    public function get_data(array $params = []): array {
        global $DB;
        $userid = (int) ($params['userid'] ?? 0);
        if (!$userid) {
            return ['labels' => [], 'datasets' => []];
        }

        // Get enrolled courses.
        $sql = "SELECT DISTINCT e.courseid, c.shortname, c.fullname
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                  JOIN {course} c ON c.id = e.courseid
                 WHERE ue.userid = :userid AND ue.status = 0 AND c.id != 1
              ORDER BY c.shortname";
        $courses = $DB->get_records_sql($sql, ['userid' => $userid], 0, 15);

        $labels = [];
        $data = [];

        foreach ($courses as $course) {
            // Get total trackable activities.
            $sql = "SELECT COUNT(*)
                      FROM {course_modules} cm
                     WHERE cm.course = :courseid AND cm.completion > 0 AND cm.deletioninprogress = 0";
            $total = $DB->count_records_sql($sql, ['courseid' => $course->courseid]);

            if ($total == 0) {
                $pct = 0;
            } else {
                $sql = "SELECT COUNT(*)
                          FROM {course_modules_completion} cmc
                          JOIN {course_modules} cm ON cm.id = cmc.coursemoduleid
                         WHERE cmc.userid = :userid AND cm.course = :courseid AND cmc.completionstate > 0";
                $done = $DB->count_records_sql($sql, ['userid' => $userid, 'courseid' => $course->courseid]);
                $pct = round(($done / $total) * 100);
            }

            $labels[] = $course->shortname;
            $data[] = $pct;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => get_string('completion_pct', 'local_analysis_dashboard'), 'data' => $data],
            ],
        ];
    }
}

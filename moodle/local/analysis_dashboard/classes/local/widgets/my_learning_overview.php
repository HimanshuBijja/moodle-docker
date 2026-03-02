<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\base_widget;

class my_learning_overview extends base_widget {
    public function get_name(): string { return 'widget_my_learning_overview'; }
    public function get_type(): string { return 'counter'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:widget_my_learning_overview'; }
    public function get_supported_context_levels(): array { return [CONTEXT_USER]; }

    public function get_data(array $params = []): array {
        global $DB;
        $userid = (int) ($params['userid'] ?? 0);
        if (!$userid) {
            return ['items' => []];
        }

        // Count enrolled courses.
        $sql = "SELECT COUNT(DISTINCT e.courseid)
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE ue.userid = :userid AND ue.status = 0";
        $enrolled = $DB->count_records_sql($sql, ['userid' => $userid]);

        // Count completed courses.
        $completed = $DB->count_records('course_completions', [
            'userid' => $userid,
            'timecompleted' => null,
        ]);
        // Actually we need completed where timecompleted IS NOT NULL.
        $sql = "SELECT COUNT(*)
                  FROM {course_completions}
                 WHERE userid = :userid AND timecompleted IS NOT NULL";
        $completed = $DB->count_records_sql($sql, ['userid' => $userid]);

        $inprogress = max(0, $enrolled - $completed);

        return [
            'items' => [
                ['label' => get_string('enrolled_courses', 'local_analysis_dashboard'), 'value' => $enrolled],
                ['label' => get_string('completed_courses', 'local_analysis_dashboard'), 'value' => $completed],
                ['label' => get_string('inprogress_courses', 'local_analysis_dashboard'), 'value' => $inprogress],
            ],
        ];
    }
}

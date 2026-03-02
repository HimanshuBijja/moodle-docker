<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\base_widget;

class overall_completion_status extends base_widget {
    public function get_name(): string { return 'widget_overall_completion_status'; }
    public function get_type(): string { return 'doughnut'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:widget_overall_completion_status'; }
    public function get_supported_context_levels(): array { return [CONTEXT_USER]; }

    public function get_data(array $params = []): array {
        global $DB;
        $userid = (int) ($params['userid'] ?? 0);
        if (!$userid) {
            return ['labels' => [], 'datasets' => []];
        }

        // Get all trackable activities across enrolled courses.
        $sql = "SELECT cm.id, cm.course
                  FROM {course_modules} cm
                  JOIN {enrol} e ON e.courseid = cm.course
                  JOIN {user_enrolments} ue ON ue.enrolid = e.id
                 WHERE ue.userid = :userid AND ue.status = 0
                   AND cm.completion > 0 AND cm.deletioninprogress = 0
                   AND cm.course != 1";
        $activities = $DB->get_records_sql($sql, ['userid' => $userid]);
        $totalactivities = count($activities);

        if ($totalactivities == 0) {
            return [
                'labels' => [],
                'datasets' => [],
                'message' => get_string('no_trackable_activities', 'local_analysis_dashboard'),
            ];
        }

        $cmids = array_keys($activities);

        // Get completions.
        list($insql, $inparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED);
        $inparams['userid'] = $userid;
        $sql = "SELECT coursemoduleid, completionstate
                  FROM {course_modules_completion}
                 WHERE userid = :userid AND coursemoduleid $insql";
        $completions = $DB->get_records_sql($sql, $inparams);

        $completed = 0;
        $pending = 0;
        foreach ($completions as $cmc) {
            if ($cmc->completionstate > 0) {
                $completed++;
            } else {
                $pending++;
            }
        }
        $notattempted = $totalactivities - $completed - $pending;

        return [
            'labels' => [
                get_string('completed', 'local_analysis_dashboard'),
                get_string('pending', 'local_analysis_dashboard'),
                get_string('not_attempted', 'local_analysis_dashboard'),
            ],
            'datasets' => [[
                'data' => [$completed, $pending, $notattempted],
                'backgroundColor' => [
                    'rgba(46, 204, 113, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(189, 195, 199, 0.8)',
                ],
            ]],
        ];
    }
}

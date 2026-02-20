<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\base_widget;

class my_recent_activity extends base_widget {
    public function get_name(): string { return 'widget_my_recent_activity'; }
    public function get_type(): string { return 'table'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:viewown'; }
    public function get_supported_context_levels(): array { return [CONTEXT_USER]; }

    public function get_data(array $params = []): array {
        global $DB;
        $userid = (int) ($params['userid'] ?? 0);
        if (!$userid) {
            return ['headers' => [], 'rows' => []];
        }

        $sevendays = time() - (7 * DAYSECS);
        $actions = ['viewed', 'submitted', 'updated', 'created', 'graded'];
        list($insql, $inparams) = $DB->get_in_or_equal($actions, SQL_PARAMS_NAMED);
        $inparams['userid'] = $userid;
        $inparams['starttime'] = $sevendays;

        $sql = "SELECT l.id, l.action, l.target, l.objecttable, l.timecreated,
                       c.shortname as coursename
                  FROM {logstore_standard_log} l
             LEFT JOIN {course} c ON c.id = l.courseid
                 WHERE l.userid = :userid
                   AND l.action $insql
                   AND l.timecreated >= :starttime
                   AND l.courseid > 1
              ORDER BY l.timecreated DESC
                 LIMIT 50";
        $records = $DB->get_records_sql($sql, $inparams);

        $rows = [];
        foreach ($records as $record) {
            $rows[] = [
                $record->coursename ?: '-',
                ucfirst($record->target),
                userdate($record->timecreated),
                ucfirst($record->action),
            ];
        }

        return [
            'headers' => [
                get_string('course'),
                get_string('activity'),
                get_string('time'),
                get_string('action', 'local_analysis_dashboard'),
            ],
            'rows' => $rows,
        ];
    }
}

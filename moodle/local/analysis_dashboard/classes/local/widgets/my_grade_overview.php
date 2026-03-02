<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\base_widget;

class my_grade_overview extends base_widget {
    public function get_name(): string { return 'widget_my_grade_overview'; }
    public function get_type(): string { return 'bar'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:widget_my_grade_overview'; }
    public function get_supported_context_levels(): array { return [CONTEXT_USER]; }

    public function get_data(array $params = []): array {
        global $DB;
        $userid = (int) ($params['userid'] ?? 0);
        if (!$userid) {
            return ['labels' => [], 'datasets' => []];
        }

        // Get course grades for user.
        $sql = "SELECT gi.courseid, c.shortname, gg.finalgrade, gi.grademax
                  FROM {grade_grades} gg
                  JOIN {grade_items} gi ON gi.id = gg.itemid
                  JOIN {course} c ON c.id = gi.courseid
                 WHERE gg.userid = :userid
                   AND gi.itemtype = 'course'
                   AND gg.finalgrade IS NOT NULL
              ORDER BY c.shortname
                 LIMIT 15";
        $grades = $DB->get_records_sql($sql, ['userid' => $userid]);

        $labels = [];
        $data = [];

        foreach ($grades as $grade) {
            $max = max((float) $grade->grademax, 1);
            $pct = round(((float) $grade->finalgrade / $max) * 100, 1);
            $labels[] = $grade->shortname;
            $data[] = $pct;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => get_string('grade_pct', 'local_analysis_dashboard'),
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                ],
            ],
        ];
    }
}

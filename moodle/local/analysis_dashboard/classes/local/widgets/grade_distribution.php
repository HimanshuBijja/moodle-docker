<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\base_widget;

/**
 * Grade Distribution widget.
 *
 * Displays a bar chart of grade distribution in 10% buckets for a course.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_distribution extends base_widget {

    public function get_name(): string {
        return 'widget_grade_distribution';
    }

    public function get_type(): string {
        return 'bar';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:widget_grade_distribution';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'grade_distribution';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['labels' => [], 'datasets' => []];
        }

        // Get the course grade item.
        $gradeitem = $DB->get_record('grade_items', [
            'courseid' => $courseid,
            'itemtype' => 'course',
        ], 'id, grademax');

        if (!$gradeitem) {
            return ['labels' => [], 'datasets' => []];
        }

        // Get all final grades.
        $grades = $DB->get_records('grade_grades', [
            'itemid' => $gradeitem->id,
        ], '', 'id, finalgrade');

        // Bucket grades into 10% ranges.
        $buckets = array_fill(0, 10, 0);
        $notgraded = 0;
        $grademax = max((float) $gradeitem->grademax, 1); // Avoid division by zero.

        foreach ($grades as $grade) {
            if ($grade->finalgrade === null) {
                $notgraded++;
                continue;
            }
            $pct = ($grade->finalgrade / $grademax) * 100;
            $bucket = min((int) floor($pct / 10), 9); // 100% goes in bucket 9.
            $buckets[$bucket]++;
        }

        $labels = [];
        for ($i = 0; $i < 10; $i++) {
            $low = $i * 10;
            $high = ($i + 1) * 10;
            $labels[] = $low . '-' . $high . '%';
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => get_string('student_count', 'local_analysis_dashboard'),
                    'data' => $buckets,
                    'backgroundColor' => 'rgba(102, 126, 234, 0.7)',
                    'borderColor' => 'rgba(102, 126, 234, 1)',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}

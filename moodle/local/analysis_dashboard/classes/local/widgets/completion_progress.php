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
 * Completion Progress widget.
 *
 * Displays a doughnut chart of course completion status (completed, in-progress, not started).
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_progress extends base_widget {

    public function get_name(): string {
        return 'widget_completion_progress';
    }

    public function get_type(): string {
        return 'doughnut';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:viewcourse';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'completion_progress';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['labels' => [], 'datasets' => []];
        }

        // Check if completion is enabled for this course.
        $course = $DB->get_record('course', ['id' => $courseid], 'id, enablecompletion');
        if (empty($course->enablecompletion)) {
            return [
                'labels' => [],
                'datasets' => [],
                'message' => get_string('completion_not_enabled', 'local_analysis_dashboard'),
            ];
        }

        // Get total enrolled users.
        $sql = "SELECT COUNT(DISTINCT ue.userid)
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid
                   AND ue.status = 0";
        $totalenrolled = $DB->count_records_sql($sql, ['courseid' => $courseid]);

        if ($totalenrolled == 0) {
            return ['labels' => [], 'datasets' => []];
        }

        // Completed users.
        $completed = $DB->count_records('course_completions', [
            'course' => $courseid,
            'timecompleted' => null,
        ]);
        // Actually, timecompleted IS NOT NULL means completed.
        $sql = "SELECT COUNT(*)
                  FROM {course_completions}
                 WHERE course = :courseid
                   AND timecompleted IS NOT NULL";
        $completed = $DB->count_records_sql($sql, ['courseid' => $courseid]);

        // In-progress: have a record but not completed.
        $sql = "SELECT COUNT(*)
                  FROM {course_completions}
                 WHERE course = :courseid
                   AND timecompleted IS NULL";
        $inprogress = $DB->count_records_sql($sql, ['courseid' => $courseid]);

        // Not started: enrolled but no completion record at all.
        $notstarted = $totalenrolled - $completed - $inprogress;
        if ($notstarted < 0) {
            $notstarted = 0;
        }

        return [
            'labels' => [
                get_string('completed', 'local_analysis_dashboard'),
                get_string('in_progress', 'local_analysis_dashboard'),
                get_string('not_started', 'local_analysis_dashboard'),
            ],
            'datasets' => [
                [
                    'data' => [$completed, $inprogress, $notstarted],
                    'backgroundColor' => ['#28a745', '#ffc107', '#dc3545'],
                ],
            ],
        ];
    }
}

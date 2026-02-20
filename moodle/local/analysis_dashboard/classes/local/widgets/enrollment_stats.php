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
 * Enrollment Stats widget.
 *
 * Displays the number of enrolled, active, and inactive users in a course.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrollment_stats extends base_widget {

    public function get_name(): string {
        return 'widget_enrollment_stats';
    }

    public function get_type(): string {
        return 'counter';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:viewcourse';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'enrollment_stats';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['items' => []];
        }

        $now = time();

        // Total enrolled.
        $sql = "SELECT COUNT(DISTINCT ue.userid)
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid";
        $total = $DB->count_records_sql($sql, ['courseid' => $courseid]);

        // Active enrolled (status = 0 and within time bounds).
        $sql = "SELECT COUNT(DISTINCT ue.userid)
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid
                   AND ue.status = 0
                   AND (ue.timestart = 0 OR ue.timestart <= :now1)
                   AND (ue.timeend = 0 OR ue.timeend >= :now2)";
        $active = $DB->count_records_sql($sql, [
            'courseid' => $courseid,
            'now1' => $now,
            'now2' => $now,
        ]);

        $inactive = $total - $active;

        return [
            'items' => [
                ['label' => get_string('total_enrolled', 'local_analysis_dashboard'), 'value' => $total],
                ['label' => get_string('active_enrolled', 'local_analysis_dashboard'), 'value' => $active],
                ['label' => get_string('inactive_enrolled', 'local_analysis_dashboard'), 'value' => $inactive],
            ],
        ];
    }
}

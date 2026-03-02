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
 * Course Visits Over Time widget.
 *
 * Displays a line chart of daily unique visitors to a course over the last 30 days.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_visits extends base_widget {

    public function get_name(): string {
        return 'widget_course_visits';
    }

    public function get_type(): string {
        return 'line';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:widget_course_visits';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'course_visits';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['labels' => [], 'datasets' => []];
        }

        $days = 30;
        $starttime = strtotime("-{$days} days midnight");

        // Get log entries for the course.
        $sql = "SELECT id, timecreated, userid
                  FROM {logstore_standard_log}
                 WHERE courseid = :courseid
                   AND action = 'viewed'
                   AND target = 'course'
                   AND timecreated >= :starttime
              ORDER BY timecreated ASC";
        $records = $DB->get_records_sql($sql, [
            'courseid' => $courseid,
            'starttime' => $starttime,
        ]);

        // Group by date and count unique users per day.
        $dailyvisitors = [];
        foreach ($records as $record) {
            $date = date('Y-m-d', $record->timecreated);
            if (!isset($dailyvisitors[$date])) {
                $dailyvisitors[$date] = [];
            }
            $dailyvisitors[$date][$record->userid] = true;
        }

        // Build labels and data with zero-fill.
        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($date));
            $data[] = isset($dailyvisitors[$date]) ? count($dailyvisitors[$date]) : 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => get_string('daily_course_visits', 'local_analysis_dashboard'),
                    'data' => $data,
                    'borderColor' => 'rgba(102, 126, 234, 1)',
                    'backgroundColor' => 'rgba(102, 126, 234, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
        ];
    }
}

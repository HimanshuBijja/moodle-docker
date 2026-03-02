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
 * Learner Activity Heatmap widget.
 *
 * Displays a 7×24 grid showing event counts grouped by day-of-week and hour-of-day.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_heatmap extends base_widget {

    public function get_name(): string {
        return 'widget_activity_heatmap';
    }

    public function get_type(): string {
        return 'heatmap';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:widget_activity_heatmap';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'activity_heatmap';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['rows' => 7, 'cols' => 24, 'data' => [], 'labels_rows' => [], 'labels_cols' => []];
        }

        $starttime = strtotime('-30 days');

        // Get log entries.
        $sql = "SELECT id, timecreated
                  FROM {logstore_standard_log}
                 WHERE courseid = :courseid
                   AND timecreated >= :starttime
                   AND userid > 0";
        $records = $DB->get_records_sql($sql, [
            'courseid' => $courseid,
            'starttime' => $starttime,
        ]);

        // Initialize 7×24 grid (Monday=0 to Sunday=6, hours 0-23).
        $grid = [];
        for ($day = 0; $day < 7; $day++) {
            $grid[$day] = array_fill(0, 24, 0);
        }

        // Fill grid.
        foreach ($records as $record) {
            // PHP date('N') returns 1=Monday to 7=Sunday, subtract 1 for 0-indexed.
            $dayofweek = (int) date('N', $record->timecreated) - 1;
            $hour = (int) date('G', $record->timecreated);
            $grid[$dayofweek][$hour]++;
        }

        $daylabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $hourlabels = [];
        for ($h = 0; $h < 24; $h++) {
            $hourlabels[] = sprintf('%02d:00', $h);
        }

        return [
            'rows' => 7,
            'cols' => 24,
            'data' => $grid,
            'labels_rows' => $daylabels,
            'labels_cols' => $hourlabels,
        ];
    }
}

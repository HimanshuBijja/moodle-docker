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
 * Enrolled Methods bar chart widget.
 *
 * Displays a breakdown of enrolment methods and user counts
 * across the entire site.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolled_methods extends base_widget {

    /**
     * Get the widget display name.
     *
     * @return string Language string key.
     */
    public function get_name(): string {
        return 'widget_enrolled_methods';
    }

    /**
     * Get the widget type.
     *
     * @return string Widget type.
     */
    public function get_type(): string {
        return 'bar';
    }

    /**
     * Get the required capability.
     *
     * @return string Capability string.
     */
    public function get_required_capability(): string {
        return 'local/analysis_dashboard:widget_enrolled_methods';
    }

    /**
     * Get the cache key.
     *
     * @return string Cache key.
     */
    public function get_cache_key(): string {
        return 'enrolled_methods';
    }

    /**
     * Get enrolment methods data.
     *
     * Queries enrol + user_enrolments to count distinct users
     * per enrolment method across all courses.
     *
     * @param array $params Optional parameters (unused).
     * @return array Widget data with labels and datasets for Chart.js bar chart.
     */
    public function get_data(array $params = []): array {
        global $DB;

        $sql = "SELECT e.enrol,
                       COUNT(DISTINCT ue.userid) AS user_count
                  FROM {enrol} e
                  JOIN {user_enrolments} ue ON ue.enrolid = e.id
              GROUP BY e.enrol
              ORDER BY user_count DESC";

        $records = $DB->get_records_sql($sql);

        $labels = [];
        $data = [];

        foreach ($records as $record) {
            // Capitalize the enrolment method name for display.
            $labels[] = ucfirst($record->enrol);
            $data[] = (int) $record->user_count;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => get_string('users_enrolled', 'local_analysis_dashboard'),
                    'data'  => $data,
                ],
            ],
        ];
    }
}

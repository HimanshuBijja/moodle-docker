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
 * Total Courses counter widget.
 *
 * Displays counts of visible courses, hidden courses, and categories.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class total_courses extends base_widget {

    /**
     * Get the widget display name.
     *
     * @return string Language string key.
     */
    public function get_name(): string {
        return 'widget_total_courses';
    }

    /**
     * Get the widget type.
     *
     * @return string Widget type.
     */
    public function get_type(): string {
        return 'counter';
    }

    /**
     * Get the required capability.
     *
     * @return string Capability string.
     */
    public function get_required_capability(): string {
        return 'local/analysis_dashboard:viewsite';
    }

    /**
     * Get the cache key.
     *
     * @return string Cache key.
     */
    public function get_cache_key(): string {
        return 'total_courses';
    }

    /**
     * Get course count data.
     *
     * @param array $params Optional parameters (unused).
     * @return array Widget data with items array.
     */
    public function get_data(array $params = []): array {
        global $DB;

        // Visible courses (exclude site course, id=1).
        $visible = $DB->count_records_select('course', 'visible = 1 AND id != :siteid', ['siteid' => SITEID]);

        // Hidden courses.
        $hidden = $DB->count_records_select('course', 'visible = 0');

        $items = [
            [
                'label' => get_string('visible_courses', 'local_analysis_dashboard'),
                'value' => (int) $visible,
            ],
            [
                'label' => get_string('hidden_courses', 'local_analysis_dashboard'),
                'value' => (int) $hidden,
            ],
        ];

        // Conditionally include Total Categories based on admin setting.
        $showcategories = get_config('local_analysis_dashboard', 'show_total_categories');
        if ($showcategories === false || $showcategories) {
            $categories = $DB->count_records('course_categories');
            $items[] = [
                'label' => get_string('total_categories', 'local_analysis_dashboard'),
                'value' => (int) $categories,
            ];
        }

        return ['items' => $items];
    }
}

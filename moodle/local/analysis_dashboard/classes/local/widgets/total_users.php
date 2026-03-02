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
 * Total Users counter widget.
 *
 * Displays counts of active, suspended, and deleted users.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class total_users extends base_widget {

    /**
     * Get the widget display name.
     *
     * @return string Language string key.
     */
    public function get_name(): string {
        return 'widget_total_users';
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
        return 'local/analysis_dashboard:widget_total_users';
    }

    /**
     * Get the cache key.
     *
     * @return string Cache key.
     */
    public function get_cache_key(): string {
        return 'total_users';
    }

    /**
     * Get user count data.
     *
     * @param array $params Optional parameters (unused).
     * @return array Widget data with items array.
     */
    public function get_data(array $params = []): array {
        global $DB;

        // Active users (not deleted, not suspended, excluding guest user id=1).
        $active = $DB->count_records_select('user', 'deleted = 0 AND suspended = 0 AND id > 1');

        // Suspended users (not deleted but suspended).
        $suspended = $DB->count_records_select('user', 'deleted = 0 AND suspended = 1');

        // Deleted users.
        $deleted = $DB->count_records_select('user', 'deleted = 1');

        return [
            'items' => [
                [
                    'label' => get_string('active_users', 'local_analysis_dashboard'),
                    'value' => (int) $active,
                ],
                [
                    'label' => get_string('suspended_users', 'local_analysis_dashboard'),
                    'value' => (int) $suspended,
                ],
                [
                    'label' => get_string('deleted_users', 'local_analysis_dashboard'),
                    'value' => (int) $deleted,
                ],
            ],
        ];
    }
}

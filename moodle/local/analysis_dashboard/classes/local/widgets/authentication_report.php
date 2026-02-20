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
 * Authentication Report bar chart widget.
 *
 * Displays login method breakdown and failed attempt counts
 * from the standard logstore over the last 30 days.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class authentication_report extends base_widget {

    /**
     * Get the widget display name.
     *
     * @return string Language string key.
     */
    public function get_name(): string {
        return 'widget_authentication_report';
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
        return 'local/analysis_dashboard:viewsite';
    }

    /**
     * Get the cache key.
     *
     * @return string Cache key.
     */
    public function get_cache_key(): string {
        return 'authentication_report';
    }

    /**
     * Get authentication report data.
     *
     * Queries the standard logstore for login events over the last 30 days,
     * grouped by authentication method.
     *
     * @param array $params Optional parameters (unused).
     * @return array Widget data with labels and datasets for Chart.js.
     */
    public function get_data(array $params = []): array {
        global $DB;

        $since = time() - (30 * DAYSECS);

        // Manual logins.
        $manual = $DB->count_records_select(
            'logstore_standard_log',
            "eventname = :event AND timecreated > :since AND other LIKE :auth",
            [
                'event' => '\\core\\event\\user_loggedin',
                'since' => $since,
                'auth'  => '%"manual"%',
            ]
        );

        // Count logins by non-manual auth methods.
        $sql = "SELECT COUNT(id)
                  FROM {logstore_standard_log}
                 WHERE eventname = :event
                   AND timecreated > :since
                   AND other NOT LIKE :auth";
        $otherlogins = (int) $DB->count_records_sql($sql, [
            'event' => '\\core\\event\\user_loggedin',
            'since' => $since,
            'auth'  => '%"manual"%',
        ]);

        // Failed login attempts.
        $failed = $DB->count_records_select(
            'logstore_standard_log',
            "eventname = :event AND timecreated > :since",
            [
                'event' => '\\core\\event\\user_login_failed',
                'since' => $since,
            ]
        );

        return [
            'labels' => [
                get_string('manual_logins', 'local_analysis_dashboard'),
                get_string('other_auth_logins', 'local_analysis_dashboard'),
                get_string('failed_login_attempts', 'local_analysis_dashboard'),
            ],
            'datasets' => [
                [
                    'label' => get_string('last_30_days', 'local_analysis_dashboard'),
                    'data'  => [(int) $manual, $otherlogins, (int) $failed],
                ],
            ],
        ];
    }
}

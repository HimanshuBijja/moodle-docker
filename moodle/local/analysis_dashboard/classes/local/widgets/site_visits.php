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
 * Site Visits Over Time line chart widget.
 *
 * Displays daily unique visitors from the logstore over the last N days.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class site_visits extends base_widget {

    /**
     * Get the widget display name.
     *
     * @return string Language string key.
     */
    public function get_name(): string {
        return 'widget_site_visits';
    }

    /**
     * Get the widget type.
     *
     * @return string Widget type.
     */
    public function get_type(): string {
        return 'line';
    }

    /**
     * Get the required capability.
     *
     * @return string Capability string.
     */
    public function get_required_capability(): string {
        return 'local/analysis_dashboard:widget_site_visits';
    }

    /**
     * Get the cache key.
     *
     * @return string Cache key.
     */
    public function get_cache_key(): string {
        return 'site_visits_30d';
    }

    /**
     * Get site visits data for Chart.js line chart.
     *
     * Queries the logstore for unique visitors per day over the configured period.
     * Uses PHP date grouping for database portability (avoids DB-specific date functions).
     *
     * @param array $params Optional parameters (unused).
     * @return array Data with 'labels' and 'datasets' arrays for Chart.js.
     */
    public function get_data(array $params = []): array {
        global $DB;

        $days = (int) get_config('local_analysis_dashboard', 'site_visits_days') ?: 30;
        $starttime = time() - ($days * DAYSECS);

        // Query logstore for all relevant events in timeframe.
        // We use userid + timecreated to count unique visitors per day.
        $sql = "SELECT id, userid, timecreated
                  FROM {logstore_standard_log}
                 WHERE timecreated > :starttime
                   AND userid > 0
                   AND action = 'viewed'
                   AND target = 'course'
              ORDER BY timecreated ASC";

        $records = $DB->get_records_sql($sql, ['starttime' => $starttime]);

        // Group unique visitors by date (PHP-side for DB portability).
        $dailyvisitors = [];
        foreach ($records as $record) {
            $date = date('Y-m-d', $record->timecreated);
            if (!isset($dailyvisitors[$date])) {
                $dailyvisitors[$date] = [];
            }
            $dailyvisitors[$date][$record->userid] = true;
        }

        // Build complete date range (fill zeros for days with no activity).
        $labels = [];
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', time() - ($i * DAYSECS));
            $labels[] = date('M j', time() - ($i * DAYSECS));
            $data[] = isset($dailyvisitors[$date]) ? count($dailyvisitors[$date]) : 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => get_string('daily_visits', 'local_analysis_dashboard'),
                    'data' => $data,
                ],
            ],
        ];
    }
}

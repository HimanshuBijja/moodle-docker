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
 * Disk Usage pie chart widget.
 *
 * Displays moodledata, database, and backup sizes as a pie chart.
 * Reads pre-computed values from config_plugins — never calls
 * filesystem functions directly.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class disk_usage extends base_widget {

    /**
     * Get the widget display name.
     *
     * @return string Language string key.
     */
    public function get_name(): string {
        return 'widget_disk_usage';
    }

    /**
     * Get the widget type.
     *
     * @return string Widget type.
     */
    public function get_type(): string {
        return 'pie';
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
        return 'disk_usage';
    }

    /**
     * Get widget data with caching via the diskusage MUC store.
     *
     * Overrides base_widget to use the dedicated diskusage cache
     * instead of the default sitestats cache.
     *
     * @param array $params Optional parameters.
     * @return array Widget data.
     */
    public function get_cached_data(array $params = []): array {
        $cache = \cache::make('local_analysis_dashboard', 'diskusage');
        $key = $this->get_cache_key();
        $data = $cache->get($key);

        if ($data !== false) {
            return $data;
        }

        $data = $this->get_data($params);
        $cache->set($key, $data);

        return $data;
    }

    /**
     * Invalidate the diskusage cache for this widget.
     *
     * @param array $params Optional parameters (unused).
     */
    public function invalidate_cache(array $params = []): void {
        $cache = \cache::make('local_analysis_dashboard', 'diskusage');
        $cache->delete($this->get_cache_key());
    }

    /**
     * Get disk usage data.
     *
     * Reads pre-computed values from config_plugins table.
     * The calculate_disk_usage scheduled task populates these.
     *
     * @param array $params Optional parameters (unused).
     * @return array Widget data with labels and datasets for Chart.js pie chart.
     */
    public function get_data(array $params = []): array {
        $moodledata = (int) get_config('local_analysis_dashboard', 'disk_moodledata_bytes');
        $database = (int) get_config('local_analysis_dashboard', 'disk_database_bytes');
        $backups = (int) get_config('local_analysis_dashboard', 'disk_backup_bytes');

        // If no data has been computed yet, return an informational message.
        if ($moodledata === 0 && $database === 0 && $backups === 0) {
            return [
                'labels' => [],
                'datasets' => [['data' => []]],
                'message' => get_string('disk_usage_not_computed', 'local_analysis_dashboard'),
            ];
        }

        // Convert bytes to MB for readable chart display.
        $tomega = function (int $bytes): float {
            return round($bytes / (1024 * 1024), 1);
        };

        return [
            'labels' => [
                get_string('moodledata_size', 'local_analysis_dashboard'),
                get_string('database_size', 'local_analysis_dashboard'),
                get_string('backup_size', 'local_analysis_dashboard'),
            ],
            'datasets' => [
                [
                    'data' => [$tomega($moodledata), $tomega($database), $tomega($backups)],
                ],
            ],
        ];
    }
}

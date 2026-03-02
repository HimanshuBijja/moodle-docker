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
 * Server Performance counter widget.
 *
 * Displays CPU load average, memory usage, and disk free space.
 * Reads pre-computed values from config_plugins — never calls
 * sys_getloadavg() or shell_exec() at render time.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class server_performance extends base_widget {

    /**
     * Get the widget display name.
     *
     * @return string Language string key.
     */
    public function get_name(): string {
        return 'widget_server_performance';
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
        return 'local/analysis_dashboard:widget_server_performance';
    }

    /**
     * Get the cache key.
     *
     * @return string Cache key.
     */
    public function get_cache_key(): string {
        return 'server_performance';
    }

    /**
     * Check whether server performance data collection is available.
     *
     * Requires sys_getloadavg() to be present (Linux/Mac).
     *
     * @return bool True if server metrics can be collected.
     */
    public function is_available(): bool {
        return function_exists('sys_getloadavg');
    }

    /**
     * Get server performance data.
     *
     * Reads pre-computed values from config_plugins table.
     * The calculate_disk_usage scheduled task populates these.
     *
     * @param array $params Optional parameters (unused).
     * @return array Widget data with items array for counter display.
     */
    public function get_data(array $params = []): array {
        $loadavg = get_config('local_analysis_dashboard', 'server_load_avg');
        $memory = get_config('local_analysis_dashboard', 'server_memory_usage');
        $diskfree = get_config('local_analysis_dashboard', 'server_disk_free');

        // If no data has been computed yet, show unavailable message.
        if ($loadavg === false && $memory === false && $diskfree === false) {
            return [
                'items' => [
                    [
                        'label' => get_string('server_data_unavailable', 'local_analysis_dashboard'),
                        'value' => '—',
                    ],
                ],
            ];
        }

        $items = [];

        if ($loadavg !== false) {
            $items[] = [
                'label' => get_string('cpu_load', 'local_analysis_dashboard'),
                'value' => $loadavg,
            ];
        }

        if ($memory !== false) {
            $items[] = [
                'label' => get_string('memory_usage', 'local_analysis_dashboard'),
                'value' => $memory,
            ];
        }

        if ($diskfree !== false) {
            // Convert bytes to human-readable.
            $diskfreebytes = (int) $diskfree;
            $items[] = [
                'label' => get_string('disk_free', 'local_analysis_dashboard'),
                'value' => display_size($diskfreebytes),
            ];
        }

        return ['items' => $items];
    }
}

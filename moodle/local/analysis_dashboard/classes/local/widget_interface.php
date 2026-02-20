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

namespace local_analysis_dashboard\local;

/**
 * Widget interface for the Analysis Dashboard.
 *
 * All widgets must implement this interface. Each widget represents
 * a self-contained data provider and renderer configuration.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface widget_interface {

    /**
     * Get the widget display name (language string key).
     *
     * @return string Language string key within local_analysis_dashboard.
     */
    public function get_name(): string;

    /**
     * Get the widget type for frontend rendering.
     *
     * @return string One of: 'counter', 'pie', 'bar', 'line', 'doughnut', 'heatmap', 'table', 'gauge'.
     */
    public function get_type(): string;

    /**
     * Get the widget data.
     *
     * @param array $params Optional parameters for data retrieval.
     * @return array Widget data in a format appropriate for the widget type.
     */
    public function get_data(array $params = []): array;

    /**
     * Get the unique cache key for this widget.
     *
     * @return string Cache key identifier.
     */
    public function get_cache_key(): string;

    /**
     * Get the cache TTL in seconds. Return 0 for no caching.
     *
     * @return int TTL in seconds.
     */
    public function get_cache_ttl(): int;

    /**
     * Get the required capability to view this widget.
     *
     * @return string Moodle capability string.
     */
    public function get_required_capability(): string;

    /**
     * Get the Moodle context for capability checks.
     *
     * @return \context Moodle context object.
     */
    public function get_context(): \context;

    /**
     * Check whether the widget is available.
     *
     * Used for graceful degradation (e.g., SecureOTP widgets when plugin not installed).
     *
     * @return bool True if the widget can render.
     */
    public function is_available(): bool;

    /**
     * Get the context levels this widget supports.
     *
     * Used to filter widgets for course-level vs site-level dashboards.
     *
     * @return array Array of CONTEXT_* constants (e.g., [CONTEXT_SYSTEM], [CONTEXT_COURSE]).
     */
    public function get_supported_context_levels(): array;
}

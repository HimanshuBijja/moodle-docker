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
 * Abstract base widget class.
 *
 * Provides common functionality for all widgets including
 * MUC caching, context defaults, and availability checks.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_widget implements widget_interface {

    /**
     * Get the unique cache key for this widget.
     *
     * Default implementation uses the class short name.
     *
     * @return string Cache key identifier.
     */
    public function get_cache_key(): string {
        $reflect = new \ReflectionClass($this);
        return $reflect->getShortName();
    }

    /**
     * Get the cache TTL in seconds.
     *
     * Default returns the site stats TTL from settings.
     *
     * @return int TTL in seconds.
     */
    public function get_cache_ttl(): int {
        return (int) get_config('local_analysis_dashboard', 'cache_ttl_sitestats') ?: 3600;
    }

    /**
     * Get the Moodle context for capability checks.
     *
     * Default returns system context.
     *
     * @return \context System context.
     */
    public function get_context(): \context {
        return \context_system::instance();
    }

    /**
     * Check whether the widget is available.
     *
     * Default returns true. Override for conditional availability.
     *
     * @return bool True if widget can render.
     */
    public function is_available(): bool {
        return true;
    }

    /**
     * Get the context levels this widget supports.
     *
     * Default returns [CONTEXT_SYSTEM]. Course widgets should override.
     *
     * @return array Array of CONTEXT_* constants.
     */
    public function get_supported_context_levels(): array {
        return [CONTEXT_SYSTEM];
    }

    /**
     * Get widget data with caching support.
     *
     * Attempts to retrieve data from MUC cache first.
     * Falls back to get_data() if cache miss or expired.
     * Uses the appropriate cache store based on context level.
     *
     * @param array $params Optional parameters.
     * @return array Widget data.
     */
    public function get_cached_data(array $params = []): array {
        $ttl = $this->get_cache_ttl();

        // Skip cache if TTL is 0.
        if ($ttl === 0) {
            return $this->get_data($params);
        }

        // Determine cache store and key based on context.
        $contextlevels = $this->get_supported_context_levels();
        if (in_array(CONTEXT_USER, $contextlevels) && !empty($params['userid'])) {
            $cachename = 'userstats';
            $key = $this->get_cache_key() . '_user_' . $params['userid'];
        } else if (in_array(CONTEXT_COURSE, $contextlevels) && !empty($params['courseid'])) {
            $cachename = 'coursestats';
            $key = $this->get_cache_key() . '_' . $params['courseid'];
        } else {
            $cachename = 'sitestats';
            $key = $this->get_cache_key();
        }

        $cache = \cache::make('local_analysis_dashboard', $cachename);
        $data = $cache->get($key);

        if ($data !== false) {
            return $data;
        }

        $data = $this->get_data($params);
        $cache->set($key, $data);

        return $data;
    }

    /**
     * Invalidate the cache for this widget.
     *
     * @param array $params Optional parameters (e.g., courseid for course widgets).
     */
    public function invalidate_cache(array $params = []): void {
        $contextlevels = $this->get_supported_context_levels();
        if (in_array(CONTEXT_USER, $contextlevels) && !empty($params['userid'])) {
            $cache = \cache::make('local_analysis_dashboard', 'userstats');
            $cache->delete($this->get_cache_key() . '_user_' . $params['userid']);
        } else if (in_array(CONTEXT_COURSE, $contextlevels) && !empty($params['courseid'])) {
            $cache = \cache::make('local_analysis_dashboard', 'coursestats');
            $cache->delete($this->get_cache_key() . '_' . $params['courseid']);
        } else {
            $cache = \cache::make('local_analysis_dashboard', 'sitestats');
            $cache->delete($this->get_cache_key());
        }
    }
}

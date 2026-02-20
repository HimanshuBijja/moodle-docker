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

namespace local_analysis_dashboard\task;

use core\task\scheduled_task;
use local_analysis_dashboard\local\widget_registry;

/**
 * Scheduled task to aggregate site statistics.
 *
 * Pre-warms the MUC cache with fresh data from all registered widgets.
 * Runs hourly to ensure dashboard loads are fast.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aggregate_site_stats extends scheduled_task {

    /**
     * Get the task name.
     *
     * @return string The task name.
     */
    public function get_name(): string {
        return get_string('task_aggregate_site_stats', 'local_analysis_dashboard');
    }

    /**
     * Execute the task.
     *
     * Iterates all registered widgets, invalidates their caches,
     * and pre-warms by calling get_cached_data().
     */
    public function execute(): void {
        $widgets = widget_registry::get_all();

        if (empty($widgets)) {
            mtrace('  No widgets registered — skipping.');
            return;
        }

        foreach ($widgets as $id => $widget) {
            $starttime = microtime(true);

            try {
                // Invalidate existing cache.
                $widget->invalidate_cache();

                // Pre-warm the cache.
                $data = $widget->get_cached_data();

                $elapsed = round(microtime(true) - $starttime, 3);
                mtrace("  Widget '{$id}' cached in {$elapsed}s.");
            } catch (\Throwable $e) {
                mtrace("  Widget '{$id}' failed: " . $e->getMessage());
            }
        }

        mtrace('  Site stats aggregation complete.');
    }
}

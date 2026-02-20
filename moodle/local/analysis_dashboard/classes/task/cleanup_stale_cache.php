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

/**
 * Scheduled task to clean up stale cache entries.
 *
 * Purges all MUC caches used by the Analysis Dashboard to prevent
 * buildup from deleted courses, users, or orphaned entries.
 *
 * Runs daily at 4:00 AM.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_stale_cache extends scheduled_task {

    /**
     * Get the task name.
     *
     * @return string The task name.
     */
    public function get_name(): string {
        return get_string('task_cleanup_stale_cache', 'local_analysis_dashboard');
    }

    /**
     * Execute the task.
     *
     * Purges all 4 plugin MUC caches: sitestats, coursestats,
     * diskusage, and userstats.
     */
    public function execute(): void {
        $starttime = microtime(true);

        $cachenames = ['sitestats', 'coursestats', 'diskusage', 'userstats'];

        foreach ($cachenames as $name) {
            try {
                $cache = \cache::make('local_analysis_dashboard', $name);
                $cache->purge();
                mtrace("  Purged '{$name}' cache.");
            } catch (\Throwable $e) {
                mtrace("  Failed to purge '{$name}' cache: " . $e->getMessage());
            }
        }

        $elapsed = round(microtime(true) - $starttime, 3);
        mtrace("  Stale cache cleanup complete ({$elapsed}s).");
    }
}

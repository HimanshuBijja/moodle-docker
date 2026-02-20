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
 * Scheduled task to calculate disk usage metrics.
 *
 * Computes moodledata size, database size, backup size, and server
 * performance metrics. Stores results in config_plugins for widgets
 * to read without filesystem access at render time.
 *
 * Runs daily at 3:30 AM.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calculate_disk_usage extends scheduled_task {

    /**
     * Get the task name.
     *
     * @return string The task name.
     */
    public function get_name(): string {
        return get_string('task_calculate_disk_usage', 'local_analysis_dashboard');
    }

    /**
     * Execute the task.
     *
     * Computes disk usage and server metrics, storing each in config_plugins.
     * Each step is wrapped in try/catch so partial failures don't block others.
     */
    public function execute(): void {
        global $CFG, $DB;

        $overallstart = microtime(true);

        // Step 1: Moodledata size.
        try {
            $starttime = microtime(true);
            $size = get_directory_size($CFG->dataroot);
            set_config('disk_moodledata_bytes', $size, 'local_analysis_dashboard');
            $elapsed = round(microtime(true) - $starttime, 2);
            mtrace("  Moodledata size: " . display_size($size) . " ({$elapsed}s)");
        } catch (\Throwable $e) {
            mtrace("  Moodledata size failed: " . $e->getMessage());
            set_config('disk_moodledata_bytes', 0, 'local_analysis_dashboard');
        }

        // Step 2: Database size.
        try {
            $starttime = microtime(true);
            $dbsize = $this->get_database_size();
            set_config('disk_database_bytes', $dbsize, 'local_analysis_dashboard');
            $elapsed = round(microtime(true) - $starttime, 2);
            mtrace("  Database size: " . display_size($dbsize) . " ({$elapsed}s)");
        } catch (\Throwable $e) {
            mtrace("  Database size failed: " . $e->getMessage());
            set_config('disk_database_bytes', 0, 'local_analysis_dashboard');
        }

        // Step 3: Backup files size.
        try {
            $starttime = microtime(true);
            $backupdir = $this->get_backup_directory();
            if ($backupdir && is_dir($backupdir)) {
                $backupsize = get_directory_size($backupdir);
            } else {
                $backupsize = 0;
            }
            set_config('disk_backup_bytes', $backupsize, 'local_analysis_dashboard');
            $elapsed = round(microtime(true) - $starttime, 2);
            mtrace("  Backup size: " . display_size($backupsize) . " ({$elapsed}s)");
        } catch (\Throwable $e) {
            mtrace("  Backup size failed: " . $e->getMessage());
            set_config('disk_backup_bytes', 0, 'local_analysis_dashboard');
        }

        // Step 4: Server load average (Linux/Mac only).
        try {
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                set_config('server_load_avg', round($load[0], 2), 'local_analysis_dashboard');
                mtrace("  CPU load avg (1min): " . round($load[0], 2));
            } else {
                mtrace("  CPU load avg: sys_getloadavg() not available.");
            }
        } catch (\Throwable $e) {
            mtrace("  CPU load avg failed: " . $e->getMessage());
        }

        // Step 5: Memory usage.
        try {
            $meminfo = $this->get_memory_info();
            if ($meminfo !== null) {
                set_config('server_memory_usage', $meminfo, 'local_analysis_dashboard');
                mtrace("  Memory usage: {$meminfo}");
            } else {
                mtrace("  Memory info: not available on this platform.");
            }
        } catch (\Throwable $e) {
            mtrace("  Memory info failed: " . $e->getMessage());
        }

        // Step 6: Disk free space.
        try {
            $diskfree = disk_free_space($CFG->dataroot);
            if ($diskfree !== false) {
                set_config('server_disk_free', (int) $diskfree, 'local_analysis_dashboard');
                mtrace("  Disk free: " . display_size((int) $diskfree));
            }
        } catch (\Throwable $e) {
            mtrace("  Disk free failed: " . $e->getMessage());
        }

        // Invalidate the diskusage cache so widgets pick up fresh data.
        try {
            $cache = \cache::make('local_analysis_dashboard', 'diskusage');
            $cache->purge();
            mtrace("  Diskusage cache purged.");
        } catch (\Throwable $e) {
            mtrace("  Cache purge failed: " . $e->getMessage());
        }

        $totalelapsed = round(microtime(true) - $overallstart, 2);
        mtrace("  Disk usage calculation complete ({$totalelapsed}s total).");
    }

    /**
     * Get the total database size in bytes.
     *
     * Uses information_schema for MariaDB/MySQL.
     *
     * @return int Database size in bytes.
     */
    private function get_database_size(): int {
        global $CFG, $DB;

        $sql = "SELECT SUM(data_length + index_length) AS total_size
                  FROM information_schema.TABLES
                 WHERE table_schema = ?";

        $result = $DB->get_record_sql($sql, [$CFG->dbname]);

        return $result && $result->total_size ? (int) $result->total_size : 0;
    }

    /**
     * Get the backup directory path.
     *
     * @return string|null Path to backup directory or null.
     */
    private function get_backup_directory(): ?string {
        global $CFG;

        // Check for configured backup directory first.
        if (!empty($CFG->backupdir) && is_dir($CFG->backupdir)) {
            return $CFG->backupdir;
        }

        // Fall back to default location.
        $default = $CFG->dataroot . '/backupdata';
        if (is_dir($default)) {
            return $default;
        }

        return null;
    }

    /**
     * Get memory usage info as a human-readable string.
     *
     * Reads /proc/meminfo on Linux, falls back to PHP memory_get_usage().
     *
     * @return string|null Memory usage string or null if unavailable.
     */
    private function get_memory_info(): ?string {
        // Try Linux /proc/meminfo.
        if (is_readable('/proc/meminfo')) {
            $meminfo = @file_get_contents('/proc/meminfo');
            if ($meminfo !== false) {
                $total = 0;
                $available = 0;

                if (preg_match('/MemTotal:\s+(\d+)\s+kB/', $meminfo, $matches)) {
                    $total = (int) $matches[1] * 1024; // Convert to bytes.
                }
                if (preg_match('/MemAvailable:\s+(\d+)\s+kB/', $meminfo, $matches)) {
                    $available = (int) $matches[1] * 1024;
                }

                if ($total > 0) {
                    $used = $total - $available;
                    $pct = round(($used / $total) * 100, 1);
                    return display_size($used) . ' / ' . display_size($total) . " ({$pct}%)";
                }
            }
        }

        // Fallback: PHP process memory.
        $usage = memory_get_usage(true);
        if ($usage > 0) {
            return display_size($usage) . ' (PHP process)';
        }

        return null;
    }
}

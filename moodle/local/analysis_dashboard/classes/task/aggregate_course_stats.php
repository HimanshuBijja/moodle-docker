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

use local_analysis_dashboard\local\widget_registry;

/**
 * Scheduled task to aggregate course statistics.
 *
 * Pre-warms caches for course-level widgets by invalidating and
 * re-computing data for courses with recent activity.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aggregate_course_stats extends \core\task\scheduled_task {

    /**
     * Get the task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_aggregate_course_stats', 'local_analysis_dashboard');
    }

    /**
     * Execute the task.
     *
     * Finds courses with recent activity, then invalidates and pre-warms
     * cache for course-level widgets.
     */
    public function execute(): void {
        global $DB;

        mtrace('Aggregating course statistics...');

        // Get courses with activity in the last 30 days (avoid processing dead courses).
        $thirtydaysago = time() - (30 * DAYSECS);
        $sql = "SELECT DISTINCT courseid
                  FROM {logstore_standard_log}
                 WHERE timecreated >= :starttime
                   AND courseid > 1
              ORDER BY courseid";
        $activecourses = $DB->get_fieldset_sql($sql, ['starttime' => $thirtydaysago]);

        if (empty($activecourses)) {
            mtrace('No active courses found. Skipping.');
            return;
        }

        mtrace('Found ' . count($activecourses) . ' active courses.');

        // Get course-level widgets.
        $coursewidgets = widget_registry::get_for_context(CONTEXT_COURSE);

        if (empty($coursewidgets)) {
            mtrace('No course-level widgets registered. Skipping.');
            return;
        }

        $widgetcount = count($coursewidgets);
        $coursecount = 0;
        $errors = 0;

        foreach ($activecourses as $courseid) {
            $coursecount++;
            foreach ($coursewidgets as $id => $widget) {
                try {
                    $params = ['courseid' => (int) $courseid];
                    $widget->invalidate_cache($params);
                    $widget->get_cached_data($params);
                } catch (\Throwable $e) {
                    $errors++;
                    mtrace("  Warning: Widget '{$id}' failed for course {$courseid}: " . $e->getMessage());
                }
            }

            if ($coursecount % 50 === 0) {
                mtrace("  Processed {$coursecount}/" . count($activecourses) . " courses...");
            }
        }

        mtrace("Completed: {$coursecount} courses × {$widgetcount} widgets. Errors: {$errors}.");
    }
}

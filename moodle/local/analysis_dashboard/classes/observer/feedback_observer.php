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

namespace local_analysis_dashboard\observer;

/**
 * Feedback event observer for the Analysis Dashboard.
 *
 * Invalidates feedback-related caches when feedback responses are
 * submitted or deleted so that the dashboard reflects the latest data.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback_observer {

    /**
     * Handle a feedback response submission.
     *
     * Purges cached data for the feedback analysis widgets in the
     * course where the response was submitted.
     *
     * @param \mod_feedback\event\response_submitted $event The event.
     */
    public static function response_submitted(\mod_feedback\event\response_submitted $event): void {
        self::invalidate_feedback_cache($event);
    }

    /**
     * Handle a feedback response deletion.
     *
     * Purges cached data for the feedback analysis widgets in the
     * course where the response was deleted.
     *
     * @param \mod_feedback\event\response_deleted $event The event.
     */
    public static function response_deleted(\mod_feedback\event\response_deleted $event): void {
        self::invalidate_feedback_cache($event);
    }

    /**
     * Invalidate all feedback-related cache entries for the course.
     *
     * Clears both the feedback_summary and all feedback_form_analysis
     * cache keys for the affected course.
     *
     * @param \core\event\base $event The event containing course context.
     */
    private static function invalidate_feedback_cache(\core\event\base $event): void {
        global $DB;

        $courseid = $event->courseid;
        if (empty($courseid)) {
            return;
        }

        $cache = \cache::make('local_analysis_dashboard', 'coursestats');

        // Invalidate the feedback_summary widget cache for this course.
        $cache->delete('feedback_summary_' . $courseid);

        // Invalidate all feedback_form_analysis caches for each feedback in this course.
        $feedbacks = $DB->get_records('feedback', ['course' => $courseid], '', 'id');
        foreach ($feedbacks as $fb) {
            $cache->delete('feedback_form_analysis_' . $courseid . '_fb_' . $fb->id);
        }
    }
}

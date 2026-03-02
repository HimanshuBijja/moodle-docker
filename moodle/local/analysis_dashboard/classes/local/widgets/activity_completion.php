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
 * Activity Completion Matrix widget.
 *
 * Displays a user × activity completion matrix for the course.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_completion extends base_widget {

    public function get_name(): string {
        return 'widget_activity_completion';
    }

    public function get_type(): string {
        return 'table';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:widget_activity_completion';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'activity_completion';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['headers' => [], 'rows' => []];
        }

        // Get course modules with completion enabled.
        $sql = "SELECT cm.id, cm.module, cm.instance, m.name AS modname
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module
                 WHERE cm.course = :courseid
                   AND cm.completion > 0
                   AND cm.deletioninprogress = 0
              ORDER BY cm.section, cm.id";
        $modules = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (empty($modules)) {
            return ['headers' => [], 'rows' => []];
        }

        // Get activity names.
        $activities = [];
        foreach ($modules as $cm) {
            $actname = $DB->get_field($cm->modname, 'name', ['id' => $cm->instance]);
            $activities[$cm->id] = [
                'cmid' => $cm->id,
                'name' => $actname ?: ($cm->modname . ' ' . $cm->instance),
                'modname' => $cm->modname,
            ];
        }

        // Limit to first 20 activities for readability.
        $activities = array_slice($activities, 0, 20, true);
        $cmids = array_keys($activities);

        // Build headers.
        $headers = [['key' => 'user', 'label' => get_string('user', 'local_analysis_dashboard')]];
        foreach ($activities as $cmid => $act) {
            $label = mb_substr($act['name'], 0, 20);
            $headers[] = ['key' => 'cm_' . $cmid, 'label' => $label];
        }

        // Get enrolled users (limit to 100 for performance).
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname
                  FROM {user} u
                  JOIN {user_enrolments} ue ON ue.userid = u.id
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid
                   AND ue.status = 0
                   AND u.deleted = 0
              ORDER BY u.lastname, u.firstname
                 LIMIT 100";
        $users = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (empty($users)) {
            return ['headers' => $headers, 'rows' => []];
        }

        // Get completion data.
        list($cminsql, $cmparams) = $DB->get_in_or_equal($cmids, SQL_PARAMS_NAMED, 'cm');
        list($userinsql, $userparams) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED, 'u');

        $sql = "SELECT id, coursemoduleid, userid, completionstate
                  FROM {course_modules_completion}
                 WHERE coursemoduleid {$cminsql}
                   AND userid {$userinsql}";
        $completions = $DB->get_records_sql($sql, array_merge($cmparams, $userparams));

        // Build completion lookup: userid -> cmid -> state.
        $complookup = [];
        foreach ($completions as $comp) {
            $complookup[$comp->userid][$comp->coursemoduleid] = (int) $comp->completionstate;
        }

        // Build rows.
        $rows = [];
        foreach ($users as $user) {
            $row = [
                'user' => fullname($user),
            ];
            foreach ($cmids as $cmid) {
                $state = $complookup[$user->id][$cmid] ?? 0;
                $row['cm_' . $cmid] = $state;
            }
            $rows[] = $row;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }
}

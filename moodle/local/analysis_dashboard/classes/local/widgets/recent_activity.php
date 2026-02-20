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
 * Recent Activity widget.
 *
 * Displays the most recent meaningful actions in the course.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recent_activity extends base_widget {

    public function get_name(): string {
        return 'widget_recent_activity';
    }

    public function get_type(): string {
        return 'table';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:viewcourse';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'recent_activity';
    }

    public function get_cache_ttl(): int {
        return 300; // 5 minutes — recent activity should be relatively fresh.
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['headers' => [], 'rows' => []];
        }

        $starttime = strtotime('-7 days');

        // Meaningful actions.
        $actions = ['submitted', 'viewed', 'graded', 'completed', 'uploaded', 'updated', 'created'];
        list($actionsql, $actionparams) = $DB->get_in_or_equal($actions, SQL_PARAMS_NAMED, 'act');

        $sql = "SELECT l.id, l.timecreated, l.userid, l.action, l.target, l.other,
                       u.firstname, u.lastname
                  FROM {logstore_standard_log} l
                  JOIN {user} u ON u.id = l.userid
                 WHERE l.courseid = :courseid
                   AND l.timecreated >= :starttime
                   AND l.action {$actionsql}
                   AND l.userid > 0
              ORDER BY l.timecreated DESC
                 LIMIT 50";

        $params = array_merge(['courseid' => $courseid, 'starttime' => $starttime], $actionparams);
        $records = $DB->get_records_sql($sql, $params);

        $headers = [
            ['key' => 'time', 'label' => get_string('time', 'local_analysis_dashboard')],
            ['key' => 'user', 'label' => get_string('user', 'local_analysis_dashboard')],
            ['key' => 'action', 'label' => get_string('action', 'local_analysis_dashboard')],
            ['key' => 'target', 'label' => get_string('target', 'local_analysis_dashboard')],
        ];

        $rows = [];
        foreach ($records as $record) {
            $rows[] = [
                'time' => userdate($record->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
                'user' => fullname($record),
                'action' => ucfirst($record->action),
                'target' => ucfirst(str_replace('_', ' ', $record->target)),
            ];
        }

        return ['headers' => $headers, 'rows' => $rows];
    }
}

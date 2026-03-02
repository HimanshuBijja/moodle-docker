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
 * At-Risk Students widget.
 *
 * Identifies students with multiple risk factors: no recent access,
 * low completion, and low grades.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class at_risk_students extends base_widget {

    public function get_name(): string {
        return 'widget_at_risk_students';
    }

    public function get_type(): string {
        return 'table';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:widget_at_risk_students';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'at_risk_students';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['headers' => [], 'rows' => []];
        }

        $fourteendaysago = time() - (14 * DAYSECS);

        // Get enrolled students.
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.lastaccess
                  FROM {user} u
                  JOIN {user_enrolments} ue ON ue.userid = u.id
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE e.courseid = :courseid
                   AND ue.status = 0
                   AND u.deleted = 0
              ORDER BY u.lastname, u.firstname";
        $users = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        if (empty($users)) {
            return ['headers' => [], 'rows' => []];
        }

        // Get course grade item for grade calculations.
        $gradeitem = $DB->get_record('grade_items', [
            'courseid' => $courseid,
            'itemtype' => 'course',
        ], 'id, grademax');

        // Get completion info.
        $course = $DB->get_record('course', ['id' => $courseid], 'id, enablecompletion');
        $completionenabled = !empty($course->enablecompletion);

        // Build risk assessment for each student.
        $atrisk = [];
        foreach ($users as $user) {
            $risk = 0;
            $completionpct = '-';
            $gradepct = '-';

            // Risk factor 1: No login in 14 days.
            if ($user->lastaccess < $fourteendaysago) {
                $risk++;
            }

            // Risk factor 2: Below 50% completion.
            if ($completionenabled) {
                $totalactivities = $DB->count_records_select('course_modules',
                    'course = :courseid AND completion > 0 AND deletioninprogress = 0',
                    ['courseid' => $courseid]);

                if ($totalactivities > 0) {
                    $completedactivities = $DB->count_records_select('course_modules_completion',
                        'userid = :userid AND coursemoduleid IN (
                            SELECT id FROM {course_modules}
                             WHERE course = :courseid AND completion > 0 AND deletioninprogress = 0
                        ) AND completionstate > 0',
                        ['userid' => $user->id, 'courseid' => $courseid]);

                    $completionpct = round(($completedactivities / $totalactivities) * 100);
                    if ($completionpct < 50) {
                        $risk++;
                    }
                }
            }

            // Risk factor 3: Grade below 50%.
            if ($gradeitem) {
                $grade = $DB->get_record('grade_grades', [
                    'itemid' => $gradeitem->id,
                    'userid' => $user->id,
                ], 'finalgrade');

                if ($grade && $grade->finalgrade !== null && $gradeitem->grademax > 0) {
                    $gradepct = round(($grade->finalgrade / $gradeitem->grademax) * 100);
                    if ($gradepct < 50) {
                        $risk++;
                    }
                }
            }

            if ($risk > 0) {
                $atrisk[] = [
                    'name' => fullname($user),
                    'last_access' => $user->lastaccess > 0
                        ? userdate($user->lastaccess, get_string('strftimedateshort', 'langconfig'))
                        : get_string('never'),
                    'completion_pct' => is_numeric($completionpct) ? $completionpct . '%' : $completionpct,
                    'grade_pct' => is_numeric($gradepct) ? $gradepct . '%' : $gradepct,
                    'risk_score' => $risk,
                    '_risksort' => $risk,
                ];
            }
        }

        // Sort by risk score descending.
        usort($atrisk, function($a, $b) {
            return $b['_risksort'] - $a['_risksort'];
        });

        // Remove sort helper and limit results.
        $atrisk = array_slice($atrisk, 0, 50);
        foreach ($atrisk as &$row) {
            unset($row['_risksort']);
        }

        $headers = [
            ['key' => 'name', 'label' => get_string('user', 'local_analysis_dashboard')],
            ['key' => 'last_access', 'label' => get_string('last_access', 'local_analysis_dashboard')],
            ['key' => 'completion_pct', 'label' => get_string('completion_pct', 'local_analysis_dashboard')],
            ['key' => 'grade_pct', 'label' => get_string('grade_pct', 'local_analysis_dashboard')],
            ['key' => 'risk_score', 'label' => get_string('risk_score', 'local_analysis_dashboard')],
        ];

        return ['headers' => $headers, 'rows' => $atrisk];
    }
}

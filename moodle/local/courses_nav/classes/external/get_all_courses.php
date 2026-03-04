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

namespace local_courses_nav\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/externallib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_value;
use core_course_category;

/**
 * External function to get all courses with pagination, search, and filtering.
 *
 * @package    local_courses_nav
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_all_courses extends external_api {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'search' => new external_value(PARAM_RAW, 'Search string', VALUE_DEFAULT, ''),
            'page' => new external_value(PARAM_INT, 'Page number (0-based)', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Items per page (0 = all)', VALUE_DEFAULT, 12),
            'sort' => new external_value(PARAM_ALPHANUMEXT, 'Sort field: fullname or lastaccess', VALUE_DEFAULT, 'fullname'),
            'classification' => new external_value(PARAM_ALPHA, 'Classification: all, inprogress, future, past', VALUE_DEFAULT, 'all'),
            'category' => new external_value(PARAM_INT, 'Category ID (0 = all)', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Get all courses.
     *
     * @param string $search Search string.
     * @param int $page Page number.
     * @param int $perpage Items per page.
     * @param string $sort Sort field.
     * @param string $classification Classification filter.
     * @param int $category Category ID filter.
     * @return array
     */
    public static function execute(
        string $search = '',
        int $page = 0,
        int $perpage = 12,
        string $sort = 'fullname',
        string $classification = 'all',
        int $category = 0
    ): array {
        global $DB, $OUTPUT, $SITE, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'search' => $search,
            'page' => $page,
            'perpage' => $perpage,
            'sort' => $sort,
            'classification' => $classification,
            'category' => $category,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        $search = $params['search'];
        $page = $params['page'];
        $perpage = $params['perpage'];
        $sort = $params['sort'];
        $classification = $params['classification'];
        $category = $params['category'];

        $now = time();

        // Build SQL conditions.
        $conditions = ['c.id != :siteid'];
        $sqlparams = ['siteid' => $SITE->id];

        // Only show visible courses to non-admins.
        if (!has_capability('moodle/course:viewhiddencourses', $context)) {
            $conditions[] = 'c.visible = 1';
        }

        // Classification filter based on start/end dates.
        switch ($classification) {
            case 'inprogress':
                $conditions[] = '(c.startdate > 0 AND c.startdate <= :now1 AND (c.enddate = 0 OR c.enddate > :now2))';
                $sqlparams['now1'] = $now;
                $sqlparams['now2'] = $now;
                break;
            case 'future':
                $conditions[] = '(c.startdate > :now3)';
                $sqlparams['now3'] = $now;
                break;
            case 'past':
                $conditions[] = '(c.enddate > 0 AND c.enddate < :now4)';
                $sqlparams['now4'] = $now;
                break;
            // 'all' - no date filter.
        }

        // Category filter.
        if (!empty($category)) {
            $conditions[] = 'c.category = :categoryid';
            $sqlparams['categoryid'] = $category;
        }

        // Search filter - search in fullname, shortname, and summary.
        if (!empty($search)) {
            $conditions[] = '(' .
                $DB->sql_like('c.fullname', ':search1', false) .
                ' OR ' . $DB->sql_like('c.shortname', ':search2', false) .
                ' OR ' . $DB->sql_like('c.summary', ':search3', false) .
            ')';
            $sqlparams['search1'] = '%' . $DB->sql_like_escape($search) . '%';
            $sqlparams['search2'] = '%' . $DB->sql_like_escape($search) . '%';
            $sqlparams['search3'] = '%' . $DB->sql_like_escape($search) . '%';
        }

        $where = implode(' AND ', $conditions);

        // Sort order.
        $orderby = 'c.fullname ASC';
        if ($sort === 'lastaccess') {
            // Join with user_lastaccess for sorting.
            $orderby = 'COALESCE(ul.timeaccess, 0) DESC, c.fullname ASC';
        }

        // Build the base SQL with optional lastaccess join.
        $joinsql = '';
        if ($sort === 'lastaccess') {
            $joinsql = ' LEFT JOIN {user_lastaccess} ul ON ul.courseid = c.id AND ul.userid = :userid';
            $sqlparams['userid'] = $USER->id;
        }

        // Count total.
        $countsql = "SELECT COUNT(DISTINCT c.id) FROM {course} c $joinsql WHERE $where";
        $totalcount = $DB->count_records_sql($countsql, $sqlparams);

        // Get courses.
        $sql = "SELECT c.id, c.fullname, c.shortname, c.summary, c.summaryformat,
                       c.startdate, c.enddate, c.visible, c.timecreated, c.category
                  FROM {course} c
                  $joinsql
                 WHERE $where
              ORDER BY $orderby";

        if ($perpage > 0) {
            $offset = $page * $perpage;
            $records = $DB->get_records_sql($sql, $sqlparams, $offset, $perpage);
        } else {
            // perpage = 0 means show all.
            $records = $DB->get_records_sql($sql, $sqlparams);
        }

        $courses = [];
        foreach ($records as $record) {
            $coursecontext = \context_course::instance($record->id);

            // Get course image.
            $courseobj = new \core_course_list_element(get_course($record->id));
            $courseimage = \core_course\external\course_summary_exporter::get_course_image($courseobj);
            if (!$courseimage) {
                $courseimage = $OUTPUT->get_generated_url_for_course($coursecontext);
            }

            // Get category name.
            $categoryname = '';
            $categoryid = 0;
            if ($record->category) {
                $cat = core_course_category::get($record->category, IGNORE_MISSING);
                if ($cat) {
                    $categoryname = format_string($cat->name);
                    $categoryid = (int)$record->category;
                }
            }

            // Get progress percentage.
            $hasprogress = false;
            $progress = 0;
            $completion = new \completion_info(get_course($record->id));
            if ($completion->is_enabled()) {
                $progressval = \core_completion\progress::get_course_progress_percentage(get_course($record->id), $USER->id);
                if ($progressval !== null) {
                    $hasprogress = true;
                    $progress = (int)round($progressval);
                }
            }

            // Format summary.
            list($summary, $summaryformat) = \core_external\util::format_text(
                $record->summary, $record->summaryformat, $coursecontext, 'course', 'summary', null
            );

            $courseurl = new \moodle_url('/course/view.php', ['id' => $record->id]);

            $courses[] = [
                'id' => (int)$record->id,
                'fullname' => format_string($record->fullname, true, ['context' => $coursecontext]),
                'shortname' => format_string($record->shortname, true, ['context' => $coursecontext]),
                'summary' => $summary,
                'summaryformat' => $summaryformat,
                'courseimage' => $courseimage,
                'viewurl' => $courseurl->out(false),
                'categoryname' => $categoryname,
                'categoryid' => $categoryid,
                'visible' => (int)$record->visible,
                'startdate' => (int)$record->startdate,
                'enddate' => (int)$record->enddate,
                'timecreated' => (int)$record->timecreated,
                'hasprogress' => $hasprogress,
                'progress' => $progress,
                'showcoursecategory' => true,
            ];
        }

        return [
            'courses' => $courses,
            'totalcount' => $totalcount,
            'page' => $page,
            'perpage' => $perpage,
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'courses' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Course ID'),
                    'fullname' => new external_value(PARAM_RAW, 'Course full name'),
                    'shortname' => new external_value(PARAM_RAW, 'Course short name'),
                    'summary' => new external_value(PARAM_RAW, 'Course summary'),
                    'summaryformat' => new external_value(PARAM_INT, 'Summary format'),
                    'courseimage' => new external_value(PARAM_URL, 'Course image URL'),
                    'viewurl' => new external_value(PARAM_URL, 'Course view URL'),
                    'categoryname' => new external_value(PARAM_RAW, 'Category name'),
                    'categoryid' => new external_value(PARAM_INT, 'Category ID'),
                    'visible' => new external_value(PARAM_INT, 'Course visibility'),
                    'startdate' => new external_value(PARAM_INT, 'Course start date'),
                    'enddate' => new external_value(PARAM_INT, 'Course end date'),
                    'timecreated' => new external_value(PARAM_INT, 'Course creation time'),
                    'hasprogress' => new external_value(PARAM_BOOL, 'Has progress'),
                    'progress' => new external_value(PARAM_INT, 'Progress percentage'),
                    'showcoursecategory' => new external_value(PARAM_BOOL, 'Show course category'),
                ])
            ),
            'totalcount' => new external_value(PARAM_INT, 'Total count of courses'),
            'page' => new external_value(PARAM_INT, 'Current page'),
            'perpage' => new external_value(PARAM_INT, 'Items per page'),
        ]);
    }
}

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
 * External function to get all courses with pagination and search.
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
            'perpage' => new external_value(PARAM_INT, 'Items per page', VALUE_DEFAULT, 12),
            'sort' => new external_value(PARAM_ALPHA, 'Sort field: fullname or timecreated', VALUE_DEFAULT, 'fullname'),
        ]);
    }

    /**
     * Get all courses.
     *
     * @param string $search Search string.
     * @param int $page Page number.
     * @param int $perpage Items per page.
     * @param string $sort Sort field.
     * @return array
     */
    public static function execute(string $search = '', int $page = 0, int $perpage = 12, string $sort = 'fullname'): array {
        global $DB, $OUTPUT, $SITE;

        $params = self::validate_parameters(self::execute_parameters(), [
            'search' => $search,
            'page' => $page,
            'perpage' => $perpage,
            'sort' => $sort,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);

        $search = $params['search'];
        $page = $params['page'];
        $perpage = $params['perpage'];
        $sort = $params['sort'];

        // Build SQL to get all visible courses (exclude site course).
        $conditions = ['c.id != :siteid'];
        $sqlparams = ['siteid' => $SITE->id];

        // Only show visible courses to non-admins.
        if (!has_capability('moodle/course:viewhiddencourses', $context)) {
            $conditions[] = 'c.visible = 1';
        }

        // Search filter.
        if (!empty($search)) {
            $conditions[] = '(' . $DB->sql_like('c.fullname', ':search1', false) .
                ' OR ' . $DB->sql_like('c.shortname', ':search2', false) . ')';
            $sqlparams['search1'] = '%' . $DB->sql_like_escape($search) . '%';
            $sqlparams['search2'] = '%' . $DB->sql_like_escape($search) . '%';
        }

        $where = implode(' AND ', $conditions);

        // Sort.
        $orderby = 'c.fullname ASC';
        if ($sort === 'timecreated') {
            $orderby = 'c.timecreated DESC';
        }

        // Count total.
        $totalcount = $DB->count_records_sql(
            "SELECT COUNT(*) FROM {course} c WHERE $where",
            $sqlparams
        );

        // Get courses.
        $offset = $page * $perpage;
        $sql = "SELECT c.id, c.fullname, c.shortname, c.summary, c.summaryformat,
                       c.startdate, c.enddate, c.visible, c.timecreated, c.category
                  FROM {course} c
                 WHERE $where
              ORDER BY $orderby";
        $records = $DB->get_records_sql($sql, $sqlparams, $offset, $perpage);

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
            if ($record->category) {
                $category = core_course_category::get($record->category, IGNORE_MISSING);
                if ($category) {
                    $categoryname = format_string($category->name);
                }
            }

            // Determine course status based on dates.
            $now = time();
            $hasprogress = false;
            $progress = 0;

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

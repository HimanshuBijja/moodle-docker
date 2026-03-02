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

namespace local_analysis_dashboard\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use context_course;

/**
 * Feedback courses listing page renderable.
 *
 * Lists all courses with feedback activities, categorised by timeline
 * (in progress, future, past), allowing navigation to feedback analysis.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback_courses_page implements renderable, templatable {

    /**
     * Export data for the template.
     *
     * @param renderer_base $output The renderer.
     * @return stdClass Template data.
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $DB, $USER;

        $data = new stdClass();
        $data->pagetitle = get_string('feedback_courses_title', 'local_analysis_dashboard');
        $data->pagesubtitle = get_string('feedback_courses_subtitle', 'local_analysis_dashboard');
        $data->no_courses_message = get_string('no_courses_with_feedback', 'local_analysis_dashboard');

        $now = time();
        $issiteadmin = is_siteadmin();

        // Get courses the user has access to.
        if ($issiteadmin || has_capability('local/analysis_dashboard:viewsite', \context_system::instance())) {
            // Admin/manager: get all courses (exclude site course).
            $courses = $DB->get_records_select('course', 'id <> :siteid',
                ['siteid' => SITEID], 'fullname', 'id, fullname, shortname, startdate, enddate, visible, category');
        } else {
            // Regular user: get enrolled courses.
            $courses = enrol_get_all_users_courses($USER->id, true, 'id, fullname, shortname, startdate, enddate, visible, category');
        }

        $courselist = [];

        foreach ($courses as $course) {
            // Check if course has any feedback activities.
            $feedbackcount = $DB->count_records('feedback', ['course' => $course->id]);
            if ($feedbackcount == 0) {
                continue;
            }

            // Check user can view course analytics.
            $coursecontext = context_course::instance($course->id);
            if (!$issiteadmin && !has_capability('local/analysis_dashboard:viewcourse', $coursecontext)) {
                continue;
            }

            // Determine timeline.
            $timeline = 'inprogress';
            if ($course->startdate > $now) {
                $timeline = 'future';
            } else if ($course->enddate > 0 && $course->enddate < $now) {
                $timeline = 'past';
            }

            // Get category name.
            $categoryname = '';
            if (!empty($course->category)) {
                $category = $DB->get_record('course_categories', ['id' => $course->category], 'name');
                if ($category) {
                    $categoryname = format_string($category->name);
                }
            }

            // Build course image URL.
            $courseobj = new \core_course_list_element($course);
            $imageurl = '';
            foreach ($courseobj->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                if ($isimage) {
                    $imageurl = moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        null,
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out(false);
                    break;
                }
            }

            $courselist[] = (object) [
                'id' => $course->id,
                'fullname' => format_string($course->fullname),
                'shortname' => format_string($course->shortname),
                'category_name' => $categoryname,
                'feedback_count' => $feedbackcount,
                'feedback_count_label' => get_string('feedback_forms_count', 'local_analysis_dashboard', $feedbackcount),
                'timeline' => $timeline,
                'is_inprogress' => ($timeline === 'inprogress'),
                'is_future' => ($timeline === 'future'),
                'is_past' => ($timeline === 'past'),
                'courseurl' => (new moodle_url('/local/analysis_dashboard/coursereport.php',
                    ['id' => $course->id, 'tab' => 'feedback']))->out(false),
                'imageurl' => $imageurl,
                'has_image' => !empty($imageurl),
            ];
        }

        // Sort by fullname.
        usort($courselist, function($a, $b) {
            return strcasecmp($a->fullname, $b->fullname);
        });

        $data->courses = $courselist;
        $data->has_courses = !empty($courselist);

        return $data;
    }
}

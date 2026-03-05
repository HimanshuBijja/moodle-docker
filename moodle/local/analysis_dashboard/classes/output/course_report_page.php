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

use renderable;
use templatable;
use renderer_base;
use stdClass;
use local_analysis_dashboard\local\widget_registry;

/**
 * Course report page renderable.
 *
 * Prepares course-level widget configuration data for the Mustache template.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_report_page implements renderable, templatable {

    /** @var int The course ID. */
    private int $courseid;

    /** @var string The active tab ('analytics' or 'feedback'). */
    private string $tab;

    /**
     * Constructor.
     *
     * @param int $courseid The course ID.
     * @param string $tab The active tab (analytics|feedback).
     */
    public function __construct(int $courseid, string $tab = 'analytics') {
        $this->courseid = $courseid;
        $this->tab = in_array($tab, ['analytics', 'feedback']) ? $tab : 'analytics';
    }

    /**
     * Export data for the template.
     *
     * @param renderer_base $output The renderer.
     * @return stdClass Template data.
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $DB;

        $data = new stdClass();
        $course = $DB->get_record('course', ['id' => $this->courseid], 'id, fullname, shortname', MUST_EXIST);
        $context = \context_course::instance($this->courseid);

        // Tab state.
        $data->tab_analytics_active = ($this->tab === 'analytics');
        $data->tab_feedback_active = ($this->tab === 'feedback');
        $data->tab_analytics_url = (new \moodle_url('/local/analysis_dashboard/coursereport.php',
            ['id' => $this->courseid, 'tab' => 'analytics']))->out(false);
        $data->tab_feedback_url = (new \moodle_url('/local/analysis_dashboard/coursereport.php',
            ['id' => $this->courseid, 'tab' => 'feedback']))->out(false);

        // Get widgets that support course context.
        $allwidgets = widget_registry::get_all();
        $data->widgets = [];

        foreach ($allwidgets as $id => $widget) {
            // Only include widgets that support CONTEXT_COURSE.
            if (!in_array(CONTEXT_COURSE, $widget->get_supported_context_levels())) {
                continue;
            }
            // Check capability.
            if (!has_capability($widget->get_required_capability(), $context)) {
                continue;
            }

            // Check if widget belongs to a special section.
            $section = method_exists($widget, 'get_section') ? $widget->get_section() : '';

            if ($section === 'feedback_analysis') {
                // Don't add feedback_analysis widgets to the main grid.
                continue;
            }

            $data->widgets[] = (object) [
                'id' => $id,
                'name' => get_string($widget->get_name(), 'local_analysis_dashboard'),
                'type' => $widget->get_type(),
            ];
        }

        $data->has_widgets = !empty($data->widgets);
        $data->widgets_json = json_encode($data->widgets);
        $data->courseid = $this->courseid;
        $data->coursename = $course->fullname;
        $data->courseshortname = $course->shortname;
        $data->pagetitle = get_string('course_report', 'local_analysis_dashboard');
        $data->pagesubtitle = get_string('course_report_subtitle', 'local_analysis_dashboard', $course->fullname);
        $data->no_widgets_message = get_string('no_widgets_available', 'local_analysis_dashboard');

        // === Feedback Analysis Section ===
        // Discover all feedback activities in this course.
        $feedbacks = $DB->get_records('feedback', ['course' => $this->courseid], 'name', 'id, name');
        $feedbackforms = [];

        foreach ($feedbacks as $fb) {
            // Check if this feedback has any textarea/textfield items (for comments toggle).
            $hascomments = $DB->record_exists_sql(
                "SELECT 1 FROM {feedback_item}
                  WHERE feedback = :fbid
                    AND (typ = 'textarea' OR typ = 'textfield')
                    AND hasvalue = 1",
                ['fbid' => $fb->id]
            );

            $feedbackforms[] = (object) [
                'id' => $fb->id,
                'name' => format_string($fb->name),
                'has_comments' => $hascomments,
            ];
        }

        $data->has_feedback_forms = !empty($feedbackforms);
        $data->feedback_forms = $feedbackforms;
        $data->feedback_forms_json = json_encode($feedbackforms);
        $data->feedback_section_title = get_string('feedback_analysis_section', 'local_analysis_dashboard');
        $data->feedback_section_desc = get_string('feedback_analysis_desc', 'local_analysis_dashboard');

        return $data;
    }
}

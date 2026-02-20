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

/**
 * Course-level analytics report page.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);

require_login($course);

$context = context_course::instance($courseid);
require_capability('local/analysis_dashboard:viewcourse', $context);

$PAGE->set_url(new moodle_url('/local/analysis_dashboard/coursereport.php', ['id' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('course_report', 'local_analysis_dashboard') . ': ' . $course->fullname);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('report');

$output = $PAGE->get_renderer('local_analysis_dashboard');
$page = new \local_analysis_dashboard\output\course_report_page($courseid);

echo $output->header();
echo $output->render($page);
echo $output->footer();

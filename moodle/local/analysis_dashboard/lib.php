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
 * Library functions for the Analysis Dashboard plugin.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends the front page navigation with the dashboard link.
 *
 * @param navigation_node $frontpage The front page navigation node.
 * @param stdClass $course The course object.
 * @param context_course $context The course context.
 */
function local_analysis_dashboard_extend_navigation_frontpage(navigation_node $frontpage, stdClass $course,
        context_course $context) {
    if (has_capability('local/analysis_dashboard:viewsite', context_system::instance())) {
        $frontpage->add(
            get_string('pluginname', 'local_analysis_dashboard'),
            new moodle_url('/local/analysis_dashboard/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'analysis_dashboard',
            new pix_icon('i/report', '')
        );
    }
}

/**
 * Extends the global navigation with the dashboard link.
 *
 * @param global_navigation $navigation The global navigation object.
 */
function local_analysis_dashboard_extend_navigation(global_navigation $navigation) {
    if (has_capability('local/analysis_dashboard:viewsite', context_system::instance())) {
        $navigation->add(
            get_string('pluginname', 'local_analysis_dashboard'),
            new moodle_url('/local/analysis_dashboard/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'analysis_dashboard',
            new pix_icon('i/report', '')
        );
        $navigation->add(
            get_string('manager_dashboard', 'local_analysis_dashboard'),
            new moodle_url('/local/analysis_dashboard/managerdashboard.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'analysis_dashboard_manager',
            new pix_icon('i/report', '')
        );
    }
}

/**
 * Extends the course settings navigation with the course analytics link.
 *
 * @param navigation_node $settingsnav The settings navigation node.
 * @param context $context The current context.
 */
function local_analysis_dashboard_extend_navigation_course(navigation_node $settingsnav, stdClass $course,
        context_course $context) {
    if (has_capability('local/analysis_dashboard:viewcourse', $context)) {
        $settingsnav->add(
            get_string('course_report', 'local_analysis_dashboard'),
            new moodle_url('/local/analysis_dashboard/coursereport.php', ['id' => $course->id]),
            navigation_node::TYPE_CUSTOM,
            null,
            'analysis_dashboard_course',
            new pix_icon('i/report', '')
        );
    }
}

/**
 * Adds 'My Analytics' to the user profile navigation.
 *
 * @param \core_user\output\myprofile\tree $tree The myprofile tree.
 * @param stdClass $user The user object.
 * @param bool $iscurrentuser Whether viewing own profile.
 * @param stdClass|null $course The course object if in course context.
 */
function local_analysis_dashboard_myprofile_navigation(\core_user\output\myprofile\tree $tree,
        stdClass $user, bool $iscurrentuser, ?stdClass $course) {
    if ($iscurrentuser && has_capability('local/analysis_dashboard:viewown', context_system::instance())) {
        $category = new \core_user\output\myprofile\category(
            'analysis_dashboard',
            get_string('pluginname', 'local_analysis_dashboard'),
            'reports'
        );
        $tree->add_category($category);

        $node = new \core_user\output\myprofile\node(
            'analysis_dashboard',
            'myanalytics',
            get_string('student_dashboard', 'local_analysis_dashboard'),
            null,
            new moodle_url('/local/analysis_dashboard/studentdashboard.php')
        );
        $tree->add_node($node);
    }
}

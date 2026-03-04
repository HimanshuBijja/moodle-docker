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
 * All Courses page - displays all courses in the system.
 *
 * @package    local_courses_nav
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

redirect_if_major_upgrade_required();

require_login();

$hassiteconfig = has_capability('moodle/site:config', context_system::instance());
if ($hassiteconfig && moodle_needs_upgrading()) {
    redirect(new moodle_url('/admin/index.php'));
}

$context = context_system::instance();

// Set up the page.
$PAGE->set_context($context);
$PAGE->set_url('/local/courses_nav/allcourses.php');
$PAGE->add_body_classes(['limitedwidth', 'page-mycourses']);
$PAGE->set_pagelayout('mycourses');
$PAGE->set_pagetype('my-index');
$PAGE->set_title(get_string('allcoursespagetitle', 'local_courses_nav'));
$PAGE->set_heading(get_string('allcoursespagetitle', 'local_courses_nav'));

// Set the active primary nav tab.
$PAGE->set_primary_active_tab('mycourses');

// Force lock all blocks like the my courses page.
$PAGE->force_lock_all_blocks();
$PAGE->theme->addblockposition = BLOCK_ADDBLOCK_POSITION_CUSTOM;

// Render the page.
$output = $PAGE->get_renderer('local_courses_nav');
$page = new \local_courses_nav\output\allcourses_page();

echo $OUTPUT->header();
echo $output->render($page);
echo $OUTPUT->footer();

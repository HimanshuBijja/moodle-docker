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

namespace local_courses_nav;

use core\hook\navigation\primary_extend;
use navigation_node;

/**
 * Hook listener for modifying primary navigation.
 *
 * @package    local_courses_nav
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_listener {

    /**
     * Modify the primary navigation to rename "My courses" to "Courses"
     * and add a dropdown with "All Courses" and "My Courses" children.
     *
     * @param primary_extend $hook The primary navigation hook.
     */
    public static function modify_primary_nav(primary_extend $hook): void {
        $primaryview = $hook->get_primaryview();

        // Find the existing 'mycourses' node.
        $mycoursesnode = $primaryview->find('mycourses', null);
        if (!$mycoursesnode) {
            return;
        }

        // Change the text to "Courses".
        $mycoursesnode->text = get_string('courses', 'local_courses_nav');

        // Make it a dropdown parent (no direct link).
        $mycoursesnode->action = null;
        $mycoursesnode->showchildreninsubmenu = true;

        // Add "All Courses" child.
        $mycoursesnode->add(
            get_string('allcourses', 'local_courses_nav'),
            new \moodle_url('/local/courses_nav/allcourses.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'allcourses'
        );

        // Add "My Courses" child.
        $mycoursesnode->add(
            get_string('mycourses', 'local_courses_nav'),
            new \moodle_url('/my/courses.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'mycourses_link'
        );
    }
}

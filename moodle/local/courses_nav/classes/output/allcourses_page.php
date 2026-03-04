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

namespace local_courses_nav\output;

use renderable;
use renderer_base;
use templatable;
use core_course_category;

/**
 * Renderable class for the All Courses page.
 *
 * @package    local_courses_nav
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class allcourses_page implements renderable, templatable {

    /**
     * Export data for template.
     *
     * @param renderer_base $output The renderer.
     * @return array Template data.
     */
    public function export_for_template(renderer_base $output): array {
        // Get all course categories for the filter dropdown.
        $categorieslist = core_course_category::make_categories_list();
        $categories = [];
        foreach ($categorieslist as $id => $name) {
            $categories[] = [
                'id' => $id,
                'name' => $name,
            ];
        }

        return [
            'uniqid' => uniqid(),
            'searchplaceholder' => get_string('searchcourses', 'local_courses_nav'),
            'nocoursesfound' => get_string('nocoursesfound', 'local_courses_nav'),
            'categories' => $categories,
            'hascategories' => !empty($categories),
        ];
    }
}

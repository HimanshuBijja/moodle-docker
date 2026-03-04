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

use plugin_renderer_base;

/**
 * Renderer for local_courses_nav.
 *
 * @package    local_courses_nav
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the all courses page.
     *
     * @param allcourses_page $page The renderable.
     * @return string HTML output.
     */
    protected function render_allcourses_page(allcourses_page $page): string {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_courses_nav/allcourses', $data);
    }
}

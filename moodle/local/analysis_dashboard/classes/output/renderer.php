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

use plugin_renderer_base;

/**
 * Renderer for the Analysis Dashboard plugin.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the dashboard page.
     *
     * @param dashboard_page $page The dashboard page renderable.
     * @return string HTML output.
     */
    protected function render_dashboard_page(dashboard_page $page): string {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_analysis_dashboard/dashboard', $data);
    }

    /**
     * Render the course report page.
     *
     * @param course_report_page $page The course report page renderable.
     * @return string HTML output.
     */
    protected function render_course_report_page(course_report_page $page): string {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_analysis_dashboard/course_report', $data);
    }

    /**
     * Render the student dashboard page.
     *
     * @param student_dashboard_page $page The student dashboard page renderable.
     * @return string HTML output.
     */
    protected function render_student_dashboard_page(student_dashboard_page $page): string {
        $data = $page->export_for_template($this);
        return $this->render_from_template('local_analysis_dashboard/student_dashboard', $data);
    }
}

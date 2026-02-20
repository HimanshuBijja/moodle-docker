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
 * Dashboard page renderable.
 *
 * Prepares widget configuration data for the Mustache template.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard_page implements renderable, templatable {

    /**
     * Export data for the template.
     *
     * @param renderer_base $output The renderer.
     * @return stdClass Template data.
     */
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();

        // Get widget config for the current user.
        $widgetconfig = widget_registry::get_config_for_current_user();

        $data->widgets = [];
        foreach ($widgetconfig as $widget) {
            $data->widgets[] = (object) [
                'id' => $widget['id'],
                'name' => $widget['name'],
                'type' => $widget['type'],
            ];
        }

        $data->has_widgets = !empty($data->widgets);
        $data->widgets_json = json_encode($data->widgets);
        $data->pagetitle = get_string('dashboard', 'local_analysis_dashboard');
        $data->pagesubtitle = get_string('dashboard_subtitle', 'local_analysis_dashboard');
        $data->no_widgets_message = get_string('no_widgets_available', 'local_analysis_dashboard');

        return $data;
    }
}

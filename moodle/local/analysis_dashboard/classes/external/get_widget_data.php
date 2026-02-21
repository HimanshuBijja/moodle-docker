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

namespace local_analysis_dashboard\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function to get widget data.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_widget_data extends external_api {

    /**
     * Describe the parameters for this external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'widgetid' => new external_value(PARAM_ALPHANUMEXT, 'Widget identifier'),
            'params' => new external_value(PARAM_RAW, 'JSON-encoded parameters', VALUE_DEFAULT, '{}'),
        ]);
    }

    /**
     * Execute the external function.
     *
     * @param string $widgetid Widget identifier.
     * @param string $params JSON-encoded parameters.
     * @return array Widget data with type and name.
     */
    public static function execute(string $widgetid, string $params = '{}'): array {
        // Validate parameters.
        $validated = self::validate_parameters(self::execute_parameters(), [
            'widgetid' => $widgetid,
            'params' => $params,
        ]);

        $widgetid = $validated['widgetid'];
        $params = $validated['params'];

        // Decode params.
        $decodedparams = json_decode($params, true) ?: [];

        // Validate context — user context if userid, course context if courseid, else system.
        if (!empty($decodedparams['userid'])) {
            $userid = (int) $decodedparams['userid'];
            // Students can only view their own data.
            global $USER;
            if ($userid !== (int) $USER->id && !is_siteadmin()) {
                throw new \moodle_exception('nopermissions', 'error', '', 'view other user data');
            }
            $context = \context_user::instance($userid);
        } else if (!empty($decodedparams['courseid'])) {
            $courseid = (int) $decodedparams['courseid'];
            $context = \context_course::instance($courseid);
        } else {
            $context = \context_system::instance();
        }
        self::validate_context($context);

        // Minimum check — all authenticated users.
        require_capability('local/analysis_dashboard:viewown', \context_system::instance());

        // Get the widget from registry.
        $widget = \local_analysis_dashboard\local\widget_registry::get($widgetid);

        // Check widget-specific capability in the appropriate context.
        // For viewown widgets, always check at system context since that is where
        // the capability is defined and role archetypes (student, teacher) are granted.
        $caprequired = $widget->get_required_capability();
        if ($caprequired === 'local/analysis_dashboard:viewown') {
            $capcontext = \context_system::instance();
        } else {
            $capcontext = $context;
        }
        require_capability($caprequired, $capcontext);

        // Get data.
        $data = $widget->get_cached_data($decodedparams);

        return [
            'data' => json_encode($data),
            'type' => $widget->get_type(),
            'name' => get_string($widget->get_name(), 'local_analysis_dashboard'),
        ];
    }

    /**
     * Describe the return value for this external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'data' => new external_value(PARAM_RAW, 'JSON-encoded widget data'),
            'type' => new external_value(PARAM_ALPHA, 'Widget type (counter, line, bar, pie, etc.)'),
            'name' => new external_value(PARAM_TEXT, 'Widget display name'),
        ]);
    }
}

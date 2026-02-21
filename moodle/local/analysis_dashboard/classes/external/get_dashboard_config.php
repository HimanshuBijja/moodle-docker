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
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function to get dashboard widget configuration.
 *
 * Returns the list of widgets visible to the current user.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_dashboard_config extends external_api {

    /**
     * Describe the parameters for this external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextlevel' => new external_value(PARAM_INT, 'Context level filter (optional)', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Execute the external function.
     *
     * @param int $contextlevel Optional context level filter.
     * @return array List of widget configurations visible to current user.
     */
    public static function execute(int $contextlevel = 0): array {
        $validated = self::validate_parameters(self::execute_parameters(), [
            'contextlevel' => $contextlevel,
        ]);
        $contextlevel = $validated['contextlevel'];

        // Validate context.
        $context = \context_system::instance();
        self::validate_context($context);

        // Minimum capability — all authenticated users.
        require_capability('local/analysis_dashboard:viewown', $context);

        // Get widget config for current user, optionally filtered by context level.
        $filter = $contextlevel > 0 ? $contextlevel : null;
        return \local_analysis_dashboard\local\widget_registry::get_config_for_current_user($filter);
    }

    /**
     * Describe the return value for this external function.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_ALPHANUMEXT, 'Widget identifier'),
                'name' => new external_value(PARAM_TEXT, 'Widget display name'),
                'type' => new external_value(PARAM_ALPHA, 'Widget type'),
            ])
        );
    }
}

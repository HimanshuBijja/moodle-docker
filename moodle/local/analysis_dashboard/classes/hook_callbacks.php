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

namespace local_analysis_dashboard;

/**
 * Hook callbacks for the Analysis Dashboard plugin.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Add the Analysis Dashboard link to the primary navigation bar.
     *
     * Redirects to the appropriate dashboard based on user capabilities:
     * - Site-level dashboard for managers (viewsite capability)
     * - Student self-service dashboard for regular users (viewown capability)
     *
     * @param \core\hook\navigation\primary_extend $hook
     */
    public static function add_primary_nav(\core\hook\navigation\primary_extend $hook): void {
        $primaryview = $hook->get_primaryview();
        $sysctx = \context_system::instance();

        // Determine the correct dashboard URL based on user capabilities.
        if (has_capability('local/analysis_dashboard:viewsite', $sysctx)) {
            // Managers/admins go to the site-level dashboard.
            $url = new \moodle_url('/local/analysis_dashboard/index.php');
        } else if (has_capability('local/analysis_dashboard:viewown', $sysctx)) {
            // Students/regular users go to the student self-service dashboard.
            $url = new \moodle_url('/local/analysis_dashboard/studentdashboard.php');
        } else {
            // User has no dashboard access, don't show the nav item.
            return;
        }

        $primaryview->add(
            get_string('pluginname', 'local_analysis_dashboard'),
            $url,
            \navigation_node::TYPE_CUSTOM,
            null,
            'analysis_dashboard',
            new \pix_icon('i/report', '')
        );
    }
}

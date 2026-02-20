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
 * Behat step definitions for the Analysis Dashboard.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: No MOODLE_INTERNAL check for behat step definitions.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ExpectationException;

/**
 * Step definitions for the Analysis Dashboard plugin.
 */
class behat_local_analysis_dashboard extends behat_base {

    /**
     * Navigate to a plugin page.
     *
     * @Given /^I am on the "local_analysis_dashboard > ([^"]*)" page$/
     * @param string $pagename The page identifier (index, managerdashboard, student, coursereport).
     */
    public function i_am_on_the_plugin_page(string $pagename): void {
        $pagemap = [
            'index' => '/local/analysis_dashboard/index.php',
            'managerdashboard' => '/local/analysis_dashboard/managerdashboard.php',
            'student' => '/local/analysis_dashboard/studentdashboard.php',
            'coursereport' => '/local/analysis_dashboard/coursereport.php',
        ];

        if (!isset($pagemap[$pagename])) {
            throw new ExpectationException(
                'Unknown plugin page: ' . $pagename . '. Valid pages: ' . implode(', ', array_keys($pagemap)),
                $this->getSession()
            );
        }

        $this->getSession()->visit($this->locate_path($pagemap[$pagename]));
    }

    /**
     * Navigate to a plugin page with a parameter.
     *
     * @Given /^I am on the "local_analysis_dashboard > ([^"]*)" page with "([^"]*)" "([^"]*)"$/
     * @param string $pagename The page identifier.
     * @param string $paramname The parameter name (e.g., 'id').
     * @param string $paramvalue The parameter value (e.g., course shortname).
     */
    public function i_am_on_the_plugin_page_with_param(string $pagename, string $paramname, string $paramvalue): void {
        global $DB;

        // Resolve shortname to ID for courses.
        if ($paramname === 'id' && $pagename === 'coursereport') {
            $course = $DB->get_record('course', ['shortname' => $paramvalue], 'id', MUST_EXIST);
            $paramvalue = $course->id;
        }

        $pagemap = [
            'coursereport' => '/local/analysis_dashboard/coursereport.php',
        ];

        if (!isset($pagemap[$pagename])) {
            throw new ExpectationException(
                'Unknown plugin page for params: ' . $pagename,
                $this->getSession()
            );
        }

        $url = $pagemap[$pagename] . '?' . $paramname . '=' . $paramvalue;
        $this->getSession()->visit($this->locate_path($url));
    }
}

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
 * Moodle Mobile app support definitions.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'local_analysis_dashboard' => [
        'handlers' => [
            'dashboard' => [
                'displaydata' => [
                    'title' => 'pluginname',
                    'icon' => 'stats-chart',
                    'class' => '',
                ],
                'delegate' => 'CoreMainMenuDelegate',
                'method' => 'mobile_dashboard_view',
                'offlinefunctions' => [],
                'styles' => [
                    'url' => '/local/analysis_dashboard/mobile/styles.css',
                    'version' => 1,
                ],
            ],
        ],
        'lang' => [
            ['pluginname', 'local_analysis_dashboard'],
            ['dashboard', 'local_analysis_dashboard'],
            ['widget_total_users', 'local_analysis_dashboard'],
            ['widget_total_courses', 'local_analysis_dashboard'],
            ['widget_site_visits', 'local_analysis_dashboard'],
            ['widget_authentication_report', 'local_analysis_dashboard'],
            ['widget_enrolled_methods', 'local_analysis_dashboard'],

            ['no_widgets_available', 'local_analysis_dashboard'],
        ],
    ],
];

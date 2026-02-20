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
 * Admin settings for the Analysis Dashboard plugin.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_analysis_dashboard',
        get_string('pluginname', 'local_analysis_dashboard')
    );

    // Cache TTL for site stats (seconds).
    $settings->add(new admin_setting_configtext(
        'local_analysis_dashboard/cache_ttl_sitestats',
        get_string('settings_cache_ttl_sitestats', 'local_analysis_dashboard'),
        get_string('settings_cache_ttl_sitestats_desc', 'local_analysis_dashboard'),
        3600,
        PARAM_INT
    ));

    // Cache TTL for course stats (seconds).
    $settings->add(new admin_setting_configtext(
        'local_analysis_dashboard/cache_ttl_coursestats',
        get_string('settings_cache_ttl_coursestats', 'local_analysis_dashboard'),
        get_string('settings_cache_ttl_coursestats_desc', 'local_analysis_dashboard'),
        1800,
        PARAM_INT
    ));

    // Number of days for site visits chart.
    $settings->add(new admin_setting_configtext(
        'local_analysis_dashboard/site_visits_days',
        get_string('settings_site_visits_days', 'local_analysis_dashboard'),
        get_string('settings_site_visits_days_desc', 'local_analysis_dashboard'),
        30,
        PARAM_INT
    ));

    $ADMIN->add('localplugins', $settings);
}

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

    // Show/hide Total Categories in Total Courses widget.
    $settings->add(new admin_setting_configcheckbox(
        'local_analysis_dashboard/show_total_categories',
        get_string('settings_show_total_categories', 'local_analysis_dashboard'),
        get_string('settings_show_total_categories_desc', 'local_analysis_dashboard'),
        1
    ));

    // ── Widget enable/disable toggles (grouped by role/category) ──
    \local_analysis_dashboard\local\widget_registry::init();

    // Define widget groups matching registry phases.
    $widgetgroups = [
        'site' => [
            'heading' => get_string('settings_heading_site_widgets', 'local_analysis_dashboard'),
            'desc' => get_string('settings_heading_site_widgets_desc', 'local_analysis_dashboard'),
            'ids' => ['total_users', 'total_courses', 'site_visits'],
        ],
        'course' => [
            'heading' => get_string('settings_heading_course_widgets', 'local_analysis_dashboard'),
            'desc' => get_string('settings_heading_course_widgets_desc', 'local_analysis_dashboard'),
            'ids' => [
                'enrollment_stats', 'completion_progress', 'grade_distribution',
                'course_visits', 'activity_completion', 'recent_activity',
                'at_risk_students', 'activity_heatmap', 'quiz_analytics',
                'feedback_form_analysis',
            ],
        ],
        'student' => [
            'heading' => get_string('settings_heading_student_widgets', 'local_analysis_dashboard'),
            'desc' => get_string('settings_heading_student_widgets_desc', 'local_analysis_dashboard'),
            'ids' => [
                'my_learning_overview', 'my_course_progress', 'my_grade_overview',
                'overall_completion_status', 'my_login_history', 'my_recent_activity',
            ],
        ],
        'secureotp_admin' => [
            'heading' => get_string('settings_heading_secureotp_admin', 'local_analysis_dashboard'),
            'desc' => get_string('settings_heading_secureotp_admin_desc', 'local_analysis_dashboard'),
            'ids' => [
                'secureotp_account_status', 'secureotp_security_summary',
                'secureotp_otp_events', 'secureotp_audit_severity',
                'secureotp_rate_limits', 'secureotp_failed_logins',
                'secureotp_failed_by_location', 'secureotp_import_history',
                'secureotp_users_by_source',
            ],
        ],
        'secureotp_demographics' => [
            'heading' => get_string('settings_heading_secureotp_demographics', 'local_analysis_dashboard'),
            'desc' => get_string('settings_heading_secureotp_demographics_desc', 'local_analysis_dashboard'),
            'ids' => [
                'secureotp_by_location', 'secureotp_by_rank', 'secureotp_by_unit',
                'secureotp_by_city', 'secureotp_by_employee_type', 'secureotp_by_cpsp',
                'secureotp_by_gender', 'secureotp_by_education',
            ],
        ],
        'secureotp_user' => [
            'heading' => get_string('settings_heading_secureotp_user', 'local_analysis_dashboard'),
            'desc' => get_string('settings_heading_secureotp_user_desc', 'local_analysis_dashboard'),
            'ids' => [
                'secureotp_my_profile', 'secureotp_my_login_history',
                'course_students_by_location', 'course_students_by_employee_type',
                'course_students_by_rank',
            ],
        ],
        'admin_analytics' => [
            'heading' => get_string('settings_heading_admin_analytics', 'local_analysis_dashboard'),
            'desc' => get_string('settings_heading_admin_analytics_desc', 'local_analysis_dashboard'),
            'ids' => [
                'authentication_report', 'disk_usage', 'server_performance', 'enrolled_methods',
            ],
        ],
    ];

    foreach ($widgetgroups as $groupkey => $group) {
        // Add heading.
        $settings->add(new admin_setting_heading(
            'local_analysis_dashboard/heading_' . $groupkey,
            $group['heading'],
            $group['desc']
        ));

        // Build choices for this group.
        $choices = [];
        foreach ($group['ids'] as $id) {
            try {
                $widget = \local_analysis_dashboard\local\widget_registry::get($id);
                $choices[$id] = get_string($widget->get_name(), 'local_analysis_dashboard');
            } catch (\Exception $e) {
                $choices[$id] = $id;
            }
        }

        $settings->add(new admin_setting_configmulticheckbox(
            'local_analysis_dashboard/disabled_widgets_' . $groupkey,
            get_string('settings_disabled_widgets', 'local_analysis_dashboard'),
            '',
            [],
            $choices
        ));
    }

    $ADMIN->add('localplugins', $settings);
}

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

namespace local_analysis_dashboard\local;

/**
 * Widget registry for the Analysis Dashboard.
 *
 * Maintains a static map of widget identifiers to their class names.
 * Provides methods to retrieve widgets filtered by user capabilities.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class widget_registry {

    /** @var array<string, string> Map of widget ID => fully qualified class name. */
    private static array $widgets = [];

    /** @var bool Whether the registry has been initialized. */
    private static bool $initialized = false;

    /**
     * Initialize the registry with all known widgets.
     *
     * Called once on first access. Uses a static map — no filesystem scanning.
     */
    public static function init(): void {
        if (self::$initialized) {
            return;
        }

        // Phase 1 widgets.
        self::register('total_users', widgets\total_users::class);
        self::register('total_courses', widgets\total_courses::class);
        self::register('site_visits', widgets\site_visits::class);

        // Phase 2 widgets — course-level.
        self::register('enrollment_stats', widgets\enrollment_stats::class);
        self::register('completion_progress', widgets\completion_progress::class);
        self::register('grade_distribution', widgets\grade_distribution::class);
        self::register('course_visits', widgets\course_visits::class);
        self::register('activity_completion', widgets\activity_completion::class);
        self::register('recent_activity', widgets\recent_activity::class);
        self::register('at_risk_students', widgets\at_risk_students::class);
        self::register('activity_heatmap', widgets\activity_heatmap::class);
        self::register('quiz_analytics', widgets\quiz_analytics::class);

        // Phase 3 widgets — student personal (CONTEXT_USER).
        self::register('my_learning_overview', widgets\my_learning_overview::class);
        self::register('my_course_progress', widgets\my_course_progress::class);
        self::register('my_grade_overview', widgets\my_grade_overview::class);
        self::register('overall_completion_status', widgets\overall_completion_status::class);
        self::register('my_login_history', widgets\my_login_history::class);
        self::register('my_recent_activity', widgets\my_recent_activity::class);


        // Phase 3 widgets — SecureOTP admin security.
        self::register('secureotp_account_status', widgets\secureotp_account_status::class);
        self::register('secureotp_security_summary', widgets\secureotp_security_summary::class);
        self::register('secureotp_otp_events', widgets\secureotp_otp_events::class);
        self::register('secureotp_audit_severity', widgets\secureotp_audit_severity::class);
        self::register('secureotp_rate_limits', widgets\secureotp_rate_limits::class);
        self::register('secureotp_failed_logins', widgets\secureotp_failed_logins::class);
        self::register('secureotp_failed_by_location', widgets\secureotp_failed_by_location::class);
        self::register('secureotp_import_history', widgets\secureotp_import_history::class);
        self::register('secureotp_users_by_source', widgets\secureotp_users_by_source::class);

        // Phase 3 widgets — SecureOTP demographics.
        self::register('secureotp_by_location', widgets\secureotp_by_location::class);
        self::register('secureotp_by_rank', widgets\secureotp_by_rank::class);
        self::register('secureotp_by_unit', widgets\secureotp_by_unit::class);
        self::register('secureotp_by_city', widgets\secureotp_by_city::class);
        self::register('secureotp_by_employee_type', widgets\secureotp_by_employee_type::class);
        self::register('secureotp_by_cpsp', widgets\secureotp_by_cpsp::class);
        self::register('secureotp_by_gender', widgets\secureotp_by_gender::class);
        self::register('secureotp_by_education', widgets\secureotp_by_education::class);

        // Phase 3 widgets — SecureOTP student (CONTEXT_USER).
        self::register('secureotp_my_profile', widgets\secureotp_my_profile::class);
        self::register('secureotp_my_login_history', widgets\secureotp_my_login_history::class);

        // Phase 3 widgets — SecureOTP course-teacher (CONTEXT_COURSE).
        self::register('course_students_by_location', widgets\course_students_by_location::class);
        self::register('course_students_by_employee_type', widgets\course_students_by_employee_type::class);
        self::register('course_students_by_rank', widgets\course_students_by_rank::class);


        // Phase 4 widgets — admin analytics.
        self::register('authentication_report', widgets\authentication_report::class);
        self::register('disk_usage', widgets\disk_usage::class);
        self::register('server_performance', widgets\server_performance::class);

        self::register('enrolled_methods', widgets\enrolled_methods::class);

        self::$initialized = true;
    }

    /**
     * Register a widget class.
     *
     * @param string $id Widget identifier (e.g., 'total_users').
     * @param string $classname Fully qualified class name.
     */
    public static function register(string $id, string $classname): void {
        self::$widgets[$id] = $classname;
    }

    /**
     * Get all registered widget IDs.
     *
     * @return array List of widget identifier strings.
     */
    public static function get_all_ids(): array {
        self::init();
        return array_keys(self::$widgets);
    }

    /**
     * Get a widget instance by ID.
     *
     * @param string $id Widget identifier.
     * @return widget_interface Widget instance.
     * @throws \moodle_exception If widget not found.
     */
    public static function get(string $id): widget_interface {
        self::init();

        if (!isset(self::$widgets[$id])) {
            throw new \moodle_exception('widget_not_found', 'local_analysis_dashboard', '', $id);
        }

        $classname = self::$widgets[$id];
        return new $classname();
    }

    /**
     * Get all registered widget instances.
     *
     * @return array<string, widget_interface> Map of widget ID => instance.
     */
    public static function get_all(): array {
        self::init();

        // Get disabled widgets from admin settings (grouped by category).
        $disabled = [];
        $groupkeys = ['site', 'course', 'student', 'secureotp_admin',
            'secureotp_demographics', 'secureotp_user', 'admin_analytics'];
        foreach ($groupkeys as $groupkey) {
            $setting = get_config('local_analysis_dashboard', 'disabled_widgets_' . $groupkey);
            if (!empty($setting)) {
                foreach (explode(',', $setting) as $id) {
                    $disabled[$id] = true;
                }
            }
        }
        // Also check legacy single setting for backward compatibility.
        $legacysetting = get_config('local_analysis_dashboard', 'disabled_widgets');
        if (!empty($legacysetting)) {
            foreach (explode(',', $legacysetting) as $id) {
                $disabled[$id] = true;
            }
        }

        $instances = [];
        foreach (self::$widgets as $id => $classname) {
            // Skip disabled widgets.
            if (isset($disabled[$id])) {
                continue;
            }
            $widget = new $classname();
            if ($widget->is_available()) {
                $instances[$id] = $widget;
            }
        }

        return $instances;
    }

    /**
     * Get widgets visible to the current user.
     *
     * Filters by capability checks for the current user.
     *
     * @return array<string, widget_interface> Map of widget ID => instance.
     */
    public static function get_for_current_user(): array {
        $all = self::get_all();
        $visible = [];

        foreach ($all as $id => $widget) {
            if (has_capability($widget->get_required_capability(), $widget->get_context())) {
                $visible[$id] = $widget;
            }
        }

        return $visible;
    }

    /**
     * Get widget configuration for the current user.
     *
     * Returns an array of widget metadata (id, name, type) for the dashboard.
     *
     * @param int|null $contextlevel Optional context level filter (CONTEXT_SYSTEM, CONTEXT_COURSE).
     * @return array List of widget config arrays.
     */
    public static function get_config_for_current_user(?int $contextlevel = null): array {
        $widgets = self::get_for_current_user();
        $config = [];

        foreach ($widgets as $id => $widget) {
            // Filter by context level if specified.
            if ($contextlevel !== null &&
                    !in_array($contextlevel, $widget->get_supported_context_levels())) {
                continue;
            }
            $config[] = [
                'id' => $id,
                'name' => get_string($widget->get_name(), 'local_analysis_dashboard'),
                'type' => $widget->get_type(),
            ];
        }

        return $config;
    }

    /**
     * Get all widgets that support a specific context level.
     *
     * @param int $contextlevel A CONTEXT_* constant.
     * @return array<string, widget_interface> Map of widget ID => instance.
     */
    public static function get_for_context(int $contextlevel): array {
        $all = self::get_all();
        $filtered = [];

        foreach ($all as $id => $widget) {
            if (in_array($contextlevel, $widget->get_supported_context_levels())) {
                $filtered[$id] = $widget;
            }
        }

        return $filtered;
    }

    /**
     * Reset the registry. Used in testing.
     */
    public static function reset(): void {
        self::$widgets = [];
        self::$initialized = false;
    }
}

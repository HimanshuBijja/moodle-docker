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
 * Language strings for the Analysis Dashboard plugin.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin.
$string['pluginname'] = 'Analysis Dashboard';

// Dashboard page.
$string['dashboard'] = 'Analysis Dashboard';
$string['dashboard_subtitle'] = 'Site-wide analytics and statistics';
$string['no_widgets_available'] = 'No widgets are available for your role.';
$string['widget_loading'] = 'Loading widget data...';
$string['widget_error'] = 'Failed to load widget data.';
$string['widget_no_data'] = 'No data available.';

// Widget names.
$string['widget_total_users'] = 'Total Users';
$string['widget_total_courses'] = 'Total Courses';
$string['widget_site_visits'] = 'Site Visits Over Time';

// Widget data labels.
$string['active_users'] = 'Active Users';
$string['suspended_users'] = 'Suspended Users';
$string['deleted_users'] = 'Deleted Users';
$string['visible_courses'] = 'Visible Courses';
$string['hidden_courses'] = 'Hidden Courses';
$string['total_categories'] = 'Total Categories';
$string['daily_visits'] = 'Daily Unique Visitors';
$string['weekly_visits'] = 'Weekly Visits';
$string['monthly_visits'] = 'Monthly Visits';

// Capabilities.
$string['analysis_dashboard:viewsite'] = 'View site-level analysis dashboard';
$string['analysis_dashboard:viewcourse'] = 'View course-level analysis dashboard';
$string['analysis_dashboard:viewuser'] = 'View user-level analysis reports';
$string['analysis_dashboard:viewown'] = 'View own analytics dashboard';

// Settings.
$string['settings_cache_ttl_sitestats'] = 'Site stats cache TTL (seconds)';
$string['settings_cache_ttl_sitestats_desc'] = 'How long site-level statistics are cached before being refreshed. Default: 3600 (1 hour).';
$string['settings_cache_ttl_coursestats'] = 'Course stats cache TTL (seconds)';
$string['settings_cache_ttl_coursestats_desc'] = 'How long course-level statistics are cached before being refreshed. Default: 1800 (30 minutes).';
$string['settings_site_visits_days'] = 'Site visits chart days';
$string['settings_site_visits_days_desc'] = 'Number of days to display in the Site Visits Over Time chart. Default: 30.';
$string['settings_disabled_widgets'] = 'Disable widgets';
$string['settings_disabled_widgets_desc'] = 'Check widgets to disable them site-wide. Disabled widgets will not appear on any dashboard.';
$string['settings_show_total_categories'] = 'Show Total Categories in Total Courses widget';
$string['settings_show_total_categories_desc'] = 'When enabled, the Total Courses widget will display a Total Categories count. Uncheck to hide it.';
$string['settings_heading_site_widgets'] = 'Site-Level Widgets (Manager)';
$string['settings_heading_site_widgets_desc'] = 'Widgets shown on the main admin/manager dashboard.';
$string['settings_heading_course_widgets'] = 'Course-Level Widgets (Teacher)';
$string['settings_heading_course_widgets_desc'] = 'Widgets shown on course analytics pages for teachers.';
$string['settings_heading_student_widgets'] = 'Student Personal Widgets';
$string['settings_heading_student_widgets_desc'] = 'Widgets shown on the student self-service dashboard.';
$string['settings_heading_secureotp_admin'] = 'SecureOTP Admin Security Widgets';
$string['settings_heading_secureotp_admin_desc'] = 'Security and audit widgets for SecureOTP administrators.';
$string['settings_heading_secureotp_demographics'] = 'SecureOTP Demographics Widgets';
$string['settings_heading_secureotp_demographics_desc'] = 'Demographic breakdown widgets for SecureOTP user data.';
$string['settings_heading_secureotp_user'] = 'SecureOTP Student & Teacher Widgets';
$string['settings_heading_secureotp_user_desc'] = 'Personal profile and course-level SecureOTP widgets.';
$string['settings_heading_admin_analytics'] = 'Admin Analytics Widgets';
$string['settings_heading_admin_analytics_desc'] = 'System-level analytics: authentication, disk usage, performance.';

// Scheduled tasks.
$string['task_aggregate_site_stats'] = 'Aggregate site statistics';
$string['task_aggregate_course_stats'] = 'Aggregate course statistics';

// Privacy.
$string['privacy:metadata'] = 'The Analysis Dashboard plugin does not store any personal data.';

// Phase 2: Course report.
$string['course_report'] = 'Course Analytics';
$string['course_report_subtitle'] = 'Analytics for {$a}';

// Phase 2: Course widget names.
$string['widget_enrollment_stats'] = 'Enrollment Stats';
$string['widget_completion_progress'] = 'Completion Progress';
$string['widget_grade_distribution'] = 'Grade Distribution';
$string['widget_course_visits'] = 'Course Visits Over Time';
$string['widget_activity_completion'] = 'Activity Completion Matrix';
$string['widget_recent_activity'] = 'Recent Activity';
$string['widget_at_risk_students'] = 'At-Risk Course Participant';
$string['widget_activity_heatmap'] = 'Learner Activity Heatmap';
$string['widget_quiz_analytics'] = 'Quiz Analytics';
$string['widget_feedback_summary'] = 'Feedback Summary';
$string['widget_feedback_form_analysis'] = 'Feedback Form Analysis';
$string['feedback_analysis_section'] = 'Feedback Analysis';
$string['feedback_analysis_desc'] = 'Response distribution across all feedback forms in this course.';
$string['chart_view'] = 'Chart';
$string['comments_view'] = 'Comments';
$string['no_comments'] = 'No comments available.';

// Phase 2: Course widget data labels.
$string['total_enrolled'] = 'Total Enrolled';
$string['active_enrolled'] = 'Active';
$string['inactive_enrolled'] = 'Inactive';
$string['completed'] = 'Completed';
$string['in_progress'] = 'In Progress';
$string['not_started'] = 'Not Started';
$string['grade_range'] = 'Grade Range';
$string['student_count'] = 'Course Participants';
$string['daily_course_visits'] = 'Daily Course Visitors';
$string['completion_not_enabled'] = 'Completion is not enabled for this course.';
$string['no_quizzes'] = 'No quizzes in this course.';
$string['average_score'] = 'Average Score %';
$string['pass_rate'] = 'Pass Rate %';
$string['total_attempts'] = 'Total Attempts';
$string['overall_average'] = 'Overall Average';
$string['last_access'] = 'Last Access';
$string['completion_pct'] = 'Completion %';
$string['grade_pct'] = 'Grade %';
$string['risk_score'] = 'Risk Score';
$string['time'] = 'Time';
$string['user'] = 'User';
$string['action'] = 'Action';
$string['target'] = 'Target';
$string['info'] = 'Info';

// Phase 3: Student dashboard.
$string['student_dashboard'] = 'My Analytics';
$string['student_dashboard_subtitle'] = 'Personal analytics dashboard for {$a}';

// Phase 3: Student widget names.
$string['widget_my_learning_overview'] = 'My Learning Overview';
$string['widget_my_course_progress'] = 'My Course Progress';
$string['widget_my_grade_overview'] = 'My Grade Overview';
$string['widget_overall_completion_status'] = 'Overall Completion Status';
$string['widget_my_login_history'] = 'My Login History';
$string['widget_my_recent_activity'] = 'My Recent Activity';


// Phase 3: Student widget data labels.
$string['enrolled_courses'] = 'Enrolled Courses';
$string['completed_courses'] = 'Completed Courses';
$string['inprogress_courses'] = 'In Progress';
$string['logins'] = 'Logins';
$string['pending'] = 'Pending';
$string['not_attempted'] = 'Not Attempted';
$string['no_trackable_activities'] = 'No trackable activities found across your enrolled courses.';
$string['duedate'] = 'Due Date';
$string['urgent'] = 'Urgent';
$string['days'] = 'days';
$string['best_score_pct'] = 'Best Score %';
$string['attempts_used'] = 'Attempts Used';

// Phase 3: SecureOTP admin widget names.
$string['widget_secureotp_account_status'] = 'SecureOTP Account Status';
$string['widget_secureotp_security_summary'] = 'SecureOTP Security Summary';
$string['widget_secureotp_otp_events'] = 'SecureOTP Events Timeline';
$string['widget_secureotp_audit_severity'] = 'SecureOTP Audit Severity';
$string['widget_secureotp_rate_limits'] = 'SecureOTP Rate Limits';
$string['widget_secureotp_failed_logins'] = 'SecureOTP Failed Logins';
$string['widget_secureotp_failed_by_location'] = 'Failed Logins by Location';
$string['widget_secureotp_import_history'] = 'SecureOTP Import History';
$string['widget_secureotp_users_by_source'] = 'Users by Source System';

// Phase 3: SecureOTP demographic widget names.
$string['widget_secureotp_by_location'] = 'Users by Working Location';
$string['widget_secureotp_by_rank'] = 'Users by Rank';
$string['widget_secureotp_by_unit'] = 'Users by Unit';
$string['widget_secureotp_by_city'] = 'Users by City';
$string['widget_secureotp_by_employee_type'] = 'Users by Employee Type';
$string['widget_secureotp_by_cpsp'] = 'Users by CP/SP Office';
$string['widget_secureotp_by_gender'] = 'Users by Gender';
$string['widget_secureotp_by_education'] = 'Users by Education';

// Phase 3: SecureOTP student/teacher widget names.
$string['widget_secureotp_my_profile'] = 'My SecureOTP Profile'; 
$string['widget_secureotp_my_login_history'] = 'My OTP Login History';
$string['widget_course_students_by_location'] = 'Course Participant by Location';
$string['widget_course_students_by_employee_type'] = 'Course Participant by Employee Type';
$string['widget_course_students_by_rank'] = 'Course Participant by Rank';

// Phase 3: SecureOTP data labels.
$string['secureotp_not_installed'] = 'The SecureOTP authentication plugin is not installed. SecureOTP widgets are unavailable.';
$string['no_secureotp_profile'] = 'No SecureOTP profile data found for your account.';
$string['locked_accounts'] = 'Locked Accounts';
$string['otp_enabled'] = 'OTP Enabled';
$string['password_enabled'] = 'Password Enabled';
$string['failed_today'] = 'Failed Today';
$string['event_count'] = 'Event Count';
$string['employee_id'] = 'Employee ID';
$string['ip_address'] = 'IP Address';
$string['user_agent'] = 'User Agent';
$string['failed_logins'] = 'Failed Logins';
$string['batch_id'] = 'Batch ID';
$string['source_system'] = 'Source System';
$string['success'] = 'Success';
$string['failed'] = 'Failed';
$string['duration'] = 'Duration';
$string['affected_users'] = 'Affected Users';
$string['user_count'] = 'Users';
$string['students'] = 'Course Participants';
$string['otp_logins'] = 'OTP Logins';
$string['field'] = 'Field';
$string['value'] = 'Value';
$string['employee_type'] = 'Employee Type';
$string['current_rank'] = 'Current Rank';
$string['working_location'] = 'Working Location';
$string['unit_name'] = 'Unit Name';
$string['cp_sp_office'] = 'CP/SP Office';

// Phase 3: Privacy.
$string['privacy:metadata:cache'] = 'The Analysis Dashboard caches widget data using Moodle Universal Cache (MUC), which may include user-keyed entries for personal dashboards.';

// Phase 4: Admin analytics widget names.
$string['widget_authentication_report'] = 'Authentication Report';
$string['widget_disk_usage'] = 'Disk Usage';
$string['widget_server_performance'] = 'Server Performance';

$string['widget_enrolled_methods'] = 'Enrolled Methods';

// Phase 4: Widget data labels.
$string['manual_logins'] = 'Manual Logins';
$string['oauth_logins'] = 'OAuth Logins';
$string['other_auth_logins'] = 'Other Auth Logins';
$string['failed_login_attempts'] = 'Failed Attempts';
$string['moodledata_size'] = 'Moodledata';
$string['database_size'] = 'Database';
$string['backup_size'] = 'Backups';
$string['disk_usage_not_computed'] = 'Run the disk usage scheduled task to populate this widget.';
$string['cpu_load'] = 'CPU Load (1 min)';
$string['memory_usage'] = 'Memory Usage';
$string['disk_free'] = 'Disk Free';
$string['server_data_unavailable'] = 'Server metrics not yet computed. They will be available after the next scheduled task run.';
$string['category_name'] = 'Category';
$string['course_count'] = 'Courses';
$string['enrolment_count'] = 'Enrolments';
$string['enrolment_method'] = 'Enrolment Method';
$string['users_enrolled'] = 'Users Enrolled';
$string['last_30_days'] = 'Last 30 Days';
$string['no_feedback_activities'] = 'No feedback activities in this course.';
$string['no_feedback_responses'] = 'No feedback responses have been submitted yet.';
$string['responses'] = 'Responses';
$string['export_csv'] = 'Export CSV';
$string['export_excel'] = 'Export Excel';

// Phase 4: Scheduled tasks.
$string['task_calculate_disk_usage'] = 'Calculate disk usage';
$string['task_cleanup_stale_cache'] = 'Clean up stale cache entries';

// Phase 5: Accessibility.
$string['loading'] = 'Loading widget data...';
$string['widget_loaded'] = 'Widget data loaded.';
$string['sr_chart_description'] = '{$a->type} chart showing {$a->title}';

// Phase 5: Manager dashboard.
$string['manager_dashboard'] = 'Manager Analytics Dashboard';
$string['manager_dashboard_desc'] = 'Organizational analytics overview for managers.';

<?php

namespace local_analysis_dashboard\util;

defined('MOODLE_INTERNAL') || die();

class data_helper {

    /**
     * Get the configured district field.
     *
     * @return string
     */
    private static function get_district_field() {
        return get_config('local_analysis_dashboard', 'district_field') ?: 'city';
    }

    /**
     * Get user count per district.
     *
     * @return array
     */
    public static function get_users_per_district() {
        global $DB;

        $districtfield = self::get_district_field();

        // Ensure the field is safe to use in query (basic whitelist check)
        $allowedfields = ['city', 'cp_sp_office', 'working_location', 'education_category'];
        if (!in_array($districtfield, $allowedfields)) {
            $districtfield = 'city';
        }

        $sql = "SELECT $districtfield as district, COUNT(u.id) as usercount
                FROM {user} u
                JOIN {auth_secureotp_userdata} sud ON u.id = sud.userid
                WHERE u.deleted = 0 AND u.suspended = 0
                GROUP BY $districtfield
                ORDER BY usercount DESC";

        return $DB->get_records_sql($sql);
    }

    /**
     * Get student count per district for a specific course.
     *
     * @param int $courseid
     * @return array
     */
    public static function get_course_students_per_district($courseid) {
        global $DB;

        $districtfield = self::get_district_field();

        // Ensure the field is safe to use in query
        $allowedfields = ['city', 'cp_sp_office', 'working_location', 'education_category'];
        if (!in_array($districtfield, $allowedfields)) {
            $districtfield = 'city';
        }

        $context = \context_course::instance($courseid);
        // Get role IDs for students (default usually 5)
        // Better approach: use get_role_users or similar API, but for raw stats SQL is faster.
        // We will assume standard student role or check enrolments.

        $sql = "SELECT sud.$districtfield as district, COUNT(DISTINCT u.id) as usercount
                FROM {user} u
                JOIN {user_enrolments} ue ON ue.userid = u.id
                JOIN {enrol} e ON e.id = ue.enrolid
                JOIN {auth_secureotp_userdata} sud ON u.id = sud.userid
                WHERE e.courseid = :courseid
                  AND u.deleted = 0
                  AND u.suspended = 0
                  AND ue.status = :active
                GROUP BY sud.$districtfield
                ORDER BY usercount DESC";

        return $DB->get_records_sql($sql, ['courseid' => $courseid, 'active' => ENROL_USER_ACTIVE]);
    }
}

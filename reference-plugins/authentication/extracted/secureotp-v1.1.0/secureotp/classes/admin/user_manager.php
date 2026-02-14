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
 * User Management class for admin operations
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * User Manager class
 */
class user_manager {

    /**
     * @var object Audit logger
     */
    private $audit_logger;

    /**
     * Constructor
     */
    public function __construct() {
        require_once(__DIR__ . '/../security/audit_logger.php');
        $this->audit_logger = new \auth_secureotp\security\audit_logger();
    }

    /**
     * Search users by various criteria
     *
     * @param string $query Search query (employee_id, name, mobile, email)
     * @param string $status Filter by status (ACTIVE, SUSPENDED, etc)
     * @param int $page Page number (0-indexed)
     * @param int $perpage Results per page
     * @return array Results with users and total count
     */
    public function search_users($query = '', $status = '', $page = 0, $perpage = 50) {
        global $DB;

        $params = array();
        $where = array('1=1'); // Always true condition.

        // Build SQL query.
        if (!empty($query)) {
            $query = trim($query);
            $where[] = '(u.username LIKE :query1 OR u.firstname LIKE :query2 OR u.lastname LIKE :query3 OR
                        u.email LIKE :query4 OR ud.employee_id LIKE :query5 OR ud.personal_mobile LIKE :query6)';
            $params['query1'] = "%$query%";
            $params['query2'] = "%$query%";
            $params['query3'] = "%$query%";
            $params['query4'] = "%$query%";
            $params['query5'] = "%$query%";
            $params['query6'] = "%$query%";
        }

        if (!empty($status)) {
            $where[] = 's.status = :status';
            $params['status'] = $status;
        }

        $where_sql = implode(' AND ', $where);

        $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email,
                       ud.employee_id, ud.personal_mobile, ud.current_rank, ud.working_location,
                       s.status, s.last_login_at, s.login_count, s.is_locked, s.locked_until
                FROM {user} u
                LEFT JOIN {auth_secureotp_userdata} ud ON u.id = ud.userid
                LEFT JOIN {auth_secureotp_security} s ON u.id = s.userid
                WHERE u.auth = 'secureotp' AND u.deleted = 0 AND $where_sql
                ORDER BY u.lastname, u.firstname";

        // Get total count.
        $count_sql = "SELECT COUNT(u.id)
                      FROM {user} u
                      LEFT JOIN {auth_secureotp_userdata} ud ON u.id = ud.userid
                      LEFT JOIN {auth_secureotp_security} s ON u.id = s.userid
                      WHERE u.auth = 'secureotp' AND u.deleted = 0 AND $where_sql";

        $total = $DB->count_records_sql($count_sql, $params);

        // Get paginated results.
        $users = $DB->get_records_sql($sql, $params, $page * $perpage, $perpage);

        // Format users for display.
        $formatted_users = array();
        foreach ($users as $user) {
            $formatted_users[] = array(
                'id' => $user->id,
                'username' => $user->username,
                'fullname' => fullname($user),
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'mobile' => $user->personal_mobile,
                'rank' => $user->current_rank,
                'location' => $user->working_location,
                'status' => $user->status,
                'status_class' => $this->get_status_class($user->status),
                'last_login' => $user->last_login_at ? userdate($user->last_login_at) : 'Never',
                'login_count' => $user->login_count,
                'is_locked' => $user->is_locked,
                'locked_until' => $user->locked_until ? userdate($user->locked_until) : null
            );
        }

        return array(
            'users' => $formatted_users,
            'total' => $total,
            'page' => $page,
            'perpage' => $perpage,
            'pages' => ceil($total / $perpage)
        );
    }

    /**
     * Get user details
     *
     * @param int $userid User ID
     * @return array User details
     */
    public function get_user_details($userid) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        $userdata = $DB->get_record('auth_secureotp_userdata', array('userid' => $userid));
        $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));

        return array(
            'user' => $user,
            'userdata' => $userdata,
            'security' => $security
        );
    }

    /**
     * Unlock user account
     *
     * @param int $userid User ID
     * @param int $admin_id Admin user ID performing action
     * @return array Result
     */
    public function unlock_account($userid, $admin_id) {
        global $DB;

        try {
            $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));

            if (!$security) {
                return array('success' => false, 'error' => 'User security record not found');
            }

            if (!$security->is_locked) {
                return array('success' => false, 'error' => 'Account is not locked');
            }

            // Unlock account.
            $DB->set_field('auth_secureotp_security', 'is_locked', 0, array('id' => $security->id));
            $DB->set_field('auth_secureotp_security', 'locked_until', null, array('id' => $security->id));
            $DB->set_field('auth_secureotp_security', 'locked_reason', null, array('id' => $security->id));
            $DB->set_field('auth_secureotp_security', 'failed_attempts', 0, array('id' => $security->id));

            // Log action.
            $this->audit_logger->log_event(
                'ACCOUNT_UNLOCKED',
                'SUCCESS',
                $userid,
                null,
                array('admin_id' => $admin_id),
                'INFO'
            );

            return array('success' => true);

        } catch (\Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    /**
     * Suspend user account
     *
     * @param int $userid User ID
     * @param int $admin_id Admin user ID performing action
     * @param string $reason Suspension reason
     * @return array Result
     */
    public function suspend_account($userid, $admin_id, $reason = '') {
        global $DB;

        try {
            $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));

            if (!$security) {
                return array('success' => false, 'error' => 'User security record not found');
            }

            // Suspend account.
            $DB->set_field('auth_secureotp_security', 'status', 'SUSPENDED', array('id' => $security->id));

            // Log action.
            $this->audit_logger->log_event(
                'ACCOUNT_SUSPENDED',
                'SUCCESS',
                $userid,
                null,
                array('admin_id' => $admin_id, 'reason' => $reason),
                'WARNING'
            );

            return array('success' => true);

        } catch (\Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    /**
     * Activate suspended account
     *
     * @param int $userid User ID
     * @param int $admin_id Admin user ID performing action
     * @return array Result
     */
    public function activate_account($userid, $admin_id) {
        global $DB;

        try {
            $security = $DB->get_record('auth_secureotp_security', array('userid' => $userid));

            if (!$security) {
                return array('success' => false, 'error' => 'User security record not found');
            }

            // Activate account.
            $DB->set_field('auth_secureotp_security', 'status', 'ACTIVE', array('id' => $security->id));

            // Log action.
            $this->audit_logger->log_event(
                'ACCOUNT_ACTIVATED',
                'SUCCESS',
                $userid,
                null,
                array('admin_id' => $admin_id),
                'INFO'
            );

            return array('success' => true);

        } catch (\Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    /**
     * Send test OTP to user
     *
     * @param int $userid User ID
     * @param int $admin_id Admin user ID performing action
     * @return array Result
     */
    public function send_test_otp($userid, $admin_id) {
        global $DB;

        try {
            $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

            // Generate test OTP.
            require_once(__DIR__ . '/../auth/otp_manager.php');
            $otp_manager = new \auth_secureotp\auth\otp_manager();
            $otp_result = $otp_manager->generate_otp($userid);

            if (!$otp_result['success']) {
                return array('success' => false, 'error' => 'Failed to generate OTP');
            }

            // Send OTP using public resend_otp() method.
            require_once(__DIR__ . '/../../auth.php');
            $auth = new \auth_plugin_secureotp();
            $send_result = $auth->resend_otp($user, $otp_result['otp']);

            if ($send_result['success']) {
                // Log action.
                $this->audit_logger->log_event(
                    'TEST_OTP_SENT',
                    'SUCCESS',
                    $userid,
                    null,
                    array('admin_id' => $admin_id, 'method' => $send_result['method']),
                    'INFO'
                );

                return array(
                    'success' => true,
                    'method' => $send_result['method'],
                    'recipient' => $send_result['recipient_masked']
                );
            } else {
                return array('success' => false, 'error' => $send_result['error']);
            }

        } catch (\Exception $e) {
            return array('success' => false, 'error' => $e->getMessage());
        }
    }

    /**
     * Get audit log for user
     *
     * @param int $userid User ID
     * @param int $limit Number of records
     * @return array Audit log entries
     */
    public function get_user_audit_log($userid, $limit = 50) {
        global $DB;

        $logs = $DB->get_records('auth_secureotp_audit',
            array('userid' => $userid),
            'timecreated DESC',
            '*',
            0,
            $limit
        );

        $formatted_logs = array();
        foreach ($logs as $log) {
            $formatted_logs[] = array(
                'event_type' => $log->event_type,
                'event_status' => $log->event_status,
                'severity' => $log->severity,
                'ip_address' => $log->ip_address,
                'event_data' => $log->event_data ? json_decode($log->event_data, true) : array(),
                'time' => userdate($log->timecreated),
                'timeago' => $this->time_ago($log->timecreated)
            );
        }

        return $formatted_logs;
    }

    /**
     * Get status CSS class
     *
     * @param string $status Status value
     * @return string CSS class
     */
    private function get_status_class($status) {
        $classes = array(
            'ACTIVE' => 'success',
            'PROVISIONED' => 'info',
            'SUSPENDED' => 'warning',
            'ARCHIVED' => 'secondary'
        );

        return isset($classes[$status]) ? $classes[$status] : 'secondary';
    }

    /**
     * Format time ago
     *
     * @param int $timestamp Unix timestamp
     * @return string Time ago string
     */
    private function time_ago($timestamp) {
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Just now';
        } else if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } else if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }

    /**
     * Bulk unlock accounts
     *
     * @param array $userids Array of user IDs
     * @param int $admin_id Admin user ID
     * @return array Result
     */
    public function bulk_unlock($userids, $admin_id) {
        $results = array('success' => 0, 'failed' => 0, 'errors' => array());

        foreach ($userids as $userid) {
            $result = $this->unlock_account($userid, $admin_id);
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "User $userid: " . $result['error'];
            }
        }

        return $results;
    }

    /**
     * Export users to CSV
     *
     * @param string $status Filter by status
     * @return string CSV content
     */
    public function export_to_csv($status = '') {
        $search_result = $this->search_users('', $status, 0, 100000);
        $users = $search_result['users'];

        $csv = "Employee ID,Username,Full Name,Email,Mobile,Rank,Location,Status,Login Count,Last Login\n";

        foreach ($users as $user) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $user['employee_id'],
                $user['username'],
                $user['fullname'],
                $user['email'],
                $user['mobile'],
                $user['rank'],
                $user['location'],
                $user['status'],
                $user['login_count'],
                $user['last_login']
            );
        }

        return $csv;
    }
}

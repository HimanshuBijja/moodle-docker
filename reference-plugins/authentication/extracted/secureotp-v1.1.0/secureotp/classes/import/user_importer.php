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
 * Bulk User Importer
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\import;

defined('MOODLE_INTERNAL') || die();

/**
 * User Importer class for bulk CSV imports.
 *
 * Note: user/lib.php and moodlelib.php are loaded during Moodle core setup.
 * Do NOT use require_once at file scope in class files — they may be autoloaded
 * from within a function where $CFG is not in scope.
 */
class user_importer {

    /**
     * @var array Import statistics
     */
    private $stats = array(
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'failed' => 0,
        'skipped' => 0,
        'errors' => array()
    );

    /**
     * @var string Batch ID (UUID)
     */
    private $batch_id;

    /**
     * @var object Audit logger
     */
    private $audit_logger;

    /**
     * @var int Batch size for transactions
     */
    private $batch_size = 100;

    /**
     * Constructor
     */
    public function __construct() {
        $this->batch_id = $this->generate_batch_id();
        require_once(__DIR__ . '/../security/audit_logger.php');
        $this->audit_logger = new \auth_secureotp\security\audit_logger();
    }

    /**
     * Import users from CSV file
     *
     * @param string $filepath Path to CSV file
     * @param string $source_system Source system identifier (HR_MASTER/STUDENT_DB)
     * @param bool $dry_run Validate only, no DB changes
     * @param int $started_by User ID who initiated import
     * @return array Import result
     */
    public function import_from_csv($filepath, $source_system, $dry_run = false, $started_by = 0) {
        global $DB;

        $started_at = time();

        try {
            // Validate file exists.
            if (!file_exists($filepath)) {
                throw new \moodle_exception('filenotfound', 'error', '', $filepath);
            }

            // Validate file is readable.
            if (!is_readable($filepath)) {
                throw new \moodle_exception('filenotreadable', 'error', '', $filepath);
            }

            // Create import log record.
            $import_log = new \stdClass();
            $import_log->batch_id = $this->batch_id;
            $import_log->source_system = $source_system;
            $import_log->source_file = basename($filepath);
            $import_log->status = 'RUNNING';
            $import_log->started_by = $started_by;
            $import_log->started_at = $started_at;
            $import_log->timecreated = time();

            if (!$dry_run) {
                $log_id = $DB->insert_record('auth_secureotp_import_log', $import_log);
            } else {
                $log_id = 0;
            }

            // Open and parse CSV.
            $handle = fopen($filepath, 'r');
            if ($handle === false) {
                throw new \moodle_exception('cannotreadfile', 'error', '', $filepath);
            }

            // Read header row.
            $headers = fgetcsv($handle);
            if ($headers === false) {
                fclose($handle);
                throw new \moodle_exception('csvemptyfile', 'error');
            }

            // Normalize headers.
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);

            // Validate required fields.
            $required_fields = array('employee_id', 'firstname', 'lastname');
            foreach ($required_fields as $field) {
                if (!in_array($field, $headers)) {
                    fclose($handle);
                    throw new \moodle_exception('missingrequiredfield', 'auth_secureotp', '', $field);
                }
            }

            // Process rows.
            $row_number = 1;
            $transaction_count = 0;

            while (($data = fgetcsv($handle)) !== false) {
                $row_number++;
                $this->stats['total']++;

                try {
                    // Ensure column count matches header count.
                    if (count($data) !== count($headers)) {
                        throw new \moodle_exception('csvcolumnmismatch', 'auth_secureotp', '',
                            "Row {$row_number}: expected " . count($headers) . " columns, got " . count($data));
                    }

                    // Combine headers with data.
                    $row = array_combine($headers, $data);

                    // Validate row data.
                    $this->validate_row($row, $row_number);

                    // Process row (create or update user).
                    if (!$dry_run) {
                        // Start transaction for every batch_size records.
                        if ($transaction_count === 0) {
                            $transaction = $DB->start_delegated_transaction();
                        }

                        $result = $this->process_row($row, $source_system);

                        if ($result['action'] === 'created') {
                            $this->stats['created']++;
                            mtrace("  Created user: {$row['employee_id']} ({$row['firstname']} {$row['lastname']})");
                        } else if ($result['action'] === 'updated') {
                            $this->stats['updated']++;
                            mtrace("  Updated user: {$row['employee_id']}");
                        } else if ($result['action'] === 'skipped') {
                            $this->stats['skipped']++;
                        }

                        $transaction_count++;

                        // Commit transaction every batch_size records.
                        if ($transaction_count >= $this->batch_size) {
                            $transaction->allow_commit();
                            mtrace("  Committed batch of {$this->batch_size} records");
                            unset($transaction); // Clear transaction object
                            $transaction_count = 0;
                        }
                    }

                } catch (\Exception $e) {
                    $this->stats['failed']++;
                    $this->stats['errors'][] = array(
                        'row' => $row_number,
                        'error' => $e->getMessage(),
                        'data' => $data
                    );

                    mtrace("  ERROR on row {$row_number}: " . $e->getMessage());

                    // Rollback transaction on error.
                    if (!$dry_run && isset($transaction)) {
                        try {
                            $transaction->rollback($e);
                            mtrace("  Rolled back transaction due to error");
                        } catch (\Exception $rollback_error) {
                            // Already rolled back.
                        }
                        unset($transaction); // Clear transaction object
                        $transaction_count = 0;
                    }

                    // Continue to next row.
                    continue;
                }
            }

            // Commit any remaining records.
            if (!$dry_run && isset($transaction) && $transaction_count > 0) {
                $transaction->allow_commit();
                mtrace("  Committed final batch of {$transaction_count} records");
                unset($transaction); // Clear transaction object
                $transaction_count = 0;
            }

            fclose($handle);

            $completed_at = time();
            $duration = $completed_at - $started_at;

            // Update import log.
            if (!$dry_run && $log_id) {
                $DB->set_field('auth_secureotp_import_log', 'total_records', $this->stats['total'], array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'created_count', $this->stats['created'], array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'updated_count', $this->stats['updated'], array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'failed_count', $this->stats['failed'], array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'success_count', $this->stats['created'] + $this->stats['updated'], array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'error_log', json_encode($this->stats['errors']), array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'status', 'COMPLETED', array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'completed_at', $completed_at, array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'duration_seconds', $duration, array('id' => $log_id));
            }

            // Log audit event (non-fatal if it fails).
            try {
                $this->audit_logger->log_event(
                    'BULK_IMPORT_COMPLETED',  // event type
                    'SUCCESS',                // event status
                    $started_by,              // userid (admin who started import)
                    'CLI',                    // ip address
                    array(                    // event data
                        'batch_id' => $this->batch_id,
                        'source_system' => $source_system,
                        'total' => $this->stats['total'],
                        'created' => $this->stats['created'],
                        'updated' => $this->stats['updated'],
                        'failed' => $this->stats['failed'],
                        'dry_run' => $dry_run,
                        'duration' => $duration
                    ),
                    'INFO'                    // severity
                );
            } catch (\Exception $audit_error) {
                // Audit logging failed, but don't fail the import
                mtrace('Warning: Audit logging failed: ' . $audit_error->getMessage());
            }

            return array(
                'success' => true,
                'batch_id' => $this->batch_id,
                'stats' => $this->stats,
                'duration' => $duration,
                'dry_run' => $dry_run
            );

        } catch (\Exception $e) {
            // Log failure.
            if (!$dry_run && isset($log_id) && $log_id) {
                $DB->set_field('auth_secureotp_import_log', 'status', 'FAILED', array('id' => $log_id));
                $DB->set_field('auth_secureotp_import_log', 'error_log', json_encode(array(array('error' => $e->getMessage()))), array('id' => $log_id));
            }

            // Log audit event (non-fatal if it fails).
            try {
                $this->audit_logger->log_event(
                    'BULK_IMPORT_FAILED',     // event type
                    'FAILURE',                // event status
                    $started_by,              // userid (admin who started import)
                    'CLI',                    // ip address
                    array(                    // event data
                        'batch_id' => $this->batch_id,
                        'error' => $e->getMessage()
                    ),
                    'CRITICAL'                // severity
                );
            } catch (\Exception $audit_error) {
                // Audit logging failed, but still return the import error
                mtrace('Warning: Audit logging failed: ' . $audit_error->getMessage());
            }

            return array(
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => $this->stats
            );
        }
    }

    /**
     * Validate row data
     *
     * @param array $row Row data
     * @param int $row_number Row number
     * @throws \moodle_exception
     */
    private function validate_row($row, $row_number) {
        // Validate employee_id.
        if (empty($row['employee_id'])) {
            throw new \moodle_exception('emptyemployeeid', 'auth_secureotp', '', $row_number);
        }

        // Validate firstname.
        if (empty($row['firstname'])) {
            throw new \moodle_exception('emptyfirstname', 'auth_secureotp', '', $row_number);
        }

        // Validate lastname.
        if (empty($row['lastname'])) {
            throw new \moodle_exception('emptylastname', 'auth_secureotp', '', $row_number);
        }

        // Validate mobile format if provided.
        if (!empty($row['personal_mobile'])) {
            $mobile = preg_replace('/[^0-9]/', '', $row['personal_mobile']);
            if (strlen($mobile) < 10) {
                throw new \moodle_exception('invalidmobile', 'auth_secureotp', '', $row_number);
            }
        }

        // Validate email format if provided.
        if (!empty($row['email']) && !validate_email($row['email'])) {
            throw new \moodle_exception('invalidemail', 'auth_secureotp', '', $row_number);
        }
    }

    /**
     * Process single row (create or update user)
     *
     * @param array $row Row data
     * @param string $source_system Source system
     * @return array Result with action (created/updated/skipped)
     */
    private function process_row($row, $source_system) {
        global $DB, $CFG;

        $employee_id = trim($row['employee_id']);

        // Calculate hash of source record for change detection.
        $source_hash = $this->calculate_record_hash($row);

        // Check if user exists by employee_id.
        $userdata = $DB->get_record('auth_secureotp_userdata', array('employee_id' => $employee_id));

        if ($userdata) {
            // User exists - check if update needed.
            if ($userdata->source_record_hash === $source_hash) {
                // No changes, skip.
                return array('action' => 'skipped', 'userid' => $userdata->userid);
            }

            // Update existing user.
            $result = $this->update_user($userdata, $row, $source_system, $source_hash);
            return array('action' => 'updated', 'userid' => $result);

        } else {
            // Create new user.
            $result = $this->create_user($row, $source_system, $source_hash);
            return array('action' => 'created', 'userid' => $result);
        }
    }

    /**
     * Create new user
     *
     * @param array $row Row data
     * @param string $source_system Source system
     * @param string $source_hash Source record hash
     * @return int User ID
     */
    private function create_user($row, $source_system, $source_hash) {
        global $DB, $CFG;

        // Ensure user/lib.php is loaded (provides user_create_user).
        require_once($CFG->dirroot . '/user/lib.php');

        // Prepare Moodle user object.
        $user = new \stdClass();
        $user->auth = 'secureotp';
        $user->username = strtolower($row['employee_id']); // Moodle requires lowercase usernames
        $user->firstname = $row['firstname'];
        $user->lastname = $row['lastname'];
        $user->email = !empty($row['email']) ? $row['email'] : $row['employee_id'] . '@training.local';
        $user->confirmed = 1;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->lang = $CFG->lang;
        $user->timezone = $CFG->timezone;

        // Set password to random (not used for OTP auth).
        $user->password = hash_internal_user_password(random_string(20));

        // Optional fields.
        if (!empty($row['city'])) {
            $user->city = $row['city'];
        }
        if (!empty($row['personal_mobile'])) {
            $user->phone1 = $row['personal_mobile'];
        }

        // Create user in Moodle.
        $userid = user_create_user($user, false, false);

        // Create extended userdata record.
        $userdata = new \stdClass();
        $userdata->userid = $userid;
        $userdata->employee_id = $row['employee_id'];
        $userdata->pao_code = !empty($row['pao_code']) ? $row['pao_code'] : null;
        $userdata->employee_type = !empty($row['employee_type']) ? $row['employee_type'] : null;
        $userdata->date_of_birth = !empty($row['date_of_birth']) ? strtotime($row['date_of_birth']) : null;
        $userdata->gender = !empty($row['gender']) ? $row['gender'] : null;
        $userdata->education_category = !empty($row['education_category']) ? $row['education_category'] : null;
        $userdata->date_of_joining = !empty($row['date_of_joining']) ? strtotime($row['date_of_joining']) : null;
        $userdata->initial_appointment_rank = !empty($row['initial_appointment_rank']) ? $row['initial_appointment_rank'] : null;
        $userdata->current_rank = !empty($row['current_rank']) ? $row['current_rank'] : null;
        $userdata->working_location = !empty($row['working_location']) ? $row['working_location'] : null;
        $userdata->cp_sp_office = !empty($row['cp_sp_office']) ? $row['cp_sp_office'] : null;
        $userdata->unit_name = !empty($row['unit_name']) ? $row['unit_name'] : null;
        $userdata->department_no = !empty($row['department_no']) ? $row['department_no'] : null;
        $userdata->personal_number = !empty($row['personal_number']) ? $row['personal_number'] : null;
        $userdata->personal_mobile = !empty($row['personal_mobile']) ? $row['personal_mobile'] : null;
        $userdata->city = !empty($row['city']) ? $row['city'] : null;
        $userdata->source_system = $source_system;
        $userdata->source_record_hash = $source_hash;
        $userdata->last_sync_at = time();
        $userdata->timecreated = time();
        $userdata->timemodified = time();

        $DB->insert_record('auth_secureotp_userdata', $userdata);

        // Create security record.
        $security = new \stdClass();
        $security->userid = $userid;
        $security->status = 'PROVISIONED';
        $security->otp_enabled = 1;
        $security->password_enabled = 0;
        $security->login_count = 0;
        $security->failed_attempts = 0;
        $security->is_locked = 0;
        $security->timecreated = time();
        $security->timemodified = time();

        $DB->insert_record('auth_secureotp_security', $security);

        return $userid;
    }

    /**
     * Update existing user
     *
     * @param object $userdata Existing userdata record
     * @param array $row New row data
     * @param string $source_system Source system
     * @param string $source_hash New source hash
     * @return int User ID
     */
    private function update_user($userdata, $row, $source_system, $source_hash) {
        global $DB, $CFG;

        // Ensure user/lib.php is loaded (provides user_update_user).
        require_once($CFG->dirroot . '/user/lib.php');

        $userid = $userdata->userid;

        // Update Moodle user record.
        $user = $DB->get_record('user', array('id' => $userid));
        if ($user) {
            $user->firstname = $row['firstname'];
            $user->lastname = $row['lastname'];
            if (!empty($row['email'])) {
                $user->email = $row['email'];
            }
            if (!empty($row['city'])) {
                $user->city = $row['city'];
            }
            if (!empty($row['personal_mobile'])) {
                $user->phone1 = $row['personal_mobile'];
            }
            $user->timemodified = time();

            user_update_user($user, false, false);
        }

        // Update userdata record.
        $update = new \stdClass();
        $update->id = $userdata->id;
        $update->pao_code = !empty($row['pao_code']) ? $row['pao_code'] : null;
        $update->employee_type = !empty($row['employee_type']) ? $row['employee_type'] : null;
        $update->date_of_birth = !empty($row['date_of_birth']) ? strtotime($row['date_of_birth']) : null;
        $update->gender = !empty($row['gender']) ? $row['gender'] : null;
        $update->education_category = !empty($row['education_category']) ? $row['education_category'] : null;
        $update->date_of_joining = !empty($row['date_of_joining']) ? strtotime($row['date_of_joining']) : null;
        $update->initial_appointment_rank = !empty($row['initial_appointment_rank']) ? $row['initial_appointment_rank'] : null;
        $update->current_rank = !empty($row['current_rank']) ? $row['current_rank'] : null;
        $update->working_location = !empty($row['working_location']) ? $row['working_location'] : null;
        $update->cp_sp_office = !empty($row['cp_sp_office']) ? $row['cp_sp_office'] : null;
        $update->unit_name = !empty($row['unit_name']) ? $row['unit_name'] : null;
        $update->department_no = !empty($row['department_no']) ? $row['department_no'] : null;
        $update->personal_number = !empty($row['personal_number']) ? $row['personal_number'] : null;
        $update->personal_mobile = !empty($row['personal_mobile']) ? $row['personal_mobile'] : null;
        $update->city = !empty($row['city']) ? $row['city'] : null;
        $update->source_record_hash = $source_hash;
        $update->last_sync_at = time();
        $update->timemodified = time();

        $DB->update_record('auth_secureotp_userdata', $update);

        return $userid;
    }

    /**
     * Calculate hash of source record for change detection
     *
     * @param array $row Row data
     * @return string SHA-256 hash
     */
    private function calculate_record_hash($row) {
        // Include only data fields (not metadata).
        $data_fields = array(
            'employee_id', 'firstname', 'lastname', 'email',
            'pao_code', 'employee_type', 'date_of_birth', 'gender',
            'education_category', 'date_of_joining', 'initial_appointment_rank',
            'current_rank', 'working_location', 'cp_sp_office', 'unit_name',
            'department_no', 'personal_number', 'personal_mobile', 'city'
        );

        $hash_data = array();
        foreach ($data_fields as $field) {
            $hash_data[$field] = isset($row[$field]) ? $row[$field] : '';
        }

        return hash('sha256', json_encode($hash_data));
    }

    /**
     * Generate unique batch ID
     *
     * @return string UUID
     */
    private function generate_batch_id() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff), random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
        );
    }

    /**
     * Get import statistics
     *
     * @return array Statistics
     */
    public function get_stats() {
        return $this->stats;
    }

    /**
     * Set batch size for transactions
     *
     * @param int $size Batch size
     */
    public function set_batch_size($size) {
        $this->batch_size = max(1, intval($size));
    }
}

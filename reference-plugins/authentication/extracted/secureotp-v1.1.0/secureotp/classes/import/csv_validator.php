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
 * CSV Validator for user imports
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp\import;

defined('MOODLE_INTERNAL') || die();

/**
 * CSV Validator class
 */
class csv_validator {

    /**
     * @var array Required fields
     */
    private $required_fields = array('employee_id', 'firstname', 'lastname');

    /**
     * @var array Optional fields
     */
    private $optional_fields = array(
        'email', 'pao_code', 'employee_type', 'date_of_birth', 'gender',
        'education_category', 'date_of_joining', 'initial_appointment_rank',
        'current_rank', 'working_location', 'cp_sp_office', 'unit_name',
        'department_no', 'personal_number', 'personal_mobile', 'city'
    );

    /**
     * @var array Validation errors
     */
    private $errors = array();

    /**
     * @var array Validation warnings
     */
    private $warnings = array();

    /**
     * Validate CSV file
     *
     * @param string $filepath Path to CSV file
     * @return array Validation result
     */
    public function validate($filepath) {
        $this->errors = array();
        $this->warnings = array();

        try {
            // Check file exists.
            if (!file_exists($filepath)) {
                $this->errors[] = 'File not found: ' . $filepath;
                return $this->get_result();
            }

            // Check file is readable.
            if (!is_readable($filepath)) {
                $this->errors[] = 'File is not readable: ' . $filepath;
                return $this->get_result();
            }

            // Check file size (warn if > 50MB).
            $filesize = filesize($filepath);
            if ($filesize > 50 * 1024 * 1024) {
                $this->warnings[] = 'Large file detected (' . round($filesize / 1024 / 1024, 2) . ' MB). Import may take significant time.';
            }

            // Open file.
            $handle = fopen($filepath, 'r');
            if ($handle === false) {
                $this->errors[] = 'Cannot open file: ' . $filepath;
                return $this->get_result();
            }

            // Read and validate header.
            $headers = fgetcsv($handle);
            if ($headers === false) {
                $this->errors[] = 'CSV file is empty or cannot read header row.';
                fclose($handle);
                return $this->get_result();
            }

            // Normalize headers.
            $headers = array_map('trim', $headers);
            $headers = array_map('strtolower', $headers);

            // Check for required fields.
            foreach ($this->required_fields as $field) {
                if (!in_array($field, $headers)) {
                    $this->errors[] = 'Missing required column: ' . $field;
                }
            }

            // Check for duplicate headers.
            $duplicates = array_diff_assoc($headers, array_unique($headers));
            if (!empty($duplicates)) {
                $this->errors[] = 'Duplicate column names found: ' . implode(', ', array_unique($duplicates));
            }

            // If header errors, stop here.
            if (!empty($this->errors)) {
                fclose($handle);
                return $this->get_result();
            }

            // Validate data rows.
            $row_number = 1;
            $total_rows = 0;
            $employee_ids = array();
            $sample_errors = 0;
            $max_sample_errors = 10; // Limit error samples.

            while (($data = fgetcsv($handle)) !== false && $sample_errors < $max_sample_errors) {
                $row_number++;
                $total_rows++;

                // Combine headers with data.
                $row = array_combine($headers, $data);

                // Validate required fields not empty.
                foreach ($this->required_fields as $field) {
                    if (empty($row[$field])) {
                        $this->errors[] = "Row $row_number: Missing value for required field '$field'";
                        $sample_errors++;
                        break;
                    }
                }

                // Check for duplicate employee_id within CSV.
                $employee_id = trim($row['employee_id']);
                if (in_array($employee_id, $employee_ids)) {
                    $this->errors[] = "Row $row_number: Duplicate employee_id '$employee_id'";
                    $sample_errors++;
                } else {
                    $employee_ids[] = $employee_id;
                }

                // Validate mobile format.
                if (!empty($row['personal_mobile'])) {
                    $mobile = preg_replace('/[^0-9]/', '', $row['personal_mobile']);
                    if (strlen($mobile) < 10) {
                        $this->warnings[] = "Row $row_number: Mobile number seems too short ('{$row['personal_mobile']}')";
                    }
                }

                // Validate email format.
                if (!empty($row['email']) && !validate_email($row['email'])) {
                    $this->warnings[] = "Row $row_number: Invalid email format ('{$row['email']}')";
                }

                // Validate date formats.
                if (!empty($row['date_of_birth']) && !$this->validate_date($row['date_of_birth'])) {
                    $this->warnings[] = "Row $row_number: Invalid date_of_birth format ('{$row['date_of_birth']}'). Expected: YYYY-MM-DD";
                }

                if (!empty($row['date_of_joining']) && !$this->validate_date($row['date_of_joining'])) {
                    $this->warnings[] = "Row $row_number: Invalid date_of_joining format ('{$row['date_of_joining']}'). Expected: YYYY-MM-DD";
                }

                // Validate gender.
                if (!empty($row['gender']) && !in_array(strtoupper($row['gender']), array('M', 'F', 'O', 'MALE', 'FEMALE', 'OTHER'))) {
                    $this->warnings[] = "Row $row_number: Unexpected gender value ('{$row['gender']}'). Expected: M/F/O";
                }
            }

            fclose($handle);

            // Add summary info.
            $this->warnings[] = "Total rows to import: $total_rows";

            if ($total_rows === 0) {
                $this->errors[] = 'CSV file contains no data rows.';
            }

            return $this->get_result();

        } catch (\Exception $e) {
            $this->errors[] = 'Validation exception: ' . $e->getMessage();
            return $this->get_result();
        }
    }

    /**
     * Validate date format (YYYY-MM-DD or DD/MM/YYYY)
     *
     * @param string $date Date string
     * @return bool Valid
     */
    private function validate_date($date) {
        // Try YYYY-MM-DD format.
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        if ($d && $d->format('Y-m-d') === $date) {
            return true;
        }

        // Try DD/MM/YYYY format.
        $d = \DateTime::createFromFormat('d/m/Y', $date);
        if ($d && $d->format('d/m/Y') === $date) {
            return true;
        }

        // Try DD-MM-YYYY format.
        $d = \DateTime::createFromFormat('d-m-Y', $date);
        if ($d && $d->format('d-m-Y') === $date) {
            return true;
        }

        return false;
    }

    /**
     * Get validation result
     *
     * @return array Result with success, errors, warnings
     */
    private function get_result() {
        return array(
            'success' => empty($this->errors),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'error_count' => count($this->errors),
            'warning_count' => count($this->warnings)
        );
    }

    /**
     * Generate sample CSV template
     *
     * @return string CSV content
     */
    public function generate_template() {
        $all_fields = array_merge($this->required_fields, $this->optional_fields);

        $csv = implode(',', $all_fields) . "\n";
        $csv .= 'MH123456,John,Doe,john.doe@example.com,MH001,CIVIL,1990-01-15,M,Graduate,2015-06-01,Constable,Sub-Inspector,Mumbai,Mumbai CP,Traffic Police,D001,12345,9876543210,Mumbai' . "\n";
        $csv .= 'MH123457,Jane,Smith,jane.smith@example.com,MH002,POLICE,1985-05-20,F,Post-Graduate,2010-03-15,Constable,Inspector,Pune,Pune CP,Crime Branch,D002,12346,9876543211,Pune' . "\n";

        return $csv;
    }

    /**
     * Get expected CSV format documentation
     *
     * @return array Format documentation
     */
    public function get_format_documentation() {
        return array(
            'required_fields' => $this->required_fields,
            'optional_fields' => $this->optional_fields,
            'field_descriptions' => array(
                'employee_id' => 'Unique employee/student ID (alphanumeric)',
                'firstname' => 'First name',
                'lastname' => 'Last name',
                'email' => 'Email address (defaults to employee_id@training.local if not provided)',
                'personal_mobile' => 'Personal mobile number (10 digits)',
                'date_of_birth' => 'Date of birth (YYYY-MM-DD format)',
                'date_of_joining' => 'Date of joining (YYYY-MM-DD format)',
                'gender' => 'Gender (M/F/O)',
                'current_rank' => 'Current rank/designation',
                'working_location' => 'Current working location'
            ),
            'notes' => array(
                'CSV must have header row with column names',
                'All dates should be in YYYY-MM-DD format',
                'Mobile numbers should be 10 digits (without country code)',
                'Email will be auto-generated if not provided',
                'Employee ID must be unique across all records'
            )
        );
    }
}

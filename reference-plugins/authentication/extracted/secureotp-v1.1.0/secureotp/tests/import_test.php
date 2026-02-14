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
 * Bulk import tests
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/auth/secureotp/classes/import/csv_validator.php');
require_once($CFG->dirroot . '/auth/secureotp/classes/import/user_importer.php');

/**
 * Import test case
 *
 * @group auth_secureotp
 */
class import_test extends \advanced_testcase {

    /**
     * Test CSV validator with valid file
     */
    public function test_csv_validator_valid() {
        global $CFG;
        $this->resetAfterTest(true);

        // Create test CSV.
        $csv_content = "employee_id,firstname,lastname,email\n";
        $csv_content .= "TEST001,John,Doe,john@example.com\n";
        $csv_content .= "TEST002,Jane,Smith,jane@example.com\n";

        $filepath = $CFG->dataroot . '/temp/test_import.csv';
        make_writable_directory(dirname($filepath));
        file_put_contents($filepath, $csv_content);

        $validator = new \auth_secureotp\import\csv_validator();
        $result = $validator->validate($filepath);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['error_count']);

        unlink($filepath);
    }

    /**
     * Test CSV validator with missing required field
     */
    public function test_csv_validator_missing_field() {
        global $CFG;
        $this->resetAfterTest(true);

        // CSV missing 'lastname' field.
        $csv_content = "employee_id,firstname,email\n";
        $csv_content .= "TEST001,John,john@example.com\n";

        $filepath = $CFG->dataroot . '/temp/test_import_invalid.csv';
        make_writable_directory(dirname($filepath));
        file_put_contents($filepath, $csv_content);

        $validator = new \auth_secureotp\import\csv_validator();
        $result = $validator->validate($filepath);

        $this->assertFalse($result['success']);
        $this->assertGreaterThan(0, $result['error_count']);

        unlink($filepath);
    }

    /**
     * Test user import dry run
     */
    public function test_user_import_dry_run() {
        global $CFG, $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create test CSV.
        $csv_content = "employee_id,firstname,lastname,email,personal_mobile\n";
        $csv_content .= "IMP001,Test,User,test@example.com,9876543210\n";

        $filepath = $CFG->dataroot . '/temp/test_import_dry.csv';
        make_writable_directory(dirname($filepath));
        file_put_contents($filepath, $csv_content);

        $importer = new \auth_secureotp\import\user_importer();
        $result = $importer->import_from_csv($filepath, 'TEST', true, 2);

        $this->assertTrue($result['success']);
        $this->assertTrue($result['dry_run']);

        // Verify no users were actually created.
        $count = $DB->count_records('auth_secureotp_userdata', array('employee_id' => 'IMP001'));
        $this->assertEquals(0, $count);

        unlink($filepath);
    }
}

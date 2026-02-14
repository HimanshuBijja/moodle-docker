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
 * OTP generation and validation tests
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/auth/secureotp/classes/auth/otp_manager.php');

/**
 * OTP test case
 *
 * @group auth_secureotp
 */
class otp_test extends \advanced_testcase {

    /**
     * Test OTP generation
     */
    public function test_generate_otp() {
        $this->resetAfterTest(true);

        $otp_manager = new \auth_secureotp\auth\otp_manager();
        $result = $otp_manager->generate_otp(1);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['otp']);
        $this->assertEquals(6, strlen($result['otp']));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $result['otp']);
    }

    /**
     * Test OTP validation
     */
    public function test_validate_otp() {
        $this->resetAfterTest(true);

        $otp_manager = new \auth_secureotp\auth\otp_manager();

        // Generate OTP.
        $result = $otp_manager->generate_otp(1);
        $otp = $result['otp'];

        // Validate correct OTP.
        $validation = $otp_manager->validate_otp(1, $otp);
        $this->assertTrue($validation['valid']);
    }

    /**
     * Test invalid OTP
     */
    public function test_invalid_otp() {
        $this->resetAfterTest(true);

        $otp_manager = new \auth_secureotp\auth\otp_manager();

        // Generate OTP.
        $otp_manager->generate_otp(1);

        // Validate wrong OTP.
        $validation = $otp_manager->validate_otp(1, '000000');
        $this->assertFalse($validation['valid']);
    }

    /**
     * Test OTP single use
     */
    public function test_otp_single_use() {
        $this->resetAfterTest(true);

        $otp_manager = new \auth_secureotp\auth\otp_manager();

        // Generate and validate OTP.
        $result = $otp_manager->generate_otp(1);
        $otp = $result['otp'];

        $validation1 = $otp_manager->validate_otp(1, $otp);
        $this->assertTrue($validation1['valid']);

        // Try to use same OTP again.
        $validation2 = $otp_manager->validate_otp(1, $otp);
        $this->assertFalse($validation2['valid']);
    }
}

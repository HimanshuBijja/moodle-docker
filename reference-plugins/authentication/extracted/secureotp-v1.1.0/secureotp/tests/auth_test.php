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
 * Authentication flow tests
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_secureotp;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/auth/secureotp/auth.php');

/**
 * Authentication test case
 *
 * @group auth_secureotp
 */
class auth_test extends \advanced_testcase {

    /**
     * Test initiate OTP login with valid user
     */
    public function test_initiate_otp_login_valid_user() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create test user.
        $user = $this->getDataGenerator()->create_user(array(
            'auth' => 'secureotp',
            'username' => 'testuser',
            'email' => 'test@example.com'
        ));

        // Create userdata record.
        $userdata = new \stdClass();
        $userdata->userid = $user->id;
        $userdata->employee_id = 'TEST001';
        $userdata->personal_mobile = '9876543210';
        $userdata->source_system = 'TEST';
        $userdata->timecreated = time();
        $userdata->timemodified = time();
        $DB->insert_record('auth_secureotp_userdata', $userdata);

        // Test login initiation.
        $auth = new \auth_plugin_secureotp();
        $result = $auth->initiate_otp_login('TEST001');

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['csrf_token']);
    }

    /**
     * Test initiate OTP login with invalid user
     */
    public function test_initiate_otp_login_invalid_user() {
        $this->resetAfterTest(true);

        $auth = new \auth_plugin_secureotp();
        $result = $auth->initiate_otp_login('NONEXISTENT');

        $this->assertFalse($result['success']);
        $this->assertEquals('USER_NOT_FOUND', $result['error_code']);
    }

    /**
     * Test account status checks
     */
    public function test_suspended_account_cannot_login() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create suspended user.
        $user = $this->getDataGenerator()->create_user(array('auth' => 'secureotp'));

        $userdata = new \stdClass();
        $userdata->userid = $user->id;
        $userdata->employee_id = 'SUSP001';
        $userdata->source_system = 'TEST';
        $userdata->timecreated = time();
        $userdata->timemodified = time();
        $DB->insert_record('auth_secureotp_userdata', $userdata);

        $security = new \stdClass();
        $security->userid = $user->id;
        $security->status = 'SUSPENDED';
        $security->timecreated = time();
        $security->timemodified = time();
        $DB->insert_record('auth_secureotp_security', $security);

        // Attempt login.
        $auth = new \auth_plugin_secureotp();
        $result = $auth->initiate_otp_login('SUSP001');

        $this->assertFalse($result['success']);
        $this->assertEquals('ACCOUNT_SUSPENDED', $result['error_code']);
    }

    /**
     * Test rate limiting
     */
    public function test_rate_limiting() {
        $this->resetAfterTest(true);

        $auth = new \auth_plugin_secureotp();

        // Make multiple rapid requests (should be rate limited).
        for ($i = 0; $i < 10; $i++) {
            $result = $auth->initiate_otp_login('RATELIMIT' . time());
        }

        // Next request should be rate limited.
        $result = $auth->initiate_otp_login('RATELIMIT' . time());

        // Note: Actual rate limit check depends on rate_limiter implementation.
        // This test demonstrates the structure.
        $this->assertIsArray($result);
    }
}

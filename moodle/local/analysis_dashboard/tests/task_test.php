<?php
namespace local_analysis_dashboard;

defined('MOODLE_INTERNAL') || die();

use local_analysis_dashboard	ask\aggregate_stats;

/**
 * PHPUnit tests for Analysis Dashboard tasks.
 *
 * @package    local_analysis_dashboard
 * @category   test
 */
class task_test extends \advanced_testcase {

    /**
     * Setup for tests.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test aggregation task logic.
     */
    public function test_aggregate_stats() {
        global $DB;

        // 1. Create Mock Table (if not exists, although in PHPUnit it should be handled by generators if possible)
        // But since auth_secureotp_userdata is external, we mock it.
        $this->create_mock_userdata_table();

        // 2. Create Users
        $user1 = $this->getDataGenerator()->create_user(['timecreated' => make_timestamp(2024, 1, 1)]);
        $user2 = $this->getDataGenerator()->create_user(['timecreated' => make_timestamp(2024, 6, 1)]);
        $user3 = $this->getDataGenerator()->create_user(['timecreated' => make_timestamp(2025, 1, 1)]);

        // 3. Populate Mock Data
        $DB->insert_record('auth_secureotp_userdata', ['userid' => $user1->id, 'cp_sp_office' => 'District A']);
        $DB->insert_record('auth_secureotp_userdata', ['userid' => $user2->id, 'cp_sp_office' => 'District A']);
        $DB->insert_record('auth_secureotp_userdata', ['userid' => $user3->id, 'cp_sp_office' => 'District B']);

        // 4. Run Task
        $task = new aggregate_stats();
        $task->execute();

        // 5. Verify Cache
        $cache = \cache::make('local_analysis_dashboard', 'site_stats');
        
        // Global
        $global = $cache->get('districts');
        $this->assertEquals(2, $global['District A']);
        $this->assertEquals(1, $global['District B']);

        // Yearly (2024)
        $y2024 = $cache->get('districts_2024');
        $this->assertEquals(2, $y2024['District A']);
        $this->assertArrayNotHasKey('District B', $y2024);

        // Yearly (2025)
        $y2025 = $cache->get('districts_2025');
        $this->assertEquals(1, $y2025['District B']);
        $this->assertArrayNotHasKey('District A', $y2025);
    }

    /**
     * Helper to create the mock table in the test DB.
     */
    private function create_mock_userdata_table() {
        global $DB;
        $dbman = $DB->get_manager();
        $table = new \xmldb_table('auth_secureotp_userdata');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('cp_sp_office', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $dbman->create_table($table);
        }
    }
}

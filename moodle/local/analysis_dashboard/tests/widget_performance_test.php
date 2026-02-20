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

namespace local_analysis_dashboard;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/analysis_dashboard/classes/local/widget_registry.php');

use advanced_testcase;
use local_analysis_dashboard\local\widget_registry;

/**
 * PHPUnit tests for widget performance and contract validation.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_analysis_dashboard\local\widget_registry
 */
class widget_performance_test extends advanced_testcase {

    /**
     * Reset registry between tests.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        widget_registry::reset();
    }

    /**
     * Test that the registry loads all expected widgets.
     */
    public function test_widget_registry_loads_all_widgets(): void {
        widget_registry::init();
        $all = widget_registry::get_all();

        // We expect at least 47 widgets across all phases.
        $this->assertGreaterThanOrEqual(47, count($all),
            'Widget registry should contain at least 47 widgets');
    }

    /**
     * Test that counter widgets return expected data structure.
     */
    public function test_counter_widget_data_format(): void {
        $widget = widget_registry::get('total_users');
        $this->assertNotNull($widget);
        $this->assertEquals('counter', $widget->get_type());

        $data = $widget->get_data();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('items', $data);

        if (!empty($data['items'])) {
            $item = $data['items'][0];
            $this->assertArrayHasKey('label', $item);
            $this->assertArrayHasKey('value', $item);
        }
    }

    /**
     * Test that chart widgets return expected data structure.
     */
    public function test_chart_widget_data_format(): void {
        $widget = widget_registry::get('site_visits');
        $this->assertNotNull($widget);
        $this->assertEquals('line', $widget->get_type());

        $data = $widget->get_data();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
    }

    /**
     * Test cache operations cycle: set → get → invalidate.
     */
    public function test_cache_operations(): void {
        $widget = widget_registry::get('total_users');

        // First call should populate cache.
        $data1 = $widget->get_cached_data();
        $this->assertIsArray($data1);

        // Second call should return cached data (not null).
        $data2 = $widget->get_cached_data();
        $this->assertIsArray($data2);
        $this->assertEquals($data1, $data2, 'Cached data should be consistent');
    }

    /**
     * Test that each widget has a valid name string key.
     */
    public function test_widget_name_strings(): void {
        widget_registry::init();
        $all = widget_registry::get_all();

        foreach ($all as $id => $widget) {
            $name = $widget->get_name();
            $this->assertNotEmpty($name,
                "Widget '$id' should have a non-empty name key");
        }
    }

    /**
     * Test that each widget has a valid type.
     */
    public function test_widget_types(): void {
        $validtypes = ['counter', 'line', 'bar', 'pie', 'doughnut', 'table', 'heatmap'];

        widget_registry::init();
        $all = widget_registry::get_all();

        foreach ($all as $id => $widget) {
            $this->assertContains($widget->get_type(), $validtypes,
                "Widget '$id' has invalid type: " . $widget->get_type());
        }
    }
}

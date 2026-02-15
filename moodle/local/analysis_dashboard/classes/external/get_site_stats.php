<?php
namespace local_analysis_dashboard\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_value;

class get_site_stats extends external_api {

    /**
     * Describes the external function parameters.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'year' => new external_value(PARAM_INT, 'Optional year filter', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * External function execution.
     */
    public static function execute(int $year = 0): array {
        // Validation
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/analysis_dashboard:view', $context);

        // Fetch from Cache
        $cache = \cache::make('local_analysis_dashboard', 'site_stats');
        
        $key = 'districts';
        if ($year > 0) {
            $key = 'districts_' . $year;
        }
        
        $data = $cache->get($key);

        // Transform for ApexCharts
        $labels = [];
        $series = [];
        if ($data) {
            foreach ($data as $district => $count) {
                $labels[] = $district;
                $series[] = $count;
            }
        }

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    /**
     * Describes the external function result value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'labels' => new external_multiple_structure(new external_value(PARAM_TEXT, 'District Name')),
            'series' => new external_multiple_structure(new external_value(PARAM_INT, 'User Count')),
        ]);
    }
}

<?php
defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_analysis_dashboard_get_site_stats' => array(
        'classname'   => 'local_analysis_dashboard\external\get_site_stats',
        'methodname'  => 'execute',
        'description' => 'Returns aggregated site statistics.',
        'type'        => 'read',
        'ajax'        => true,
    ),
);

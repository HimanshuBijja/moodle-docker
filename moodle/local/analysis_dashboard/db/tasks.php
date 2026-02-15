<?php
defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => '\local_analysis_dashboard\task\aggregate_stats',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '2', // Run daily at 2 AM
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ),
);

<?php
defined('MOODLE_INTERNAL') || die();

$definitions = array(
    // Cache for site-wide statistics (Users per District).
    // Populated by \local_analysis_dashboard	ask\aggregate_stats.
    'site_stats' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true, // We will use simple keys like 'districts', 'summary', etc.
        'ttl' => 86400,       // Default TTL: 24 hours. Task runs daily.
    ),
    
    // Cache for course-specific statistics.
    // Key = courseid. Value = Array of stats.
    // Note: If this grows too large, we might need a different strategy (e.g., dedicated table).
    'course_stats' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true, // Key is course ID (integer).
        'ttl' => 86400,       // Default TTL: 24 hours.
    ),
);

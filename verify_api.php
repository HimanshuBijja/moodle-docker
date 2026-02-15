<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/config.php');
require_once($CFG->libdir . '/externallib.php');

// Mock Admin User
$admin = $DB->get_record('user', ['username' => 'admin']);
\core\session\manager::init_empty_session();
\core\session\manager::set_user($admin);

// Execute API
try {
    $start = microtime(true);
    $result = \local_analysis_dashboard\external\get_site_stats::execute();
    $end = microtime(true);
    
    echo "API Execution Success!\n";
    echo "Time: " . number_format(($end - $start) * 1000, 2) . " ms\n";
    // print_r($result); // Suppress full output for timing check
} catch (Exception $e) {
    echo "API Execution Failed: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

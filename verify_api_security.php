<?php
define('CLI_SCRIPT', true);
require(__DIR__ . '/config.php');
require_once($CFG->libdir . '/externallib.php');

// Mock Guest User (should fail)
$guest = $DB->get_record('user', ['username' => 'guest']);
\core\session\manager::init_empty_session();
\core\session\manager::set_user($guest);

// Execute API
try {
    echo "Attempting execution as guest...
";
    $result = \local_analysis_dashboard\external\get_site_stats::execute();
    echo "API Execution Unexpectedly Succeeded!
";
    exit(1);
} catch (required_capability_exception $e) {
    echo "Security Check Passed: " . $e->getMessage() . "
";
} catch (Exception $e) {
    // Moodle might throw a different exception for guests (e.g. login required or moodle_exception)
    // checking for "nopermissions" string key often works.
    if (strpos($e->getMessage(), 'Sorry, but you do not currently have permissions to do that') !== false) {
         echo "Security Check Passed (Generic Exception): " . $e->getMessage() . "
";
    } else {
        echo "Unexpected Exception: " . $e->getMessage() . "
";
        echo get_class($e) . "
";
        exit(1);
    }
}

<?php
// This file is part of Moodle - http://moodle.org/
define('CLI_SCRIPT', true);

// --- START PATH FIX ---
// Automatically find config.php regardless of Docker nesting
$current = __DIR__;
while (!file_exists($current . '/config.php') && $current !== '/') {
    $current = dirname($current);
}
if (!file_exists($current . '/config.php')) {
    fwrite(STDERR, "Error: config.php not found. Please run from Moodle root.\n");
    exit(1);
}
require($current . '/config.php');
// --- END PATH FIX ---

require_once($CFG->libdir . '/clilib.php');
require_once(__DIR__ . '/../classes/import/user_importer.php');
require_once(__DIR__ . '/../classes/import/csv_validator.php');

// Enable full debugging to catch the HIDDEN database error
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;

list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false, 'file' => '', 'source' => 'HR_MASTER',
        'dry-run' => false, 'validate-only' => false, 'batch-size' => 100, 'template' => false
    ),
    array('h' => 'help', 'f' => 'file', 's' => 'source', 'd' => 'dry-run', 'v' => 'validate-only', 'b' => 'batch-size', 't' => 'template')
);

if ($options['help']) {
    echo "Usage: php import_users.php --file=<path> [options]\n";
    exit(0);
}

if (empty($options['file']) && !$options['template']) {
    cli_error('Error: --file parameter is required.');
}

$filepath = $options['file'];
$source_system = $options['source'];
$dry_run = $options['dry-run'];
$validate_only = $options['validate-only'];
$batch_size = intval($options['batch-size']);

// Set admin context
\core\session\manager::set_user(get_admin());

cli_heading('SecureOTP Bulk User Import (Patched Version)');

// Step 1: Validate CSV.
$validator = new \auth_secureotp\import\csv_validator();
$validation = $validator->validate($filepath);

if (!$validation['success']) {
    cli_error("Validation failed: " . implode("\n", $validation['errors']));
}

if ($validate_only) {
    cli_heading('Validation Complete');
    exit(0);
}

// Step 2: Import users.
cli_heading('Step 2: Importing users');

$importer = new \auth_secureotp\import\user_importer();
$importer->set_batch_size($batch_size);

echo "Starting import...\n";

try {
    // We execute the import. If the 'audit_log' table is still messy,
    // the debug settings above will now show the EXACT SQL error.
    $result = $importer->import_from_csv($filepath, $source_system, $dry_run, get_admin()->id);

    if (!$result['success']) {
        cli_error("Import failed: " . $result['error']);
    }

    cli_heading('Import Complete');
    echo "Total records: " . $result['stats']['total'] . "\n";
    echo "Created: " . $result['stats']['created'] . "\n";
    exit(0);

} catch (\Exception $e) {
    // If it's a DML error, Moodle usually wraps it. Let's dig it out.
    echo "\n" . str_repeat('!', 60) . "\n";
    echo "DATABASE ERROR DETECTED:\n";
    echo $e->getMessage() . "\n";
    if (isset($e->debuginfo)) {
        echo "DEBUG INFO: " . $e->debuginfo . "\n";
    }
    echo str_repeat('!', 60) . "\n";
    exit(1);
}
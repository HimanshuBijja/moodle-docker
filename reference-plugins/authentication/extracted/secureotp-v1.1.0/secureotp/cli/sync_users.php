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
 * CLI script to sync users with HR database
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

list($options, $unrecognized) = cli_get_params(
    array('help' => false, 'file' => '', 'source' => 'HR_MASTER'),
    array('h' => 'help', 'f' => 'file', 's' => 'source')
);

if ($options['help'] || empty($options['file'])) {
    echo "Sync users with HR database\n\n";
    echo "Usage: php sync_users.php --file=/path/to/latest_export.csv [--source=HR_MASTER]\n";
    exit(0);
}

require_once(__DIR__ . '/../classes/import/user_importer.php');

$admin = get_admin();
\core\session\manager::set_user($admin);

cli_heading('User Synchronization');

$importer = new \auth_secureotp\import\user_importer();
$result = $importer->import_from_csv($options['file'], $options['source'], false, $admin->id);

if ($result['success']) {
    echo "Sync completed successfully!\n";
    echo "Created: {$result['stats']['created']}, Updated: {$result['stats']['updated']}, Failed: {$result['stats']['failed']}\n";
} else {
    cli_error("Sync failed: " . $result['error']);
}

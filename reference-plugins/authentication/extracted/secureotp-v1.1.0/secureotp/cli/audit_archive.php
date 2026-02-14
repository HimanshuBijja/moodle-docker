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
 * CLI script to archive old audit logs
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');

list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'days' => 365,
        'export' => ''
    ),
    array('h' => 'help', 'd' => 'days', 'e' => 'export')
);

if ($options['help']) {
    echo "Archive old audit logs\n\n";
    echo "Usage: php audit_archive.php [--days=365] [--export=/path/to/archive/]\n\n";
    echo "Options:\n";
    echo "  --days=<n>    Archive logs older than N days (default: 365)\n";
    echo "  --export=<path>  Export to CSV files (optional)\n";
    exit(0);
}

cli_heading('Audit Log Archive');

$days = intval($options['days']);
$export_path = $options['export'];

$cutoff_date = time() - ($days * 86400);

echo "Archiving logs older than $days days (" . userdate($cutoff_date) . ")\n\n";

// Count records to archive.
$count = $DB->count_records_select('auth_secureotp_audit', 'timecreated < ?', array($cutoff_date));

echo "Found $count records to archive\n";

if ($count === 0) {
    echo "Nothing to archive\n";
    exit(0);
}

// Export to CSV if path provided.
if (!empty($export_path)) {
    if (!is_dir($export_path)) {
        mkdir($export_path, 0755, true);
    }

    $filename = 'audit_archive_' . date('Y-m-d') . '.csv';
    $filepath = rtrim($export_path, '/') . '/' . $filename;

    echo "Exporting to $filepath...\n";

    $records = $DB->get_records_select('auth_secureotp_audit', 'timecreated < ?', array($cutoff_date), 'timecreated ASC');

    $fp = fopen($filepath, 'w');
    fputcsv($fp, array('ID', 'Event Type', 'Status', 'Severity', 'User ID', 'Employee ID', 'IP Address', 'User Agent', 'Device FP', 'Event Data', 'Signature', 'Timestamp'));

    foreach ($records as $record) {
        fputcsv($fp, array(
            $record->id,
            $record->event_type,
            $record->event_status,
            $record->severity,
            $record->userid,
            $record->employee_id,
            $record->ip_address,
            $record->user_agent,
            $record->device_fingerprint,
            $record->event_data,
            $record->signature,
            userdate($record->timecreated)
        ));
    }

    fclose($fp);

    echo "Exported $count records to $filepath\n";
    echo "File size: " . filesize($filepath) . " bytes\n\n";
}

// NOTE: For compliance, we should NOT delete audit logs.
// They must be retained for 7 years as per requirements.
// This script only exports them for external archival.

echo "Archive process completed\n";
echo "NOTE: Audit logs are retained in database for compliance (7-year retention requirement)\n";

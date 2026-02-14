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
 * Upgrade script for auth_secureotp
 *
 * @package    auth_secureotp
 * @copyright  2026 Government Training Institute
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for auth_secureotp
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_secureotp_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Moodle v4.5.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2026021301) {
        // Initial installation - no upgrade needed.
        // Tables are created via install.xml.
        upgrade_plugin_savepoint(true, 2026021301, 'auth', 'secureotp');
    }

    if ($oldversion < 2026021302) {
        // Increase current_otp_hash field size to accommodate password_hash output (bcrypt = 60 chars).
        $table = new xmldb_table('auth_secureotp_security');
        $field = new xmldb_field('current_otp_hash', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'is_locked');

        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_precision($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026021302, 'auth', 'secureotp');
    }

    return true;
}

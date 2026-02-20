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
 * Local plugin "My Dashboard" library functions.
 *
 * @package    local_mydashboard
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends the global navigation by adding a "My Dashboard" link.
 *
 * @param global_navigation $navigation The navigation node to extend.
 */
function local_mydashboard_extend_navigation(global_navigation $navigation) {
    global $CFG, $PAGE;

    // Only add the navigation item if the user is logged in
    if (isloggedin() && !isguestuser()) {
        // Add a node to the main navigation menu.
        $node = navigation_node::create(
            get_string('pluginname', 'local_mydashboard'),
            new moodle_url('/local/mydashboard/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'mydashboard',
            new pix_icon('t/dashboard', get_string('pluginname', 'local_mydashboard'))
        );
        
        $navigation->add_node($node);
    }
}

/**
 * Extends the user navigation by adding a "My Dashboard" link.
 *
 * @param navigation_node $navigation The navigation node to extend.
 */
function local_mydashboard_extend_navigation_user(navigation_node $navigation) {
    global $CFG;

    // Only add the navigation item if the user is logged in
    if (isloggedin() && !isguestuser()) {
        // Add a node to the user navigation menu.
        $node = navigation_node::create(
            get_string('pluginname', 'local_mydashboard'),
            new moodle_url('/local/mydashboard/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'mydashboard',
            new pix_icon('t/dashboard', get_string('pluginname', 'local_mydashboard'))
        );
        
        $navigation->add_node($node);
    }
}

/**
 * Add nodes to the settings navigation
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param context $context The context
 */
function local_mydashboard_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
    global $PAGE;
    
    // Only add the navigation item if we're on the dashboard page and user is logged in
    if (isloggedin() && !isguestuser() && 
        $PAGE->url->compare(new moodle_url('/local/mydashboard/index.php'), URL_MATCH_BASE)) {
        
        $node = $settingsnav->add(
            get_string('pluginname', 'local_mydashboard'),
            new moodle_url('/local/mydashboard/index.php'),
            settings_navigation::TYPE_CUSTOM,
            null,
            'mydashboard',
            new pix_icon('t/dashboard', get_string('pluginname', 'local_mydashboard'))
        );
    }
}

/**
 * Get user's saved links
 *
 * @param int $userid The user ID
 * @return array Array of user's saved links
 */
function local_mydashboard_get_user_links($userid) {
    global $DB;
    
    return $DB->get_records('local_mydashboard_links', array('userid' => $userid), 'timecreated DESC');
}

/**
 * Save a new link for a user
 *
 * @param array $data Link data
 * @return int ID of the newly created record
 */
function local_mydashboard_save_link($data) {
    global $DB;
    
    $data->timecreated = time();
    $data->timemodified = time();
    
    return $DB->insert_record('local_mydashboard_links', $data);
}

/**
 * Update an existing link
 *
 * @param array $data Link data
 */
function local_mydashboard_update_link($data) {
    global $DB;
    
    $data->timemodified = time();
    $DB->update_record('local_mydashboard_links', $data);
}

/**
 * Delete a link
 *
 * @param int $id Link ID
 * @param int $userid User ID
 */
function local_mydashboard_delete_link($id, $userid) {
    global $DB;
    
    $DB->delete_records('local_mydashboard_links', array('id' => $id, 'userid' => $userid));
}
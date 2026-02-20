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
 * My Dashboard main page.
 *
 * @package    local_mydashboard
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/mydashboard/classes/form/link_form.php');

// Set up page context
$PAGE->set_url('/local/mydashboard/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('dashboardtitle', 'local_mydashboard'));
$PAGE->set_heading(get_string('dashboardtitle', 'local_mydashboard'));
$PAGE->set_pagelayout('standard');

// Include CSS
$PAGE->requires->css(new moodle_url('/local/mydashboard/styles/style.css'));

// Check if user is logged in
require_login();

$userid = $USER->id;
$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

// Handle form submission
$linkform = new \local_mydashboard\form\link_form();

if ($action === 'delete' && $id) {
    // Delete link
    $link = $DB->get_record('local_mydashboard_links', array('id' => $id, 'userid' => $userid));
    if ($link) {
        local_mydashboard_delete_link($id, $userid);
        \core\notification::success(get_string('linkdeleted', 'local_mydashboard'));
        redirect(new moodle_url('/local/mydashboard/index.php'));
    } else {
        \core\notification::error(get_string('error:notyours', 'local_mydashboard'));
    }
} else if ($linkform->is_cancelled()) {
    // Redirect on cancel
    redirect(new moodle_url('/local/mydashboard/index.php'));
} else if ($data = $linkform->get_data()) {
    // Process form submission
    if (!empty($data->id)) {
        // Update existing link
        $data->id = $data->id;
        $data->userid = $userid;
        local_mydashboard_update_link($data);
        \core\notification::success(get_string('linkupdated', 'local_mydashboard'));
    } else {
        // Add new link
        $data->userid = $userid;
        local_mydashboard_save_link($data);
        \core\notification::success(get_string('linkadded', 'local_mydashboard'));
    }
    redirect(new moodle_url('/local/mydashboard/index.php'));
} else if ($action === 'edit' && $id) {
    // Pre-populate form for editing
    $link = $DB->get_record('local_mydashboard_links', array('id' => $id, 'userid' => $userid));
    if ($link) {
        $linkform->set_data($link);
    } else {
        \core\notification::error(get_string('error:notyours', 'local_mydashboard'));
        redirect(new moodle_url('/local/mydashboard/index.php'));
    }
} else if ($action === 'add') {
    // Show empty form for adding new link
    $linkform->set_data(array('userid' => $userid));
}

// Get user's links
$userlinks = local_mydashboard_get_user_links($userid);

echo $OUTPUT->header();
echo html_writer::tag('h2', get_string('dashboardtitle', 'local_mydashboard'), array('class' => 'main'));
echo html_writer::tag('p', get_string('dashboardsubtitle', 'local_mydashboard'), array('class' => 'subtitle'));

// Display form if adding or editing
if ($action === 'add' || $action === 'edit' || $linkform->is_submitted()) {
    echo $OUTPUT->box_start('generalbox boxwidthnormal boxaligncenter');
    $linkform->display();
    echo $OUTPUT->box_end();
} else {
    // Display "Add Link" button
    echo html_writer::start_div('add-link-container mb-3');
    echo html_writer::link(
        new moodle_url('/local/mydashboard/index.php', array('action' => 'add')),
        get_string('addnewlink', 'local_mydashboard'),
        array('class' => 'btn btn-primary')
    );
    echo html_writer::end_div();
    
    // Display user's links as cards
    if (!empty($userlinks)) {
        echo html_writer::start_div('dashboard-cards-container');
        
        $counter = 0;
        foreach ($userlinks as $link) {
            if ($counter % 2 == 0) {
                // Start a new row every 2 cards
                if ($counter > 0) {
                    echo html_writer::end_div(); // Close previous row
                }
                echo html_writer::start_div('row');
            }
            
            // Card column
            echo html_writer::start_div('col-md-6 mb-3');
            echo html_writer::start_div('card h-100');
            
            // Card body
            echo html_writer::start_div('card-body');
            echo html_writer::tag('h5', format_string($link->title), array('class' => 'card-title'));
            
            if (!empty($link->description)) {
                echo html_writer::tag('p', format_text($link->description), array('class' => 'card-text'));
            }
            
            echo html_writer::end_div(); // card-body
            
            // Card footer with link and actions
            echo html_writer::start_div('card-footer bg-transparent border-top-0');
            echo html_writer::link(
                $link->url,
                get_string('visit', 'local_mydashboard'),
                array('class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank')
            );
            
            // Action buttons
            echo html_writer::start_span('float-end');
            echo html_writer::link(
                new moodle_url('/local/mydashboard/index.php', array('action' => 'edit', 'id' => $link->id)),
                get_string('editlink', 'local_mydashboard'),
                array('class' => 'btn btn-sm btn-outline-secondary mx-1')
            );
            echo html_writer::link(
                new moodle_url('/local/mydashboard/index.php', array('action' => 'delete', 'id' => $link->id)),
                get_string('deletelink', 'local_mydashboard'),
                array(
                    'class' => 'btn btn-sm btn-outline-danger mx-1',
                    'onclick' => 'return confirm("' . get_string('confirmdelete', 'local_mydashboard') . '");'
                )
            );
            echo html_writer::end_span();
            
            echo html_writer::end_div(); // card-footer
            echo html_writer::end_div(); // card
            echo html_writer::end_div(); // col-md-6
            
            $counter++;
        }
        
        if ($counter > 0 && $counter % 2 != 0) {
            // Close the last row if it has only one card
            echo html_writer::end_div(); // row
        }
        
        echo html_writer::end_div(); // dashboard-cards-container
    } else {
        // No links message
        echo $OUTPUT->box(get_string('nolinks', 'local_mydashboard'), 'noticebox');
    }
}

echo $OUTPUT->footer();
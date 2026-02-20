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
 * Form for adding/editing dashboard links.
 *
 * @package    local_mydashboard
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mydashboard\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class link_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        // Add hidden field for ID if editing
        if (!empty($this->_customdata['id'])) {
            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
        }

        // Title field
        $mform->addElement('text', 'title', get_string('title', 'local_mydashboard'), array('size' => '50'));
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', null, 'required', null, 'client');

        // URL field
        $mform->addElement('text', 'url', get_string('url', 'local_mydashboard'), array('size' => '50'));
        $mform->setType('url', PARAM_URL);
        $mform->addRule('url', null, 'required', null, 'client');

        // Description field
        $mform->addElement('textarea', 'description', get_string('description', 'local_mydashboard'), 
                          array('rows' => 4, 'cols' => 50));
        $mform->setType('description', PARAM_TEXT);

        // Add action buttons
        $this->add_action_buttons(true, get_string('savechanges', 'local_mydashboard'));
    }

    /**
     * Validation function
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate URL format
        if (!empty($data['url']) && !filter_var($data['url'], FILTER_VALIDATE_URL)) {
            $errors['url'] = get_string('error:invalidlink', 'local_mydashboard');
        }

        return $errors;
    }
}
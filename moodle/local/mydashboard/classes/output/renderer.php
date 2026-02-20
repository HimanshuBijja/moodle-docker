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
 * Renderer for local_mydashboard plugin.
 *
 * @package    local_mydashboard
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mydashboard\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {

    /**
     * Render the dashboard with links
     *
     * @param array $links The user's saved links
     * @return string HTML for the dashboard
     */
    public function render_dashboard($links) {
        $html = '';
        
        if (!empty($links)) {
            $html .= html_writer::start_div('dashboard-cards-container');
            
            $counter = 0;
            foreach ($links as $link) {
                if ($counter % 2 == 0) {
                    // Start a new row every 2 cards
                    if ($counter > 0) {
                        $html .= html_writer::end_div(); // Close previous row
                    }
                    $html .= html_writer::start_div('row');
                }
                
                // Card column
                $html .= html_writer::start_div('col-md-6 mb-3');
                $html .= html_writer::start_div('card h-100');
                
                // Card body
                $html .= html_writer::start_div('card-body');
                $html .= html_writer::tag('h5', format_string($link->title), array('class' => 'card-title'));
                
                if (!empty($link->description)) {
                    $html .= html_writer::tag('p', format_text($link->description), array('class' => 'card-text'));
                }
                
                $html .= html_writer::end_div(); // card-body
                
                // Card footer with link and actions
                $html .= html_writer::start_div('card-footer bg-transparent border-top-0');
                $html .= html_writer::link(
                    $link->url,
                    get_string('visit', 'local_mydashboard'),
                    array('class' => 'btn btn-sm btn-outline-primary', 'target' => '_blank')
                );
                
                // Action buttons
                $html .= html_writer::start_span('float-end');
                $html .= html_writer::link(
                    new moodle_url('/local/mydashboard/index.php', array('action' => 'edit', 'id' => $link->id)),
                    get_string('editlink', 'local_mydashboard'),
                    array('class' => 'btn btn-sm btn-outline-secondary mx-1')
                );
                $html .= html_writer::link(
                    new moodle_url('/local/mydashboard/index.php', array('action' => 'delete', 'id' => $link->id)),
                    get_string('deletelink', 'local_mydashboard'),
                    array(
                        'class' => 'btn btn-sm btn-outline-danger mx-1',
                        'onclick' => 'return confirm("' . get_string('confirmdelete', 'local_mydashboard') . '");'
                    )
                );
                $html .= html_writer::end_span();
                
                $html .= html_writer::end_div(); // card-footer
                $html .= html_writer::end_div(); // card
                $html .= html_writer::end_div(); // col-md-6
                
                $counter++;
            }
            
            if ($counter > 0 && $counter % 2 != 0) {
                // Close the last row if it has only one card
                $html .= html_writer::end_div(); // row
            }
            
            $html .= html_writer::end_div(); // dashboard-cards-container
        } else {
            // No links message
            $html = $this->output->box(get_string('nolinks', 'local_mydashboard'), 'noticebox');
        }
        
        return $html;
    }
}
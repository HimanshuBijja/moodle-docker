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
 * Event observer definitions for the Analysis Dashboard plugin.
 *
 * Invalidates feedback-related caches when feedback responses are
 * submitted or deleted, ensuring the dashboard shows fresh data.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => '\mod_feedback\event\response_submitted',
        'callback'  => 'local_analysis_dashboard\observer\feedback_observer::response_submitted',
    ],
    [
        'eventname' => '\mod_feedback\event\response_deleted',
        'callback'  => 'local_analysis_dashboard\observer\feedback_observer::response_deleted',
    ],
];

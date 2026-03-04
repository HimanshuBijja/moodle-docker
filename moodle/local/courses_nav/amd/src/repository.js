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
 * Repository module for All Courses page.
 *
 * @module     local_courses_nav/repository
 * @copyright  2026 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/ajax'], function(Ajax) {

    /**
     * Get all courses with pagination, search, and filtering.
     *
     * @param {Object} args The request arguments.
     * @return {Promise} Resolved with courses data.
     */
    var getAllCourses = function(args) {
        var request = {
            methodname: 'local_courses_nav_get_all_courses',
            args: args
        };
        return Ajax.call([request])[0];
    };

    return {
        getAllCourses: getAllCourses
    };
});

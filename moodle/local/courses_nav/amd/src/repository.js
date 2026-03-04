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

import Ajax from 'core/ajax';

/**
 * Get all courses with pagination and search.
 *
 * @param {Object} args The request arguments.
 * @param {string} args.search Search string.
 * @param {number} args.page Page number.
 * @param {number} args.perpage Items per page.
 * @param {string} args.sort Sort field.
 * @return {Promise} Resolved with courses data.
 */
export const getAllCourses = (args) => {
    const request = {
        methodname: 'local_courses_nav_get_all_courses',
        args: args,
    };
    return Ajax.call([request])[0];
};

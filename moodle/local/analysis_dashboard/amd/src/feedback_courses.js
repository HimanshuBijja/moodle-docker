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
 * Feedback courses listing — client-side search and filter.
 *
 * @module     local_analysis_dashboard/feedback_courses
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {

    /**
     * Filter and search the course cards.
     */
    var applyFilters = function() {
        var searchTerm = $('#feedback-course-search').val().toLowerCase().trim();
        var filterValue = $('#feedback-course-filter').val();
        var visibleCount = 0;

        $('#feedback-courses-grid .feedback-course-card').each(function() {
            var card = $(this);
            var courseName = (card.data('coursename') || '').toString().toLowerCase();
            var timeline = card.data('timeline');

            var matchesSearch = !searchTerm || courseName.indexOf(searchTerm) !== -1;
            var matchesFilter = (filterValue === 'all') || (timeline === filterValue);

            if (matchesSearch && matchesFilter) {
                card.show();
                visibleCount++;
            } else {
                card.hide();
            }
        });

        // Show/hide no-results message.
        if (visibleCount === 0) {
            $('#feedback-no-results').show();
        } else {
            $('#feedback-no-results').hide();
        }
    };

    return {
        /**
         * Initialize the feedback courses page filters.
         */
        init: function() {
            $('#feedback-course-search').on('input', applyFilters);
            $('#feedback-course-filter').on('change', applyFilters);
        }
    };
});

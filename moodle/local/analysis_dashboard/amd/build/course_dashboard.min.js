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
 * Course dashboard initializer for the Analysis Dashboard.
 *
 * Loads course-level widget data via AJAX, passing courseid.
 * Also handles the Feedback Analysis section with per-form widgets.
 *
 * @module     local_analysis_dashboard/course_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'local_analysis_dashboard/widget_renderer',
        'local_analysis_dashboard/lazy_loader'],
    function($, Ajax, Notification, WidgetRenderer, LazyLoader) {

    /**
     * Load data for a single widget via AJAX.
     *
     * @param {Object} widget Widget config object with id, name, type.
     * @param {Number} courseid Course ID to pass to the external function.
     */
    var loadWidget = function(widget, courseid) {
        var container = $('[data-widget-id="' + widget.id + '"]').not('.feedback-form-widget');
        if (!container.length) {
            return;
        }

        var loadingRegion = container.find('[data-region="widget-loading"]');
        var contentRegion = container.find('[data-region="widget-content"]');
        var errorRegion = container.find('[data-region="widget-error"]');
        var nodataRegion = container.find('[data-region="widget-nodata"]');

        // Show loading state.
        loadingRegion.show();
        contentRegion.hide();
        errorRegion.hide();
        nodataRegion.hide();

        // Call external function with courseid.
        var promises = Ajax.call([{
            methodname: 'local_analysis_dashboard_get_widget_data',
            args: {
                widgetid: widget.id,
                params: JSON.stringify({courseid: courseid})
            }
        }]);

        promises[0].then(function(response) {
            loadingRegion.hide();

            var data;
            try {
                data = JSON.parse(response.data);
            } catch (e) {
                errorRegion.show();
                return;
            }

            // Check for empty data.
            if (!data || (Array.isArray(data.items) && data.items.length === 0) ||
                (Array.isArray(data.labels) && data.labels.length === 0) ||
                (Array.isArray(data.rows) && data.rows.length === 0)) {
                nodataRegion.show();
                return;
            }

            // Render the widget.
            contentRegion.show();
            WidgetRenderer.render(contentRegion[0], response.type, data, widget.name);

            return;
        }).catch(function(error) {
            loadingRegion.hide();
            errorRegion.show();
            Notification.exception(error);
        });
    };

    /**
     * Load data for a feedback form widget via AJAX.
     *
     * @param {HTMLElement} el The feedback form widget card element.
     * @param {Number} courseid Course ID.
     */
    var loadFeedbackForm = function(el, courseid) {
        var container = $(el);
        var feedbackId = container.data('feedback-id');

        var loadingRegion = container.find('[data-region="widget-loading"]');
        var contentRegion = container.find('[data-region="widget-content"]');
        var commentsRegion = container.find('[data-region="widget-comments"]');
        var errorRegion = container.find('[data-region="widget-error"]');
        var nodataRegion = container.find('[data-region="widget-nodata"]');

        loadingRegion.show();
        contentRegion.hide();
        commentsRegion.hide();
        errorRegion.hide();
        nodataRegion.hide();

        var promises = Ajax.call([{
            methodname: 'local_analysis_dashboard_get_widget_data',
            args: {
                widgetid: 'feedback_form_analysis',
                params: JSON.stringify({courseid: courseid, feedbackid: feedbackId})
            }
        }]);

        promises[0].then(function(response) {
            loadingRegion.hide();

            var data;
            try {
                data = JSON.parse(response.data);
            } catch (e) {
                errorRegion.show();
                return;
            }

            // Check for empty/message data.
            if (data.message) {
                nodataRegion.find('.text-center').html(
                    '<i class="fa fa-info-circle fa-2x mb-2 d-block"></i>' + data.message
                );
                nodataRegion.show();
                return;
            }

            if (!data || !data.labels || data.labels.length === 0) {
                nodataRegion.show();
                return;
            }

            // Render the diverging bar chart.
            contentRegion.show();
            WidgetRenderer.render(contentRegion[0], 'diverging_bar', data, container.find('.card-title').text());

            // Render comments if available.
            if (data.comments && data.comments.length > 0) {
                renderComments(commentsRegion[0], data.comments);
            }

            // Set up nav toggle.
            setupNavToggle(container);

            return;
        }).catch(function(error) {
            loadingRegion.hide();
            errorRegion.show();
            Notification.exception(error);
        });
    };

    /**
     * Render comments into the comments region.
     *
     * @param {HTMLElement} container The comments container element.
     * @param {Array} comments Array of {question, responses} objects.
     */
    var renderComments = function(container, comments) {
        var html = '<div class="feedback-comments-list">';

        comments.forEach(function(commentGroup) {
            html += '<div class="feedback-comment-group mb-3">';
            html += '<h6 class="font-weight-bold text-muted mb-2">';
            html += '<i class="fa fa-question-circle mr-1"></i>' + commentGroup.question;
            html += '</h6>';
            html += '<div class="list-group">';

            commentGroup.responses.forEach(function(response, idx) {
                html += '<div class="list-group-item list-group-item-action py-2 px-3">';
                html += '<small class="text-muted mr-2">' + (idx + 1) + '.</small>';
                html += '<span>' + response + '</span>';
                html += '</div>';
            });

            html += '</div>';
            html += '</div>';
        });

        html += '</div>';
        container.innerHTML = html;
    };

    /**
     * Set up Chart/Comments nav toggle for a feedback form widget.
     *
     * @param {jQuery} container The feedback form widget card jQuery object.
     */
    var setupNavToggle = function(container) {
        container.find('[data-toggle-view]').on('click', function(e) {
            e.preventDefault();
            var view = $(this).data('toggle-view');

            // Update active state.
            container.find('[data-toggle-view]').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');

            // Toggle visibility.
            var contentRegion = container.find('[data-region="widget-content"]');
            var commentsRegion = container.find('[data-region="widget-comments"]');

            if (view === 'chart') {
                contentRegion.show();
                commentsRegion.hide();
            } else if (view === 'comments') {
                contentRegion.hide();
                commentsRegion.show();
            }
        });
    };

    return {
        /**
         * Initialize the course dashboard.
         *
         * @param {Array} widgets Array of widget config objects.
         * @param {Number} courseid Course ID.
         * @param {Array} feedbackForms Array of feedback form config objects (optional).
         */
        init: function(widgets, courseid, feedbackForms) {
            // === Standard widgets ===
            if (widgets && widgets.length) {
                var widgetMap = {};
                widgets.forEach(function(widget) {
                    widgetMap[widget.id] = widget;
                });

                var widgetElements = document.querySelectorAll(
                    '.analysis-dashboard-grid .analysis-dashboard-widget[data-widget-id]'
                );
                LazyLoader.init(widgetElements, function(el) {
                    var wid = el.dataset.widgetId;
                    if (widgetMap[wid]) {
                        loadWidget(widgetMap[wid], courseid);
                    }
                });
            }

            // === Feedback form widgets ===
            var feedbackCards = document.querySelectorAll('.feedback-form-widget[data-feedback-id]');
            if (feedbackCards.length > 0) {
                LazyLoader.init(feedbackCards, function(el) {
                    loadFeedbackForm(el, courseid);
                });
            }

            // Also load the feedback_summary widget if it exists in the feedback section.
            var summaryCards = document.querySelectorAll(
                '.feedback-analysis-section .analysis-dashboard-widget[data-widget-id="feedback_summary"]'
            );
            if (summaryCards.length > 0) {
                LazyLoader.init(summaryCards, function(el) {
                    loadWidget({
                        id: 'feedback_summary',
                        name: el.getAttribute('aria-label') || 'Feedback Summary',
                        type: el.dataset.widgetType || 'bar'
                    }, courseid);
                });
            }
        }
    };
});

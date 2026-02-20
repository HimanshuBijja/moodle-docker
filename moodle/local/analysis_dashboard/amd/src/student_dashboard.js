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
 * Student dashboard module for the Analysis Dashboard.
 *
 * Initialises student-level widgets and loads data via AJAX,
 * passing the user ID parameter.
 *
 * @module     local_analysis_dashboard/student_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'local_analysis_dashboard/widget_renderer',
        'local_analysis_dashboard/lazy_loader'],
function($, Ajax, Notification, WidgetRenderer, LazyLoader) {

    /**
     * Load data for a single widget.
     *
     * @param {string} widgetId The widget identifier.
     * @param {string} widgetType The widget type.
     * @param {HTMLElement} container The widget content container.
     * @param {Number} userid The user ID.
     */
    var loadWidget = function(widgetId, widgetType, container, userid) {
        var params = JSON.stringify({userid: userid});

        Ajax.call([{
            methodname: 'local_analysis_dashboard_get_widget_data',
            args: {widgetid: widgetId, params: params}
        }])[0].done(function(response) {
            var data = JSON.parse(response.data);
            container.innerHTML = '';

            if (data.message) {
                container.innerHTML = '<div class="alert alert-info">' + data.message + '</div>';
                return;
            }

            WidgetRenderer.render(container, response.type, data, response.name);
        }).fail(function(error) {
            container.innerHTML = '<div class="alert alert-danger">' +
                '<i class="fa fa-exclamation-triangle"></i> ' +
                'Failed to load widget data.</div>';
            Notification.exception(error);
        });
    };

    return {
        /**
         * Initialise the student dashboard.
         *
         * @param {Number} userid The user ID.
         * @param {Array} widgets Array of widget configuration objects.
         */
        init: function(userid, widgets) {
            $(document).ready(function() {
                // Lazy-load widgets as they approach the viewport.
                var widgetElements = document.querySelectorAll('.analysis-dashboard-widget[data-widget-id]');
                LazyLoader.init(widgetElements, function(el) {
                    var widgetId = el.dataset.widgetId;
                    // Find matching widget config.
                    var widget = null;
                    for (var i = 0; i < widgets.length; i++) {
                        if (widgets[i].id === widgetId) {
                            widget = widgets[i];
                            break;
                        }
                    }
                    if (widget) {
                        var container = el.querySelector('.widget-content');
                        if (container) {
                            loadWidget(widget.id, widget.type, container, userid);
                        }
                    }
                });
            });
        }
    };
});

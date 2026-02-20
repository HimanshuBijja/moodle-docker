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
        var container = $('[data-widget-id="' + widget.id + '"]');
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

    return {
        /**
         * Initialize the course dashboard.
         *
         * @param {Array} widgets Array of widget config objects.
         * @param {Number} courseid Course ID.
         */
        init: function(widgets, courseid) {
            if (!widgets || !widgets.length) {
                return;
            }

            // Build a lookup map of widget configs by ID.
            var widgetMap = {};
            widgets.forEach(function(widget) {
                widgetMap[widget.id] = widget;
            });

            // Lazy-load widgets as they approach the viewport.
            var widgetElements = document.querySelectorAll('.analysis-dashboard-widget[data-widget-id]');
            LazyLoader.init(widgetElements, function(el) {
                var wid = el.dataset.widgetId;
                if (widgetMap[wid]) {
                    loadWidget(widgetMap[wid], courseid);
                }
            });
        }
    };
});

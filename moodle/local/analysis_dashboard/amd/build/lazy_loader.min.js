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
 * Lazy loader for dashboard widgets using IntersectionObserver.
 *
 * Defers AJAX loading of widget data until the widget card is
 * near the viewport. Falls back to immediate loading if the
 * IntersectionObserver API is not available.
 *
 * @module     local_analysis_dashboard/lazy_loader
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {

    return {
        /**
         * Initialize lazy loading for widget elements.
         *
         * @param {NodeList|Array} elements Widget elements to observe.
         * @param {Function} loadCallback Called with (element) when element nears viewport.
         */
        init: function(elements, loadCallback) {
            if (!elements || !elements.length) {
                return;
            }

            // Fallback: if IntersectionObserver not supported, load all immediately.
            if (typeof IntersectionObserver === 'undefined') {
                Array.prototype.forEach.call(elements, function(el) {
                    loadCallback(el);
                });
                return;
            }

            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var el = entry.target;
                        // Only load once.
                        if (!el.dataset.lazyLoaded) {
                            el.dataset.lazyLoaded = 'true';
                            loadCallback(el);
                        }
                        observer.unobserve(el);
                    }
                });
            }, {
                rootMargin: '200px',
                threshold: 0.1
            });

            Array.prototype.forEach.call(elements, function(el) {
                observer.observe(el);
            });
        }
    };
});

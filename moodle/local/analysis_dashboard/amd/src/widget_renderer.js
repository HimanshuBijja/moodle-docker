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
 * Widget renderer for the Analysis Dashboard.
 *
 * Dispatches to type-specific renderers (counter, line, bar, pie, doughnut).
 *
 * @module     local_analysis_dashboard/widget_renderer
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/chartjs'], function(ChartJS) {

    // Color palette for charts.
    var COLORS = [
        'rgba(54, 162, 235, 0.8)',   // Blue
        'rgba(255, 99, 132, 0.8)',   // Red
        'rgba(75, 192, 192, 0.8)',   // Teal
        'rgba(255, 206, 86, 0.8)',   // Yellow
        'rgba(153, 102, 255, 0.8)',  // Purple
        'rgba(255, 159, 64, 0.8)',   // Orange
        'rgba(46, 204, 113, 0.8)',   // Green
        'rgba(231, 76, 60, 0.8)',    // Dark Red
    ];

    var BORDER_COLORS = [
        'rgba(54, 162, 235, 1)',
        'rgba(255, 99, 132, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(46, 204, 113, 1)',
        'rgba(231, 76, 60, 1)',
    ];

    /**
     * Render a counter widget.
     *
     * @param {HTMLElement} container The container element.
     * @param {Object} data Widget data with items array.
     */
    var renderCounter = function(container, data) {
        var html = '<div class="widget-counter-grid">';

        if (data.items && data.items.length) {
            data.items.forEach(function(item) {
                var formattedValue = Number(item.value).toLocaleString();
                html += '<div class="widget-counter-item">';
                html += '<div class="widget-counter-value">' + formattedValue + '</div>';
                html += '<div class="widget-counter-label">' + item.label + '</div>';
                html += '</div>';
            });
        } else {
            html += '<div class="widget-counter-item">';
            html += '<div class="widget-counter-value text-muted">0</div>';
            html += '<div class="widget-counter-label text-muted">No data available</div>';
            html += '</div>';
        }

        html += '</div>';
        container.innerHTML = html;
    };

    /**
     * Create a Chart.js chart.
     *
     * @param {HTMLElement} container The container element.
     * @param {string} type Chart type (line, bar, pie, doughnut).
     * @param {Object} data Chart data with labels and datasets.
     * @param {string} title Chart title.
     */
    var renderChart = function(container, type, data, title) {
        // Show message if present (e.g., "completion not enabled").
        if (data.message) {
            container.innerHTML = '<div class="alert alert-info">' + data.message + '</div>';
            return;
        }

        // Check if data is empty.
        var hasData = data.labels && data.labels.length > 0 &&
            data.datasets && data.datasets.length > 0 &&
            data.datasets.some(function(ds) {
                return ds.data && ds.data.length > 0 && ds.data.some(function(v) { return v !== 0 && v !== null; });
            });

        // Create wrapper for positioning the overlay.
        var wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        container.appendChild(wrapper);

        var canvas = document.createElement('canvas');
        canvas.style.maxHeight = '300px';
        canvas.setAttribute('role', 'img');
        canvas.setAttribute('aria-label', type + ' chart: ' + (title || 'data visualization'));
        wrapper.appendChild(canvas);

        var chartLabels = data.labels || [];
        var datasets = [];

        if (!hasData) {
            // Render an empty placeholder chart.
            if (type === 'pie' || type === 'doughnut') {
                chartLabels = ['No data'];
                datasets.push({
                    label: '',
                    data: [1],
                    backgroundColor: ['#e9ecef'],
                    borderColor: ['#dee2e6'],
                    borderWidth: 1,
                });
            } else {
                chartLabels = ['', '', '', '', ''];
                datasets.push({
                    label: 'No data',
                    data: [0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(200, 200, 200, 0.3)',
                    borderColor: 'rgba(200, 200, 200, 0.5)',
                    borderWidth: 1,
                });
            }
        } else {
            data.datasets.forEach(function(dataset, index) {
                var ds = {
                    label: dataset.label || '',
                    data: dataset.data || [],
                    borderWidth: dataset.borderWidth || 2,
                };

                if (type === 'pie' || type === 'doughnut') {
                    ds.backgroundColor = dataset.backgroundColor || COLORS.slice(0, dataset.data.length);
                    ds.borderColor = dataset.borderColor || BORDER_COLORS.slice(0, dataset.data.length);
                } else {
                    ds.backgroundColor = dataset.backgroundColor || COLORS[index % COLORS.length];
                    ds.borderColor = dataset.borderColor || BORDER_COLORS[index % BORDER_COLORS.length];
                    if (type === 'line') {
                        ds.fill = dataset.fill !== undefined ? dataset.fill : false;
                        ds.tension = dataset.tension || 0.3;
                        ds.pointRadius = 3;
                        ds.pointHoverRadius = 5;
                    }
                }
                datasets.push(ds);
            });
        }

        new ChartJS(canvas, {
            type: type,
            data: {
                labels: chartLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: hasData,
                        position: (type === 'pie' || type === 'doughnut') ? 'right' : 'top',
                    },
                    title: {
                        display: false
                    },
                    tooltip: {
                        enabled: hasData
                    }
                },
                scales: (type === 'pie' || type === 'doughnut') ? {} : {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Add overlay text for empty charts.
        if (!hasData) {
            var overlay = document.createElement('div');
            overlay.className = 'widget-empty-overlay';
            overlay.innerHTML = '<i class="fa fa-chart-bar"></i><span>No data available</span>';
            wrapper.appendChild(overlay);
        }
    };

    /**
     * Map a value to a color from white -> green intensity.
     *
     * @param {Number} value The value to map.
     * @param {Number} max The maximum value.
     * @return {string} CSS color string.
     */
    var heatmapColor = function(value, max) {
        if (value === 0 || max === 0) {
            return '#f8f9fa';
        }
        var intensity = Math.min(value / max, 1);
        var r = Math.round(255 - (255 - 40) * intensity);
        var g = Math.round(255 - (255 - 167) * intensity);
        var b = Math.round(255 - (255 - 69) * intensity);
        return 'rgb(' + r + ',' + g + ',' + b + ')';
    };

    /**
     * Render a heatmap widget (7×24 grid).
     *
     * @param {HTMLElement} container The container element.
     * @param {Object} data Heatmap data with rows, cols, data, labels_rows, labels_cols.
     */
    var renderHeatmap = function(container, data) {
        if (!data.labels_rows || !data.labels_cols) {
            container.innerHTML = '<div class="alert alert-warning">Invalid heatmap data</div>';
            return;
        }

        // Find max value for color scaling.
        var maxVal = 0;
        var hasAnyData = false;
        for (var r = 0; r < data.rows; r++) {
            for (var c = 0; c < data.cols; c++) {
                if (data.data && data.data[r] && data.data[r][c] > 0) {
                    hasAnyData = true;
                    if (data.data[r][c] > maxVal) {
                        maxVal = data.data[r][c];
                    }
                }
            }
        }

        var html = '<div class="widget-heatmap">';
        html += '<table>';

        // Header row with hour labels.
        html += '<tr><th></th>';
        data.labels_cols.forEach(function(label) {
            html += '<th>' + label + '</th>';
        });
        html += '</tr>';

        // Data rows — show grid even if empty.
        for (var row = 0; row < data.rows; row++) {
            html += '<tr><th>' + data.labels_rows[row] + '</th>';
            for (var col = 0; col < data.cols; col++) {
                var val = (data.data && data.data[row] && data.data[row][col]) || 0;
                var color = heatmapColor(val, maxVal);
                html += '<td style="background-color:' + color + '" title="' +
                    data.labels_rows[row] + ' ' + data.labels_cols[col] + ': ' + val + ' events">';
                html += val > 0 ? val : '';
                html += '</td>';
            }
            html += '</tr>';
        }

        html += '</table>';

        if (!hasAnyData) {
            html += '<div class="text-center text-muted small mt-2">No activity data available</div>';
        }

        html += '</div>';
        container.innerHTML = html;
    };

    /**
     * Render a table widget using the datatable renderer.
     *
     * @param {HTMLElement} container The container element.
     * @param {Object} data Table data with headers and rows.
     * @param {string} title Table title.
     */
    var renderTable = function(container, data, title) {
        require(['local_analysis_dashboard/datatable_renderer'], function(DataTable) {
            DataTable.render(container, data, title);
        });
    };

    return {
        /**
         * Render a widget based on its type.
         *
         * @param {HTMLElement} container The container element.
         * @param {string} type Widget type.
         * @param {Object} data Widget data.
         * @param {string} title Widget title.
         */
        render: function(container, type, data, title) {
            switch (type) {
                case 'counter':
                    renderCounter(container, data);
                    break;
                case 'line':
                case 'bar':
                case 'pie':
                case 'doughnut':
                    renderChart(container, type, data, title);
                    break;
                case 'heatmap':
                    renderHeatmap(container, data);
                    break;
                case 'table':
                    renderTable(container, data, title);
                    break;
                default:
                    container.innerHTML = '<div class="alert alert-warning">Unknown widget type: ' + type + '</div>';
            }
        }
    };
});

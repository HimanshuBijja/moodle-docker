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
 * DataTable renderer for the Analysis Dashboard.
 *
 * Creates sortable, filterable HTML tables from widget data.
 *
 * @module     local_analysis_dashboard/datatable_renderer
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'local_analysis_dashboard/export'], function($, Export) {

    /**
     * Create a sortable HTML table.
     *
     * @param {HTMLElement} container The container element.
     * @param {Object} data Data with headers and rows.
     * @param {string} title Table title.
     */
    var createTable = function(container, data, title) {
        if (!data.headers || !data.rows) {
            container.innerHTML = '<div class="alert alert-warning">Invalid table data</div>';
            return;
        }

        var tableId = 'datatable-' + Math.random().toString(36).substr(2, 9);
        var html = '';

        // Controls: search filter + export buttons.
        html += '<div class="datatable-controls mb-2 d-flex justify-content-between align-items-center">';
        html += '<input type="text" class="form-control form-control-sm datatable-search" ';
        html += 'data-table="' + tableId + '" placeholder="Search..." style="max-width: 300px;">';
        html += Export.createButtons();
        html += '</div>';

        // Table.
        html += '<div class="table-responsive">';
        html += '<table class="table table-striped table-hover table-sm" id="' + tableId + '">';

        // Header.
        html += '<thead class="thead-light"><tr>';
        data.headers.forEach(function(header) {
            html += '<th class="datatable-sortable" data-sort-key="' + header.key + '">';
            html += header.label;
            html += ' <i class="fa fa-sort text-muted ml-1"></i>';
            html += '</th>';
        });
        html += '</tr></thead>';

        // Body.
        html += '<tbody>';
        data.rows.forEach(function(row) {
            html += '<tr>';
            data.headers.forEach(function(header) {
                var value = row[header.key];
                if (value === undefined || value === null) {
                    value = '';
                }
                html += '<td>' + value + '</td>';
            });
            html += '</tr>';
        });
        html += '</tbody>';

        html += '</table>';
        html += '</div>';

        // Pagination info.
        html += '<div class="datatable-info text-muted small">';
        html += 'Showing ' + data.rows.length + ' records';
        html += '</div>';

        container.innerHTML = html;

        // Bind export buttons.
        var tableEl = container.querySelector('#' + tableId);
        var exportTitle = title || 'table_export';
        $(container).find('.export-csv').on('click', function() {
            Export.exportCSV(tableEl, exportTitle);
        });
        $(container).find('.export-excel').on('click', function() {
            Export.exportExcel(tableEl, exportTitle);
        });

        // Bind search.
        $(container).find('.datatable-search').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            var table = $('#' + $(this).data('table'));
            table.find('tbody tr').each(function() {
                var rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(searchText) > -1);
            });
        });

        // Bind sort.
        $(container).find('.datatable-sortable').on('click', function() {
            var table = $(this).closest('table');
            var colIndex = $(this).index();
            var tbody = table.find('tbody');
            var rows = tbody.find('tr').toArray();
            var isAsc = $(this).hasClass('sort-asc');

            // Reset all headers.
            table.find('.datatable-sortable').removeClass('sort-asc sort-desc')
                .find('i').attr('class', 'fa fa-sort text-muted ml-1');

            // Sort rows.
            rows.sort(function(a, b) {
                var aVal = $(a).find('td').eq(colIndex).text();
                var bVal = $(b).find('td').eq(colIndex).text();
                // Try numeric sort.
                var aNum = parseFloat(aVal);
                var bNum = parseFloat(bVal);
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return isAsc ? bNum - aNum : aNum - bNum;
                }
                return isAsc ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
            });

            // Apply sort.
            if (isAsc) {
                $(this).addClass('sort-desc').find('i').attr('class', 'fa fa-sort-down ml-1');
            } else {
                $(this).addClass('sort-asc').find('i').attr('class', 'fa fa-sort-up ml-1');
            }

            tbody.empty().append(rows);
        });
    };

    return {
        /**
         * Render a data table.
         *
         * @param {HTMLElement} container The container element.
         * @param {Object} data Data with headers and rows arrays.
         * @param {string} title Table title.
         */
        render: function(container, data, title) {
            createTable(container, data, title);
        }
    };
});

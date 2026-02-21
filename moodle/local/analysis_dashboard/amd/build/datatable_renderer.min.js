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

    var ROWS_PER_PAGE = 10;

    /**
     * Create a sortable, paginated HTML table.
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
        var currentPage = 1;
        var rowsPerPage = ROWS_PER_PAGE;
        var allRows = data.rows.slice(); // Clone.
        var filteredRows = allRows.slice();

        var html = '';

        // Controls: page size + search + export.
        html += '<div class="datatable-controls mb-2">';
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<label class="mb-0 small text-muted mr-1">Show</label>';
        html += '<select class="form-control form-control-sm datatable-pagesize" style="width:auto;">';
        html += '<option value="10"' + (rowsPerPage === 10 ? ' selected' : '') + '>10</option>';
        html += '<option value="25">25</option>';
        html += '<option value="50">50</option>';
        html += '<option value="-1">All</option>';
        html += '</select>';
        html += '<label class="mb-0 small text-muted ml-1">entries</label>';
        html += '</div>';
        html += '<div class="d-flex align-items-center gap-2">';
        html += '<input type="text" class="form-control form-control-sm datatable-search" ';
        html += 'data-table="' + tableId + '" placeholder="Search..." style="max-width: 250px;">';
        html += Export.createButtons();
        html += '</div>';
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

        // Body placeholder.
        html += '<tbody></tbody>';
        html += '</table>';
        html += '</div>';

        // Pagination footer.
        html += '<div class="datatable-footer d-flex justify-content-between align-items-center mt-2">';
        html += '<div class="datatable-info text-muted small"></div>';
        html += '<nav class="datatable-pagination" aria-label="Table pagination">';
        html += '<ul class="pagination pagination-sm mb-0"></ul>';
        html += '</nav>';
        html += '</div>';

        container.innerHTML = html;

        var $container = $(container);
        var $tbody = $container.find('#' + tableId + ' tbody');
        var $info = $container.find('.datatable-info');
        var $pagination = $container.find('.datatable-pagination .pagination');

        /**
         * Render the current page of rows.
         */
        var renderPage = function() {
            $tbody.empty();
            var total = filteredRows.length;
            var start, end, pageRows;

            if (rowsPerPage === -1) {
                // Show all.
                start = 0;
                end = total;
                pageRows = filteredRows;
            } else {
                var totalPages = Math.max(1, Math.ceil(total / rowsPerPage));
                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }
                start = (currentPage - 1) * rowsPerPage;
                end = Math.min(start + rowsPerPage, total);
                pageRows = filteredRows.slice(start, end);
            }

            if (pageRows.length === 0) {
                $tbody.append('<tr><td colspan="' + data.headers.length +
                    '" class="text-center text-muted py-3">No matching records</td></tr>');
            } else {
                pageRows.forEach(function(row) {
                    var tr = '<tr>';
                    data.headers.forEach(function(header) {
                        var value = row[header.key];
                        if (value === undefined || value === null) {
                            value = '';
                        }
                        tr += '<td>' + value + '</td>';
                    });
                    tr += '</tr>';
                    $tbody.append(tr);
                });
            }

            // Update info.
            if (total === 0) {
                $info.text('No records');
            } else {
                $info.text('Showing ' + (start + 1) + ' to ' + end + ' of ' + total + ' entries' +
                    (filteredRows.length < allRows.length ?
                        ' (filtered from ' + allRows.length + ' total)' : ''));
            }

            // Update pagination buttons.
            renderPagination(total);
        };

        /**
         * Render pagination buttons.
         */
        var renderPagination = function(total) {
            $pagination.empty();

            if (rowsPerPage === -1 || total <= rowsPerPage) {
                return; // No pagination needed.
            }

            var totalPages = Math.ceil(total / rowsPerPage);

            // Previous button.
            $pagination.append(
                '<li class="page-item' + (currentPage === 1 ? ' disabled' : '') + '">' +
                '<a class="page-link datatable-page-prev" href="#" aria-label="Previous">' +
                '<span aria-hidden="true">&laquo;</span></a></li>'
            );

            // Page numbers (show max 5 around current).
            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) {
                startPage = Math.max(1, endPage - 4);
            }

            for (var p = startPage; p <= endPage; p++) {
                $pagination.append(
                    '<li class="page-item' + (p === currentPage ? ' active' : '') + '">' +
                    '<a class="page-link datatable-page-num" href="#" data-page="' + p + '">' + p + '</a></li>'
                );
            }

            // Next button.
            $pagination.append(
                '<li class="page-item' + (currentPage === totalPages ? ' disabled' : '') + '">' +
                '<a class="page-link datatable-page-next" href="#" aria-label="Next">' +
                '<span aria-hidden="true">&raquo;</span></a></li>'
            );
        };

        // Initial render.
        renderPage();

        // Bind page size change.
        $container.find('.datatable-pagesize').on('change', function() {
            rowsPerPage = parseInt($(this).val(), 10);
            currentPage = 1;
            renderPage();
        });

        // Bind pagination clicks.
        $container.on('click', '.datatable-page-prev', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                renderPage();
            }
        });

        $container.on('click', '.datatable-page-next', function(e) {
            e.preventDefault();
            var totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderPage();
            }
        });

        $container.on('click', '.datatable-page-num', function(e) {
            e.preventDefault();
            currentPage = parseInt($(this).data('page'), 10);
            renderPage();
        });

        // Bind export buttons.
        var tableEl = container.querySelector('#' + tableId);
        var exportTitle = title || 'table_export';
        $container.find('.export-csv').on('click', function() {
            Export.exportCSV(tableEl, exportTitle);
        });
        $container.find('.export-excel').on('click', function() {
            Export.exportExcel(tableEl, exportTitle);
        });

        // Bind search — filter and reset to page 1.
        $container.find('.datatable-search').on('keyup', function() {
            var searchText = $(this).val().toLowerCase();
            if (searchText === '') {
                filteredRows = allRows.slice();
            } else {
                filteredRows = allRows.filter(function(row) {
                    var rowText = '';
                    data.headers.forEach(function(header) {
                        var val = row[header.key];
                        if (val !== undefined && val !== null) {
                            rowText += String(val).toLowerCase() + ' ';
                        }
                    });
                    return rowText.indexOf(searchText) > -1;
                });
            }
            currentPage = 1;
            renderPage();
        });

        // Bind sort.
        $container.find('.datatable-sortable').on('click', function() {
            var $th = $(this);
            var colKey = $th.data('sort-key');
            var isAsc = $th.hasClass('sort-asc');

            // Reset all headers.
            $container.find('.datatable-sortable').removeClass('sort-asc sort-desc')
                .find('i').attr('class', 'fa fa-sort text-muted ml-1');

            // Sort filtered rows.
            filteredRows.sort(function(a, b) {
                var aVal = a[colKey];
                var bVal = b[colKey];
                if (aVal === undefined || aVal === null) { aVal = ''; }
                if (bVal === undefined || bVal === null) { bVal = ''; }
                var aNum = parseFloat(aVal);
                var bNum = parseFloat(bVal);
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return isAsc ? bNum - aNum : aNum - bNum;
                }
                return isAsc ? String(bVal).localeCompare(String(aVal))
                    : String(aVal).localeCompare(String(bVal));
            });

            if (isAsc) {
                $th.addClass('sort-desc').find('i').attr('class', 'fa fa-sort-down ml-1');
            } else {
                $th.addClass('sort-asc').find('i').attr('class', 'fa fa-sort-up ml-1');
            }

            currentPage = 1;
            renderPage();
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

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
 * Export module for table widgets in the Analysis Dashboard.
 *
 * Provides CSV and Excel export functionality via pure JavaScript.
 * No external dependencies — uses Blob API for downloads.
 *
 * @module     local_analysis_dashboard/export
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {

    /**
     * Escape a CSV field value per RFC 4180.
     *
     * If the value contains commas, double quotes, or newlines,
     * it is wrapped in double quotes with internal quotes doubled.
     *
     * @param {*} value The value to escape.
     * @return {string} Escaped CSV field.
     */
    var escapeCSVField = function(value) {
        var str = String(value === null || value === undefined ? '' : value);
        // Strip any HTML tags.
        str = str.replace(/<[^>]*>/g, '');
        if (str.indexOf(',') > -1 || str.indexOf('"') > -1 || str.indexOf('\n') > -1) {
            return '"' + str.replace(/"/g, '""') + '"';
        }
        return str;
    };

    /**
     * Generate a filename with date suffix.
     *
     * @param {string} title Widget or table title.
     * @param {string} extension File extension (csv, xls).
     * @return {string} Filename string.
     */
    var generateFilename = function(title, extension) {
        var safeName = title.replace(/[^a-zA-Z0-9]/g, '_').replace(/_+/g, '_').toLowerCase();
        var date = new Date().toISOString().slice(0, 10);
        return safeName + '_' + date + '.' + extension;
    };

    /**
     * Trigger a browser download for a string/blob.
     *
     * @param {string} content File content.
     * @param {string} filename Download filename.
     * @param {string} mimeType MIME type.
     */
    var downloadFile = function(content, filename, mimeType) {
        var blob = new Blob([content], {type: mimeType});
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        // Cleanup after a short delay.
        setTimeout(function() {
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }, 100);
    };

    /**
     * Extract headers and rows from an HTML table element.
     *
     * @param {HTMLElement} tableEl The table element.
     * @return {Object} Object with headers (string[]) and rows (string[][]).
     */
    var extractTableData = function(tableEl) {
        var headers = [];
        var rows = [];

        // Extract headers.
        var headerCells = tableEl.querySelectorAll('thead th');
        headerCells.forEach(function(th) {
            headers.push(th.textContent.trim());
        });

        // Extract rows (only visible rows).
        var bodyRows = tableEl.querySelectorAll('tbody tr');
        bodyRows.forEach(function(tr) {
            if (tr.style.display === 'none') {
                return; // Skip filtered-out rows.
            }
            var row = [];
            tr.querySelectorAll('td').forEach(function(td) {
                row.push(td.textContent.trim());
            });
            rows.push(row);
        });

        return {headers: headers, rows: rows};
    };

    return {
        /**
         * Export a table to CSV format and trigger download.
         *
         * @param {HTMLElement} tableEl The table element.
         * @param {string} title Table title for filename.
         */
        exportCSV: function(tableEl, title) {
            var data = extractTableData(tableEl);
            var lines = [];

            // Header row.
            lines.push(data.headers.map(escapeCSVField).join(','));

            // Data rows.
            data.rows.forEach(function(row) {
                lines.push(row.map(escapeCSVField).join(','));
            });

            var csv = lines.join('\n');
            downloadFile(csv, generateFilename(title, 'csv'), 'text/csv;charset=utf-8;');
        },

        /**
         * Export a table to Excel-compatible HTML format and trigger download.
         *
         * Uses the HTML table trick — wraps table in minimal HTML with
         * Excel-compatible MIME type to create a readable .xls file.
         *
         * @param {HTMLElement} tableEl The table element.
         * @param {string} title Table title for filename.
         */
        exportExcel: function(tableEl, title) {
            var data = extractTableData(tableEl);

            var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ';
            html += 'xmlns:x="urn:schemas-microsoft-com:office:excel">';
            html += '<head><meta charset="UTF-8">';
            html += '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>';
            html += '<x:ExcelWorksheet><x:Name>' + title + '</x:Name>';
            html += '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
            html += '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
            html += '</head><body><table border="1">';

            // Header.
            html += '<thead><tr>';
            data.headers.forEach(function(h) {
                html += '<th style="font-weight:bold;background:#f0f0f0">' + h + '</th>';
            });
            html += '</tr></thead>';

            // Body.
            html += '<tbody>';
            data.rows.forEach(function(row) {
                html += '<tr>';
                row.forEach(function(cell) {
                    html += '<td>' + cell + '</td>';
                });
                html += '</tr>';
            });
            html += '</tbody></table></body></html>';

            downloadFile(html, generateFilename(title, 'xls'), 'application/vnd.ms-excel');
        },

        /**
         * Create export button HTML.
         *
         * @return {string} HTML string for CSV and Excel export buttons.
         */
        createButtons: function() {
            var html = '<div class="widget-export-controls">';
            html += '<button type="button" class="widget-export-btn export-csv" title="Export as CSV">';
            html += '<i class="fa fa-file-text-o"></i> CSV';
            html += '</button>';
            html += '<button type="button" class="widget-export-btn export-excel" title="Export as Excel">';
            html += '<i class="fa fa-file-excel-o"></i> Excel';
            html += '</button>';
            html += '</div>';
            return html;
        }
    };
});

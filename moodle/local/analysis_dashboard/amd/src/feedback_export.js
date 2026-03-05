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
 * Feedback export module for the Analysis Dashboard.
 *
 * Exports the entire master feedback data (all feedback forms in a course)
 * as CSV, Excel, or PDF. Collects data via AJAX from each feedback form
 * and assembles a unified export with separators between forms.
 *
 * @module     local_analysis_dashboard/feedback_export
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {

    /**
     * Escape a CSV field value per RFC 4180.
     *
     * @param {*} value The value to escape.
     * @return {string} Escaped CSV field.
     */
    var escapeCSV = function(value) {
        var str = String(value === null || value === undefined ? '' : value);
        str = str.replace(/<[^>]*>/g, '');
        if (str.indexOf(',') > -1 || str.indexOf('"') > -1 || str.indexOf('\n') > -1) {
            return '"' + str.replace(/"/g, '""') + '"';
        }
        return str;
    };

    /**
     * Generate a filename with date suffix.
     *
     * @param {string} title Title for the filename.
     * @param {string} ext File extension.
     * @return {string}
     */
    var generateFilename = function(title, ext) {
        var safeName = title.replace(/[^a-zA-Z0-9]/g, '_').replace(/_+/g, '_').toLowerCase();
        var date = new Date().toISOString().slice(0, 10);
        return safeName + '_' + date + '.' + ext;
    };

    /**
     * Trigger a browser download.
     *
     * @param {string|Blob} content File content.
     * @param {string} filename Download filename.
     * @param {string} mimeType MIME type.
     */
    var downloadFile = function(content, filename, mimeType) {
        var blob;
        if (content instanceof Blob) {
            blob = content;
        } else {
            blob = new Blob([content], {type: mimeType});
        }
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        setTimeout(function() {
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        }, 100);
    };

    /**
     * Collect all feedback data from the server for all forms.
     * Returns data in form order, with ALL options (even zero count),
     * using raw counts (not percentages).
     *
     * @param {Array} feedbackForms Array of {id, name} objects.
     * @param {Number} courseid Course ID.
     * @return {Promise} Resolves with array of form data blocks.
     */
    var collectAllData = function(feedbackForms, courseid) {
        // Build sequential promises to maintain order.
        var allFormData = [];

        var promiseChain = $.Deferred().resolve().promise();

        feedbackForms.forEach(function(form) {
            promiseChain = promiseChain.then(function() {
                return Ajax.call([{
                    methodname: 'local_analysis_dashboard_get_widget_data',
                    args: {
                        widgetid: 'feedback_form_analysis',
                        params: JSON.stringify({courseid: courseid, feedbackid: form.id})
                    }
                }])[0].then(function(response) {
                    var data;
                    try {
                        data = JSON.parse(response.data);
                    } catch (e) {
                        allFormData.push({formName: form.name, rows: []});
                        return;
                    }

                    var rows = [];

                    // Process chart data — use counts[] (raw numbers), not data[] (percentages).
                    // Include ALL options, even those with 0 count, in the original order.
                    if (data.datasets && data.datasets.length > 0 && data.labels && data.labels.length > 0) {
                        // Each label is a question. Each dataset is an option.
                        // Datasets are in the same order as the options in the feedback form.
                        data.labels.forEach(function(questionLabel, qIdx) {
                            // For each question, iterate through all option datasets in order.
                            data.datasets.forEach(function(dataset) {
                                var count = 0;
                                // Use 'counts' array if available (raw count), else fall back to absolute data value.
                                if (dataset.counts && dataset.counts[qIdx] !== undefined) {
                                    count = dataset.counts[qIdx];
                                } else if (dataset.data && dataset.data[qIdx] !== undefined) {
                                    count = Math.abs(dataset.data[qIdx]); // Abs because diverging uses negative %.
                                }

                                rows.push({
                                    feedbackForm: form.name,
                                    question: questionLabel,
                                    type: 'Choice',
                                    option: dataset.label,
                                    count: count,
                                    comment: ''
                                });
                            });
                        });
                    }

                    // Process comments (text/textarea responses).
                    if (data.comments && data.comments.length > 0) {
                        data.comments.forEach(function(commentGroup) {
                            commentGroup.responses.forEach(function(resp) {
                                rows.push({
                                    feedbackForm: form.name,
                                    question: commentGroup.question,
                                    type: 'Text',
                                    option: '',
                                    count: '',
                                    comment: resp
                                });
                            });
                        });
                    }

                    allFormData.push({formName: form.name, rows: rows});
                }).catch(function() {
                    allFormData.push({formName: form.name, rows: []});
                });
            });
        });

        return promiseChain.then(function() {
            return allFormData;
        });
    };

    var HEADERS = ['Feedback Form', 'Question', 'Type', 'Option/Answer', 'Count', 'Comment'];
    var COL_COUNT = HEADERS.length;

    /**
     * Build a flat row list with separator rows between forms.
     * Separator rows are objects with a special 'separator' flag.
     *
     * @param {Array} formDataList Array of {formName, rows} objects.
     * @return {Array} Flat list of row objects with separators.
     */
    var buildFlatRows = function(formDataList) {
        var result = [];
        formDataList.forEach(function(formData, idx) {
            // Add form data rows.
            formData.rows.forEach(function(row) {
                result.push(row);
            });
            // After each form (except the last), add two separator rows.
            if (idx < formDataList.length - 1) {
                result.push({separator: true, formName: formData.formName});
                result.push({separator: true, formName: formData.formName});
            }
        });
        return result;
    };

    /**
     * Export as CSV.
     *
     * @param {Array} formDataList Array of form data blocks.
     * @param {string} courseName Course name for filename.
     */
    var exportCSV = function(formDataList, courseName) {
        var flatRows = buildFlatRows(formDataList);
        var lines = [];
        lines.push(HEADERS.map(escapeCSV).join(','));

        flatRows.forEach(function(row) {
            if (row.separator) {
                // Separator row: empty fields.
                lines.push(new Array(COL_COUNT).fill('').join(','));
            } else {
                lines.push([
                    escapeCSV(row.feedbackForm),
                    escapeCSV(row.question),
                    escapeCSV(row.type),
                    escapeCSV(row.option),
                    escapeCSV(row.count),
                    escapeCSV(row.comment)
                ].join(','));
            }
        });
        var csv = '\uFEFF' + lines.join('\n'); // BOM for Excel UTF-8 compat.
        downloadFile(csv, generateFilename('feedback_' + courseName, 'csv'), 'text/csv;charset=utf-8;');
    };

    /**
     * Export as Excel (HTML table approach for Excel compatibility).
     * Separator rows are colored red.
     *
     * @param {Array} formDataList Array of form data blocks.
     * @param {string} courseName Course name for filename.
     */
    var exportExcel = function(formDataList, courseName) {
        var flatRows = buildFlatRows(formDataList);

        var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ';
        html += 'xmlns:x="urn:schemas-microsoft-com:office:excel">';
        html += '<head><meta charset="UTF-8">';
        html += '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>';
        html += '<x:ExcelWorksheet><x:Name>Feedback</x:Name>';
        html += '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        html += '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        html += '</head><body><table border="1">';

        // Header row.
        html += '<thead><tr>';
        HEADERS.forEach(function(h) {
            html += '<th style="font-weight:bold;background:#4472C4;color:#fff;padding:8px">' + h + '</th>';
        });
        html += '</tr></thead>';

        // Data rows.
        html += '<tbody>';
        var dataRowIdx = 0;
        flatRows.forEach(function(row) {
            if (row.separator) {
                // Red separator row spanning all columns.
                html += '<tr>';
                for (var i = 0; i < COL_COUNT; i++) {
                    html += '<td style="background:#C00000;color:#C00000;padding:6px">&nbsp;</td>';
                }
                html += '</tr>';
            } else {
                var bg = dataRowIdx % 2 === 0 ? '#D9E2F3' : '#ffffff';
                html += '<tr>';
                html += '<td style="background:' + bg + ';padding:6px">' + (row.feedbackForm || '') + '</td>';
                html += '<td style="background:' + bg + ';padding:6px">' + (row.question || '') + '</td>';
                html += '<td style="background:' + bg + ';padding:6px">' + (row.type || '') + '</td>';
                html += '<td style="background:' + bg + ';padding:6px">' + (row.option || '') + '</td>';
                html += '<td style="background:' + bg + ';padding:6px;text-align:center">' +
                    (row.count !== '' ? row.count : '') + '</td>';
                html += '<td style="background:' + bg + ';padding:6px">' + (row.comment || '') + '</td>';
                html += '</tr>';
                dataRowIdx++;
            }
        });
        html += '</tbody></table></body></html>';

        downloadFile(html, generateFilename('feedback_' + courseName, 'xls'), 'application/vnd.ms-excel');
    };

    /**
     * Export as PDF using a print-optimized hidden window.
     * Separator rows are colored red.
     *
     * @param {Array} formDataList Array of form data blocks.
     * @param {string} courseName Course name for filename.
     */
    var exportPDF = function(formDataList, courseName) {
        var flatRows = buildFlatRows(formDataList);

        var html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        html += '<title>Feedback Export - ' + courseName + '</title>';
        html += '<style>';
        html += 'body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }';
        html += 'h1 { font-size: 18px; color: #333; margin-bottom: 10px; }';
        html += 'h2 { font-size: 13px; color: #666; margin-bottom: 20px; }';
        html += 'table { width: 100%; border-collapse: collapse; margin-top: 10px; }';
        html += 'th { background: #4472C4; color: #fff; padding: 8px 6px; text-align: left; font-size: 11px; }';
        html += 'td { padding: 6px; border: 1px solid #ddd; font-size: 10px; }';
        html += 'tr:nth-child(even) td:not(.sep) { background: #f2f6fc; }';
        html += 'td.sep { background: #C00000; }';
        html += '@media print { body { margin: 0; } }';
        html += '</style></head><body>';
        html += '<h1>Feedback Export</h1>';
        html += '<h2>' + courseName + ' &mdash; ' + new Date().toLocaleDateString() + '</h2>';
        html += '<table><thead><tr>';
        HEADERS.forEach(function(h) {
            html += '<th>' + h + '</th>';
        });
        html += '</tr></thead><tbody>';

        flatRows.forEach(function(row) {
            if (row.separator) {
                html += '<tr>';
                for (var i = 0; i < COL_COUNT; i++) {
                    html += '<td class="sep">&nbsp;</td>';
                }
                html += '</tr>';
            } else {
                html += '<tr>';
                html += '<td>' + (row.feedbackForm || '') + '</td>';
                html += '<td>' + (row.question || '') + '</td>';
                html += '<td>' + (row.type || '') + '</td>';
                html += '<td>' + (row.option || '') + '</td>';
                html += '<td style="text-align:center">' + (row.count !== '' ? row.count : '') + '</td>';
                html += '<td>' + (row.comment || '') + '</td>';
                html += '</tr>';
            }
        });
        html += '</tbody></table></body></html>';

        // Open in a new window for printing (Save as PDF via browser print dialog).
        var printWindow = window.open('', '_blank', 'width=900,height=700');
        if (printWindow) {
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
            }, 500);
        }
    };

    return {
        /**
         * Initialize the feedback export controls.
         *
         * @param {Array} feedbackForms Array of feedback form config objects.
         * @param {Number} courseid Course ID.
         * @param {string} courseName Course name for filenames.
         */
        init: function(feedbackForms, courseid, courseName) {
            if (!feedbackForms || feedbackForms.length === 0) {
                return;
            }

            var container = document.querySelector('[data-region="feedback-export"]');
            if (!container) {
                return;
            }

            // Bind export button clicks.
            $(container).on('click', '[data-export-format]', function(e) {
                e.preventDefault();
                var format = $(this).data('export-format');
                var btn = $(this);
                var dropdown = btn.closest('.dropdown').find('.dropdown-toggle');

                // Show loading state.
                dropdown.prop('disabled', true).addClass('disabled');
                var origText = dropdown.html();
                dropdown.html('<i class="fa fa-spinner fa-spin mr-1"></i> Exporting...');

                collectAllData(feedbackForms, courseid).then(function(formDataList) {
                    // Check if any form has data.
                    var hasData = false;
                    formDataList.forEach(function(fd) {
                        if (fd.rows.length > 0) {
                            hasData = true;
                        }
                    });

                    if (!hasData) {
                        Str.get_string('widget_no_data', 'local_analysis_dashboard').then(function(str) {
                            Notification.addNotification({
                                message: str,
                                type: 'warning'
                            });
                        }).catch(Notification.exception);
                        return;
                    }

                    switch (format) {
                        case 'csv':
                            exportCSV(formDataList, courseName);
                            break;
                        case 'xlsx':
                            exportExcel(formDataList, courseName);
                            break;
                        case 'pdf':
                            exportPDF(formDataList, courseName);
                            break;
                    }
                }).catch(function(error) {
                    Notification.exception(error);
                }).always(function() {
                    dropdown.prop('disabled', false).removeClass('disabled');
                    dropdown.html(origText);
                });
            });
        }
    };
});

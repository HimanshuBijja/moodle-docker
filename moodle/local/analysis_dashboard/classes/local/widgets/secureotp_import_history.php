<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_import_history extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_import_history'; }
    public function get_type(): string { return 'table'; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;

        $sql = "SELECT id, batch_id, source_system, total_records, success_count,
                       failed_count, status, duration_seconds, started_at
                  FROM {auth_secureotp_import_log}
              ORDER BY started_at DESC
                 LIMIT 20";
        $records = $DB->get_records_sql($sql);

        $rows = [];
        foreach ($records as $record) {
            $rows[] = [
                \core_text::substr($record->batch_id, 0, 12),
                $record->source_system ?: '-',
                (int) $record->total_records,
                (int) $record->success_count,
                (int) $record->failed_count,
                $record->status,
                round((float) $record->duration_seconds, 1) . 's',
            ];
        }

        return [
            'headers' => [
                get_string('batch_id', 'local_analysis_dashboard'),
                get_string('source_system', 'local_analysis_dashboard'),
                get_string('total'),
                get_string('success', 'local_analysis_dashboard'),
                get_string('failed', 'local_analysis_dashboard'),
                get_string('status'),
                get_string('duration', 'local_analysis_dashboard'),
            ],
            'rows' => $rows,
        ];
    }
}

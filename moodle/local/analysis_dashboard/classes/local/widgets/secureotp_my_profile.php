<?php
namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\secureotp_base;

class secureotp_my_profile extends secureotp_base {
    public function get_name(): string { return 'widget_secureotp_my_profile'; }
    public function get_type(): string { return 'table'; }
    public function get_required_capability(): string { return 'local/analysis_dashboard:widget_secureotp_my_profile'; }
    public function get_supported_context_levels(): array { return [CONTEXT_USER]; }

    protected function get_secureotp_data(array $params = []): array {
        global $DB;
        $userid = (int) ($params['userid'] ?? 0);
        if (!$userid) {
            return ['headers' => [], 'rows' => []];
        }

        $record = $DB->get_record('auth_secureotp_userdata', ['userid' => $userid]);
        if (!$record) {
            return [
                'headers' => [],
                'rows' => [],
                'message' => get_string('no_secureotp_profile', 'local_analysis_dashboard'),
            ];
        }

        return [
            'headers' => [
                get_string('field', 'local_analysis_dashboard'),
                get_string('value', 'local_analysis_dashboard'),
            ],
            'rows' => [
                [get_string('employee_id', 'local_analysis_dashboard'), $record->employee_id ?: '-'],
                [get_string('employee_type', 'local_analysis_dashboard'), $record->employee_type ?: '-'],
                [get_string('current_rank', 'local_analysis_dashboard'), $record->current_rank ?: '-'],
                [get_string('working_location', 'local_analysis_dashboard'), $record->working_location ?: '-'],
                [get_string('unit_name', 'local_analysis_dashboard'), $record->unit_name ?: '-'],
                [get_string('cp_sp_office', 'local_analysis_dashboard'), $record->cp_sp_office ?: '-'],
            ],
        ];
    }
}

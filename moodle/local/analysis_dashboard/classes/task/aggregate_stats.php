<?php
namespace local_analysis_dashboard\task;

defined('MOODLE_INTERNAL') || die();

class aggregate_stats extends \core\task\scheduled_task {

    /**
     * Get the task name.
     */
    public function get_name() {
        return get_string('task_aggregate_stats', 'local_analysis_dashboard');
    }

    /**
     * Execute the task logic.
     */
    public function execute() {
        global $DB;

        mtrace("Starting aggregation task...");

        $cache = \cache::make('local_analysis_dashboard', 'site_stats');

        // 1. Site Stats: Users per District (Global)
        $sql = "SELECT aud.cp_sp_office AS district, COUNT(u.id) AS count
                FROM {user} u
                JOIN {auth_secureotp_userdata} aud ON aud.userid = u.id
                WHERE u.deleted = 0 AND u.suspended = 0
                GROUP BY aud.cp_sp_office
                ORDER BY count DESC";

        mtrace("Running global site stats query...");
        $site_stats = $DB->get_records_sql($sql);
        $formatted_stats = $this->format_stats($site_stats);
        $cache->set('districts', $formatted_stats);

        // 2. Yearly Stats: Users per District per Year (last 5 years)
        $currentyear = (int)date('Y');
        for ($year = $currentyear; $year > $currentyear - 5; $year--) {
            mtrace("Aggregating stats for year $year...");
            $yearlysql = "SELECT aud.cp_sp_office AS district, COUNT(u.id) AS count
                          FROM {user} u
                          JOIN {auth_secureotp_userdata} aud ON aud.userid = u.id
                          WHERE u.deleted = 0 AND u.suspended = 0
                            AND u.timecreated >= :start AND u.timecreated <= :end
                          GROUP BY aud.cp_sp_office
                          ORDER BY count DESC";
            
            $start = make_timestamp($year, 1, 1, 0, 0, 0);
            $end = make_timestamp($year, 12, 31, 23, 59, 59);
            
            $yearly_stats = $DB->get_records_sql($yearlysql, ['start' => $start, 'end' => $end]);
            $formatted_yearly = $this->format_stats($yearly_stats);
            $cache->set('districts_' . $year, $formatted_yearly);
        }

        mtrace("Aggregation task complete.");
    }

    /**
     * Helper to format DB results for cache.
     */
    private function format_stats($records) {
        $formatted = [];
        foreach ($records as $record) {
            $district = empty($record->district) ? 'Unknown' : $record->district;
            $formatted[$district] = (int)$record->count;
        }
        return $formatted;
    }
}

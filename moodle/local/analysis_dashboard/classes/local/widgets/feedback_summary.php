<?php
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

namespace local_analysis_dashboard\local\widgets;

use local_analysis_dashboard\local\base_widget;

/**
 * Feedback Summary widget.
 *
 * Aggregates all Feedback activities in a course and displays a stacked bar
 * chart showing the response distribution per question, grouped by feedback
 * form name.
 *
 * Supports multichoice, multichoicerated, and numeric feedback item types.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback_summary extends base_widget {

    /** Separator constants matching mod_feedback definitions. */
    private const MULTICHOICE_TYPE_SEP = '>>>>>';
    private const MULTICHOICE_LINE_SEP = '|';
    private const MULTICHOICERATED_TYPE_SEP = '>>>>>';
    private const MULTICHOICERATED_LINE_SEP = '|';
    private const MULTICHOICERATED_VALUE_SEP = '####';
    private const MULTICHOICERATED_ADJUST_SEP = '<<<<<';

    /** @var array Color palette for stacked datasets. */
    private const COLORS = [
        'rgba(54, 162, 235, 0.75)',   // Blue
        'rgba(255, 99, 132, 0.75)',   // Red
        'rgba(75, 192, 192, 0.75)',   // Teal
        'rgba(255, 206, 86, 0.75)',   // Yellow
        'rgba(153, 102, 255, 0.75)',  // Purple
        'rgba(255, 159, 64, 0.75)',   // Orange
        'rgba(46, 204, 113, 0.75)',   // Green
        'rgba(231, 76, 60, 0.75)',    // Dark Red
        'rgba(52, 73, 94, 0.75)',     // Dark Blue
        'rgba(241, 196, 15, 0.75)',   // Gold
    ];

    private const BORDER_COLORS = [
        'rgba(54, 162, 235, 1)',
        'rgba(255, 99, 132, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(46, 204, 113, 1)',
        'rgba(231, 76, 60, 1)',
        'rgba(52, 73, 94, 1)',
        'rgba(241, 196, 15, 1)',
    ];

    public function get_name(): string {
        return 'widget_feedback_summary';
    }

    public function get_type(): string {
        return 'bar';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:viewcourse';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'feedback_summary';
    }

    /**
     * Get the section this widget belongs to.
     *
     * @return string Section identifier.
     */
    public function get_section(): string {
        return 'feedback_analysis';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['labels' => [], 'datasets' => []];
        }

        // Get all feedback activities in this course.
        $feedbacks = $DB->get_records('feedback', ['course' => $courseid], 'id', 'id, name');
        if (empty($feedbacks)) {
            return [
                'labels' => [],
                'datasets' => [],
                'message' => get_string('no_feedback_activities', 'local_analysis_dashboard'),
            ];
        }

        // Collect all question labels and response distributions.
        // Structure: $questions[] = ['label' => string, 'feedback_name' => string, 'options' => [optionlabel => count]].
        $questions = [];
        // Track all unique option labels across all questions for dataset keys.
        $alloptions = [];

        foreach ($feedbacks as $feedback) {
            // Get items that have values (actual questions, not labels/pagebreaks).
            $items = $DB->get_records('feedback_item', [
                'feedback' => $feedback->id,
                'hasvalue' => 1,
            ], 'position', 'id, name, typ, presentation');

            if (empty($items)) {
                continue;
            }

            foreach ($items as $item) {
                $questiondata = $this->process_feedback_item($item, $feedback, $DB);
                if ($questiondata !== null) {
                    $questions[] = $questiondata;
                    foreach (array_keys($questiondata['options']) as $optlabel) {
                        if (!isset($alloptions[$optlabel])) {
                            $alloptions[$optlabel] = true;
                        }
                    }
                }
            }
        }

        if (empty($questions)) {
            return [
                'labels' => [],
                'datasets' => [],
                'message' => get_string('no_feedback_responses', 'local_analysis_dashboard'),
            ];
        }

        // Build labels (X-axis): "FeedbackName: QuestionName" truncated.
        $labels = [];
        foreach ($questions as $q) {
            $label = mb_substr($q['feedback_name'], 0, 20) . ': ' . mb_substr($q['label'], 0, 30);
            $labels[] = $label;
        }

        // Build datasets: one per unique option label.
        $optionkeys = array_keys($alloptions);
        $datasets = [];
        foreach ($optionkeys as $idx => $optlabel) {
            $data = [];
            foreach ($questions as $q) {
                $data[] = $q['options'][$optlabel] ?? 0;
            }
            $coloridx = $idx % count(self::COLORS);
            $datasets[] = [
                'label' => $optlabel,
                'data' => $data,
                'backgroundColor' => self::COLORS[$coloridx],
                'borderColor' => self::BORDER_COLORS[$coloridx],
                'borderWidth' => 1,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'stacked' => true,
        ];
    }

    /**
     * Process a single feedback item and return its response distribution.
     *
     * @param \stdClass $item The feedback_item record.
     * @param \stdClass $feedback The feedback record.
     * @param \moodle_database $DB Database instance.
     * @return array|null Array with 'label', 'feedback_name', 'options' or null if no data.
     */
    private function process_feedback_item(\stdClass $item, \stdClass $feedback, $DB): ?array {
        switch ($item->typ) {
            case 'multichoice':
                return $this->process_multichoice($item, $feedback, $DB);
            case 'multichoicerated':
                return $this->process_multichoicerated($item, $feedback, $DB);
            case 'numeric':
                return $this->process_numeric($item, $feedback, $DB);
            default:
                return null;
        }
    }

    /**
     * Process a multichoice feedback item.
     *
     * @param \stdClass $item The feedback_item record.
     * @param \stdClass $feedback The feedback record.
     * @param \moodle_database $DB Database instance.
     * @return array|null
     */
    private function process_multichoice(\stdClass $item, \stdClass $feedback, $DB): ?array {
        $presentation = $item->presentation;
        // Extract subtype and options: format is "r>>>>>opt1|opt2|opt3" or "c>>>>>opt1|opt2".
        $parts = explode(self::MULTICHOICE_TYPE_SEP, $presentation);
        $optionstring = count($parts) > 1 ? $parts[1] : $parts[0];
        // Remove adjustment separator if present.
        $adjparts = explode('<<<<<', $optionstring);
        $optionstring = $adjparts[0];

        $optionlabels = explode(self::MULTICHOICE_LINE_SEP, $optionstring);
        if (empty($optionlabels)) {
            return null;
        }

        // Get completed response values for this item.
        $sql = "SELECT fv.id, fv.value
                  FROM {feedback_value} fv
                  JOIN {feedback_completed} fc ON fc.id = fv.completed
                 WHERE fv.item = :itemid
                   AND fv.value != ''";
        $values = $DB->get_records_sql($sql, ['itemid' => $item->id]);

        // Count responses per option.
        $optioncounts = [];
        foreach ($optionlabels as $label) {
            $cleanlabel = trim(strip_tags($label));
            if ($cleanlabel !== '') {
                $optioncounts[$cleanlabel] = 0;
            }
        }

        foreach ($values as $val) {
            // Value is 1-indexed. For checkboxes it can be "1|3|5".
            $selectedindices = explode(self::MULTICHOICE_LINE_SEP, $val->value);
            foreach ($selectedindices as $selidx) {
                $selidx = (int) $selidx;
                if ($selidx > 0 && $selidx <= count($optionlabels)) {
                    $cleanlabel = trim(strip_tags($optionlabels[$selidx - 1]));
                    if (isset($optioncounts[$cleanlabel])) {
                        $optioncounts[$cleanlabel]++;
                    }
                }
            }
        }

        // Only return if we have responses.
        $totalresponses = array_sum($optioncounts);
        if ($totalresponses === 0) {
            return null;
        }

        return [
            'label' => format_string($item->name),
            'feedback_name' => format_string($feedback->name),
            'options' => $optioncounts,
        ];
    }

    /**
     * Process a multichoicerated feedback item.
     *
     * @param \stdClass $item The feedback_item record.
     * @param \stdClass $feedback The feedback record.
     * @param \moodle_database $DB Database instance.
     * @return array|null
     */
    private function process_multichoicerated(\stdClass $item, \stdClass $feedback, $DB): ?array {
        $presentation = $item->presentation;
        // Format: "r>>>>>1####Excellent|2####Good|3####Average" possibly with "<<<<<1" adjustment.
        $parts = explode(self::MULTICHOICERATED_TYPE_SEP, $presentation);
        $optionstring = count($parts) > 1 ? $parts[1] : $parts[0];
        // Remove adjustment separator.
        $adjparts = explode(self::MULTICHOICERATED_ADJUST_SEP, $optionstring);
        $optionstring = $adjparts[0];

        $lines = explode(self::MULTICHOICERATED_LINE_SEP, $optionstring);
        if (empty($lines)) {
            return null;
        }

        // Parse option labels: each line is "weight####text".
        $optionlabels = [];
        foreach ($lines as $line) {
            $lineparts = explode(self::MULTICHOICERATED_VALUE_SEP, $line, 2);
            if (count($lineparts) === 2) {
                $optionlabels[] = trim(strip_tags($lineparts[1]));
            } else {
                $optionlabels[] = trim(strip_tags($line));
            }
        }

        // Get completed response values.
        $sql = "SELECT fv.id, fv.value
                  FROM {feedback_value} fv
                  JOIN {feedback_completed} fc ON fc.id = fv.completed
                 WHERE fv.item = :itemid
                   AND fv.value != ''";
        $values = $DB->get_records_sql($sql, ['itemid' => $item->id]);

        // Count responses per option.
        $optioncounts = [];
        foreach ($optionlabels as $label) {
            if ($label !== '') {
                $optioncounts[$label] = 0;
            }
        }

        foreach ($values as $val) {
            $selidx = (int) $val->value;
            if ($selidx > 0 && $selidx <= count($optionlabels)) {
                $label = $optionlabels[$selidx - 1];
                if (isset($optioncounts[$label])) {
                    $optioncounts[$label]++;
                }
            }
        }

        $totalresponses = array_sum($optioncounts);
        if ($totalresponses === 0) {
            return null;
        }

        return [
            'label' => format_string($item->name),
            'feedback_name' => format_string($feedback->name),
            'options' => $optioncounts,
        ];
    }

    /**
     * Process a numeric feedback item by bucketing into ranges.
     *
     * @param \stdClass $item The feedback_item record.
     * @param \stdClass $feedback The feedback record.
     * @param \moodle_database $DB Database instance.
     * @return array|null
     */
    private function process_numeric(\stdClass $item, \stdClass $feedback, $DB): ?array {
        // Numeric presentation format: "min|max" e.g. "1|10" or just a range.
        $parts = explode(self::MULTICHOICE_LINE_SEP, $item->presentation);
        $rangemin = isset($parts[0]) ? (float) $parts[0] : 0;
        $rangemax = isset($parts[1]) ? (float) $parts[1] : 10;

        if ($rangemax <= $rangemin) {
            $rangemax = $rangemin + 10;
        }

        // Get completed response values.
        $sql = "SELECT fv.id, fv.value
                  FROM {feedback_value} fv
                  JOIN {feedback_completed} fc ON fc.id = fv.completed
                 WHERE fv.item = :itemid
                   AND fv.value != ''";
        $values = $DB->get_records_sql($sql, ['itemid' => $item->id]);

        if (empty($values)) {
            return null;
        }

        // Create 5 equal buckets.
        $numbuckets = 5;
        $bucketsize = ($rangemax - $rangemin) / $numbuckets;
        $buckets = [];
        for ($i = 0; $i < $numbuckets; $i++) {
            $low = $rangemin + ($i * $bucketsize);
            $high = $low + $bucketsize;
            $label = round($low, 1) . '-' . round($high, 1);
            $buckets[$label] = 0;
        }

        foreach ($values as $val) {
            $numval = (float) $val->value;
            $bucketidx = min((int) floor(($numval - $rangemin) / $bucketsize), $numbuckets - 1);
            $bucketidx = max($bucketidx, 0);
            $bucketkeys = array_keys($buckets);
            if (isset($bucketkeys[$bucketidx])) {
                $buckets[$bucketkeys[$bucketidx]]++;
            }
        }

        $totalresponses = array_sum($buckets);
        if ($totalresponses === 0) {
            return null;
        }

        return [
            'label' => format_string($item->name),
            'feedback_name' => format_string($feedback->name),
            'options' => $buckets,
        ];
    }
}

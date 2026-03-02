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
 * Feedback Form Analysis widget — diverging stacked bar chart.
 *
 * Analyses a single feedback form and produces a diverging stacked bar chart
 * where each question is on the Y-axis and the response distribution is
 * shown as negative (lower ratings) and positive (higher ratings) percentages.
 *
 * Also collects text/textarea responses for a comments view.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback_form_analysis extends base_widget {

    /** Separator constants matching mod_feedback definitions. */
    private const TYPE_SEP = '>>>>>';
    private const LINE_SEP = '|';
    private const VALUE_SEP = '####';
    private const ADJUST_SEP = '<<<<<';

    public function get_name(): string {
        return 'widget_feedback_form_analysis';
    }

    public function get_type(): string {
        return 'diverging_bar';
    }

    public function get_required_capability(): string {
        return 'local/analysis_dashboard:viewcourse';
    }

    public function get_supported_context_levels(): array {
        return [CONTEXT_COURSE];
    }

    public function get_cache_key(): string {
        return 'feedback_form_analysis';
    }

    /**
     * Get the section this widget belongs to.
     *
     * Widgets returning 'feedback_analysis' are rendered in the dedicated
     * Feedback Analysis section instead of the standard widget grid.
     *
     * @return string Section identifier.
     */
    public function get_section(): string {
        return 'feedback_analysis';
    }

    /**
     * Override caching to include feedbackid in key.
     *
     * @param array $params Parameters including courseid and feedbackid.
     * @return array Widget data.
     */
    public function get_cached_data(array $params = []): array {
        $feedbackid = $params['feedbackid'] ?? 0;
        $courseid = $params['courseid'] ?? 0;

        $cache = \cache::make('local_analysis_dashboard', 'coursestats');
        $key = $this->get_cache_key() . '_' . $courseid . '_fb_' . $feedbackid;

        $data = $cache->get($key);
        if ($data !== false) {
            return $data;
        }

        $data = $this->get_data($params);
        $cache->set($key, $data);

        return $data;
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        $feedbackid = $params['feedbackid'] ?? 0;

        if (empty($courseid) || empty($feedbackid)) {
            return ['labels' => [], 'datasets' => [], 'diverging' => true];
        }

        // Verify the feedback belongs to this course.
        $feedback = $DB->get_record('feedback', ['id' => $feedbackid, 'course' => $courseid], 'id, name');
        if (!$feedback) {
            return ['labels' => [], 'datasets' => [], 'diverging' => true];
        }

        // Get all items with values (rated questions).
        $items = $DB->get_records('feedback_item', [
            'feedback' => $feedbackid,
            'hasvalue' => 1,
        ], 'position', 'id, name, typ, presentation, position');

        if (empty($items)) {
            return [
                'labels' => [],
                'datasets' => [],
                'diverging' => true,
                'message' => get_string('no_feedback_responses', 'local_analysis_dashboard'),
            ];
        }

        // Separate rated items from text items.
        $rateditems = [];
        $comments = [];

        foreach ($items as $item) {
            if ($item->typ === 'textarea' || $item->typ === 'textfield') {
                // Collect text responses as comments.
                $textvals = $this->get_text_responses($item->id, $DB);
                if (!empty($textvals)) {
                    $comments[] = [
                        'question' => format_string($item->name),
                        'responses' => $textvals,
                    ];
                }
            } else if ($item->typ === 'multichoice' || $item->typ === 'multichoicerated') {
                $rateditems[] = $item;
            }
            // Skip numeric, info, label, pagebreak types for diverging chart.
        }

        if (empty($rateditems)) {
            return [
                'labels' => [],
                'datasets' => [],
                'diverging' => true,
                'comments' => $comments,
                'message' => get_string('no_feedback_responses', 'local_analysis_dashboard'),
            ];
        }

        // Find the maximum number of options across all items to normalize datasets.
        $alloptionlabels = [];
        $itemdata = [];

        foreach ($rateditems as $item) {
            $parsed = $this->parse_item_options($item);
            if ($parsed === null) {
                continue;
            }

            // Count responses.
            $counts = $this->count_responses($item->id, count($parsed['options']), $DB);
            $totalresponses = array_sum($counts);

            if ($totalresponses === 0) {
                continue;
            }

            // Convert to percentages.
            $percentages = [];
            foreach ($counts as $count) {
                $percentages[] = round(($count / $totalresponses) * 100, 1);
            }

            $itemdata[] = [
                'label' => 'Q' . $item->position . ': ' . mb_substr(format_string($item->name), 0, 50),
                'options' => $parsed['options'],
                'percentages' => $percentages,
            ];

            // Track all unique option labels.
            foreach ($parsed['options'] as $opt) {
                if (!in_array($opt, $alloptionlabels)) {
                    $alloptionlabels[] = $opt;
                }
            }
        }

        if (empty($itemdata)) {
            return [
                'labels' => [],
                'datasets' => [],
                'diverging' => true,
                'comments' => $comments,
                'message' => get_string('no_feedback_responses', 'local_analysis_dashboard'),
            ];
        }

        // Build diverging datasets.
        // Use the option labels from the first item as the canonical set
        // (most feedback forms have consistent options across questions).
        $canonicaloptions = $itemdata[0]['options'];
        $optioncount = count($canonicaloptions);

        // Split: first half = negative (left), second half = positive (right).
        // For odd counts, middle option is split.
        $midpoint = (int) floor($optioncount / 2);
        $hasmiddle = ($optioncount % 2 === 1);

        // Build labels (Y-axis = question names).
        $labels = [];
        foreach ($itemdata as $qdata) {
            $labels[] = $qdata['label'];
        }

        // Color scales: reds/oranges for negative side, blues/greens for positive.
        $negativecolors = ['#c0392b', '#e74c3c', '#e67e73', '#f0a8a0'];
        $neutralcolor = '#bdc3c7';
        $positivecolors = ['#73c0de', '#5dade2', '#2e86c1', '#2471a3'];

        $datasets = [];

        for ($optidx = 0; $optidx < $optioncount; $optidx++) {
            $isNegative = $optidx < $midpoint;
            $isMiddle = $hasmiddle && $optidx === $midpoint;
            $isPositive = $optidx > $midpoint || (!$hasmiddle && $optidx >= $midpoint);

            $data = [];
            foreach ($itemdata as $qdata) {
                $pct = $qdata['percentages'][$optidx] ?? 0;
                if ($isNegative) {
                    $data[] = -$pct; // Negative = left side.
                } else if ($isMiddle) {
                    // Split middle: show as negative (centered).
                    $data[] = -$pct;
                } else {
                    $data[] = $pct; // Positive = right side.
                }
            }

            // Pick color.
            if ($isMiddle) {
                $color = $neutralcolor;
            } else if ($isNegative) {
                $coloridx = $optidx % count($negativecolors);
                $color = $negativecolors[$coloridx];
            } else {
                $posidx = $optidx - $midpoint - ($hasmiddle ? 1 : 0);
                $coloridx = $posidx % count($positivecolors);
                $color = $positivecolors[$coloridx];
            }

            $optlabel = $canonicaloptions[$optidx] ?? ('Option ' . ($optidx + 1));

            $datasets[] = [
                'label' => $optlabel,
                'data' => $data,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'borderWidth' => 0,
                'borderSkipped' => false,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'diverging' => true,
            'comments' => $comments,
        ];
    }

    /**
     * Parse the options from a feedback item's presentation field.
     *
     * @param \stdClass $item The feedback_item record.
     * @return array|null Array with 'options' key, or null on failure.
     */
    private function parse_item_options(\stdClass $item): ?array {
        $presentation = $item->presentation;

        // Extract the options portion (after type separator).
        $parts = explode(self::TYPE_SEP, $presentation);
        $optionstring = count($parts) > 1 ? $parts[1] : $parts[0];

        // Remove adjustment separator.
        $adjparts = explode(self::ADJUST_SEP, $optionstring);
        $optionstring = $adjparts[0];

        $lines = explode(self::LINE_SEP, $optionstring);
        if (empty($lines)) {
            return null;
        }

        $options = [];
        if ($item->typ === 'multichoicerated') {
            foreach ($lines as $line) {
                $lineparts = explode(self::VALUE_SEP, $line, 2);
                if (count($lineparts) === 2) {
                    $options[] = trim(strip_tags($lineparts[1]));
                } else {
                    $options[] = trim(strip_tags($line));
                }
            }
        } else {
            // multichoice.
            foreach ($lines as $line) {
                $options[] = trim(strip_tags($line));
            }
        }

        return empty($options) ? null : ['options' => $options];
    }

    /**
     * Count responses per option for a feedback item.
     *
     * @param int $itemid The feedback_item ID.
     * @param int $optioncount The number of options.
     * @param \moodle_database $DB Database instance.
     * @return array Indexed array of counts (0-indexed, matching option index).
     */
    private function count_responses(int $itemid, int $optioncount, $DB): array {
        $counts = array_fill(0, $optioncount, 0);

        $sql = "SELECT fv.id, fv.value
                  FROM {feedback_value} fv
                  JOIN {feedback_completed} fc ON fc.id = fv.completed
                 WHERE fv.item = :itemid
                   AND fv.value != ''
                   AND fv.value != '0'";
        $values = $DB->get_records_sql($sql, ['itemid' => $itemid]);

        foreach ($values as $val) {
            // Multichoice can have pipe-separated values for checkboxes.
            $selectedindices = explode(self::LINE_SEP, $val->value);
            foreach ($selectedindices as $selidx) {
                $idx = (int) $selidx - 1; // Convert 1-indexed to 0-indexed.
                if ($idx >= 0 && $idx < $optioncount) {
                    $counts[$idx]++;
                }
            }
        }

        return $counts;
    }

    /**
     * Get text responses for a textarea/textfield item.
     *
     * @param int $itemid The feedback_item ID.
     * @param \moodle_database $DB Database instance.
     * @return array Array of response strings.
     */
    private function get_text_responses(int $itemid, $DB): array {
        $sql = "SELECT fv.id, fv.value
                  FROM {feedback_value} fv
                  JOIN {feedback_completed} fc ON fc.id = fv.completed
                 WHERE fv.item = :itemid
                   AND fv.value != ''
              ORDER BY fc.timemodified DESC";
        $values = $DB->get_records_sql($sql, ['itemid' => $itemid]);

        $responses = [];
        foreach ($values as $val) {
            $clean = trim($val->value);
            if ($clean !== '') {
                $responses[] = format_string($clean);
            }
        }

        return $responses;
    }
}

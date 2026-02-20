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
 * Quiz Analytics widget.
 *
 * Displays per-quiz statistics: average score, pass rate, attempt counts.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_analytics extends base_widget {

    public function get_name(): string {
        return 'widget_quiz_analytics';
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
        return 'quiz_analytics';
    }

    public function get_data(array $params = []): array {
        global $DB;

        $courseid = $params['courseid'] ?? 0;
        if (empty($courseid)) {
            return ['labels' => [], 'datasets' => []];
        }

        // Get all quizzes in the course.
        $quizzes = $DB->get_records('quiz', ['course' => $courseid], 'id', 'id, name, sumgrades, grade');

        if (empty($quizzes)) {
            return [
                'labels' => [],
                'datasets' => [],
                'message' => get_string('no_quizzes', 'local_analysis_dashboard'),
            ];
        }

        $labels = [];
        $avgscore = [];
        $passrate = [];
        $totalattempts = 0;
        $overallavg = 0;
        $quizcount = 0;

        foreach ($quizzes as $quiz) {
            // Get finished attempts.
            $sql = "SELECT id, sumgrades
                      FROM {quiz_attempts}
                     WHERE quiz = :quizid
                       AND state = 'finished'
                       AND sumgrades IS NOT NULL";
            $attempts = $DB->get_records_sql($sql, ['quizid' => $quiz->id]);

            $attemptcount = count($attempts);
            $totalattempts += $attemptcount;

            if ($attemptcount === 0) {
                $labels[] = mb_substr($quiz->name, 0, 30);
                $avgscore[] = 0;
                $passrate[] = 0;
                continue;
            }

            // Calculate average score as percentage.
            $max = max((float) $quiz->sumgrades, 0.01);
            $sumscores = 0;
            $passed = 0;
            $passthreshold = (float) $quiz->grade * 0.5; // 50% of max grade to pass.

            foreach ($attempts as $attempt) {
                $pct = ((float) $attempt->sumgrades / $max) * 100;
                $sumscores += $pct;

                // Check pass using the quiz grade (rescaled).
                $rescaledgrade = ((float) $attempt->sumgrades / $max) * (float) $quiz->grade;
                if ($rescaledgrade >= $passthreshold) {
                    $passed++;
                }
            }

            $avg = round($sumscores / $attemptcount, 1);
            $labels[] = mb_substr($quiz->name, 0, 30);
            $avgscore[] = $avg;
            $passrate[] = round(($passed / $attemptcount) * 100, 1);

            $overallavg += $avg;
            $quizcount++;
        }

        $overallavg = $quizcount > 0 ? round($overallavg / $quizcount, 1) : 0;

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => get_string('average_score', 'local_analysis_dashboard'),
                    'data' => $avgscore,
                    'backgroundColor' => 'rgba(102, 126, 234, 0.7)',
                    'borderColor' => 'rgba(102, 126, 234, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => get_string('pass_rate', 'local_analysis_dashboard'),
                    'data' => $passrate,
                    'backgroundColor' => 'rgba(40, 167, 69, 0.7)',
                    'borderColor' => 'rgba(40, 167, 69, 1)',
                    'borderWidth' => 1,
                ],
            ],
            'summary' => [
                'total_quizzes' => count($quizzes),
                'total_attempts' => $totalattempts,
                'overall_avg' => $overallavg,
            ],
        ];
    }
}

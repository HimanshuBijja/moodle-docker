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

namespace local_analysis_dashboard\output;

use local_analysis_dashboard\local\widget_registry;

/**
 * Mobile output handler for the Analysis Dashboard.
 *
 * Renders counter-type widgets for the Moodle Mobile app.
 * Chart widgets are excluded as Chart.js is not available
 * in the mobile web view.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {

    /**
     * Returns the mobile dashboard view.
     *
     * @param array $args Arguments from mobile app.
     * @return array Mobile view response with templates and data.
     */
    public static function mobile_dashboard_view(array $args): array {
        global $OUTPUT;

        try {
            widget_registry::init();

            // Get counter-type widgets only (mobile-friendly).
            $widgetconfig = widget_registry::get_config_for_current_user(CONTEXT_SYSTEM);
            $counters = [];

            foreach ($widgetconfig as $wc) {
                if ($wc['type'] !== 'counter') {
                    continue;
                }

                $widget = widget_registry::get($wc['id']);
                $data = $widget->get_data();

                if (!empty($data) && !empty($data['items'])) {
                    $counters[] = [
                        'name' => $wc['name'],
                        'items' => $data['items'],
                    ];
                }
            }

            $templatedata = [
                'has_counters' => !empty($counters),
                'counters' => $counters,
                'pagetitle' => get_string('dashboard', 'local_analysis_dashboard'),
            ];

            return [
                'templates' => [
                    [
                        'id' => 'main',
                        'html' => $OUTPUT->render_from_template(
                            'local_analysis_dashboard/mobile_dashboard',
                            $templatedata
                        ),
                    ],
                ],
                'javascript' => '',
                'otherdata' => json_encode($templatedata),
                'files' => [],
            ];
        } catch (\Exception $e) {
            return [
                'templates' => [
                    [
                        'id' => 'main',
                        'html' => '<ion-card><ion-card-content>' .
                                  '<p>' . get_string('widget_error', 'local_analysis_dashboard') . '</p>' .
                                  '</ion-card-content></ion-card>',
                    ],
                ],
                'javascript' => '',
                'otherdata' => '{}',
                'files' => [],
            ];
        }
    }
}

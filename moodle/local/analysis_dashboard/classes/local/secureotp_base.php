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

namespace local_analysis_dashboard\local;

/**
 * Abstract base class for SecureOTP widgets.
 *
 * Provides graceful degradation when auth_secureotp is not installed.
 * Subclasses implement get_secureotp_data() instead of get_data().
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class secureotp_base extends base_widget {

    /**
     * Check if the auth_secureotp plugin is installed.
     *
     * @return bool True if the auth_secureotp tables exist.
     */
    public static function is_secureotp_installed(): bool {
        global $DB;
        $dbman = $DB->get_manager();
        return $dbman->table_exists('auth_secureotp_security');
    }

    /**
     * Default context: system-level.
     *
     * @return array Array of CONTEXT_* constants.
     */
    public function get_supported_context_levels(): array {
        return [CONTEXT_SYSTEM];
    }

    /**
     * Default capability: derived from widget name for per-widget control.
     *
     * Subclasses can override this if they need a different capability.
     *
     * @return string Capability string.
     */
    public function get_required_capability(): string {
        // Derive capability from widget name: widget_secureotp_xxx -> local/analysis_dashboard:widget_secureotp_xxx.
        $widgetname = $this->get_name();
        return 'local/analysis_dashboard:' . $widgetname;
    }

    /**
     * Get data with graceful degradation.
     *
     * If auth_secureotp is not installed, returns a message.
     * Otherwise, delegates to get_secureotp_data().
     *
     * @param array $params Optional parameters.
     * @return array Widget data.
     */
    public function get_data(array $params = []): array {
        if (!self::is_secureotp_installed()) {
            return [
                'message' => get_string('secureotp_not_installed', 'local_analysis_dashboard'),
            ];
        }
        return $this->get_secureotp_data($params);
    }

    /**
     * Check availability — widget is only available when SecureOTP is installed.
     *
     * @return bool True if SecureOTP is installed.
     */
    public function is_available(): bool {
        return self::is_secureotp_installed();
    }

    /**
     * Get SecureOTP-specific data.
     *
     * Subclasses implement this method to query auth_secureotp_* tables.
     *
     * @param array $params Optional parameters.
     * @return array Widget data.
     */
    abstract protected function get_secureotp_data(array $params = []): array;
}

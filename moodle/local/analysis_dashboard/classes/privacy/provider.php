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

namespace local_analysis_dashboard\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

/**
 * Privacy Subsystem implementation for local_analysis_dashboard.
 *
 * This plugin stores no user data in its own tables. It reads from
 * Moodle core tables and auth_secureotp tables. The only user-keyed
 * data is transient MUC cache entries.
 *
 * @package    local_analysis_dashboard
 * @copyright  2026 Analysis Dashboard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider {

    /**
     * Describe the type of data stored by this plugin.
     *
     * @param collection $collection The privacy metadata collection.
     * @return collection Updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        // The plugin uses MUC caches with user-keyed entries.
        $collection->add_subsystem_link('core_cache', [], 'privacy:metadata:cache');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information.
     *
     * This plugin does not store any user data persistently.
     *
     * @param int $userid The user to search.
     * @return contextlist Empty context list.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        return new contextlist();
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users.
     */
    public static function get_users_in_context(userlist $userlist): void {
        // No persistent user data stored.
    }

    /**
     * Export all user data for the specified approved contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export data for.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        // No persistent user data to export — caches are transient.
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        // Purge all dashboard caches.
        \cache::make('local_analysis_dashboard', 'sitestats')->purge();
        \cache::make('local_analysis_dashboard', 'coursestats')->purge();
        \cache::make('local_analysis_dashboard', 'userstats')->purge();
    }

    /**
     * Delete all user data for the specified user.
     *
     * @param approved_contextlist $contextlist The approved contexts and user.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        // We cannot selectively purge MUC cache entries by user, so purge user stats.
        \cache::make('local_analysis_dashboard', 'userstats')->purge();
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        // Purge user-level caches.
        \cache::make('local_analysis_dashboard', 'userstats')->purge();
    }
}

<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Extend site navigation.
 *
 * @param global_navigation $navigation
 */
function local_analysis_dashboard_extend_navigation(global_navigation $navigation) {
    if (has_capability('local/analysis_dashboard:view', context_system::instance())) {
        $node = navigation_node::create(
            get_string('nav_analysis_dashboard', 'local_analysis_dashboard'),
            new moodle_url('/local/analysis_dashboard/index.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'local_analysis_dashboard'
        );
        $navigation->add_node($node);
    }
}

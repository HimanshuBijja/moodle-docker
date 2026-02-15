<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_analysis_dashboard', get_string('settings', 'local_analysis_dashboard'));

    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configselect(
        'local_analysis_dashboard/district_field',
        get_string('district_field', 'local_analysis_dashboard'),
        get_string('district_field_desc', 'local_analysis_dashboard'),
        'cp_sp_office',
        [
            'city' => 'City',
            'cp_sp_office' => 'CP/SP Office',
            'working_location' => 'Working Location',
            'education_category' => 'Education Category'
        ]
    ));

    $settings->add(new admin_setting_configduration(
        'local_analysis_dashboard/cache_ttl',
        get_string('cache_ttl', 'local_analysis_dashboard'),
        get_string('cache_ttl_desc', 'local_analysis_dashboard'),
        86400
    ));
}

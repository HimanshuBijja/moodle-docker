<?php
require_once('../../config.php');

// 1. Check Login
require_login();

// 2. Set Context
$context = context_system::instance();
$PAGE->set_context($context);

// 3. Check Capability
require_capability('local/analysis_dashboard:view', $context);

// 4. Page Setup
$url = new moodle_url('/local/analysis_dashboard/index.php');
$PAGE->set_url($url);
$PAGE->set_title(get_string('pluginname', 'local_analysis_dashboard'));
$PAGE->set_heading(get_string('pluginname', 'local_analysis_dashboard'));

// 5. Load JS
$PAGE->requires->js_call_amd('local_analysis_dashboard/dashboard', 'init', ['#district-chart']);

// 6. Output
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_analysis_dashboard/dashboard', []);
echo $OUTPUT->footer();

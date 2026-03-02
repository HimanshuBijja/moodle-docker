# Codebase Concerns

**Analysis Date:** 2026-03-02

## Tech Debt

**Logstore Queries in Widgets:**
- Issue: Several widgets query the `logstore_standard_log` table directly and process results in PHP.
- Files: `classes/local/widgets/site_visits.php`, `classes/local/widgets/activity_heatmap.php`
- Impact: On sites with large log tables (millions of rows), these queries will cause significant database load and PHP memory exhaustion.
- Fix approach: Use aggregated tables or Moodle's `stats` API. Implement incremental aggregation instead of full table scans.

**Direct Dependency on auth_secureotp Tables:**
- Issue: SecureOTP widgets query `auth_secureotp_*` tables directly without an abstraction layer or checking if the plugin is actually compatible.
- Files: `classes/local/secureotp_base.php`, `classes/local/widgets/secureotp_security_summary.php`
- Impact: Breaks if the `auth_secureotp` schema changes.
- Fix approach: Define an interface or service in the SecureOTP plugin that this dashboard can consume.

## Performance Bottlenecks

**Disk Usage Calculation:**
- Problem: Calling `get_directory_size($CFG->dataroot)` is extremely slow and resource-intensive on production systems.
- Files: `classes/task/calculate_disk_usage.php`
- Cause: Recursively traversing the entire `moodledata` directory can take minutes or hours and cause high I/O.
- Improvement path: Use `du` command via `exec` if available, or rely on file system metadata if possible. Alternatively, only calculate size for specific subdirectories.

**Database Size Query:**
- Problem: Querying `information_schema.TABLES` can be slow and may require specific permissions.
- Files: `classes/task/calculate_disk_usage.php`
- Cause: Metadata queries in MySQL/MariaDB can block or be slow on large instances.
- Improvement path: Cache this value heavily or use a more efficient way to estimate size.

## Scalability Limits

**Course Stats Aggregation:**
- Current capacity: Loops through all courses with activity in 30 days.
- Limit: On sites with tens of thousands of active courses, this task will exceed the PHP execution limit or the cron window.
- Scaling path: Implement a queue system or process courses in batches across multiple task runs. Use a "dirty" flag to only process courses that haven't been aggregated since their last activity.

## Security Considerations

**External API Parameter Handling:**
- Risk: `get_widget_data` accepts a `params` string which is JSON-decoded and used to set context and filter data.
- Files: `classes/external/get_widget_data.php`
- Current mitigation: Basic `validate_parameters` and `require_capability` checks.
- Recommendations: Implement stricter schema validation for the `params` JSON to prevent unexpected input from influencing the logic.

## Missing Critical Features

**Missing `get_directory_size` Definition:**
- Problem: The function `get_directory_size` is called but not defined within the plugin and is not a standard Moodle global function.
- Files: `classes/task/calculate_disk_usage.php`
- Blocks: The `calculate_disk_usage` task will likely fail with a fatal error.
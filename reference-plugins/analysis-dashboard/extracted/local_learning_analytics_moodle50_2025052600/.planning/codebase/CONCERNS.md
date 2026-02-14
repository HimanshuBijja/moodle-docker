# Codebase Concerns

**Analysis Date:** 2025-01-24

## Tech Debt

**Hardcoded Report Logic:**
- Issue: The router has hardcoded logic for specific reports.
- Files: `classes/router.php`
- Impact: New main reports might require manual updates to the router logic instead of being fully dynamic.
- Fix approach: Implement a registration system or configuration setting for the default/main report.

**Missing Automated Tests:**
- Issue: No PHPUnit or Behat tests found.
- Files: Entire plugin.
- Impact: High risk of regression when updating Moodle versions or refactoring queries.
- Fix approach: Add PHPUnit tests for `query_helper` classes and Behat tests for dashboard accessibility.

## Known Bugs

**None reported:**
- No bug tracking files or issue lists found in the repository except for a generic `TODO.md`.

## Security Considerations

**Student Data Exposure:**
- Risk: Capability `local/learning_analytics:view_statistics` allows students to view statistics by default.
- Files: `db/access.php`
- Current mitigation: Basic capability check.
- Recommendations: Ensure that student-viewable reports only show their own data or aggregate data that doesn't violate privacy policies.

**Privacy Thresholds:**
- Risk: Small data sets might inadvertently reveal individual student behavior.
- Files: `classes/settings.php`, `reports/activities/classes/query_helper.php`
- Current mitigation: A `dataprivacy_threshold` setting is used in queries (e.g., `HAVING count(*) >= ?`).

## Performance Bottlenecks

**Large Log Queries:**
- Problem: Queries directly join against the logstore table, which can grow extremely large.
- Files: `reports/*/classes/query_helper.php`
- Cause: `logstore_lanalytics_log` table may contain millions of rows.
- Improvement path: Implement materialized views or aggregate tables for frequently accessed analytics data.

## Fragile Areas

**Router URI Parsing:**
- Files: `classes/router.php`
- Why fragile: Uses regex and `$_SERVER['REQUEST_URI']` to determine routing, which might behave differently depending on Moodle's URL configuration (slashargs).
- Safe modification: Use Moodle's `$PAGE->url` or `get_params()` where possible instead of raw URI parsing.

## Scaling Limits

**Database Load:**
- Current capacity: Dependent on Moodle DB performance.
- Limit: Complex joins on log tables will eventually time out or slow down the page for large courses.

## Dependencies at Risk

**`logstore_lanalytics`:**
- Risk: This plugin is entirely dependent on a specific logstore implementation.
- Impact: If `logstore_lanalytics` is disabled or fails to log data, the dashboard remains empty but might not show a clear error to the user.
- Migration plan: Abstract the data source to support Moodle's standard logstore if needed.

## Missing Critical Features

**Data Export:**
- Problem: No obvious way to export the visualized data to CSV/Excel.
- Blocks: Users cannot perform further analysis in external tools.

---

*Concerns audit: 2025-01-24*

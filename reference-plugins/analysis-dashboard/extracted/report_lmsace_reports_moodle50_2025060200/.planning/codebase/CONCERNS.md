# Codebase Concerns

**Analysis Date:** 2025-02-15

## Tech Debt

**Duplicated Class Structures:**
- Issue: Identical or near-identical widget and table classes exist in both `classes/` and `classes/local/`.
- Files: `classes/widgets/*`, `classes/local/widgets/*`, `classes/table/*`, `classes/local/table/*`
- Impact: Maintenance overhead, confusion about which file is the source of truth, risk of divergence.
- Fix approach: Consolidate to `local/` namespace and remove duplicates.

**God Object (report_helper):**
- Issue: `report_helper` class contains diverse responsibilities: SQL aggregation, disk space calculation, JS data loading, and capability checks.
- Files: `classes/report_helper.php`
- Impact: Low cohesion, high coupling, difficult to unit test.
- Fix approach: Split into specialized services (e.g., `ReportingService`, `EnvironmentService`, `SecurityHelper`).

## Security Considerations

**Direct SQL Queries:**
- Issue: Many complex SQL queries are built manually.
- Files: `classes/report_helper.php`, `classes/local/widgets/*`
- Risk: Potential for SQL injection if parameters are not handled correctly (though `$DB->get_records_sql` is used).
- Current mitigation: Use of placeholder parameters.
- Recommendations: Review all manual SQL for complex concatenation or dynamic table names.

**Capability Checks:**
- Issue: Relies heavily on `require_capability()` but some helper methods don't check permissions internally.
- Files: `classes/report_helper.php`
- Risk: Potential data leakage if helper methods are called from other contexts without proper checks.
- Recommendations: Implement defensive permission checks within data-fetching methods.

## Performance Bottlenecks

**Disk Space Calculation:**
- Issue: `get_foldersize` uses `RecursiveIteratorIterator` to traverse the entire `dirroot` and `dataroot`.
- Files: `classes/report_helper.php`
- Cause: Synchronous file system traversal.
- Impact: Significant page load delay or timeout on large Moodle installations.
- Improvement path: Ensure this is ONLY run via a scheduled task or background process, never on a web request. The current caching helps but the first hit or cache miss is dangerous.

**Unoptimized SQL for Logs:**
- Issue: Querying `logstore_standard_log` without strict indexes can be very slow.
- Files: `classes/report_helper.php` (method `get_site_visits`)
- Cause: Logs table can contain millions of rows.
- Improvement path: Optimize query and consider using a summary table or analytics API.

## Fragile Areas

**Widget Registration:**
- Issue: Widgets are hardcoded in `report_helper::get_default_widgets()`.
- Files: `classes/report_helper.php`
- Why fragile: Adding a new widget requires modifying the core helper class.
- Safe modification: Move to a plugin-based or configuration-based registration system.

## Scaling Limits

**Chart Rendering:**
- Issue: Rendering 20+ charts on a single page using Chart.js.
- Limit: Browser memory and CPU usage on client-side.
- Scaling path: Lazy load charts as they enter the viewport.

## Missing Critical Features

**Unit Tests:**
- Problem: Complete lack of PHPUnit tests for the extensive logic in `report_helper`.
- Blocks: Refactoring and ensuring data accuracy.

**Export Functionality:**
- Problem: Reports are visual but lack a standard "Download as CSV/PDF" feature for the aggregated data.

---

*Concerns audit: 2025-02-15*

# Codebase Concerns

**Analysis Date:** 2025-02-15

## Tech Debt

**[Performance] Synchronous Heavy Calculations:**
- Issue: `edudashboard_renderable::export_for_template` calls `site_access_data::categoria_fulldata()` directly.
- Files: `classes/output/edudashboard_renderable.php`
- Impact: Significant page load delays. This method iterates through all categories, courses, and user grades. It should be handled exclusively by scheduled tasks or cached properly.
- Fix approach: Remove the direct call and rely on cached data updated by the scheduled task.

**[Efficiency] Heavy Disk Usage Calculation:**
- Issue: `util::getsystemfilessize` iterates through every course and every module context to sum file sizes.
- Files: `classes/extra/util.php`
- Impact: Severe performance bottleneck on large Moodle sites with many courses/modules.
- Fix approach: Use a more efficient SQL query to aggregate sizes by course or category in a single pass, or run this calculation less frequently in a task.

**[Dependency Management] Bundled Third-Party Libraries:**
- Issue: Bundles multiple charting libraries (ApexCharts, Highcharts, Chart.js) and Bootstrap Grid, leading to asset bloat.
- Files: `externaljs/build/`
- Impact: Increased page weight and potential conflicts.
- Fix approach: Consolidate to a single charting library (e.g., Moodle's native Chart.js) and use Moodle's core Bootstrap.

**[Logic] Manual Date Formatting:**
- Issue: `util::dateconverter` manually formats dates instead of using Moodle's localization-aware `userdate()` function.
- Files: `classes/extra/util.php`
- Impact: Inconsistent date formatting and lack of localization support.
- Fix approach: Replace `dateconverter` with `userdate()`.

## Performance Bottlenecks

**[Scheduled Tasks] Aggressive Schedule:**
- Problem: Tasks for disk usage and site access are scheduled to run every minute (`* * * * *`).
- Files: `db/tasks.php`
- Cause: Unnecessarily frequent updates for data that doesn't change that fast.
- Improvement path: Change schedule to hourly or daily.

**[Logic] site_access_data::categoria_fulldata:**
- Problem: Deep nesting of loops (Categories -> Courses -> Users) with individual grade/completion checks.
- Files: `classes/task/site_access_data.php`
- Cause: Inefficient data retrieval.
- Improvement path: Use bulk SQL queries to retrieve grades and completions for multiple users/courses at once.

## Fragile Areas

**[Integration] Totara Compatibility:**
- Files: `classes/extra/util.php`, `classes/extra/course_report.php`
- Why fragile: Relies on `file_exists($CFG->dirroot . "/totara")` which might not be the most robust way to detect the platform.
- Safe modification: Use Moodle's version/component checks or specific Totara API availability checks.

## Missing Critical Features

**[Testing] Lack of Automated Tests:**
- Problem: No PHPUnit or Behat tests found.
- Blocks: Ensuring reliability during upgrades or modifications.
- Priority: High.

**[GDPR] Privacy Provider Scope:**
- Problem: The privacy provider exports standard Moodle logs (`logstore_standard_log`).
- Files: `classes/privacy/provider.php`
- Risk: Potential duplication of data in privacy exports and exporting data not directly owned by the plugin.
- Priority: Medium.

## Test Coverage Gaps

**[Untested Area] All Logic:**
- What's not tested: Data aggregation, report generation, utility functions.
- Files: `classes/extra/*.php`, `classes/task/*.php`.
- Risk: Regressions in statistics calculation.
- Priority: High.

---

*Concerns audit: 2025-02-15*

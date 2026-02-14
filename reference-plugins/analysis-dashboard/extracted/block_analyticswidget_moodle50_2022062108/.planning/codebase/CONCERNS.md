# Codebase Concerns

**Analysis Date:** 2025-01-24

## Tech Debt

**Mobile Output Copy-Paste:**
- Issue: `classes/output/mobile.php` contains docblocks and possibly logic leftovers from a plugin named `block_deft`.
- Files: `classes/output/mobile.php`
- Impact: Confusing documentation and potential maintenance issues if the logic is not fully adapted.
- Fix approach: Clean up docblocks and verify that all `use` statements are relevant to `block_analyticswidget`.

**Dynamic Loading via Glob:**
- Issue: Use of `glob` and `require_once` for widget discovery instead of a more modern service provider or class-map approach.
- Files: `classes/widget.php`
- Impact: Performance (filesystem hits) and lack of formal dependency injection.
- Fix approach: Implement a registry or use Moodle's autoloading capabilities more effectively.

## Known Bugs

**Role Configuration Dependency:**
- Symptoms: Block might be empty or crash if student/teacher roles are not correctly configured in plugin settings.
- Files: `classes/widget.php`, `settings.php`
- Trigger: Installing the plugin without configuring role IDs.
- Workaround: Ensure roles are selected in site administration immediately after installation.

## Security Considerations

**Capabilities:**
- Risk: Users might see analytics they shouldn't if capabilities are not strictly enforced.
- Files: `db/access.php`, `block_analyticswidget.php`
- Current mitigation: Standard Moodle block access checks.
- Recommendations: Add granular checks for "view teacher analytics" vs "view my analytics".

## Performance Bottlenecks

**Uncached Database Queries:**
- Problem: Complex enrolment and completion queries run on every dashboard load if cache is stale.
- Files: `classes/widgets/my/course_stats.php`
- Cause: Calculating completion for all active courses.
- Improvement path: Ensure the `awstat` cache is utilized effectively for these expensive calculations.

## Fragile Areas

**Chart.js Configuration in Templates:**
- Files: `templates/my/course_stats.mustache`
- Why fragile: JS logic is embedded directly in Mustache files, making it hard to test and prone to syntax errors during refactoring.
- Safe modification: Move JS logic to AMD modules (e.g., `amd/src/charts.js`).
- Test coverage: Zero.

## Dependencies at Risk

**core/chartjs:**
- Risk: Changes in Moodle's Chart.js wrapper in future versions (e.g., Moodle 5.0+) might break custom chart configurations.
- Impact: Charts fail to render on the dashboard.
- Migration plan: Monitor Moodle core updates regarding Chart.js.

## Missing Critical Features

**Actual Test Suite:**
- Problem: CI is configured to run PHPUnit and Behat, but no actual tests exist in the plugin.
- Blocks: Validation of logic during upgrades.
- Priority: High.

## Test Coverage Gaps

**Untested Widget Logic:**
- What's not tested: Enrolment counts, completion calculations.
- Files: `classes/widgets/my/course_stats.php`, `classes/widgets/teacher/activity_stats.php`
- Risk: Incorrect stats shown to users after Moodle core updates.
- Priority: High.

---

*Concerns audit: 2025-01-24*

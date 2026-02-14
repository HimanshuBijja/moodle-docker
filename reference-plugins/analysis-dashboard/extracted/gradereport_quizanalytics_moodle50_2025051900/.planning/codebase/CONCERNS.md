# Codebase Concerns

**Analysis Date:** 2025-02-15

## Tech Debt

**Monolithic External Function:**
- Issue: `quizanalytics_analytic` in `externallib.php` is extremely large and handles multiple independent analytics calculations.
- Files: `quizanalytics/externallib.php`
- Impact: Hard to maintain, debug, or extend. Difficult to optimize individual calculations.
- Fix approach: Refactor the large function into smaller, specialized classes/methods within a `classes/` directory.

**Direct JSON Return from Web Service:**
- Issue: The external function returns a JSON string instead of a structured array/object as per Moodle Web Service best practices.
- Files: `quizanalytics/externallib.php`
- Impact: Breaks Moodle's built-in Web Service documentation and type checking. Makes it harder for other clients (e.g., Mobile App) to consume.
- Fix approach: Update `quizanalytics_analytic_returns()` to define the structure and return an array.

**Inconsistent JS Loading:**
- Issue: `Chart.js` is loaded via `$PAGE->requires->js()` while other logic uses AMD modules.
- Files: `quizanalytics/index.php`, `quizanalytics/amd/src/analytic.js`
- Impact: Potential race conditions or dependency issues. Not following modern Moodle AMD standards.
- Fix approach: Package `Chart.js` as an AMD module or use a version already provided by Moodle core if available.

## Known Bugs

**Nested Functions in PHP:**
- Symptoms: `random_color_part` and `random_color` are defined inside `quizanalytics_analytic`.
- Files: `quizanalytics/externallib.php`
- Trigger: If the function is called multiple times in the same request, it will throw a "Cannot redeclare function" error.
- Workaround: Move these to private methods or a utility class.

## Security Considerations

**Raw SQL Queries:**
- Risk: Potential for SQL injection if parameters are not handled correctly.
- Files: `quizanalytics/externallib.php`
- Current mitigation: Uses `$DB->get_records_sql` with placeholders.
- Recommendations: Ensure all dynamic inputs are strictly validated.

## Performance Bottlenecks

**Complex SQL on Large Data:**
- Problem: The SQL queries join multiple large tables (`quiz_attempts`, `question_attempts`, etc.) and perform aggregations on the fly.
- Files: `quizanalytics/externallib.php`
- Cause: Calculating analytics for every quiz attempt on every page load.
- Improvement path: Implement caching for analytics results (MUC).

## Fragile Areas

**Frontend Selectors:**
- Files: `quizanalytics/amd/src/analytic.js`
- Why fragile: Relies on specific DOM structures (e.g., `parentNode.parentNode.querySelector("#userSelect")`).
- Safe modification: Use specific data attributes or unique IDs for stable selectors.

## Test Coverage Gaps

**Missing Automated Tests:**
- What's not tested: Entire plugin functionality.
- Files: All files.
- Risk: Regressions are likely when updating for new Moodle versions.
- Priority: High.

---

*Concerns audit: 2025-02-15*

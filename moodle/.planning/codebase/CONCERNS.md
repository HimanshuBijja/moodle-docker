# Concerns

**Analysis Date:** 2024-05-24

## Technical Debt & Maintainability
- **Ubiquitous "Hacks"**: The codebase contains hundreds of comments explicitly labeling code as "nasty hack," "bloody hack," or "horrible hack." These are often used to bypass core APIs or handle legacy constraints.
  - Examples: `repository/lib.php`, `user/lib.php`, `mod/quiz/lib.php`.
- **Legacy Deprecations**: Significant technical debt is tracked via "MDL" Jira references in `TODO` comments, many of which date back several years and involve pending deletions or refactors (e.g., `webservice/lib.php`, `user/classes/external/user_summary_exporter.php`).
- **Global Variable Reliance**: Frequent "hacky" use of globals like `global $PAGE`, `global $SESSION`, and `global $COURSE` in areas where they shouldn't ideally be used, complicating testing and state management.
  - Examples: `user/filters/user_filter_forms.php`, `theme/boost/tests/boostnavbar_test.php`.

## Security Risks
- **Direct Superglobal Access**: While Moodle prefers `optional_param()` and `required_param()`, there are instances of direct `$_GET`, `$_POST`, and `$_REQUEST` access, particularly in Web Services and Portfolio plugins, which could bypass standard sanitization if not handled carefully.
  - Examples: `webservice/rest/locallib.php`, `portfolio/add.php`.
- **"Evil Script" Markers**: Comments in tests (e.g., `question/tests/importexport_test.php`) show awareness of XSS risks but also highlight fragile areas where "evil" inputs are manually cleaned.

## Performance Bottlenecks
- **Query & Preloading Hacks**: Evidence of performance-related shortcuts, such as preloading user contexts manually to avoid N+1 issues in ways that are described as "hacks" (`user/classes/table/participants_search.php`).
- **Complex Modules**: Large local libraries in modules like `assign`, `forum`, and `quiz` suggest high cyclomatic complexity and maintenance risk.

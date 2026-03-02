# Codebase Concerns

**Analysis Date:** 2025-01-24

## Tech Debt

**Legacy Procedural Entry Points:**
- Issue: Many core user pages remain procedural with heavy use of `require_once` and global variables, making them difficult to unit test and maintain.
- Files: `user/view.php`, `user/profile.php`, `user/index.php`, `user/edit.php`, `user/editadvanced.php`
- Impact: Increased maintenance cost, harder to implement modern architectural patterns (like DI), and difficulty in writing isolated tests.
- Fix approach: Gradually migrate logic into classes and use the Moodle output API more consistently.

**Large Monolithic Files:**
- Issue: Several files have exceeded 1000 lines, combining multiple responsibilities.
- Files: `user/externallib.php` (2067 lines), `user/lib.php` (1521 lines), `user/selector/lib.php` (1029 lines), `user/classes/table/participants_search.php` (1125 lines)
- Impact: High cognitive load for developers, increased risk of merge conflicts, and harder to navigate.
- Fix approach: Refactor into smaller, more focused classes and traits.

**Incomplete Custom Field Support:**
- Issue: The user summary exporter does not support custom user profile fields.
- Files: `user/classes/external/user_summary_exporter.php` (Line 51)
- Impact: Consumers of this exporter (like mobile app or modern UI components) miss important user data.
- Fix approach: Implement custom field support as tracked in MDL-70456.

**Pending Deprecations:**
- Issue: Legacy permission checks like `canviewuseremail` are still in use despite being slated for removal.
- Files: `user/lib.php` (Line 527)
- Impact: Accumulation of legacy code paths that should have been removed (MDL-37479).
- Fix approach: Complete the deprecation and removal process.

## Security Considerations

**Complex Visibility Logic:**
- Risk: The logic for determining which user fields are visible to whom is extremely complex and spread across multiple conditions.
- Files: `user/lib.php` (Function `user_get_user_details`)
- Impact: Potential for unintentional data exposure if a condition is incorrectly implemented or missed during a refactor.
- Current mitigation: Capability checks (`has_capability`) and `validate_context` are used throughout.
- Recommendations: Centralize visibility rules into a dedicated service or policy class that can be thoroughly unit tested.

**Parental Access "Hack":**
- Risk: An "ugly hack" is used to bypass normal enrollment checks for "parents" viewing child profiles.
- Files: `user/view.php` (Line 99)
- Impact: Fragile security logic that relies on "guesses" about intent, potentially leading to unauthorized access if parent-child relationships are misconfigured.
- Current mitigation: Basic check for `role_assignments` and `moodle/user:viewdetails`.
- Recommendations: Formalize the parental access check into the core permission system rather than a page-specific hack.

## Performance Bottlenecks

**Complex Participant Searching:**
- Problem: Generating SQL for participants with multiple filters and large datasets can be slow.
- Files: `user/classes/table/participants_search.php`
- Cause: Dynamically built complex queries with multiple joins and subqueries.
- Improvement path: Optimize the generated SQL, ensure proper indexing on frequently filtered fields, and consider caching for common searches.

## Fragile Areas

**User Editing Forms:**
- Files: `user/edit_form.php`, `user/editadvanced_form.php`, `user/edit.php`, `user/editadvanced.php`
- Why fragile: Overlapping logic between standard and advanced edit forms, with subtle differences in capability checks and field handling.
- Safe modification: Changes to user profile saving logic must be tested across both forms and all user types (self-edit, admin-edit, new user creation).
- Test coverage: Rely heavily on Behat tests for end-to-end validation.

## Test Coverage Gaps

**External API Methods:**
- What's not tested: Some edge cases in `user/externallib.php` methods might lack comprehensive coverage for all parameter combinations and error states.
- Files: `user/externallib.php`
- Risk: Regressions in web service behavior that could impact mobile applications or external integrations.
- Priority: Medium

---

*Concerns audit: 2025-01-24*

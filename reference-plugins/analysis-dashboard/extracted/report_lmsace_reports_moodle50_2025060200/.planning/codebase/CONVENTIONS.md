# Coding Conventions

**Analysis Date:** 2025-02-15

## Naming Patterns

**Files:**
- PHP files in `classes/` follow PSR-4-like naming related to their class name and namespace.
- JS files use lowercase and underscores (e.g., `chartjs-plugin-datalabels.js`).
- Templates use lowercase and underscores.

**Functions:**
- PHP: `snake_case` for global functions in `lib.php`, `snake_case` for methods in `report_helper`.
- JS: `camelCase` for class methods (e.g., `generateRandomColor`).

**Variables:**
- PHP: `snake_case` (e.g., `$default_course`).
- JS: `camelCase` (e.g., `$dataValue`).

**Types:**
- PHP: Type hinting is used in newer parts but missing in older parts of `report_helper`.

## Code Style

**Formatting:**
- Follows Moodle Coding Style (based on PEAR/PSR but with specific tabs/spacing rules).
- 4-space indentation for PHP (tabs are often used in Moodle, but this plugin seems to use spaces in many places).

**Linting:**
- Configured via `.eslintrc`, `.jshintrc`, `.stylelintrc`.
- Uses `phpcs.xml.dist` for PHP linting.

## Import Organization

**Order:**
1. Moodle Core components.
2. Local plugin components.
3. Third-party libraries.

**Path Aliases:**
- AMD modules are prefixed with `report_lmsace_reports/`.

## Error Handling

**Patterns:**
- Use `require_capability()` at the start of entry points.
- Wrap complex operations in try-catch (rarely seen in this codebase, relies more on Moodle core handlers).
- Frontend uses `.fail(Notification.exception)` for AJAX errors.

## Logging

**Framework:** Moodle Standard Logstore.

**Patterns:**
- Queries `logstore_standard_log` directly for activity data.

## Comments

**When to Comment:**
- PHPDoc headers are required for all classes and functions.
- Inline comments used sparingly for complex logic (e.g., SQL generation).

**JSDoc/TSDoc:**
- Used for AMD modules and classes in `amd/src/`.

## Function Design

**Size:** `report_helper` methods are often large (50+ lines), especially those containing SQL.

**Parameters:** Often use optional parameters with default values (e.g., `$selectid = 0`).

**Return Values:** Usually return arrays (for Mustache data) or objects.

## Module Design

**Exports:** AMD modules export an object with an `init` function or a class definition.

**Barrel Files:** `amd/src/main.js` acts as a barrel file/orchestrator for all widgets.

---

*Convention analysis: 2025-02-15*

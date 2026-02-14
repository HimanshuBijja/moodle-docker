# Coding Conventions

**Analysis Date:** 2025-02-15

## Naming Patterns

**Files:**
- PHP files follow Moodle's lowercase and underscore convention (e.g., `externallib.php`).
- JS files in `amd/src` follow lowercase (e.g., `analytic.js`).
- Third-party JS files may follow their own naming (e.g., `Chart.js`).

**Functions:**
- PHP functions: `snake_case` (e.g., `quizanalytics_analytic`).
- JS functions: `camelCase` for properties in AMD modules (e.g., `init: function()`).

**Variables:**
- PHP: `lowercase` or `snake_case` (e.g., `$courseid`, `$totalquizattempted`).
- JS: `camelCase` (e.g., `userID`, `viewAnalyticsLinks`).

**Types:**
- Standard PHP types where applicable in external function definitions.

## Code Style

**Formatting:**
- Generally follows Moodle coding style, though some inconsistencies in indentation exist between files.

**Linting:**
- Relies on Moodle's core linting rules (ESLint for JS, PHPCS for PHP).

## Import Organization

**Order (PHP):**
1. `require_once('../../../config.php')`
2. `require_once($CFG->libdir . '/...')`
3. Internal plugin files.

**Path Aliases (JS):**
- `core/ajax` -> Moodle's AJAX library.
- `gradereport_quizanalytics/datatables` -> Plugin's local datatables module.

## Error Handling

**Patterns:**
- PHP: `throw new moodle_exception('error_string', 'component')`.
- JS: Minimal error handling observed; relies on Moodle's core AJAX failure reporting.

## Logging

**Framework:** Moodle Core Logging.

**Patterns:**
- No explicit custom logging found in the source code.

## Comments

**When to Comment:**
- File headers contain standard GPL license and @package tags.
- Functions usually have DocBlock comments (`/** ... */`).

**JSDoc/TSDoc:**
- Basic comments in `analytic.js`.

## Function Design

**Size:**
- `externallib.php` contains very large functions (e.g., `quizanalytics_analytic` is ~500 lines) which handles too many responsibilities (data fetching, calculation, formatting).

**Parameters:**
- Uses Moodle's `external_function_parameters` for Web Service inputs.

**Return Values:**
- External functions return JSON strings instead of structured arrays.

## Module Design

**Exports:**
- AMD modules return an object with methods (e.g., `return { init: ..., analytic: ... }`).

**Barrel Files:**
- None used.

---

*Convention analysis: 2025-02-15*

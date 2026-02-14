# Coding Conventions

**Analysis Date:** 2025-02-15

## Naming Patterns

**Files:**
- PHP files follow Moodle's auto-loading convention where directory structure matches namespaces.
- JS files in `amd/src/` use `snake_case`.
- Template files use `snake_case`.

**Functions:**
- Moodle hooks in `lib.php` use `pluginname_hookname` (e.g., `local_kopere_dashboard_extend_navigation`).
- Internal methods in classes use `camelCase` (e.g., `url_util::makeurl`, `dashboard::last_grades`).

**Variables:**
- Variables generally use `snake_case` (e.g., `$last_grades`, `$user_fullname`).
- Global Moodle variables are used correctly (`$DB`, `$CFG`, `$PAGE`, `$OUTPUT`).

**Types:**
- Classes are namespaced under `local_kopere_dashboard`.
- Value Objects are named with the full plugin component prefix (e.g., `local_kopere_dashboard_pages`).

## Code Style

**Formatting:**
- Indentation: 4 spaces.
- Braces: Standard PSR-2/Moodle style (opening brace on same line for methods/classes is common in this plugin, though Moodle usually prefers new line for classes).

**Linting:**
- Travis CI and GitHub Actions configurations suggest adherence to Moodle's coding standards (`moodle-plugin-ci`).

## Import Organization

**Order:**
1. Standard `use` statements at the top of PHP files.
2. `defined('MOODLE_INTERNAL') || die();` check where applicable.

**Path Aliases:**
- `autoload.php` defines aliases for legacy class names (e.g., `local_kopere_dashboard\util\mensagem` to `local_kopere_dashboard\util\message`).

## Error Handling

**Patterns:**
- Try-catch blocks are used for database operations and capability checks.
- AJAX errors are returned as JSON with a `success: 0` or `error: 1` flag.
- `local_kopere_dashboard\util\json::error($message)` is the standard way to terminate an AJAX request with an error.

## Logging

**Framework:** Moodle Events API.

**Patterns:**
- Events are triggered for major actions like imports or page creations.
- Located in `classes/event/`.

## Comments

**When to Comment:**
- Docblocks are present on almost all classes and methods.
- Includes `@package`, `@copyright`, `@license`.

**JSDoc/TSDoc:**
- Basic JSDoc is present in AMD modules.

## Function Design

**Size:** Generally small to medium (20-100 lines). Controllers like `dashboard.php` are well-partitioned.

**Parameters:** Methods often take arrays or specific IDs. Routing methods usually take no parameters and use `optional_param` or `required_param` internally via `url_util::params()`.

**Return Values:**
- Controller methods usually `echo` content directly (for HTML views) or use `json::encode()` (for AJAX).
- Utility methods return specific types (string, array, boolean).

## Module Design

**Exports:** AMD modules return objects with init/start functions (e.g., `return { start: function() { ... } };`).

**Barrel Files:** Not used; direct imports of modules.

---

*Convention analysis: 2025-02-15*

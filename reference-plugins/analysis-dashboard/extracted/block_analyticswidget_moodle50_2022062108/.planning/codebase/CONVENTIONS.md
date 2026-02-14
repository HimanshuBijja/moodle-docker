# Coding Conventions

**Analysis Date:** 2025-01-24

## Naming Patterns

**Files:**
- Snake case for all PHP and Mustache files (e.g., `course_stats.php`, `mobile_widget.mustache`).

**Functions/Methods:**
- Mix of snake_case (e.g., `export_html`, `studying_in`) and some standard Moodle method names (e.g., `get_content`, `init`).

**Variables:**
- Mix of snake_case (e.g., `$active_courses`) and prefixed properties (e.g., `$_userid`).

**Types:**
- PSR-4 namespaces used: `block_analyticswidget`, `block_analyticswidget\widgets\my`.

## Code Style

**Formatting:**
- Standard Moodle coding style (approximate).
- Indentation: 4 spaces.

**Linting:**
- `moodle-plugin-ci` runs `codechecker`, `phplint`, and `phpmd`.

## Import Organization

**Order:**
1. Namespace declaration
2. Use statements
3. Defined('MOODLE_INTERNAL') check

**Path Aliases:**
- None detected; standard Moodle autoloading.

## Error Handling

**Patterns:**
- Use of `moodle_exception` for critical failures in constructor/logic.
- Check for configuration before execution (e.g., `if (!get_config(...))`).

## Logging

**Framework:** Moodle standard logging (implicitly via core).

**Patterns:**
- No custom logging implemented in this plugin.

## Comments

**When to Comment:**
- File headers with GPL license.
- Class and method docblocks (PHPDoc).

**JSDoc/TSDoc:**
- Basic comments in Mustache templates for template documentation.

## Function Design

**Size:** Generally small, single-responsibility methods (e.g., `enrolment()`, `completed()`).

**Parameters:** Mostly passing primitive IDs or standard Moodle objects (`$course`).

**Return Values:** Mix of HTML strings, data arrays, or `void`.

## Module Design

**Exports:**
- Classes are exported via PSR-4 namespaces.
- Templates are exported via Moodle's `render_from_template`.

**Barrel Files:**
- None; uses dynamic directory scanning for widget discovery.

---

*Convention analysis: 2025-01-24*

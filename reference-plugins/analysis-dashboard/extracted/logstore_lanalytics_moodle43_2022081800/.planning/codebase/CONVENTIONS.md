# Coding Conventions

**Analysis Date:** 2025-02-15

## Naming Patterns

**Files:**
- Moodle standard: `lowercase_with_underscores.php`.

**Functions:**
- Standard Moodle: `lowercase_with_underscores()`.

**Variables:**
- `lowercase_with_underscores`.

**Types:**
- Classes: `lowercase_with_underscores` (matching Moodle namespace components).

## Code Style

**Formatting:**
- Follows Moodle Coding Guide (though some indentation might vary).
- Uses 4-space indentation.

**Linting:**
- Not explicitly configured in the plugin (no `.eslintrc` or `phpcs.xml` found in the plugin root), but usually follows Moodle core's `phpcs` rules.

## Import Organization

**Order:**
- Follows Moodle style: `defined('MOODLE_INTERNAL') || die();` at the top.
- `use` statements follow.

## Error Handling

**Patterns:**
- Use of `$DB` methods which throw exceptions on failure.
- Silencing/Ignoring non-critical failures in logging (e.g., `IGNORE_MISSING` context).

## Logging

**Framework:** `tool_log` and `mtrace` for CLI/Tasks.

## Comments

**When to Comment:**
- PHPDoc headers on all files and classes.
- Explanations for non-obvious logic (e.g., in `store.php` write method).

**JSDoc/TSDoc:**
- N/A (PHP-focused).

## Function Design

**Size:** Generally small, modular functions. `insert_event_entries` is the largest but remains readable.

**Parameters:** Standard Moodle pattern (passing objects like `$event`).

**Return Values:** Explicit returns or void where appropriate.

## Module Design

**Exports:** Classes are defined in namespaces matching Moodle's autoloader structure.

**Barrel Files:** Not used (standard for PHP/Moodle).

---

*Convention analysis: 2025-02-15*

# Coding Conventions

**Analysis Date:** 2025-02-15

## Naming Patterns

**Files:**
- PHP files in `classes/` follow PSR-4-like structure but use lowercase and underscores for filenames (e.g., `course_report.php` for class `course_report`).
- Mustache templates use lowercase: `edudashboard.mustache`.

**Functions:**
- Mostly lowercase with underscores: `getsitecourses`, `categoria_fulldata`.

**Variables:**
- Mostly lowercase with underscores: `$export`, `$context`, `$showhiddencategories`.

**Types:**
- Classes use lowercase with underscores: `class util`, `class course_report`.
- Namespaces: `local_edudashboard\extra`, `local_edudashboard\output`.

## Code Style

**Formatting:**
- Follows Moodle coding style (mostly).
- Indentation: 4 spaces.
- Braces: Same line for classes/functions (Moodle style).

**Linting:**
- Not explicitly configured in the plugin, but adheres to Moodle's `.eslintrc` and `.jshintrc` if run in a Moodle environment.

## Import Organization

**Order:**
1. `defined('MOODLE_INTERNAL') || die();`
2. `use` statements (Namespaces).
3. `global` declarations.
4. `require_once` for library files.

**Path Aliases:**
- Uses Moodle `$CFG->dirroot` and `$CFG->libdir` for absolute paths.

## Error Handling

**Patterns:**
- `try-catch` blocks for operations that might fail (e.g., Cache creation).
- `debugging()` for developer-level info (found in `privacy/provider.php`).
- `mtrace()` for task execution output.

## Logging

**Framework:** Moodle standard logging and `mtrace`.

**Patterns:**
- Tasks use `mtrace` to output progress: `mtrace("--->>Lets take site acess data");`.

## Comments

**When to Comment:**
- Docblocks for classes and methods (PHPDoc).
- File headers with Moodle license and package info.

**JSDoc/TSDoc:**
- Basic JSDoc used in AMD modules.

## Function Design

**Size:** Some functions are quite large (e.g., `categoria_fulldata` in `site_access_data.php`), violating SRP.

**Parameters:** Standard PHP parameters, often with default values.

**Return Values:** Mix of objects (`stdClass`), arrays, and booleans.

## Module Design

**Exports:**
- AMD modules return an object with an `init` function.
- PHP classes use namespaced exports.

**Barrel Files:**
- Not used.

---

*Convention analysis: 2025-02-15*

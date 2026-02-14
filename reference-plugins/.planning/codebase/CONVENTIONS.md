# Coding Conventions

**Analysis Date:** 2025-02-14

## Naming Patterns

**Files:**
- Standard Moodle plugin structure: `version.php`, `settings.php`, `lib.php`.
- Namespaced classes in `classes/` directory following PSR-4.

**Functions:**
- Legacy functions in `lib.php` usually follow `[pluginname]_[functionname]` pattern.
- Modern methods in classes use camelCase or snake_case depending on the specific plugin's age.

**Types:**
- Classes use namespaces e.g., `local_edudashboard\privacy\provider` in `reference-plugins/analysis-dashboard/extracted/local_edudashboard_moodle45_2025042400/edudashboard/classes/privacy/provider.php`.

## Code Style

**Formatting:**
- Most plugins follow Moodle's PHP coding style (tabs for indentation, specific brace placement).
- Some use `Gruntfile.js` for JS/CSS compilation.

**Linting:**
- CI configurations (e.g., `reference-plugins/analysis-dashboard/extracted/block_analyticswidget_moodle50_2022062108/analyticswidget/.github/workflows/ci.yml`) show usage of `moodle-plugin-ci` which includes `codechecker`, `phplint`, and `phpdoc`.

## Import Organization

**Order:**
1. Moodle core files (`defined('MOODLE_INTERNAL') || die();`)
2. Use statements for namespaced classes.

**Path Aliases:**
- Standard Moodle autoloader.

## Error Handling

**Patterns:**
- Use of `throw new \moodle_exception()` for fatal errors.
- Use of `debugging()` for developer-level info.

## Logging

**Framework:** Moodle Standard Logging (Logstore).

## Module Design

**Exports:**
- AMD modules in `amd/src/` (e.g., `reference-plugins/analysis-dashboard/extracted/block_progressanalytics_moodle45_2024092202/progressanalytics/amd/src/charts.js`).
- Mustache templates in `templates/`.

---

*Convention analysis: 2025-02-14*

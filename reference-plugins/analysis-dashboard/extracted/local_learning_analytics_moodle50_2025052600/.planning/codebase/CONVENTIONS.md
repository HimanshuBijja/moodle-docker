# Coding Conventions

**Analysis Date:** 2025-01-24

## Naming Patterns

**Files:**
- PHP files follow Moodle's autoloading standards: `classes/` uses namespaced directory structures.
- Subplugin entry files: `lareport_{name}.php`.

**Functions:**
- camelCase for methods (e.g., `run_report_or_page`, `add_series`).

**Variables:**
- lowercase snake_case or simple lowercase (e.g., `$courseid`, `$plot_data`).

**Types:**
- Namespaced classes: `local_learning_analyticsouter`.

## Code Style

**Formatting:**
- Follows Moodle coding style (GPL headers, 4-space indentation).

**Linting:**
- ESLint is used for JavaScript files in `amd/src/`.
- Configured via `Gruntfile.js`.

## Import Organization

**Order:**
1. Namespaced imports (`use ...`) follow the PHP file header.

**Path Aliases:**
- Standard Moodle AMD pathing: `local_learning_analytics/outputs`.
- Plotly alias defined in `amd/src/outputs.js`: `local_learning_analytics/plotly`.

## Error Handling

**Patterns:**
- Extensive use of `moodle_exception` for invalid parameters or access denied.
- `MUST_EXIST` flag used in `$DB->get_record()` to automatically handle missing data.

## Logging

**Framework:** Moodle Event API.

**Patterns:**
- Triggering custom events in `router::run_report_or_page` using `report_viewed::create()`.

## Comments

**When to Comment:**
- Classes and methods typically have Javadoc-style headers.
- Inline comments used for complex logic in `router.php`.

**JSDoc/TSDoc:**
- Basic headers in JS files.

## Function Design

**Size:**
- Report `run()` methods vary in size; some like `lareport_coursedashboard::run` handle significant layout logic.

**Parameters:**
- Parameters are often passed as arrays (e.g., `run(array $params)`).

**Return Values:**
- `run()` methods in reports are expected to return an array of `output_base` objects or strings.

## Module Design

**Exports:**
- AMD modules return an object containing functions (e.g., `return { plot: ... }`).

**Barrel Files:**
- Not used; direct imports of specific classes or modules.

---

*Convention analysis: 2025-01-24*

# Coding Conventions

**Analysis Date:** 2025-03-05

## Naming Patterns

### Files
- **PHP Classes:** `snake_case.php` located in `classes/` according to Moodle's PSR-4 mapping (e.g., `classes/local/base_widget.php` for `local_analysis_dashboard\local\base_widget`).
- **Templates:** `snake_case.mustache` located in `templates/` (e.g., `templates/widget_card.mustache`).
- **JavaScript:** `snake_case.js` located in `amd/src/` (e.g., `amd/src/dashboard.js`).
- **Tests:** `snake_case_test.php` in `tests/` for PHPUnit; `name.feature` in `tests/behat/` for Behat.

### Functions
- **PHP Methods:** `snake_case` (e.g., `get_cached_data`).
- **JavaScript Functions:** `camelCase` (e.g., `loadWidget`).
- **External API:** `[component]_[function_name]` (e.g., `local_analysis_dashboard_get_widget_data`).

### Variables
- **PHP Variables:** `snake_case` (e.g., `$cache_key`).
- **JavaScript Variables:** `camelCase` (e.g., `widgetMap`).

### Types (PHP)
- **Namespaces:** `local_analysis_dashboard\[subnamespace]` (e.g., `local_analysis_dashboard\local`).
- **Interfaces:** `snake_case` ending in `_interface` (e.g., `widget_interface`).

## Code Style

### Formatting
- **Standard:** Follows [Moodle Coding Style](https://moodledev.io/general/development/policies/codingstyle).
- **Indentation:** 4 spaces for PHP and JS; 2 spaces for Mustache.
- **Line Length:** Typically kept under 120 characters where possible.

### Linting
- **PHP:** `phpcs` using Moodle ruleset.
- **JavaScript:** `eslint` using Moodle configuration.

## Import Organization

### PHP
- Use `use` statements at the top of the file after `namespace`.
- Order: Built-in types, then Moodle core classes, then plugin classes.

### JavaScript (AMD)
- Uses `define(['dep1', 'dep2'], function(Dep1, Dep2) { ... })`.
- Dependencies are ordered alphabetically or by importance (core first).

## Error Handling

### Patterns
- **PHP:** Throw `moodle_exception` for user-facing errors; `coding_exception` for developer errors.
- **JavaScript:** Use `.catch(Notification.exception)` to handle AJAX failures.
- **External Functions:** Catch exceptions and return clean structures or throw `invalid_parameter_exception`.

## Logging

### Framework
- **PHP:** Uses `debugging()` for developer logs or `add_to_config_log()` for configuration changes.
- **JS:** `console.error` (sparingly) or `Notification.exception`.

## Comments

### When to Comment
- Use docblocks for every class, method, and property.
- Complex logic should have inline comments explaining the *why* rather than the *what*.

### JSDoc/TSDoc
- Follow Moodle JSDoc standards for AMD modules.
- Include `@module`, `@copyright`, and `@license`.

## Function Design

### Size
- Keep functions focused and small (ideally < 50 lines).

### Parameters
- Use type hints for all PHP method parameters (e.g., `array $params = []`).

### Return Values
- Use return type hints for all PHP methods (e.g., `: string`).

## Module Design

### Exports
- **JavaScript:** Return an object containing public methods (e.g., `init`).

### Barrel Files
- **PHP:** Uses `classes/local/widget_registry.php` as a central point for widget management.

---

*Convention analysis: 2025-03-05*

# Coding Conventions

**Analysis Date:** 2025-02-14

## Naming Patterns

**Files:**
- PHP Classes: Snake_case, matching the class name, e.g., `set_plugin_order.php` for class `set_plugin_order`.
- PHP Libraries: Snake_case, often ending in `lib.php`, e.g., `accesslib.php`.
- Javascript (AMD): Snake_case, e.g., `modal_save.js` in `amd/src/`.
- Templates: Snake_case, e.g., `user_menu.mustache`.

**Functions:**
- PHP: Snake_case for global functions and methods, e.g., `has_capability()`, `get_role_access()`.
- Javascript: CamelCase for functions and methods, e.g., `init()`, `registerEventListeners()`.

**Variables:**
- PHP: Snake_case, e.g., `$context_id`, `$user_id`.
- Javascript: CamelCase, e.g., `elementId`, `configData`.

**Types:**
- PHP Classes: Snake_case (traditional) or Namespaced Snake_case (modern), e.g., `core_admin\external\set_plugin_order`.
- PHP Interfaces: Usually end with `_interface` or similar, but Moodle often uses abstract classes or standard naming.

## Code Style

**Formatting:**
- **PHP:**
  - 4-space indentation.
  - Braces on the same line for control structures (`1tbs` style).
  - Max line length around 132 characters.
  - `<?php` opening tag required, closing tag `?>` omitted for pure PHP files.
- **Javascript:**
  - 4-space indentation.
  - `1tbs` brace style.
  - Semicolons are required.
  - Max line length 132.
- **CSS/SCSS:**
  - 4-space indentation.
  - Lowercase hex colors and properties.

**Linting:**
- **PHP:** PHP CodeSniffer (`phpcs`) using a custom `moodle` standard defined in `phpcs.xml.dist`.
- **Javascript:** ESLint configured in `.eslintrc`. Includes plugins for `@babel`, `promise`, and `jsdoc`.
- **CSS:** Stylelint configured in `.stylelintrc` using `postcss-scss`.
- **Gherkin:** `gherkin-lint` for Behat feature files.

## Import Organization

**Order:**
1. Built-in PHP/JS imports.
2. Third-party libraries.
3. Moodle core modules.
4. Local component modules.

**Path Aliases:**
- Javascript AMD uses component-based paths: `core/modal`, `core_admin/external`.
- PHP uses namespaces: `core_admin\external`.

## Error Handling

**Patterns:**
- PHP: Use of exceptions (e.g., `moodle_exception`) for fatal errors.
- Web Services: Return structures often include error messaging or throw exceptions that are caught by the external API layer.

## Logging

**Framework:** Custom Moodle logging system.

**Patterns:**
- Use `add_to_log` (deprecated) or the new Events API for permanent logs.
- `debugging()` function for developer-level warnings.

## Comments

**When to Comment:**
- Required for all classes, functions, and files.
- Used to explain complex logic, especially in older library files.

**JSDoc/TSDoc:**
- Extensive use of JSDoc for Javascript modules.
- PHPDoc required for all PHP elements.

## Function Design

**Size:** Preference for modular functions, but many legacy functions (especially in `lib/`) are very large.

**Parameters:** Type hints are increasingly used in modern PHP code.

**Return Values:** Type hints are used in modern PHP code.

## Module Design

**Exports:**
- Javascript: AMD modules `define(['jquery'], function($) { ... })`.
- PHP: Namespaced classes and functions.

**Barrel Files:** Not commonly used; Moodle relies on its own autoloader and component structure.

---

*Convention analysis: 2025-02-14*

# Coding Conventions

**Analysis Date:** 2025-02-14

## Naming Patterns

**Files:**
- **PHP Classes:** Snake_case, matching the class name exactly. Location: `classes/[subdir]/[classname].php` maps to namespace `[component]\[subdir]\[classname]`.
- **PHP Libraries:** Snake_case, usually `lib.php` for general plugin functions, or `[name]lib.php` for specific libraries in `lib/`.
- **Javascript (AMD):** Snake_case, e.g., `modal_save.js` in `amd/src/`.
- **Templates:** Snake_case, e.g., `user_menu.mustache` in `templates/`.
- **Plugin Entry Points:** Standardized names: `index.php`, `view.php`, `settings.php`, `version.php`.

**Functions:**
- **PHP:** Lowercase snake_case, e.g., `get_course_info()`. Global functions in plugins should be prefixed with the plugin name, e.g., `local_analytics_get_data()`.
- **Javascript:** CamelCase for functions and methods within ES6 modules/AMD, e.g., `init()`, `registerEventListeners()`.

**Variables:**
- **PHP:** Lowercase snake_case, e.g., `$context_id`.
- **Javascript:** CamelCase, e.g., `elementId`, `configData`.

**Types:**
- **PHP Classes:** Snake_case for legacy classes, but modern code uses namespaced snake_case.
- **Interfaces:** Often end with `_interface` or are named descriptively without a prefix/suffix.

## Code Style

**Formatting:**
- **PHP:**
  - 4-space indentation (strict).
  - Braces on the same line for control structures (`1tbs` style).
  - Max line length 132 characters.
  - No closing `?>` tag in pure PHP files.
  - `defined('MOODLE_INTERNAL') || die();` must be at the top of every PHP file that isn't a direct entry point.
- **Javascript:**
  - 4-space indentation.
  - `1tbs` brace style.
  - Semicolons are required.
  - Max line length 132.
  - Use ES6 features in `amd/src/` (transpiled by Babel).
- **CSS/SCSS:**
  - 4-space indentation.
  - Stylelint enforced.

**Linting:**
- **PHP:** PHP CodeSniffer (`phpcs`) with the `moodle` ruleset. Enforced by `moodle/phpcs.xml.dist`.
- **Javascript:** ESLint with plugins for `promise`, `jsdoc`, and `@babel`. Stricter rules for AMD modules (JSDoc required for all functions).
- **CSS:** Stylelint for SCSS and CSS.
- **Gherkin:** `gherkin-lint` for Behat feature files.

## Import Organization

**Order:**
1. Moodle Core modules (e.g., `core/ajax`, `core/notification`).
2. Plugin-specific modules.
3. Third-party libraries (if not bundled).

**Path Aliases:**
- **Javascript (AMD):** `[component]/[module_name]`. Example: `local_analytics/data_processor`.
- **PHP Namespaces:** `[component]\[subdir]`. Example: `local_analytics\output`.

## Error Handling

**Patterns:**
- **PHP:** Throw `moodle_exception(errorcode, component, link, debuginfo)`.
- **Javascript:** Use `core/notification` to display errors to users. Return rejected Promises in AMD modules.
- **Web Services:** Exceptions thrown in external functions are automatically converted to JSON/XML error responses.

## Logging

**Framework:** Moodle Events API.

**Patterns:**
- Define events in `classes/event/`.
- Trigger events using `\local_analytics\event\report_viewed::create([...])->trigger();`.
- Use `debugging('message', DEBUG_DEVELOPER)` for development warnings.

## Comments

**When to Comment:**
- **Files:** Every file must have a GPL license header and `@package` tag.
- **Classes/Functions:** Every element must have a PHPDoc/JSDoc block.
- **Logic:** Explain *why*, not *what*.

**JSDoc/PHPDoc:**
- Required tags: `@param`, `@return`, `@throws`, `@package`, `@copyright`, `@license`.
- AMD modules require `@module` tag.

## Function Design

**Size:** Aim for small, single-responsibility functions. Many legacy files violate this, but new code should be modular.

**Parameters:** Type hinting is mandatory for new PHP code. Use `stdClass` for generic objects.

**Return Values:** Type hinting is mandatory for new PHP code.

## Module Design

**Exports:**
- **Javascript:** AMD `define()` or ES6 `export`.
- **PHP:** Autoloaded classes in `classes/`.

**Barrel Files:** Not used.

## Plugin Specifics (Reference Plugins)
- Reference plugins (`local_learning_analytics`, `local_edudashboard`) generally follow the `classes/` and `templates/` structure.
- Some plugins (`block_analytics_graphs`) use legacy patterns with many top-level PHP files and direct library inclusion in `externalref/`. **Avoid this for new development.**
- Subplugins are supported (e.g., `local_learning_analytics/reports/`).

---

*Convention analysis: 2025-02-14*

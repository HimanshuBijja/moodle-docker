# Coding Conventions

**Analysis Date:** 2026-02-15

## Naming Patterns

**Files:**
- PHP files use `snake_case`: `auth_secureotp.php`, `input_sanitizer.php`, `otp_manager.php`.
- Test files use `snake_case` with `_test.php` suffix: `auth_test.php`, `otp_test.php`.
- Template files use `snake_case`: `login.mustache`, `otp_verify.mustache`.

**Functions/Methods:**
- Methods use `snake_case`: `initiate_otp_login()`, `sanitize_phone_number()`, `generate_otp()`.

**Variables:**
- Local variables and properties use `snake_case`: `$otp_manager`, `$sanitized`, `$user_id`.
- Global variables (when used) follow Moodle standards: `$CFG`, `$DB`, `$SESSION`, `$OUTPUT`.

**Types/Classes:**
- Plugin class: `auth_plugin_secureotp` (Standard Moodle authentication naming).
- Namespaced classes: `snake_case` for class names, e.g., `\auth_secureotp\security\input_sanitizer`. *Note: This deviates from PSR-12 PascalCase but is common in Moodle development.*

## Code Style

**Formatting:**
- Indentation: 4 spaces (standard Moodle).
- Bracket style: K&R style (opening brace on the same line for control structures, but often on the next line for classes and methods in older Moodle style).
- String quotes: Prefers single quotes for literal strings.

**Linting:**
- Not explicitly configured within the plugin, but follows Moodle's standard `phpcs.xml.dist` patterns found in core.

## Import Organization

**Order:**
1. Moodle core libraries (`require_once($CFG->libdir . '/authlib.php')`)
2. Local plugin classes (often explicitly via `require_once`)

**Path Aliases:**
- Uses `$CFG->dirroot` for absolute paths.
- Uses `__DIR__` for relative paths within the plugin.
- Uses Namespaces: `auth_secureotp\...`

## Error Handling

**Patterns:**
- Returns associative arrays containing:
    - `success` (boolean)
    - `message` (translated string via `get_string`)
    - `error_code` (string constant for programmatic handling)
- Example:
```php
return array(
    'success' => false,
    'message' => get_string('error_user_not_found', 'auth_secureotp'),
    'error_code' => 'USER_NOT_FOUND'
);
```

## Logging

**Framework:** Custom audit logger.

**Patterns:**
- Centralized logging via `audit_logger` class in `classes/security/audit_logger.php`.
- Captured via `log_audit_event($event_type, $status, $userid, $employee_id, $data)` method.
- Events logged to `auth_secureotp_audit` table.

## Comments

**When to Comment:**
- Structural documentation (Files, Classes, Methods).
- Complex logic explanations (e.g., regex for sanitization).

**JSDoc/TSDoc:**
- PHPDoc is used consistently for all methods and classes.
- Includes `@package`, `@copyright`, `@license`, `@param`, `@return`, and `@throws`.

## Function Design

**Size:** Generally small, single-responsibility methods.

**Parameters:** Methods often take primitive types or simple objects. Type hinting is primarily done via PHPDoc.

**Return Values:** Consistent use of associative arrays for complex results or booleans for simple checks.

## Module Design

**Exports:** All logic is encapsulated in classes.

**Barrel Files:** Not used (typical for PHP/Moodle).

---

*Convention analysis: 2026-02-15*

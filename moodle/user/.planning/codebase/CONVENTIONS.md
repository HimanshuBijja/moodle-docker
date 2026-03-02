# Coding Conventions

**Analysis Date:** 2025-03-02

## Naming Patterns

**Files:**
- PHP classes (namespaced): `classes/[subfolder]/[classname].php`. Example: `classes/external/user_summary_exporter.php`
- PHP library files: `[name].php`. Example: `lib.php`, `externallib.php`
- JS files (AMD/ESM): `amd/src/[name].js`. Example: `amd/src/participants.js`
- Test files: `tests/[name]_test.php`. Example: `tests/userlib_test.php`
- Behat feature files: `tests/behat/[name].feature`. Example: `tests/behat/addnewuser.feature`

**Functions:**
- Global/Library functions: snake_case, often prefixed with component name. Example: `user_create_user()` in `lib.php`
- Class methods: snake_case (standard Moodle practice, though newer APIs might use camelCase, most in this directory are snake_case). Example: `create_users_parameters()` in `externallib.php`

**Variables:**
- PHP variables: lowercase snake_case or simple lowercase. Example: `$user`, `$updatepassword`
- JS variables: camelCase. Example: `bulkActionSelect`, `pendingPromise`

**Types:**
- PHP Classes: snake_case (legacy) or namespaced with CamelCase/snake_case components. Example: `core_user_external`, `\core_user\hook\extend_user_menu`

## Code Style

**Formatting:**
- **PHP:** 4 spaces for indentation. Opening braces on the same line for functions/classes (K&R style variation common in Moodle).
- **JS:** 4 spaces for indentation. ESM-like syntax for AMD modules.

**Linting:**
- PHP: PHP_CodeSniffer (Moodle standard ruleset - not explicitly in this directory but implied by structure).
- JS: ESLint and Prettier (standard Moodle practice).

## Import Organization

**Order:**
1. Built-in/Vendor imports (PHP `use` or JS `import`)
2. Core Moodle imports
3. Local component imports

**Path Aliases:**
- JS uses AMD module names: `core_user/participants` maps to `user/amd/src/participants.js`.
- PHP uses namespaces: `core_user` for classes in `user/classes/`.

## Error Handling

**Patterns:**
- PHP: Throwing `moodle_exception` with a language string identifier. Example: `throw new moodle_exception('invalidusernameblank');`
- JS: Using Moodle's `Notification` module for user-facing errors.

## Logging

**Framework:** `debugging()` function for development logs; standard Moodle logging system for audit trails (not extensively seen in `user/` but part of core).

## Comments

**When to Comment:**
- Every file must have a standard Moodle license header.
- Every function/method must have a PHPDoc/JSDoc block.
- Complex logic should have inline comments explaining "why" rather than "what".

**JSDoc/TSDoc:**
- Used for AMD modules, defining `@module`, `@method`, `@private`, etc.

## Function Design

**Size:** Library functions in `lib.php` can be large (e.g., `user_create_user` is ~100 lines), but modern class-based methods are more focused.

**Parameters:** Often use `stdClass` objects for complex data (like `$user`).

**Return Values:** Usually return IDs for creation, boolean for success, or specific structured arrays for external APIs.

## Module Design

**Exports:**
- JS: `export const init = ...` for AMD entry points.
- PHP: Classes in `classes/` are autoloaded based on namespace.

**Barrel Files:** Not used in Moodle.

---

*Convention analysis: 2025-03-02*

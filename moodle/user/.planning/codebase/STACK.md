# Technology Stack

**Analysis Date:** 2025-02-14

## Languages

**Primary:**
- PHP 8.1+ - Core backend logic in `user/lib.php`, `user/externallib.php`, and classes in `user/classes/`.

**Secondary:**
- JavaScript (AMD) - Frontend logic in `user/amd/src/`.
- Mustache - Templating for UI components in `user/templates/`.

## Runtime

**Environment:**
- PHP 8.1+ (Minimum for Moodle 4.5)

**Package Manager:**
- Composer (at root, managed by Moodle)
- NPM (at root, for Grunt/Build tasks)
- Lockfile: `composer.lock` and `npm-shrinkwrap.json` present in root.

## Frameworks

**Core:**
- Moodle 4.5.5 (core_user subsystem) - The entire codebase is built as a subsystem of the Moodle LMS.

**Testing:**
- Behat - BDD testing, features located in `user/tests/behat/`.
- PHPUnit - Unit and integration testing, tests located in `user/tests/`.

**Build/Dev:**
- Grunt - Used for compiling JS (AMD) and CSS (implied by Moodle core patterns).
- Gulp (likely not used directly here, but Moodle core uses it for some tasks).

## Key Dependencies

**Critical:**
- `core_external` - Used for Web Services API implementation in `user/externallib.php`.
- `core_privacy` - Used for GDPR/Privacy compliance in `user/classes/privacy/provider.php`.
- `core_user` - The subsystem itself, providing core user management.

**Infrastructure:**
- Moodle DML (Database Manipulation Layer) - Accessed via global `$DB`.
- Moodle File API - Used for user files and profile pictures, e.g., in `user/lib.php`.

## Configuration

**Environment:**
- Configured via `config.php` in the root (outside this subsystem).
- Subsystem-specific settings are often in the database (accessed via `get_config()`).

**Build:**
- `user/amd/build/` - Contains minified and transpiled JavaScript.

## Platform Requirements

**Development:**
- PHP 8.1+
- Database (MySQL, PostgreSQL, MariaDB, MSSQL, or Oracle)
- Web Server (Apache or Nginx)

**Production:**
- Standard Moodle requirements for version 4.5.

---

*Stack analysis: 2025-02-14*

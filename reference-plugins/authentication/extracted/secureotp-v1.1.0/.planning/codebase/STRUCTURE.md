# Codebase Structure

**Analysis Date:** 2025-02-15

## Directory Layout

```
secureotp/
├── amd/                # Asynchronous Module Definition (JS)
│   ├── build/          # Minified JS files
│   └── src/            # Original JS source files
├── classes/            # Autoloaded PHP classes (PSR-4 style)
│   ├── admin/          # Admin-side dashboards and reports
│   ├── auth/           # Core authentication logic (OTP, Rate Limiting)
│   ├── event/          # Moodle Event definitions
│   ├── import/         # User import and validation logic
│   ├── messaging/      # Delivery gateways (SMS, Email, Twilio)
│   ├── security/       # Security utilities (Audit, Encryption, Sanitization)
│   └── task/           # Scheduled task implementations
├── cli/                # Command-line interface scripts
├── config/             # Placeholder for configuration templates
├── db/                 # Database schema and upgrade scripts
│   ├── install.xml     # XMLDB schema definition
│   └── upgrade.php     # Database migration logic
├── lang/               # Localization (English, Hindi, Telugu)
├── otp_providers/      # Specific implementations for OTP delivery
├── templates/          # Mustache UI templates
├── tests/              # Unit, PHPUnit, and Behat tests
├── auth.php            # Main plugin class entry point
├── login.php           # User login entry page
├── verify_otp.php      # OTP verification page
├── settings.php        # Admin settings definition
└── version.php         # Plugin version and dependency metadata
```

## Directory Purposes

**classes/:**
- Purpose: Contains the primary business logic organized by namespace.
- Contains: PHP classes following Moodle's autoloading convention.
- Key files: `classes/auth/otp_manager.php`, `classes/security/audit_logger.php`.

**db/:**
- Purpose: Defines the database structure and handles migrations.
- Contains: `install.xml` for table definitions and `upgrade.php` for updates.
- Key files: `db/install.xml`.

**templates/:**
- Purpose: Separates UI logic from PHP code.
- Contains: `.mustache` files for the login and verification screens.
- Key files: `templates/login.mustache`, `templates/otp_verify.mustache`.

**cli/:**
- Purpose: Provides tools for administrative tasks and automation.
- Contains: PHP scripts designed to be run from the command line.
- Key files: `cli/import_users.php`, `cli/cleanup_otps.php`.

## Key File Locations

**Entry Points:**
- `secureotp/auth.php`: Core integration with Moodle's authentication system.
- `secureotp/login.php`: Custom landing page for OTP-based login.
- `secureotp/verify_otp.php`: Final verification step for authentication.

**Configuration:**
- `secureotp/settings.php`: Defines the admin UI for plugin configuration.
- `secureotp/version.php`: Defines the plugin version and required Moodle version.

**Core Logic:**
- `secureotp/classes/auth/otp_manager.php`: Central hub for OTP generation and validation.
- `secureotp/classes/auth/rate_limiter.php`: Handles anti-brute force logic.
- `secureotp/classes/security/audit_logger.php`: Manages secure event logging.

**Testing:**
- `secureotp/tests/phpunit/`: PHPUnit test suites for back-end logic.
- `secureotp/tests/behat/`: E2E tests for the login flow.

## Naming Conventions

**Files:**
- [Classes]: snake_case.php (e.g., `otp_manager.php`) inside namespaced directories.
- [Scripts]: snake_case.php (e.g., `verify_otp.php`).
- [Templates]: snake_case.mustache (e.g., `otp_verify.mustache`).

**Directories:**
- [General]: lowercase_names (e.g., `otp_providers`, `classes`).

## Where to Add New Code

**New Feature:**
- Primary logic: Add to `secureotp/classes/[category]/`.
- Controller/Page: Add a new `.php` file in the root directory (e.g., `secureotp/new_feature.php`).
- UI: Add a mustache template in `secureotp/templates/`.

**New Messaging Provider:**
- Implementation: Add to `secureotp/classes/messaging/` or `secureotp/otp_providers/`.
- Configuration: Update `secureotp/settings.php` to include options for the new provider.

**New CLI Tool:**
- Implementation: Add to `secureotp/cli/`.

## Special Directories

**amd/src:**
- Purpose: Contains JavaScript modules.
- Generated: No.
- Committed: Yes.

**amd/build:**
- Purpose: Contains minified/transpiled JavaScript.
- Generated: Yes (via Grunt).
- Committed: Yes (standard Moodle practice).

---

*Structure analysis: 2025-02-15*

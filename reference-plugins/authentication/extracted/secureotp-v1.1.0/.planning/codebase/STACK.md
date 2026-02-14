# Technology Stack

**Analysis Date:** 2026-02-15

## Languages

**Primary:**
- PHP 8.0+ - Core plugin logic, authentication flows, and API integrations. `auth.php`, `classes/`

**Secondary:**
- JavaScript (ES6) - Frontend interactions for OTP verification and admin dashboard. `amd/src/`
- SQL - Custom database schema for user metadata, audit logs, and rate limiting. `db/install.xml`
- CSS/Less - Styling for the login page and admin UI. `styles.less`

## Runtime

**Environment:**
- PHP 8.0 or higher (Required for Moodle 4.5+ and modern security features)
- Redis 6.0+ (Required for high-performance OTP storage and rate limiting)

**Package Manager:**
- None (External dependencies are either bundled or use native PHP extensions to minimize footprint and maintain government compliance)

## Frameworks

**Core:**
- Moodle 4.5+ Framework - Provides the plugin architecture, user management, and core security APIs.

**Testing:**
- PHPUnit - Unit and integration testing. `tests/phpunit/`
- Behat - End-to-end functional testing. `tests/behat/`

**Build/Dev:**
- Grunt - Used for compiling JS (AMD) and CSS (Less). `Gruntfile.js` (in Moodle root)

## Key Dependencies

**Critical:**
- `php-redis` Extension - Required for the `Redis` class used in `classes/auth/otp_manager.php`.
- `curl` Extension - Required for Moodle's `curl` wrapper used in `classes/messaging/twilio_gateway.php`.
- `openssl` Extension - Used for HMAC signatures and encryption in `classes/security/encryption.php`.

**Infrastructure:**
- PostgreSQL 12+ (Recommended) - Used for persistent storage, specifically for audit partitioning. `db/install.xml`
- MySQL 8.0+ (Alternative) - Supported as a fallback database.

## Configuration

**Environment:**
- Moodle `$CFG` object - Stores core plugin settings. `settings.php`
- Redis Config - Configured via `$CFG` variables in `config.php` (e.g., `$CFG->auth_secureotp_redis_host`).

**Build:**
- `version.php` - Defines plugin version and Moodle requirements.
- `db/install.xml` - XML-based database schema definition.

## Platform Requirements

**Development:**
- Moodle 4.5 Development Environment
- Redis Server
- Twilio Account (for SMS testing)

**Production:**
- High-availability Redis Cluster (recommended for scale)
- PostgreSQL with partitioning support (for large audit logs)
- 1GB+ PHP Memory Limit (for bulk imports of 80k+ users)

---

*Stack analysis: 2026-02-15*

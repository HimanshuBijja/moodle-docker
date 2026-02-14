# External Integrations

**Analysis Date:** 2026-02-15

## APIs & External Services

**SMS Gateways:**
- Twilio - Primary SMS provider for OTP delivery.
  - SDK/Client: Direct REST API via Moodle's `\curl` class (`lib/filelib.php`).
  - Auth: `account_sid`, `auth_token`, `from_number` (configured in plugin settings).
  - Implementation: `classes/messaging/twilio_gateway.php`

**Email:**
- Moodle Core Email - Fallback for OTP delivery when SMS fails or is unavailable.
  - Implementation: `classes/messaging/email_gateway.php` using `email_to_user()`.

## Data Storage

**Databases:**
- PostgreSQL/MySQL
  - Connection: Moodle global `$DB`.
  - Client: Moodle Database API.
  - Custom Tables: `mdl_auth_secureotp_userdata`, `mdl_auth_secureotp_security`, `mdl_auth_secureotp_audit`, `mdl_auth_secureotp_import_log`, `mdl_auth_secureotp_rate_limit`.

**Caching & Transient Storage:**
- Redis 6.0+
  - Connection: Native PHP `Redis` class.
  - Usage: Primary storage for active OTPs (`auth_secureotp:otp:{userid}`) and rate limiting counters (`auth_secureotp:ratelimit:{type}:{id}`).
  - Fallback: Database tables used if Redis is unavailable.
  - Implementation: `classes/auth/otp_manager.php`, `classes/auth/rate_limiter.php`.

## Authentication & Identity

**Auth Provider:**
- SecureOTP (Custom Moodle Authentication Plugin)
  - Implementation: Passwordless login flow using Employee ID and OTP.
  - Core Class: `auth.php` extending `auth_plugin_base`.

## Monitoring & Observability

**Error Tracking:**
- Moodle Error Logs - Standard Moodle error reporting.

**Logs:**
- Immutable Audit Logs - Custom audit trail for security events with HMAC-SHA256 signatures.
  - Implementation: `classes/security/audit_logger.php`.
  - Retention: 7-year design requirement mentioned in `README.md`.

## CI/CD & Deployment

**Hosting:**
- Standard Moodle Web Server (Apache/Nginx) with PHP-FPM.

**CI Pipeline:**
- PHPUnit/Behat - Integrated with Moodle's testing framework. `tests/`

## Environment Configuration

**Required env vars:**
- N/A (Uses Moodle `config.php` and database-resident settings).

**Secrets location:**
- Moodle Database (`config_plugins` table) - Encrypted by Moodle core if configured, or stored as plain text in plugin settings.

## Webhooks & Callbacks

**Incoming:**
- Twilio Status Callbacks - Optional endpoint for SMS delivery tracking. `classes/messaging/twilio_gateway.php`

**Outgoing:**
- Twilio REST API - Outgoing requests to `https://api.twilio.com`.

---

*Integration audit: 2026-02-15*

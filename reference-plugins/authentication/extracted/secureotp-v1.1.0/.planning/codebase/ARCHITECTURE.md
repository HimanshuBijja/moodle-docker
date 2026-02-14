# Architecture

**Analysis Date:** 2025-02-15

## Pattern Overview

**Overall:** Moodle Authentication Plugin (extending `auth_plugin_base`)

**Key Characteristics:**
- **Custom Login Flow:** Deviates from the traditional `user_login()` by providing an `initiate_otp_login()` mechanism.
- **Security-First Design:** Includes built-in rate limiting, input sanitization, and device fingerprinting.
- **Resilient OTP Storage:** Implements a dual-layer storage strategy using Redis with a database fallback for OTP codes.
- **Immutable Auditing:** Uses HMAC-SHA256 signatures to ensure the integrity of authentication audit logs.

## Layers

**Authentication Layer:**
- Purpose: Handles the user login process and OTP verification.
- Location: `secureotp/auth.php` and `secureotp/classes/auth/`
- Contains: `auth_plugin_secureotp` class and `otp_manager`.
- Depends on: `Moodle Core Auth API`, `Redis` (optional), `DB`
- Used by: `Moodle Core Login System`, `secureotp/login.php`

**Security Layer:**
- Purpose: Protects against brute-force attacks and ensures data integrity.
- Location: `secureotp/classes/security/` and `secureotp/classes/auth/rate_limiter.php`
- Contains: `rate_limiter`, `input_sanitizer`, `encryption`, and `csrf_protection`.
- Depends on: `DB`

**Messaging Layer:**
- Purpose: Delivers OTP codes via various channels (SMS, Email, Twilio).
- Location: `secureotp/classes/messaging/` and `secureotp/otp_providers/`
- Contains: `sms_gateway`, `email_gateway`, `twilio_gateway`.
- Depends on: `External APIs` (Twilio, SMS services), `Moodle Mail API`

**Data Layer:**
- Purpose: Manages extended user profiles and authentication metadata.
- Location: `secureotp/db/install.xml`
- Contains: Table definitions for userdata, security status, and audit logs.
- Depends on: `Moodle XMLDB API`

## Data Flow

**OTP Login Flow:**

1. User enters identifier (Employee ID/Mobile/Email) on `secureotp/login.php`.
2. `auth_plugin_secureotp->initiate_otp_login()` is called.
3. Identifier is sanitized via `input_sanitizer`.
4. Rate limit is checked via `rate_limiter`.
5. User is looked up in `mdl_user` and `auth_secureotp_userdata`.
6. `otp_manager` generates an OTP and stores it (Redis or DB).
7. `messaging` layer sends the OTP to the user.
8. User is redirected to `secureotp/verify_otp.php`.

**Verification Flow:**

1. User submits OTP on `secureotp/verify_otp.php`.
2. `otp_manager` validates the OTP.
3. If valid, `complete_user_login()` is called to establish a Moodle session.
4. Audit event is logged via `audit_logger`.

**State Management:**
- **Temporary State:** OTPs are stored in Redis (primary) or `auth_secureotp_security` table (fallback) with expiration.
- **Permanent State:** User security metadata and extended profiles are stored in custom tables.
- **Session State:** Managed by Moodle's core session handler after successful verification.

## Key Abstractions

**Gateway Pattern:**
- Purpose: Decouples OTP delivery from the core logic.
- Examples: `secureotp/classes/messaging/sms_gateway.php`, `secureotp/classes/messaging/twilio_gateway.php`
- Pattern: Adapter/Strategy Pattern.

**Audit Logger:**
- Purpose: Provides a secure, tamper-evident log of all security events.
- Examples: `secureotp/classes/security/audit_logger.php`
- Pattern: Singleton/Utility with HMAC signing.

## Entry Points

**Main Plugin Class:**
- Location: `secureotp/auth.php`
- Triggers: Moodle core authentication calls and internal logic.
- Responsibilities: Implements the `auth_plugin_base` interface.

**Login Page:**
- Location: `secureotp/login.php`
- Triggers: User navigation to login.
- Responsibilities: Initial identifier collection and login initiation.

**Verification Page:**
- Location: `secureotp/verify_otp.php`
- Triggers: Redirect after successful login initiation.
- Responsibilities: OTP collection and validation.

**CLI Tools:**
- Location: `secureotp/cli/`
- Triggers: Cron jobs or manual execution.
- Responsibilities: Bulk user import, audit archiving, and maintenance.

## Error Handling

**Strategy:** Multi-tiered error handling with user-facing localized messages and detailed internal audit logging.

**Patterns:**
- **Graceful Fallback:** OTP storage falls back from Redis to DB if Redis is unavailable.
- **Audit Failure:** All failed authentication attempts are logged with detailed context (IP, User Agent, Fingerprint).

## Cross-Cutting Concerns

**Logging:** Handled by `\auth_secureotp\security\audit_logger` with HMAC signatures.
**Validation:** Handled by `\auth_secureotp\security\input_sanitizer` and `\auth_secureotp\import\csv_validator`.
**Authentication:** Managed by the custom OTP flow integrated with Moodle's session API.

---

*Architecture analysis: 2025-02-15*

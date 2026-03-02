# External Integrations

**Analysis Date:** 2025-02-14

## APIs & External Services

**Moodle Web Services:**
- REST, SOAP, XML-RPC, and AMF support via `core_external`.
  - Defined in `user/externallib.php` (e.g., `core_user_external`).
  - Auth: Handled by Moodle's web service tokens and protocol-specific authentication.

**Moodle Network (MNet):**
- Used for single sign-on (SSO) and remote user management between Moodle sites.
  - Seen in `user/profilelib_test.php` and `user/classes/privacy/provider.php`.

## Data Storage

**Databases:**
- Standard Moodle Database Support (MySQL, PostgreSQL, MariaDB, MSSQL, Oracle).
  - Connection: Configured in `config.php`.
  - Client: Moodle DML (Database Manipulation Layer) via `$DB`.

**File Storage:**
- Moodle File API (File pool system).
  - Used for profile images and user documents.
  - Relevant files: `user/lib.php`, `user/classes/external/update_private_files.php`.

**Caching:**
- Moodle Universal Cache (MUC).
  - Used for performance optimization (implied as part of Moodle core usage).

## Authentication & Identity

**Auth Provider:**
- Moodle's Internal Auth and Pluggable Auth (Manual, LDAP, SSO, OAuth2).
  - Implementation: Handled at the core level, but `user/externallib.php` allows creating users with specified auth types.

## Monitoring & Observability

**Error Tracking:**
- Moodle Error Handling.
  - Logs: Standard Moodle logging subsystem (uses database and/or files).

## CI/CD & Deployment

**Hosting:**
- Platform-independent (any server supporting PHP and a database).

**CI Pipeline:**
- Moodle uses Github Actions for its own CI (outside of this repository scope).

## Environment Configuration

**Required env vars:**
- `$CFG->dirroot`, `$CFG->dataroot`, `$CFG->wwwroot` - Standard Moodle configuration variables.

**Secrets location:**
- Stored in `config.php` (for database and admin passwords).

## Webhooks & Callbacks

**Incoming:**
- Web Services (REST) endpoints handled by `webservice/rest/server.php` which maps to `user/externallib.php`.

**Outgoing:**
- Event API (Moodle Events) - Core system allows other components to subscribe to user-related events (e.g., user created, user deleted).

---

*Integration audit: 2025-02-14*

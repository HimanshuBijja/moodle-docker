# External Integrations

**Analysis Date:** 2025-02-17

## APIs & External Services

**AI Services:**
- OpenAI - Text and image generation (`moodle/ai/provider/openai/`)
- Azure AI - Enterprise AI services (`moodle/ai/provider/azureai/`)

**Communication:**
- Matrix - Decentralized communication protocol (`moodle/communication/provider/matrix/`)
- Custom Link - Generic external communication links (`moodle/communication/provider/customlink/`)

**SMS Gateway:**
- AWS SNS - Simple Notification Service via AWS (`moodle/sms/gateway/aws/`)

**Payments:**
- PayPal - Payment gateway integration (`moodle/payment/gateway/paypal/`)

**Learning Tools:**
- LTI (Learning Tools Interoperability) - Integration with external learning tools (`moodle/auth/lti/`, `moodle/enrol/lti/`)

## Data Storage

**Databases:**
- Supported: MySQL/MariaDB, PostgreSQL, MS SQL Server, Oracle.
- Connection: Configured in `moodle/config.php`.
- Client: Moodle DML (Database Abstraction Layer).

**File Storage:**
- S3 - Amazon Simple Storage Service (`moodle/repository/s3/`)
- Dropbox - Integration with Dropbox API (`moodle/repository/dropbox/`)
- Google Drive - Integration with Google Workspace (`moodle/repository/googledocs/`)
- OneDrive - Microsoft 365 integration (`moodle/repository/onedrive/`)
- Nextcloud - Open source content collaboration (`moodle/repository/nextcloud/`)
- WebDAV - Standard web-based file management (`moodle/repository/webdav/`)

**Caching:**
- Redis - Distributed caching (`moodle/cache/stores/redis/`)
- APCu - Local PHP caching (`moodle/cache/stores/apcu/`)

## Authentication & Identity

**Auth Provider:**
- OAuth2 - Supporting Google, Microsoft, LinkedIn, and generic providers (`moodle/auth/oauth2/`).
- LDAP - Enterprise directory integration (`moodle/auth/ldap/`).
- CAS / Shibboleth - SSO solutions (`moodle/auth/cas/`, `moodle/auth/shibboleth/`).
- MNet - Moodle-to-Moodle networking (`moodle/auth/mnet/`).

## Monitoring & Observability

**Error Tracking:**
- Custom error handling and logging to database/files.

**Logs:**
- Standard Moodle logging system with support for database and legacy log stores (`moodle/admin/report/log/`).

## CI/CD & Deployment

**Hosting:**
- Docker-based environment provided (`docker-compose.yml`).

**CI Pipeline:**
- Not explicitly detected in root or `moodle/` directory, though Moodle HQ uses Travis/GitHub Actions/Jenkins externally.

## Environment Configuration

**Required env vars:**
- Typically handled via `moodle/config.php` rather than environment variables, though Docker setup uses:
  - `MYSQL_ROOT_PASSWORD`
  - `MYSQL_DATABASE`
  - `MYSQL_USER`
  - `MYSQL_PASSWORD`

**Secrets location:**
- `moodle/config.php` contains sensitive database credentials.

## Webhooks & Callbacks

**Incoming:**
- `moodle/admin/oauth2callback.php` - OAuth2 authentication callbacks.
- `moodle/payment/gateway/paypal/` - PayPal payment notifications.

**Outgoing:**
- AI provider requests.
- SMS gateway requests.
- Communication provider (Matrix) requests.

---

*Integration audit: 2025-02-17*

# External Integrations

**Analysis Date:** 2025-02-14

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

**Media & Tags:**
- YouTube API - Video integration in `moodle/blocks/tag_youtube/`
- Flickr API - Image integration in `moodle/blocks/tag_flickr/`

## Data Storage

**Databases:**
- Supported: MySQL/MariaDB, PostgreSQL, MS SQL Server, Oracle, SQLite (via PDO).
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
- WebAuthn - Passwordless authentication (`moodle/lib/webauthn/`).

## Monitoring & Observability

**Error Tracking:**
- Custom error handling and logging to database/files.

**Logs:**
- Standard Moodle logging system (`mdl_logstore_standard_log`).
- Plugins like `local_learning_analytics` and `local_edudashboard` consume these logs to generate reports.

## CI/CD & Deployment

**Hosting:**
- Docker-based environment provided (`docker-compose.yml`).

**CI Pipeline:**
- Moodle core uses Travis CI / GitHub Actions for automated testing.

## Environment Configuration

**Required env vars:**
- Typically handled via `moodle/config.php` rather than environment variables, though Docker setup uses:
  - `MYSQL_ROOT_PASSWORD`
  - `MYSQL_DATABASE`
  - `MYSQL_USER`
  - `MYSQL_PASSWORD`

**Secrets location:**
- `moodle/config.php` contains sensitive database credentials.
- AI provider keys and OAuth secrets are stored in Moodle's configuration database (accessible via Site Administration).

## Webhooks & Callbacks

**Incoming:**
- `moodle/admin/oauth2callback.php` - OAuth2 authentication callbacks.
- `moodle/payment/gateway/paypal/` - PayPal payment notifications.

**Outgoing:**
- AI provider requests (OpenAI/Azure).
- SMS gateway requests (AWS SNS).
- Communication provider (Matrix) requests.

---

*Integration audit: 2025-02-14*

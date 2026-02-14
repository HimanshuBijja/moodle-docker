# External Integrations

**Analysis Date:** 2025-02-15

## APIs & External Services

**Server Monitoring:**
- Linux System Utilities - Integrates with `top`, `uptime`, `du`, and `/proc/meminfo` via PHP `shell_exec` to provide real-time server health data.
  - Implementation: `classes/server/performancemonitor.php`

**oEmbed Services:**
- Video & Social Platforms - Proxy for loading oEmbed content from YouTube, Vimeo, X (Twitter), Reddit.
  - Implementation: `_editor/save.php` (oembedProxy action)
  - Allowed Domains: `ALLOWED_OEMBED_DOMAINS` constant in `_editor/save.php`

## Data Storage

**Databases:**
- Moodle Database (MySQL/PostgreSQL/MariaDB)
  - Tables: `local_kopere_dashboard_menu`, `local_kopere_dashboard_pages`, `local_kopere_dashboard_event`
  - Client: Moodle `$DB` Global

**File Storage:**
- Moodle File API (`file_storage`)
  - Usage: Storing editor assets and webpage images.
  - Areas: `overviewfiles`, `editor_webpages`.

**Caching:**
- Moodle Universal Cache (MUC)
  - Cache Definitions: `db/caches.php`
  - Usage: `report_getdata_cache`, `performancemonitor_cache`.

## Authentication & Identity

**Auth Provider:**
- Moodle Standard Authentication
  - Implementation: `require_login()` and `require_capability()` in `view.php` and `view-ajax.php`.

## Monitoring & Observability

**Error Tracking:**
- Moodle Error Logs
- Custom JSON Error Handler: `local_kopere_dashboard\util\json::error()`

**Logs:**
- Moodle Events API
- Custom Event Tracking: `classes/event/` and `classes/events/`.

## CI/CD & Deployment

**Hosting:**
- Self-hosted Moodle Environments.

**CI Pipeline:**
- Travis CI: `.travis.yml`
- GitHub Actions: `.github/workflows/ci.yml`, `.github/workflows/release.yml`.

## Environment Configuration

**Required env vars:**
- Standard Moodle `$CFG` variables.

**Secrets location:**
- Moodle `config.php`.

## Webhooks & Callbacks

**Incoming:**
- `view-ajax.php` - Central endpoint for frontend AJAX requests.
- `_editor/save.php` - Endpoint for the embedded editor to save content.

**Outgoing:**
- None detected.

---

*Integration audit: 2025-02-15*

# External Integrations

**Analysis Date:** 2025-01-24

## APIs & External Services

**Visualizations:**
- Plotly.js - Integrated via local file `js/plotly.min.js` and loaded through Moodle AMD system.

## Data Storage

**Databases:**
- Moodle Database (PostgreSQL/MySQL/MariaDB)
  - Connection: Managed by Moodle core.
  - Client: Moodle `$DB` API.
  - Critical Tables: `{logstore_lanalytics_log}` (external dependency), `{course_modules}`, `{modules}`, `{context}`.

**File Storage:**
- Local filesystem only - CSS, JS, and PHP files.

**Caching:**
- Moodle Universal Cache (MUC) - Implicitly via Moodle APIs, though no explicit custom cache definitions were found in `db/caches.php`.

## Authentication & Identity

**Auth Provider:**
- Moodle Core - Authentication handled by `require_login()`.

## Monitoring & Observability

**Error Tracking:**
- Moodle error logging.

**Logs:**
- Custom logstore: `logstore_lanalytics` - This plugin is the primary data source for the analytics.
- Custom events: `local_learning_analytics\eventeport_viewed` triggered on dashboard access.

## CI/CD & Deployment

**Hosting:**
- Moodle site.

**CI Pipeline:**
- Not detected.

## Environment Configuration

**Required env vars:**
- Standard Moodle environment.

**Secrets location:**
- Not applicable - No external API keys or secrets detected within the plugin logic.

## Webhooks & Callbacks

**Incoming:**
- None.

**Outgoing:**
- None.

---

*Integration audit: 2025-01-24*

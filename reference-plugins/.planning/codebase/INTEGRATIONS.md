# External Integrations

**Analysis Date:** 2024-02-14

## APIs & External Services

**Server Monitoring:**
- System Shell - Integration via `shell_exec` to monitor CPU, RAM, and Disk usage (used in `reference-plugins/analysis-dashboard/extracted/local_kopere_dashboard_moodle51_2025110600 (1)/kopere_dashboard/classes/server/performancemonitor.php`)

**Content & Assets:**
- Google Fonts - Used for typography in visual editors.
- OEmbed - Support for embedding external media content (detected in `reference-plugins/analysis-dashboard/extracted/local_kopere_dashboard_moodle51_2025110600 (1)/kopere_dashboard/_editor/VvvebJs/libs/builder/oembed.js`)

## Data Storage

**Databases:**
- Moodle Database API (`$DB`) - Primary data storage for reports, logs, and settings.
- Direct SQL Queries - Used for complex analytical reports (e.g., `reference-plugins/analysis-dashboard/extracted/local_learning_analytics_moodle50_2025052600/learning_analytics/reports/learners/classes/query_helper.php`)

**File Storage:**
- Moodle File API - Used for storing exported reports, images, and user-uploaded content via `pluginfile.php`.

**Caching:**
- Moodle Universal Cache (MUC) - Used for caching performance monitor results and report data (e.g., `reference-plugins/analysis-dashboard/extracted/local_kopere_dashboard_moodle51_2025110600 (1)/kopere_dashboard/db/caches.php`)

## Authentication & Identity

**Auth Provider:**
- Moodle Core Auth - All plugins rely on Moodle's session and user management.

## Monitoring & Observability

**Error Tracking:**
- Moodle Error Handling - Integration with Moodle's standard error reporting.

**Logs:**
- Moodle Log Store - Plugins read from and potentially write to Moodle's log tables for analytics (e.g., `reference-plugins/analysis-dashboard/extracted/local_learning_analytics_moodle50_2025052600/learning_analytics/lib.php`)

## CI/CD & Deployment

**Hosting:**
- Platform-independent (Moodle-based).

**CI Pipeline:**
- GitHub Actions - Automated testing and linting (e.g., `reference-plugins/analysis-dashboard/extracted/report_lmsace_reports_moodle50_2025060200/lmsace_reports/.github/workflows/moodle-ci.yml`)
- Travis CI - Used in `local_kopere_dashboard`.

## Environment Configuration

**Required env vars:**
- Standard Moodle environment variables (`$CFG` in `config.php`).

**Secrets location:**
- Moodle's `config_plugins` table (accessed via `get_config`/`set_config`).

## Webhooks & Callbacks

**Incoming:**
- Moodle External Functions (Web Services) - Exposing data for mobile or external apps (e.g., `reference-plugins/analysis-dashboard/extracted/report_lmsace_reports_moodle50_2025060200/lmsace_reports/externallib.php`)

**Outgoing:**
- Moodle Messaging API - Sending notifications and reports via email or site alerts (e.g., `reference-plugins/analysis-dashboard/extracted/local_kopere_dashboard_moodle51_2025110600 (1)/kopere_dashboard/classes/output/events/send_events.php`)

---

*Integration audit: 2024-02-14*

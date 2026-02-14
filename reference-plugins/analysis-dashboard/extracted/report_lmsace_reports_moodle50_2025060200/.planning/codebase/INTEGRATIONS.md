# External Integrations

**Analysis Date:** 2025-02-15

## APIs & External Services

**Moodle Core APIs:**
- **Navigation API:** Used in `lib.php` to inject report links into course and user navigation menus.
- **External Functions API:** Used in `externallib.php` to expose AJAX endpoints for frontend charts.
- **Completion API:** Used in `report_helper::get_user_overall_courseinfo` to track user progress.
- **Enrolment API:** Used in `report_helper` to fetch course and user enrollment data.
- **Logstore API:** Used to query `logstore_standard_log` for site and user visit statistics.

## Data Storage

**Databases:**
- **Moodle Database:** Primary storage for configuration and source of truth for report data.
- **Standard Logstore Table:** Queried in `report_helper::get_site_visits`.

**File Storage:**
- **Moodle Dataroot:** Disk usage is analyzed in `report_helper::get_moodle_spaces`.

**Caching:**
- **Moodle Cache API (MUC):** Custom cache definition in `db/caches.php` (definition `reportwidgets`) used to cache disk size calculations.

## Authentication & Identity

**Auth Provider:**
- **Moodle Internal:** Uses Moodle's `require_login()` and capability checks (`require_capability()`) for security.
- **Context-based Security:** Reports are scoped to System, Course, or User contexts.

## Monitoring & Observability

**Error Tracking:**
- **Moodle Debugging:** Uses `debugging()` and `Notification.exception` for error reporting.

**Logs:**
- **Standard Moodle Logging:** Relies on core logs for its own reporting functionality.

## CI/CD & Deployment

**CI Pipeline:**
- **GitHub Actions:** `.github/workflows/moodle-ci.yml` runs Moodle CI checks (linting, PHPUnit, Behat).

## Environment Configuration

**Required env vars:**
- Standard Moodle `config.php` variables (`$CFG->wwwroot`, `$CFG->dataroot`, etc.).

**Secrets location:**
- Not applicable (managed by Moodle core).

## Webhooks & Callbacks

**Incoming:**
- **AJAX Endpoints:** Defined in `db/services.php` for fetching chart data asynchronously.

**Outgoing:**
- None.

---

*Integration audit: 2025-02-15*

# External Integrations

**Analysis Date:** 2025-02-15

## APIs & External Services

**Moodle Core Services:**
- Completion API - `\completion_info` and `\core_completion\progress` for course progress.
- Gradebook API - `\grade_get_course_grade` for user performance data.
- Enrollment API - `get_enrolled_users` for participant lists.
- Logstore API - `logstore_standard_log` for authentication reporting.

## Data Storage

**Databases:**
- Moodle Database (PostgreSQL/MySQL/MariaDB)
  - Tables: `user`, `course`, `course_categories`, `course_completions`, `files`, `context`.
  - Client: Moodle `$DB` global.

**File Storage:**
- Moodle File API - Used to calculate system and course disk usage via the `files` table.

**Caching:**
- Moodle Universal Cache (MUC)
  - Definitions in `db/caches.php`: `admininfos`, `siteaccess`.

## Authentication & Identity

**Auth Provider:**
- Moodle Core Authentication
  - Implementation: Tracks last access and authentication logs via `logstore_standard_log`.

## Monitoring & Observability

**Error Tracking:**
- Moodle Debugging/Logging.

**Logs:**
- Moodle standard logs (`logstore_standard_log`).

## CI/CD & Deployment

**Hosting:**
- Any Moodle-compatible web server (Apache/Nginx).

**CI Pipeline:**
- Not detected.

## Environment Configuration

**Required env vars:**
- Standard Moodle `config.php` settings.

**Secrets location:**
- Standard Moodle `config.php`.

## Webhooks & Callbacks

**Incoming:**
- None.

**Outgoing:**
- None.

## Platform Compatibility

**Totara Support:**
- The plugin includes specific checks for Totara in `classes/extra/util.php` (`istotara()` method) and `classes/extra/course_report.php` (`getuser_course_progress_percentage` method).

---

*Integration audit: 2025-02-15*

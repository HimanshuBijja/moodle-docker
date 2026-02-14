# External Integrations

**Analysis Date:** 2025-02-15

## APIs & External Services

**Learning Analytics Integration:**
- `local_learning_analytics` - Companion plugin that provides the user interface for the logged data.
  - Link: https://github.com/rwthanalytics/moodle-local_learning_analytics

## Data Storage

**Databases:**
- Moodle Database (PostgreSQL/MySQL/MariaDB)
  - Tables:
    - `{logstore_lanalytics_log}`: Stores the actual log entries.
    - `{logstore_lanalytics_evtname}`: Lookup table for event names to save space.
  - Client: Moodle `$DB` global object.

## Authentication & Identity

**Auth Provider:**
- Moodle Internal Auth - Leverages Moodle's session and user management.
- Does NOT store user IDs in its own tables, likely for privacy/anonymization.

## Monitoring & Observability

**Logs:**
- Integrates into Moodle's logging subsystem.
- Custom logs for cleanup tasks via `mtrace`.

## CI/CD & Deployment

**Hosting:**
- Hosted as part of a Moodle installation.

## Environment Configuration

**Required env vars:**
- Standard Moodle `config.php` settings.

## Webhooks & Callbacks

**Incoming:**
- `write(\core\event\base $event)` - Called by Moodle whenever a loggable event occurs.

**Outgoing:**
- Calls `lalog` plugins' `logger::log` method as an extension point.
  - Path: `local/learning_analytics/logs/{plugin_name}/classes/lalog/logger.php`

---

*Integration audit: 2025-02-15*

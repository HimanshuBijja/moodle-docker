# Architecture

**Analysis Date:** 2025-02-15

## Pattern Overview

**Overall:** Event-Driven Log Store

**Key Characteristics:**
- **Buffering:** Uses a buffered writer to minimize database writes by batching log entries.
- **Normalization:** Normalizes event names into a separate lookup table (`logstore_lanalytics_evtname`).
- **Anonymization:** Deliberately excludes user IDs from the log table to protect privacy while still providing analytics.

## Layers

**Log Writer Layer:**
- Purpose: Interfaces with Moodle's event system to capture logs.
- Location: `lanalytics/classes/log/store.php`
- Contains: `store` class implementing `	ool_log\log\writer`.
- Depends on: `tool_log`, `devices`, Moodle `$DB`.

**Device Detection Layer:**
- Purpose: Parses User-Agent strings to categorize devices and operating systems.
- Location: `lanalytics/classes/devices.php`
- Contains: `devices` class with regex-based detection logic.

**Maintenance Layer:**
- Purpose: Handles data cleanup and legacy data import.
- Location: `lanalytics/classes/task/cleanup_task.php`, `lanalytics/cli/import.php`.

## Data Flow

**Logging Flow:**

1. Moodle triggers an event (`\core\event\base`).
2. `logstore_lanalytics\log\store::write()` is called.
3. Event is filtered based on login status and configuration (course scope, user roles).
4. Device info is appended using `devices::get_device()`.
5. Event is added to an internal buffer.
6. When buffer reaches `buffersize`, `flush()` is called, which invokes `insert_event_entries()`.
7. `insert_event_entries()` maps event names to IDs and saves records to `{logstore_lanalytics_log}`.
8. External `lalog` plugins are notified.

**State Management:**
- Stateless logging. State is persisted solely in the database.

## Key Abstractions

**logstore_lanalytics\log\store:**
- Purpose: Primary entry point for capturing and persisting events.
- Examples: `lanalytics/classes/log/store.php`
- Pattern: Adapter/Writer pattern for Moodle Log Stores.

**logstore_lanalytics\devices:**
- Purpose: Abstracting device and OS detection.
- Examples: `lanalytics/classes/devices.php`

## Entry Points

**Moodle Event Hook:**
- Location: `lanalytics/classes/log/store.php`
- Triggers: Moodle core events.
- Responsibilities: Filtering, augmenting, and buffering logs.

**Scheduled Task:**
- Location: `lanalytics/classes/task/cleanup_task.php`
- Triggers: Moodle Cron / Scheduled Tasks.
- Responsibilities: Deleting logs older than the configured lifetime.

## Error Handling

**Strategy:** Fail silently during logging to avoid disrupting the user experience.

**Patterns:**
- `IGNORE_MISSING` used when retrieving contexts.
- Empty returns if logging is disabled or event is ignored.

---

*Architecture analysis: 2025-02-15*

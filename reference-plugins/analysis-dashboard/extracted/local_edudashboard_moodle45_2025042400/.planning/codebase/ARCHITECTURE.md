# Architecture

**Analysis Date:** 2025-02-15

## Pattern Overview

**Overall:** Moodle Plugin Architecture (Local Type)

**Key Characteristics:**
- **Modular Dashboard:** Uses renderables and Mustache templates to build a modular UI.
- **Scheduled Data Aggregation:** Uses background tasks to pre-calculate heavy statistics.
- **Cache-Heavy:** Relies on MUC (Moodle Universal Cache) to store aggregated data and avoid repeated DB hits.

## Layers

**Presentation Layer:**
- Purpose: Rendering the dashboard and reports.
- Location: `classes/output/` and `templates/`
- Contains: Renderer (`renderer.php`), Renderables (`edudashboard_renderable.php`, `pagesreport_renderable.php`), and Mustache templates.
- Depends on: Logic layer and Moodle output API.
- Used by: `index.php`, `coursereport.php`, `authenticationreport.php`.

**Logic Layer:**
- Purpose: Business logic for report generation and utility functions.
- Location: `classes/extra/`
- Contains: `util.php` (general helpers), `course_report.php` (course-specific stats).
- Depends on: Moodle core APIs.
- Used by: Presentation layer and Tasks.

**Asynchronous Layer:**
- Purpose: Background processing of heavy data (disk usage, site access).
- Location: `classes/task/`
- Contains: `diskusage.php`, `site_access_data.php`.
- Depends on: Logic layer.

**Data Layer:**
- Purpose: Persisting settings and cached data.
- Location: `db/`
- Contains: Cache definitions (`caches.php`), task schedules (`tasks.php`), and capabilities (`access.php`).

## Data Flow

**Report Generation:**

1. Scheduled task (`site_access_data.php`) runs via Cron.
2. Task calls logic layer (`util.php`) to aggregate data from core tables.
3. Data is saved to `config_plugins` or purged/warmed in MUC.
4. User accesses `index.php`.
5. `edudashboard_renderable` retrieves data from cache or configuration.
6. Renderer merges data with Mustache templates for display.

**State Management:**
- Global state is handled via Moodle's `$DB` and `$CFG`.
- Transient reporting state is handled via MUC and `config_plugins`.

## Key Abstractions

**Renderables:**
- Purpose: Encapsulate data preparation for specific UI components.
- Examples: `classes/output/edudashboard_renderable.php`, `classes/output/pagesreport_renderable.php`.
- Pattern: Moodle Templatable/Renderable.

**Utility Class:**
- Purpose: Centralized logic for data processing.
- Examples: `classes/extra/util.php`.
- Pattern: Static helper class.

## Entry Points

**Main Dashboard:**
- Location: `index.php`
- Triggers: User navigation.
- Responsibilities: Initialize page, load JS/CSS, render main dashboard.

**Reports:**
- Location: `coursereport.php`, `authenticationreport.php`.
- Triggers: Links from dashboard.
- Responsibilities: Render specific detailed reports.

## Error Handling

**Strategy:** Standard Moodle exception handling.

**Patterns:**
- Try-catch blocks in cache operations (e.g., in `course_report::getcoursefilessize`).
- Requirement checks (e.g., `defined('MOODLE_INTERNAL') || die();`).

## Cross-Cutting Concerns

**Logging:** Uses Moodle standard logging and `mtrace` for tasks.
**Validation:** Uses Moodle `PARAM_*` types for input parameters in `index.php`.
**Authentication:** Managed by `require_login()` and capability checks `require_capability()`.

---

*Architecture analysis: 2025-02-15*

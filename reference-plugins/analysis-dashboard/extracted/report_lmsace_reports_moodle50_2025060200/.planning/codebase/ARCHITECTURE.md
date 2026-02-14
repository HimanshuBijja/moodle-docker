# Architecture

**Analysis Date:** 2025-02-15

## Pattern Overview

**Overall:** Widget-based Plugin Architecture

**Key Characteristics:**
- **Modular Widgets:** Each report component (chart/table) is implemented as a standalone widget with both PHP (data) and JS (rendering) parts.
- **Context-Aware:** The system automatically switches between Site, Course, and User reporting based on the current context.
- **Asynchronous Data Loading:** UI renders first, then fetches chart data via Moodle AJAX External Functions.

## Layers

**UI Layer (Templates/JS):**
- Purpose: Renders the dashboard shell and initializes chart widgets.
- Location: `templates/`, `amd/src/`
- Contains: Mustache templates, AMD modules, Chart.js logic.
- Depends on: Moodle Core AJAX, Chart.js.

**API/Controller Layer:**
- Purpose: Handles web requests and AJAX calls, performs capability checks.
- Location: `index.php`, `externallib.php`
- Contains: Entry points, external function definitions.
- Depends on: Logic Layer.

**Logic Layer (Widgets/Helpers):**
- Purpose: Aggregates data from the database and prepares it for the UI.
- Location: `classes/local/widgets/`, `report_helper.php`
- Contains: SQL queries, data formatting logic, widget classes.
- Depends on: Moodle Database API, Cache API.

## Data Flow

**Report Generation Flow:**

1. User visits `index.php` (optionally with `courseinfo` or `userinfo` parameters).
2. `index.php` validates capabilities and context.
3. `lmsace_reports` renderer outputs the Mustache template `lmsace_reports.mustache`.
4. The template includes placeholders for various widgets.
5. `report_lmsace_reports/main` JS module initializes.
6. Each widget JS module (e.g., `widgets/sitevisits.js`) makes an AJAX call to `report_lmsace_reports_external`.
7. `externallib.php` calls `report_helper::ajax_chart_reports`.
8. The helper instantiates the specific PHP widget class to fetch data.
9. Data is returned as JSON to the frontend and rendered via Chart.js.

**State Management:**
- Stateless server-side, relies on URL parameters for context.
- Frontend state managed within individual JS widget modules.

## Key Abstractions

**Widget Interface (Implicit):**
- Purpose: Standardizes how data is fetched and formatted for charts.
- Examples: `classes/local/widgets/sitevisitswidget.php`
- Pattern: Strategy pattern for different report types.

**Report Helper:**
- Purpose: Centralized utility for data aggregation and common logic.
- Examples: `classes/report_helper.php`
- Pattern: Singleton-like Static Utility Class (God Object).

## Entry Points

**Web Index:**
- Location: `index.php`
- Triggers: User navigation to Reports.
- Responsibilities: Routing, Security, Initial Rendering.

**External Functions:**
- Location: `externallib.php`
- Triggers: Frontend AJAX calls.
- Responsibilities: Secure data exposure for charts.

## Error Handling

**Strategy:** Moodle Standard Error Handling

**Patterns:**
- `require_capability()` for access control.
- `debugging()` for developer-level errors in PHP.
- `Notification.exception` for frontend error display.

## Cross-Cutting Concerns

**Logging:** Uses Moodle Standard Logstore as a data source.
**Validation:** `optional_param()` with strict types (`PARAM_INT`, `PARAM_TEXT`).
**Authentication:** Managed by Moodle `require_login()`.

---

*Architecture analysis: 2025-02-15*

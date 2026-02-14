# Architecture

**Analysis Date:** 2025-01-24

## Pattern Overview

**Overall:** Modular Plugin Architecture with Router Pattern.

**Key Characteristics:**
- **Subplugin System:** Extensible via `lareport` and `lalog` subplugin types.
- **Router-based Navigation:** Uses a custom router to map URLs to specific report classes.
- **Abstract Layering:** Base classes for reports and outputs to ensure consistent implementation across modules.

## Layers

**Routing Layer:**
- Purpose: Dispatches requests to appropriate report subplugins based on URL slash arguments.
- Location: `classes/router.php`
- Contains: Logic for parsing `/reports/{report}/{page}` patterns.
- Depends on: Moodle URL and Component APIs.
- Used by: `index.php`.

**Report Layer (Subplugins):**
- Purpose: Defines the business logic and data aggregation for specific analytics views.
- Location: `reports/`
- Contains: Classes extending `report_base.php`.
- Depends on: `query_helper` classes within subplugins.
- Used by: `router.php`.

**Output Layer:**
- Purpose: Handles the structural representation of data (plots, tables, etc.).
- Location: `classes/local/outputs/`
- Contains: `plot.php`, `table.php`, `splitter.php`.
- Depends on: `output_base.php`.
- Used by: Report classes.

**Rendering Layer:**
- Purpose: Final conversion of output objects into HTML/JS.
- Location: `classes/output/renderer.php`
- Contains: `local_learning_analytics_renderer`.
- Depends on: Mustache templates in `templates/`.

## Data Flow

**Report Request Flow:**

1. User accesses `index.php?course=ID`.
2. `index.php` validates capabilities and course status.
3. `router::run()` parses the URI.
4. Router instantiates the corresponding `lareport` class.
5. Report class calls its `run()` method, which uses `query_helper` to fetch data.
6. Report class returns an array of `output_base` objects.
7. Renderer iterates through outputs and calls their `print()` methods.
8. `plot` outputs trigger AMD JavaScript to initialize Plotly charts.

**State Management:**
- Server-side: Stateless, derived from Moodle session and database.
- Client-side: Minimal state managed by Plotly.js for interactive charts.

## Key Abstractions

**`report_base`:**
- Purpose: Interface for all report subplugins.
- Examples: `classes/report_base.php`.
- Pattern: Abstract Base Class.

**`output_base`:**
- Purpose: Common interface for renderable components.
- Examples: `classes/output_base.php`.

## Entry Points

**Dashboard Index:**
- Location: `index.php`
- Triggers: User clicking the "Learning Analytics" link in course navigation.
- Responsibilities: Auth check, Course validation, Routing.

**Settings Page:**
- Location: `settings.php`
- Triggers: Site administration access.
- Responsibilities: Plugin configuration.

## Error Handling

**Strategy:** Standard Moodle Exception handling.

**Patterns:**
- `throw new moodle_exception()` for access violations or missing reports.

## Cross-Cutting Concerns

**Logging:** Uses custom events (`report_viewed.php`) and relies on `logstore_lanalytics` for raw data.
**Validation:** `required_param()` and `optional_param()` for input sanitization.
**Authentication:** Moodle `require_login()` and capability checks.

---

*Architecture analysis: 2025-01-24*

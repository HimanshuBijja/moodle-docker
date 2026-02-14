# Architecture

**Analysis Date:** 2025-02-15

## Pattern Overview

**Overall:** Controller-based Routing with Custom UI Abstraction

**Key Characteristics:**
- Unified Entry Points: Uses `view.php` and `view-ajax.php` as routers rather than many flat PHP files.
- Domain-Driven Logic: Logic is partitioned into namespaces like `report`, `server`, `task`, `util`.
- Custom HTML DSL: Uses a custom PHP-based UI builder (`classes/html/`) instead of standard Moodle forms for its internal dashboard.

## Layers

**Routing Layer:**
- Purpose: Dispatches requests to specific classes and methods.
- Location: `view.php`, `view-ajax.php`, `autoload.php`
- Contains: Request parameter parsing and class instantiation logic.
- Depends on: Moodle Core, `local_kopere_dashboard\util\url_util`
- Used by: Browser and Frontend JS

**Controller/Logic Layer:**
- Purpose: Handles business logic for specific dashboard features.
- Location: `classes/` (e.g., `dashboard.php`, `users.php`, `courses.php`)
- Contains: Data fetching, processing, and template preparation.
- Depends on: Moodle `$DB`, `util` namespace.

**Data Access/VO Layer:**
- Purpose: Represents data entities and provides structured access.
- Location: `classes/vo/`
- Contains: Value Objects representing database records.
- Examples: `local_kopere_dashboard_pages.php`

**UI Abstraction Layer:**
- Purpose: Provides a programmatic way to build complex HTML components like Tables and Forms.
- Location: `classes/html/`
- Contains: `table.php`, `form.php`, `button.php` and various `inputs/`.
- Depends on: Mustache templates.

## Data Flow

**Dashboard Page Load:**

1. Browser requests `view.php?classname=dashboard&method=start`.
2. `view.php` initializes Moodle environment and calls `local_kopere_dashboard_load_class()`.
3. `autoload.php` instantiates `\local_kopere_dashboard\dashboard` and calls `start()`.
4. `start()` calls `dashboard_util::start_page()`, renders `dashboard_start.mustache`, and requires AMD module `local_kopere_dashboard/dashboard`.
5. `dashboard_util::end_page()` completes the output.

**AJAX Data Refresh:**

1. AMD module `local_kopere_dashboard/dashboard` calls `view-ajax.php?classname=dashboard&method=monitor`.
2. `view-ajax.php` dispatches to `dashboard->monitor()`.
3. `monitor()` fetches data, renders a Mustache template, and returns a JSON response via `json::encode()`.

**State Management:**
- Server-side: Managed via Moodle Session and MUC (`cache`).
- Client-side: Minimal state, primarily handles DOM updates from AJAX responses.

## Key Abstractions

**Routing Instance:**
- Purpose: Standardized routing to class methods.
- Examples: `autoload.php` (`local_kopere_dashboard_load_class`)
- Pattern: Front Controller / Command Pattern.

**HTML Components:**
- Purpose: Programmatic UI construction.
- Examples: `classes/html/data_table.php`, `classes/html/form.php`
- Pattern: Builder Pattern.

## Entry Points

**Web Dashboard:**
- Location: `view.php`
- Triggers: User navigation to plugin dashboard.
- Responsibilities: Rendering the main UI shell and dispatching to controllers.

**AJAX API:**
- Location: `view-ajax.php`
- Triggers: Client-side JS requests.
- Responsibilities: Returning JSON data or HTML fragments for partial updates.

**Embedded Editor:**
- Location: `_editor/index.php`
- Triggers: Admin clicking "Edit" on a webpage.
- Responsibilities: Providing the VvvebJs editor interface.

## Error Handling

**Strategy:** Exception catching and JSON error responses for AJAX.

**Patterns:**
- Try-catch blocks in `view-ajax.php`.
- `local_kopere_dashboard\util\json::error()` for structured error reporting.

## Cross-Cutting Concerns

**Logging:** Uses Moodle Events API (`classes/event/`).
**Validation:** Custom validation in `classes/html/form.php` and jQuery Validation plugin on the frontend.
**Authentication:** Capability-based access control (`local/kopere_dashboard:view`, `local/kopere_dashboard:manage`).

---

*Architecture analysis: 2025-02-15*

# Architecture

**Analysis Date:** 2025-03-04

## Pattern Overview

**Overall:** Moodle Local Plugin with a Modular Widget-based Architecture.

**Key Characteristics:**
- **Registry-driven:** All analytics components (widgets) are managed by a central registry, allowing for dynamic capability-based filtering and centralized configuration.
- **Asynchronous Data Loading:** The dashboard UI is rendered initially as an empty skeleton. Widget data is fetched asynchronously via AJAX after the page loads, improving perceived performance.
- **Lazy Loading:** Widgets only fetch their data when they enter the viewport, reducing server load for dashboards with many widgets.

## Layers

**UI Layer (Templates):**
- Purpose: Defines the layout of the dashboard and individual widget cards.
- Location: `templates/`
- Contains: Mustache templates (`dashboard.mustache`, `widget_card.mustache`).
- Depends on: None.
- Used by: Output Renderers.

**Frontend Logic (AMD):**
- Purpose: Orchestrates data fetching, lazy loading, and rendering of visualizations.
- Location: `amd/src/`
- Contains: JavaScript modules (`dashboard.js`, `widget_renderer.js`, `lazy_loader.js`).
- Depends on: `core/ajax`, `core/chartjs`, `local_analysis_dashboard/widget_renderer`.
- Used by: Mustache templates via `{{#js}}` blocks.

**API Layer (External Functions):**
- Purpose: Bridges the frontend and backend, providing a secure endpoint for widget data.
- Location: `classes/external/`
- Contains: `get_widget_data.php`, `get_dashboard_config.php`.
- Depends on: `local_analysis_dashboard\local\widget_registry`.
- Used by: AMD modules.

**Business Logic Layer (Widgets):**
- Purpose: Encapsulates data retrieval and processing logic for specific analytics.
- Location: `classes/local/widgets/`
- Contains: Concrete widget classes (e.g., `total_users.php`, `site_visits.php`).
- Depends on: `local_analysis_dashboard\local\base_widget`, Moodle DB (`$DB`).
- Used by: `widget_registry`, `external\get_widget_data`.

**Data Storage & Caching Layer:**
- Purpose: Persists processed analytics data to improve performance.
- Location: Moodle Universal Cache (MUC) configuration in `db/caches.php`.
- Contains: Cache definitions for site, course, and user stats.
- Depends on: Moodle's `cache` API.
- Used by: `base_widget`.

## Data Flow

**Widget Rendering Flow:**

1. **Initial Request:** User visits `local/analysis_dashboard/index.php`.
2. **Page Preparation:** `dashboard_page` renderable uses `widget_registry` to get a list of metadata for widgets visible to the user.
3. **Template Render:** `renderer` renders `dashboard.mustache`, creating empty containers for each widget and passing the list of widget IDs to the AMD module.
4. **AMD Init:** `dashboard.js` initializes and registers widgets with `lazy_loader.js`.
5. **Viewport Trigger:** When a widget container scrolls into view, `lazy_loader` triggers a callback.
6. **Data Fetch:** `dashboard.js` calls `local_analysis_dashboard_get_widget_data` (external function) via AJAX.
7. **Processing:** The external function identifies the widget, checks capabilities, and calls `get_cached_data()` on the widget instance.
8. **Visualization:** `widget_renderer.js` receives the JSON data and renders the appropriate visualization (Chart.js, HTML table, or custom counter) inside the container.

**State Management:**
- Stateless backend. All parameters required for data retrieval (like `courseid` or `userid`) are passed from the frontend to the external function.

## Key Abstractions

**widget_interface:**
- Purpose: Defines the contract for all widgets, ensuring consistent methods for name, type, and data retrieval.
- Examples: `classes/local/widget_interface.php`
- Pattern: Interface Pattern.

**base_widget:**
- Purpose: Provides shared logic for all widgets, specifically handling caching (MUC), context defaults, and availability checks.
- Examples: `classes/local/base_widget.php`
- Pattern: Template Method Pattern.

**widget_registry:**
- Purpose: Centralized directory of all widgets. It handles instantiation and filtering based on user permissions and dashboard context (site vs course).
- Examples: `classes/local/widget_registry.php`
- Pattern: Service Locator / Registry Pattern.

## Entry Points

**Web Entry Points:**
- `index.php`: Main site-level dashboard.
- `coursereport.php`: Course-level dashboard (requires `id` param).
- `studentdashboard.php`: Personal student dashboard.
- `managerdashboard.php`: Dashboard for managers.

**Web Service Entry Point:**
- `classes/external/get_widget_data.php`: The primary data source for all frontend visualizations.

## Error Handling

**Strategy:** Graceful degradation at the widget level.

**Patterns:**
- **Frontend Catch:** `dashboard.js` catches AJAX errors and shows a "Widget Error" message in the specific widget card, allowing other widgets to continue loading.
- **Empty Data:** `widget_renderer.js` handles empty or null data by showing a specialized "No data available" placeholder rather than failing.
- **Availability Checks:** Widgets can implement `is_available()` to hide themselves if certain dependencies (like other plugins) are missing.

## Cross-Cutting Concerns

**Logging:** Uses Moodle standard logging for dashboard access and report views.
**Validation:** External functions use Moodle's `PARAM_*` types for input validation.
**Authentication:** Handled by `require_login()` in entry point PHP files and `validate_context()` in external functions.
**Capability Checks:** Centrally managed in `widget_registry::get_for_current_user()` and verified again in `external/get_widget_data.php`.

---

*Architecture analysis: 2025-03-04*

# Architecture

**Analysis Date:** 2025-01-24

## Pattern Overview

**Overall:** Plug-and-play Widget Architecture within a Moodle Block.

**Key Characteristics:**
- Dynamic widget discovery via file system scanning (`glob`).
- Interface-driven design using `widgetfacade`.
- Decoupled data gathering (Widgets) from presentation (Mustache/Renderer).

## Layers

**Controller Layer:**
- Purpose: Orchestrates the block rendering and mobile app response.
- Location: `block_analyticswidget.php` and `classes/output/mobile.php`
- Contains: Entry points for web and mobile.
- Depends on: `\block_analyticswidget\widget`
- Used by: Moodle Core (Block API)

**Business Logic Layer:**
- Purpose: Aggregates statistics and manages widget instances.
- Location: `classes/widget.php`
- Contains: Main widget manager and `widgetfacade` interface.
- Depends on: Moodle DB and enrolment APIs.

**Widget Domain:**
- Purpose: Specific statistical calculations (Course stats, Activity stats).
- Location: `classes/widgets/my/` and `classes/widgets/teacher/`
- Contains: Individual widget classes implementing `widgetfacade`.
- Depends on: Moodle completion and enrolment libraries.

**Presentation Layer:**
- Purpose: Renders HTML/JS using Mustache templates and Chart.js.
- Location: `templates/` and `classes/output/renderer.php`
- Contains: Mustache templates and PHP Renderer.

## Data Flow

**Web Rendering:**

1. `block_analyticswidget::get_content()` is called by Moodle.
2. Instantiates `\block_analyticswidget\widget`.
3. Calls `$renderer->render($widget)`.
4. `widget->export_for_template()` dynamically loads widgets from `classes/widgets/`.
5. Each widget calculates data (e.g., enrolment count, completion progress).
6. Data is passed to `widget.mustache` which initializes `Chart.js` via `core/chartjs`.

**State Management:**
- Stateless; data is calculated on demand or retrieved from MUC (`awstat`).

## Key Abstractions

**widgetfacade:**
- Purpose: Defines the contract for all analytics widgets.
- Examples: `classes/widget.php` (Interface definition)
- Pattern: Strategy Pattern

**Widget Implementation:**
- Purpose: Encapsulates specific logic for a dashboard tile.
- Examples: `classes/widgets/my/course_stats.php`

## Entry Points

**Block Entry:**
- Location: `block_analyticswidget.php`
- Triggers: Dashboard page load.
- Responsibilities: Initialization and content generation.

**Mobile Entry:**
- Location: `classes/output/mobile.php`
- Triggers: Moodle App block request.
- Responsibilities: Return template and data for mobile view.

## Error Handling

**Strategy:** Moodle standard exceptions.

**Patterns:**
- Use of `moodle_exception` for missing parameters (e.g., in `course_stats.php`).
- Silent failure/empty return for missing roles or data to avoid breaking the dashboard.

## Cross-Cutting Concerns

**Logging:** Standard Moodle logging (not explicitly implemented in plugin code).
**Validation:** Handled via `moodle-plugin-ci` during development.
**Authentication:** Checked via `MOODLE_INTERNAL` and `require_login` (in mobile view).

---

*Architecture analysis: 2025-01-24*

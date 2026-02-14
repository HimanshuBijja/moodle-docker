# Architecture

**Analysis Date:** 2025-02-15

## Pattern Overview

**Overall:** Client-Server Analytics Pattern

**Key Characteristics:**
- **Decoupled Data Retrieval:** Charts are populated via asynchronous AJAX calls to a dedicated Web Service.
- **Client-Side Rendering:** Graphical visualizations are rendered in the browser using `Chart.js` based on JSON data.
- **Standard Moodle Plugin:** Adheres to the `grade/report` plugin type architecture.

## Layers

**Presentation Layer:**
- Purpose: Displays the quiz analytics dashboard and interactive tables.
- Location: `quizanalytics/index.php` and `quizanalytics/amd/src/analytic.js`
- Contains: HTML templates (rendered via PHP), CSS, and JS logic for chart initialization.
- Depends on: `quizanalytics/externallib.php` (for data), `Chart.js`, `DataTables`.
- Used by: End users (Students and Teachers) via the Gradebook.

**Service Layer (Web Service):**
- Purpose: Provides a standard interface for the frontend to request analytics data.
- Location: `quizanalytics/externallib.php`
- Contains: `moodle_gradereport_quizanalytics_external` class.
- Depends on: Moodle Core External API, DB API.
- Used by: `quizanalytics/amd/src/analytic.js` via `core/ajax`.

**Data Access Layer:**
- Purpose: Executes complex SQL queries to aggregate quiz attempt data.
- Location: `quizanalytics/externallib.php`
- Contains: Raw SQL queries using `$DB->get_records_sql()`.
- Depends on: Moodle Database.

## Data Flow

**Analytics Dashboard Loading:**

1. User visits `quizanalytics/index.php?id=[courseid]`.
2. PHP validates permissions and renders the initial table of quizzes.
3. User clicks "View Analytics" for a specific quiz.
4. `quizanalytics/amd/src/analytic.js` catches the click event and extracts `quizid`.
5. `core/ajax` sends a request to `moodle_quizanalytics_analytic`.
6. `quizanalytics/externallib.php` executes multiple SQL queries to calculate summary stats, improvement curves, and category hardness.
7. Data is returned as a JSON object.
8. `analytic.js` parses the JSON and initializes `Chart.js` instances on hidden canvas elements.

## Key Abstractions

**External Function:**
- Purpose: Formalized API for data retrieval.
- Examples: `moodle_quizanalytics_analytic` in `quizanalytics/externallib.php`.
- Pattern: Remote Procedure Call (RPC) via AJAX.

**AMD Module:**
- Purpose: Encapsulates frontend logic and manages dependencies.
- Examples: `quizanalytics/amd/src/analytic.js`.
- Pattern: Asynchronous Module Definition.

## Entry Points

**Dashboard Page:**
- Location: `quizanalytics/index.php`
- Triggers: User navigation from Gradebook.
- Responsibilities: Initial permission checks, setting up page requirements (JS/CSS), rendering the quiz list.

**Web Service Function:**
- Location: `quizanalytics/externallib.php` -> `quizanalytics_analytic()`
- Triggers: AJAX call from frontend.
- Responsibilities: Data aggregation, calculation of analytics metrics, JSON formatting.

## Error Handling

**Strategy:** standard Moodle exception handling.

**Patterns:**
- `throw new moodle_exception()` for access violations in `index.php`.
- AJAX promise `fail()` handling (though minimal in `analytic.js`).

## Cross-Cutting Concerns

**Logging:** Standard Moodle logging via core APIs.
**Validation:** `PARAM_INT` validation in `externallib.php` and `optional_param`/`required_param` in `index.php`.
**Authentication:** `require_login()` and `require_capability('gradereport/quizanalytics:view', $context)`.

---

*Architecture analysis: 2025-02-15*

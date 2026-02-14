# Architecture

**Analysis Date:** 2024-02-14

## Pattern Overview

**Overall:** Modular Plugin-based Architecture (Hooks & Events)

**Key Characteristics:**
- **Highly Modular:** Almost every feature is a plugin (Activities, Blocks, Reports, Authentication, etc.).
- **Hook-driven:** Communication between core and plugins happens via standard hook functions in `lib.php`.
- **Event-driven:** System actions trigger events that plugins can observe and handle.

## Layers

**Presentation Layer:**
- Purpose: Handles UI rendering and user interaction.
- Location: `templates/`, `classes/output/`, `amd/src/`
- Contains: Mustache templates, Renderers, and JavaScript modules.
- Depends on: Business Logic Layer, Core APIs.
- Used by: End users.

**Business Logic Layer:**
- Purpose: Processes data and enforces rules.
- Location: `classes/`, `lib.php`, `locallib.php`
- Contains: Plugin-specific classes, hook implementations.
- Depends on: Data Access Layer, Core Subsystems.
- Used by: Presentation Layer, Web Services.

**Data Access Layer (DML/DDL):**
- Purpose: Provides an abstraction over the database.
- Location: `lib/dml/`, `lib/ddl/`
- Contains: Database abstraction layer (SQL generation, table management).
- Depends on: PHP Database Extensions (PDO, etc.).
- Used by: Business Logic Layer.

## Data Flow

**Standard Page Request:**

1. User requests a page (e.g., `mod/assign/view.php`).
2. `config.php` is included, initializing the `$CFG` global and database connection.
3. Access control is checked via `require_login()` and `has_capability()`.
4. Plugin logic processes parameters and interacts with the database via `$DB`.
5. Data is passed to a Renderer.
6. Renderer uses a Mustache template to generate HTML.
7. HTML is sent to the user.

**Web Service Call:**

1. External system calls a Web Service endpoint (`webservice/rest/server.php`).
2. Authentication and authorization are checked.
3. The request is routed to a method in an `external_api` class (e.g., `mod_assign_external`).
4. Logic is executed, and a structured response is returned.

**State Management:**
- Global `$CFG`: Site-wide configuration.
- Global `$DB`: Database access object.
- Global `$USER`: Current user object.
- Global `$PAGE`: Current page configuration.
- Global `$OUTPUT`: Standard renderer for the current page/theme.

## Key Abstractions

**Renderer (`core\output\renderer_base`):**
- Purpose: Separates logic from presentation.
- Examples: `mod/assign/classes/output/renderer.php`
- Pattern: Strategy / Decorator

**External API (`external_api`):**
- Purpose: Standardized interface for Web Services.
- Examples: `mod/assign/externallib.php`, `reference-plugins/analysis-dashboard/extracted/gradereport_quizanalytics_moodle50_2025051900/quizanalytics/externallib.php`
- Pattern: Facade

**Events (`core\event\base`):**
- Purpose: Decouples system actions from side effects (logging, notifications).
- Examples: `lib/classes/event/course_module_viewed.php`
- Pattern: Observer

## Entry Points

**Web Entry Points:**
- Location: `index.php`, `mod/*/view.php`, `admin/index.php`
- Triggers: Browser requests.
- Responsibilities: Initialization, access check, UI generation.

**CLI Entry Points:**
- Location: `admin/cli/*.php`
- Triggers: Command line execution.
- Responsibilities: Maintenance tasks, cron, installations.

**Web Services:**
- Location: `webservice/*/server.php`
- Triggers: API calls.
- Responsibilities: External integration.

## Error Handling

**Strategy:** Exception-based error handling.

**Patterns:**
- `moodle_exception`: Standard exception for user-facing errors.
- `coding_exception`: For developer errors.
- `dml_exception`: For database-related errors.

## Cross-Cutting Concerns

**Logging:** Handled by the Event system (`logstore` plugins).
**Validation:** Handled by `moodleform` (`lib/formslib.php`) and `PARAM_*` constants for input cleaning.
**Authentication:** Managed via `auth` plugins (`auth/`).
**Authorization:** Controlled by the Permissions/Capabilities system (`access.php` in plugins).

---

*Architecture analysis: 2024-02-14*

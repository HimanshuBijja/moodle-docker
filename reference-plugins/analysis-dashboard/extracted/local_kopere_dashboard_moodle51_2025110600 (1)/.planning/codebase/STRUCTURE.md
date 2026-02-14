# Codebase Structure

**Analysis Date:** 2025-02-15

## Directory Layout

```
kopere_dashboard/
├── _editor/            # Embedded VvvebJs editor and save handlers
│   └── VvvebJs/        # VvvebJs core library and assets
├── amd/                # AMD (RequireJS) modules
│   ├── build/          # Minified JS files (built)
│   └── src/            # Source JS files
├── assets/             # Static assets (SCSS, CSS, images, icons)
├── classes/            # PSR-4 logic classes (namespaced)
│   ├── event/          # Moodle Event definitions
│   ├── external/       # Web service external functions
│   ├── html/           # Custom UI component builders (Table, Form, etc.)
│   ├── install/        # Installation and upgrade logic
│   ├── report/         # Reporting and benchmarking logic
│   ├── server/         # Server monitoring logic
│   ├── task/           # Scheduled tasks
│   ├── util/           # Helper/Utility classes
│   └── vo/             # Value Objects (Data entities)
├── db/                 # Moodle database and plugin definitions
├── lang/               # Language strings (English, Portuguese, etc.)
├── pix/                # Plugin icons and screenshot assets
├── templates/          # Mustache templates for UI rendering
├── autoload.php        # Custom autoloader and routing logic
├── index.php           # Entry for public-facing webpages
├── lib.php             # Moodle hooks and navigation extension
├── styles.css          # Primary compiled CSS
├── version.php         # Plugin version and requirements
├── view.php            # Main dashboard entry point (Router)
└── view-ajax.php       # Main AJAX entry point (Router)
```

## Directory Purposes

**_editor/:**
- Purpose: Contains the "Visual Webpage Builder" logic.
- Contains: Editor UI, save/load handlers, and third-party editor libraries.
- Key files: `save.php`, `index.php`.

**classes/html/:**
- Purpose: A custom UI framework for building Moodle dashboard components.
- Contains: Classes that generate HTML/Mustache output for complex widgets.
- Key files: `form.php`, `data_table.php`, `input_base.php`.

**classes/util/:**
- Purpose: Generic helpers used across the plugin.
- Contains: URL builders, JSON handlers, String manipulation, Date helpers.
- Key files: `url_util.php`, `json.php`, `dashboard_util.php`.

**amd/src/:**
- Purpose: Client-side logic.
- Contains: RequireJS modules for interactivity, DataTables initialization, and AJAX polling.
- Key files: `dashboard.js`, `monitor.js`, `dataTables_init.js`.

## Key File Locations

**Entry Points:**
- `view.php`: Main dashboard router.
- `view-ajax.php`: AJAX request router.
- `index.php`: Public webpage entry point for created pages.

**Configuration:**
- `db/install.xml`: Database schema definition.
- `db/services.php`: Web service definitions.
- `settings.php`: Admin settings UI.

**Core Logic:**
- `classes/dashboard.php`: Main dashboard controller.
- `classes/server/performancemonitor.php`: Server health monitoring logic.

**Testing:**
- `classes/report/report_benchmark_test.php`: Internal benchmarking suite.

## Naming Conventions

**Files:**
- PHP Classes: Namespaced and matching directory structure (e.g., `local_kopere_dashboard\util\url_util` in `classes/util/url_util.php`).
- Mustache Templates: `snake_case` (e.g., `dashboard_start.mustache`).

**Directories:**
- standard Moodle plugin structure: `db`, `lang`, `pix`, `templates`, `classes`.

## Where to Add New Code

**New Dashboard Feature:**
- Logic: Create a method in `classes/dashboard.php` or a new class in `classes/`.
- Template: Add to `templates/`.
- Entry: Access via `view.php?classname=...&method=...`.

**New UI Component:**
- Logic: Add to `classes/html/`.
- Template: Add to `templates/` with `html-` prefix.

**New Utility:**
- Add to `classes/util/`.

## Special Directories

**_editor/VvvebJs:**
- Purpose: Third-party visual editor library.
- Generated: No
- Committed: Yes

---

*Structure analysis: 2025-02-15*

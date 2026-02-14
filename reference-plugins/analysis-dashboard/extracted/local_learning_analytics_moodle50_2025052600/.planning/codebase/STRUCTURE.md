# Codebase Structure

**Analysis Date:** 2025-01-24

## Directory Layout

```
learning_analytics/
├── amd/                # JavaScript AMD modules
│   ├── build/          # Minified JS (generated)
│   └── src/            # Source JS
├── classes/            # Autoloaded PHP classes
│   ├── event/          # Moodle events
│   ├── local/          # Internal logic and output types
│   ├── output/         # Plugin renderer
│   ├── plugininfo/     # Subplugin type definitions
│   └── privacy/        # Privacy API implementation
├── db/                 # Database definitions (access, subplugins, etc.)
├── js/                 # Third-party JS libraries (Plotly)
├── lang/               # Language strings (en, de)
├── logs/               # lalog subplugins
├── reports/            # lareport subplugins (actual dashboard views)
│   ├── activities/
│   ├── coursedashboard/
│   ├── learners/
│   ├── quiz_assign/
│   └── weekheatmap/
├── static/             # Static assets (CSS)
├── templates/          # Mustache templates
├── Gruntfile.js        # Build task configuration
├── index.php           # Main entry point
├── lib.php             # Moodle standard hooks
├── settings.php        # Admin settings
└── version.php         # Plugin version and dependencies
```

## Directory Purposes

**`reports/`:**
- Purpose: Contains individual dashboard modules.
- Contains: Directories for each `lareport` subplugin.
- Key files: `reports/{name}/lareport_{name}.php` (Main report class).

**`classes/local/outputs/`:**
- Purpose: Definitions for UI components like plots and tables.
- Contains: `plot.php`, `table.php`, `splitter.php`.

**`amd/src/`:**
- Purpose: Frontend logic for initializing visualizations.
- Key files: `outputs.js` (Handles Plotly initialization).

## Key File Locations

**Entry Points:**
- `index.php`: Handles all dashboard requests.
- `classes/router.php`: Internal dispatcher for reports.

**Configuration:**
- `settings.php`: Admin settings for the plugin.
- `db/subplugins.php`: Defines subplugin types.

**Core Logic:**
- `classes/report_base.php`: Base class for all reports.
- `classes/output_base.php`: Base class for all output components.

## Naming Conventions

**Files:**
- Subplugin entry files: `lareport_{pluginname}.php`.
- Classes: CamelCase, namespaced according to directory (e.g., `local_learning_analyticsouter`).

**Directories:**
- Subplugin names: lowercase, snake_case (e.g., `quiz_assign`).

## Where to Add New Code

**New Report:**
- Primary code: Create a new directory in `reports/`. Include a `version.php`, `lang/`, and the main report class `lareport_{name}.php` extending `report_base`.

**New Visualization Type:**
- Implementation: Add a new class in `classes/local/outputs/` extending `output_base`.
- JS logic: Update `amd/src/outputs.js` if custom Plotly logic is needed.

**New Utility:**
- Shared helpers: `classes/local/` or within a specific subplugin's `classes/` directory.

## Special Directories

**`amd/build/`:**
- Purpose: Minified JS files.
- Generated: Yes (via Grunt).
- Committed: Yes (standard Moodle practice).

---

*Structure analysis: 2025-01-24*

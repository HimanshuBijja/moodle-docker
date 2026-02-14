# Codebase Structure

**Analysis Date:** 2025-02-15

## Directory Layout

```
edudashboard/
├── amd/                # AMD JavaScript modules
│   ├── build/          # Minified JS assets
│   └── src/            # Source JS files (main.js, charts)
├── classes/            # Autoloaded PHP classes
│   ├── extra/          # Business logic and utility classes
│   ├── output/         # Renderers and Renderables
│   ├── privacy/        # GDPR/Privacy providers
│   └── task/           # Scheduled tasks
├── db/                 # Moodle database/system configuration
├── externaljs/         # Third-party JS libraries
│   └── build/          # ApexCharts, Highcharts, etc.
├── lang/               # Language strings (en, pt)
├── localstyles/        # Additional CSS and assets
├── pix/                # Images and icons
├── templates/          # Mustache templates
├── index.php           # Main entry point
├── lib.php             # Moodle hook implementations
└── settings.php        # Admin settings definition
```

## Directory Purposes

**amd/src/:**
- Purpose: Contains modern Moodle AMD modules.
- Contains: Initialization logic and chart wrappers.
- Key files: `main.js`, `apexporgresschart.js`.

**classes/extra/:**
- Purpose: Core logic for data extraction and formatting.
- Contains: Utility and report classes.
- Key files: `util.php`, `course_report.php`.

**classes/output/:**
- Purpose: Prepares data for the view layer.
- Contains: Renderables and the plugin renderer.
- Key files: `renderer.php`, `edudashboard_renderable.php`.

**classes/task/:**
- Purpose: Background data processing.
- Contains: Scheduled task implementations.
- Key files: `site_access_data.php`, `diskusage.php`.

**db/:**
- Purpose: Defines plugin capabilities, caches, and tasks.
- Key files: `access.php`, `caches.php`, `tasks.php`.

**templates/:**
- Purpose: UI structure.
- Contains: Mustache templates.
- Key files: `edudashboard.mustache`, `coursereport.mustache`.

## Key File Locations

**Entry Points:**
- `index.php`: Main dashboard view.
- `coursereport.php`: Detailed course completion report.
- `authenticationreport.php`: Authentication logs report.

**Configuration:**
- `settings.php`: Admin settings for the plugin.
- `version.php`: Plugin metadata and requirements.

**Core Logic:**
- `classes/extra/util.php`: Central utility functions.
- `classes/extra/course_report.php`: Course-specific data logic.

**Testing:**
- Not detected.

## Naming Conventions

**Files:**
- PHP Classes: Lowercase with underscores (e.g., `course_report.php`) inside namespaced directories.
- Templates: Lowercase (e.g., `edudashboard.mustache`).
- JS Modules: Lowercase (e.g., `main.js`).

**Directories:**
- Namespaces follow Moodle's `[plugintype]_[pluginname]` structure.

## Where to Add New Code

**New Report:**
- Create a new PHP file in root (e.g., `myreport.php`).
- Create a corresponding renderable in `classes/output/`.
- Create a template in `templates/`.

**New Metric/Logic:**
- Add a method to `classes/extra/util.php` or `classes/extra/course_report.php`.
- If heavy, add a scheduled task in `classes/task/` and define it in `db/tasks.php`.

**New Chart:**
- Add third-party lib to `externaljs/`.
- Create an AMD wrapper in `amd/src/`.
- Call the wrapper from the renderable.

## Special Directories

**externaljs/:**
- Purpose: Bundled third-party libraries.
- Generated: No (Manually included).
- Committed: Yes.

---

*Structure analysis: 2025-02-15*

# Codebase Structure

**Analysis Date:** 2025-02-15

## Directory Layout

```
lmsace_reports/
├── amd/                  # JavaScript sources and builds
│   ├── build/            # Minified/built JS (Generated)
│   └── src/              # ES6 Source files
│       └── widgets/      # Widget-specific rendering logic
├── classes/              # PHP Classes (PSR-4 namespace: report_lmsace_reports)
│   ├── cache/            # Cache loader
│   ├── local/            # Local logic (Widgets and Tables)
│   │   ├── table/        # Dynamic table definitions
│   │   └── widgets/      # Data aggregation for charts
│   ├── output/           # Renderers and Renderables
│   └── privacy/          # GDPR/Privacy provider
├── db/                   # Database and API definitions
├── form/                 # Moodle Forms
├── lang/                 # Language strings (i18n)
├── templates/            # Mustache templates
│   └── widgets/          # Component templates
├── tests/                # Automated tests
│   └── behat/            # BDD scenarios
├── externallib.php       # AJAX External Functions
├── index.php             # Main entry point
├── lib.php               # Hooks and navigation
├── settings.php          # Admin configuration
├── styles.css            # Stylesheets
└── version.php           # Plugin metadata
```

## Directory Purposes

**amd/src/widgets/:**
- Purpose: Frontend rendering logic for each report widget.
- Contains: JS modules that interact with Chart.js.
- Key files: `sitevisits.js`, `courseactivity.js`.

**classes/local/widgets/:**
- Purpose: Backend data providers for widgets.
- Contains: Classes that execute SQL and format data for JSON consumption.
- Key files: `sitevisitswidget.php`, `coursevisitswidget.php`.

**classes/local/table/:**
- Purpose: Defines dynamic tables used in reports.
- Contains: Moodle Table API implementations.

**templates/widgets/:**
- Purpose: UI structure for individual dashboard components.
- Contains: Mustache files.

## Key File Locations

**Entry Points:**
- `index.php`: Main dashboard view.
- `externallib.php`: AJAX entry points for data fetching.

**Configuration:**
- `settings.php`: Controls visibility of various widgets.
- `db/services.php`: Registers AJAX functions.

**Core Logic:**
- `classes/report_helper.php`: Centralized data processing and environment checks.

**Testing:**
- `tests/behat/reports_management.feature`: Main E2E test suite.

## Naming Conventions

**Files:**
- PHP Classes: `[name].php` (must match class name).
- JS Modules: `[name].js` (lowercase).
- Templates: `[name].mustache`.

**Directories:**
- Plural nouns for groups (`widgets`, `templates`, `tests`).

## Where to Add New Code

**New Report Widget:**
- Backend data: `classes/local/widgets/newwidget.php`.
- Frontend rendering: `amd/src/widgets/newwidget.js`.
- Template: `templates/widgets/newwidget.mustache` (if needed).
- Registration: Add to `report_helper::get_default_widgets()`.

**New Data Helper:**
- Add static method to `classes/report_helper.php`.

**New Language String:**
- Add to `lang/en/report_lmsace_reports.php`.

## Special Directories

**amd/build/:**
- Purpose: Contains minified JS for production.
- Generated: Yes (via Grunt).
- Committed: Yes.

---

*Structure analysis: 2025-02-15*

# Codebase Structure

**Analysis Date:** 2025-02-15

## Directory Layout

```
quizanalytics/
├── amd/                # Asynchronous Module Definition (JS)
│   ├── build/          # Minified JS files for production
│   └── src/            # Source JS files (analytic.js, datatables.js)
├── css/                # Stylesheets (frontend.css, datatables.css)
├── db/                 # Database definitions (access.php, services.php)
├── js/                 # Third-party JS libraries (Chart.js)
├── lang/               # Localized strings
│   └── en/             # English translations
├── pix/                # Images and icons
├── externallib.php     # Web service implementation
├── index.php           # Main dashboard entry point
├── README.txt          # Plugin documentation
├── settings.php        # Admin settings definition
└── version.php         # Plugin version and requirements
```

## Directory Purposes

**quizanalytics/amd/src:**
- Purpose: Contains the core interactive logic of the plugin.
- Contains: JavaScript modules.
- Key files: `analytic.js` (Handles AJAX calls and Chart.js initialization).

**quizanalytics/db:**
- Purpose: Configuration for Moodle's database-related features.
- Contains: PHP configuration files.
- Key files: `services.php` (Registers web services), `access.php` (Defines capabilities).

**quizanalytics/lang/en:**
- Purpose: Provides all user-facing text in English.
- Contains: PHP string files.
- Key files: `gradereport_quizanalytics.php`.

## Key File Locations

**Entry Points:**
- `quizanalytics/index.php`: The main page for the quiz analytics report.

**Configuration:**
- `quizanalytics/settings.php`: Defines site-wide settings for the report.
- `quizanalytics/version.php`: Metadata about the plugin version and dependencies.

**Core Logic:**
- `quizanalytics/externallib.php`: Backend logic for data aggregation and analytics calculations.
- `quizanalytics/amd/src/analytic.js`: Frontend logic for data visualization.

**Testing:**
- Not detected. No tests folder exists.

## Naming Conventions

**Files:**
- Standard Moodle: `lowercase_with_underscores.php` for core files, but some third-party files use `CamelCase.js` (e.g., `Chart.js`).

**Directories:**
- Standard Moodle: `lowercase`.

## Where to Add New Code

**New Feature (Backend):**
- Primary code: `quizanalytics/externallib.php` (Update `moodle_gradereport_quizanalytics_external` class).
- Registration: `quizanalytics/db/services.php`.

**New Chart/Visualization:**
- Implementation: `quizanalytics/amd/src/analytic.js` (Update `analytic()` function).
- Styling: `quizanalytics/css/frontend.css`.

**Utilities:**
- Shared helpers: Should be added to a `quizanalytics/classes/` directory (following modern Moodle standards, though currently absent).

## Special Directories

**quizanalytics/amd/build:**
- Purpose: Contains minified versions of source JS.
- Generated: Yes (via Grunt).
- Committed: Yes.

---

*Structure analysis: 2025-02-15*

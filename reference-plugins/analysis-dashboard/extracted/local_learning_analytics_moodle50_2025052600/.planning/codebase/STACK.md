# Technology Stack

**Analysis Date:** 2025-01-24

## Languages

**Primary:**
- PHP 7.1+ - Core backend logic, Moodle plugin implementation.
- JavaScript (ES6) - Client-side visualizations and interactivity via AMD modules.

**Secondary:**
- CSS - Custom styling for dashboard components.
- Mustache - Templating for UI components.

## Runtime

**Environment:**
- Moodle 3.4+ (based on `requires = 2017111302` in `version.php`).

**Package Manager:**
- Composer - Managed via `composer.json`.
- Grunt - Task runner for JavaScript linting and minification.

## Frameworks

**Core:**
- Moodle Plugin API - Specifically `local` plugin type with custom subplugins (`lareport`, `lalog`).

**Testing:**
- Not detected - No `tests/` directory or PHPUnit/Behat configuration found within the plugin.

**Build/Dev:**
- Grunt - Uses `grunt-eslint` and `grunt-contrib-uglify`.

## Key Dependencies

**Critical:**
- `logstore_lanalytics` - Required for data collection; the dashboard queries its tables.
- `Plotly.js` - Used for all data visualizations (included as a third-party lib in `js/plotly.min.js`).

**Infrastructure:**
- Moodle Database API - Extensive use of SQL for analytics queries.

## Configuration

**Environment:**
- Moodle standard configuration via `settings.php` and `db/install.php`.

**Build:**
- `Gruntfile.js`: Configures JavaScript build pipeline.
- `composer.json`: Defines PHP dependencies (primarily installers).

## Platform Requirements

**Development:**
- Node.js (for Grunt tasks).
- PHP 7.1+.

**Production:**
- Moodle 3.4+ environment with `logstore_lanalytics` enabled.

---

*Stack analysis: 2025-01-24*

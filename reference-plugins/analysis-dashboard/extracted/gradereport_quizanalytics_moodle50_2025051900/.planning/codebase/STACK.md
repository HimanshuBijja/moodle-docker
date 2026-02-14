# Technology Stack

**Analysis Date:** 2025-02-15

## Languages

**Primary:**
- PHP 8.x (Standard Moodle requirement) - Backend logic, database interaction, and page rendering.

**Secondary:**
- JavaScript (ES5/ES6 via AMD) - Interactive charts and data tables.
- CSS3 - Styling for frontend components and third-party libraries.

## Runtime

**Environment:**
- Moodle 4.4+ (Requires version 2024042210 or later as per `version.php`).
- Web Server (Apache/Nginx) with PHP-FPM.

**Package Manager:**
- None detected within the plugin (relies on Moodle's core dependencies).

## Frameworks

**Core:**
- Moodle Framework - Plugin architecture (`grade/report`), Web Services API, DB API.

**Testing:**
- Not detected. No `tests/` directory present in the plugin.

**Build/Dev:**
- Grunt (Standard Moodle build tool for AMD modules) - Indicated by the presence of `amd/build` and `amd/src` directories.

## Key Dependencies

**Critical:**
- `Chart.js` (v2.x or similar, located in `js/Chart.js`) - Used for all graphical visualizations (Improvement Curve, Question Analysis, etc.).
- `DataTables` (located in `amd/src/datatables.js` and `css/datatables.css`) - Used for enhancing HTML tables with sorting and paging.

**Infrastructure:**
- Moodle `core/ajax` - Handles communication between the frontend and the `externallib.php` web service.
- Moodle `core/str` - Handles localized strings in JavaScript.

## Configuration

**Environment:**
- Configured via Moodle's standard `config.php`.
- Plugin-specific settings in `settings.php` (Cut-off percentage, Grade boundaries).

**Build:**
- Standard Moodle `thirdpartylibs.xml` (though not present, third-party JS is included directly).

## Platform Requirements

**Development:**
- Moodle development environment with PHP and MySQL/PostgreSQL.

**Production:**
- Standard Moodle production environment.

---

*Stack analysis: 2025-02-15*

# Technology Stack

**Analysis Date:** 2025-02-15

## Languages

**Primary:**
- PHP 8.0+ - Backend logic, Moodle integration, and data processing.

**Secondary:**
- JavaScript (ES6) - Frontend interactivity, chart initialization (ApexCharts, Highcharts).
- CSS/Stylus - UI styling, AdminLTE integration.

## Runtime

**Environment:**
- Moodle 4.0+ (Requires 2022041912)

**Package Manager:**
- None detected (Standard Moodle plugin structure)
- Lockfile: missing

## Frameworks

**Core:**
- Moodle Framework - Plugin architecture, database abstraction, rendering engine (Mustache).

**Frontend:**
- AdminLTE (Styles/Components) - Dashboard UI components like small-boxes.
- Bootstrap Grid 4.1.3 - Layout management.

**Charting Libraries:**
- ApexCharts 3.37.2 - Modern interactive charts.
- Chart.js 4.4.0 - Versatile charting.
- Highcharts JS 10.1.0 - Advanced charting (Commercial license noted in `thirdpartylibs.xml`).

## Key Dependencies

**Critical:**
- `core_completion` - Used for tracking user progress and course completion.
- `core_grades` - Used for retrieving user and course grades.
- `core_enrol` - Used for managing and identifying enrolled users.

**Infrastructure:**
- Moodle Universal Cache (MUC) - Used for caching heavy report data (`admininfos`, `siteaccess`).

## Configuration

**Environment:**
- Moodle Admin Settings - Configured via `settings.php` and stored in `config_plugins`.

**Build:**
- None (Uses pre-built assets in `externaljs/build/` and `amd/build/`).

## Platform Requirements

**Development:**
- Moodle development environment.
- PHP extension `gd` (likely required for some Moodle operations).

**Production:**
- Moodle LMS 4.0 or higher.
- Disk space for cache and data storage.

---

*Stack analysis: 2025-02-15*

# Technology Stack

**Analysis Date:** 2025-02-15

## Languages

**Primary:**
- PHP 7.4+ - Backend logic, Moodle integration, data aggregation.
- JavaScript (ES6) - Frontend chart rendering, AJAX interactions.

**Secondary:**
- Mustache - Templating engine for UI components.
- CSS - Custom styling for report widgets in `styles.css`.

## Runtime

**Environment:**
- Moodle 4.0 - 5.0 (Requires Moodle 2022041900+)
- Web Server (Apache/Nginx) with PHP support.

**Package Manager:**
- Moodle Plugin Manager - For installation and updates.
- npm/Grunt - Used for building AMD modules (indicated by `amd/build` directory).

## Frameworks

**Core:**
- Moodle Framework - Plugin architecture, database abstraction, output system.

**Testing:**
- Behat - BDD testing framework for end-to-end scenarios.
- Moodle CI - Automated testing workflow via GitHub Actions.

**Frontend:**
- Chart.js (Moodle core `core/chartjs`) - Used for all visual data representations.
- AMD (Asynchronous Module Definition) - Moodle's standard for modular JavaScript.

## Key Dependencies

**Critical:**
- `chartjs-plugin-datalabels` - Enhanced data labeling for Chart.js charts.
- `core/chartjs` - Moodle core library for charting.

**Infrastructure:**
- Moodle Cache API (`cache`) - Used for caching expensive operations like folder size calculations.
- Moodle Logstore (`logstore_standard_log`) - Primary data source for site visit reports.

## Configuration

**Environment:**
- Configured via Moodle's `settings.php` and `db/caches.php`.
- Requires specific capabilities defined in `db/access.php`.

**Build:**
- Standard Moodle Grunt build process for minifying JS in `amd/src` to `amd/build`.

## Platform Requirements

**Development:**
- Moodle development environment.
- Grunt (optional, for JS builds).

**Production:**
- Moodle instance (v4.0 to v5.0).
- PHP extensions required by Moodle (gd, intl, etc.).

---

*Stack analysis: 2025-02-15*

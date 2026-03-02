# Technology Stack

**Analysis Date:** 2026-03-02

## Languages
**Primary:**
- PHP 8.x - Server-side logic, Moodle plugin structure.
- JavaScript (ES6+) - Frontend interactivity via AMD modules.

**Secondary:**
- Mustache - Templating engine for UI components.
- CSS/LESS - Styling for dashboard widgets and mobile views.

## Runtime
**Environment:**
- Moodle 4.5 (Requires version 2024100100 or later).
- Web Server: Apache/Nginx.
- Database: MySQL/PostgreSQL/MariaDB (via Moodle $DB abstraction).

**Package Manager:**
- Moodle Plugin Management - Standard Moodle installation.

## Frameworks
**Core:**
- Moodle Framework - Plugin type `local`.

**Testing:**
- PHPUnit - Unit and integration testing.
- Behat - E2E functional testing.

**Build/Dev:**
- Grunt - Moodle standard for processing AMD modules and CSS.

## Key Dependencies
**Critical:**
- `core/chartjs` - Used for all data visualizations (pie, bar, line, diverging bar).
- `core/ajax` - Used for lazy-loading widget data.
- `jquery` - DOM manipulation and event handling.

## Configuration
**Environment:**
- `version.php` - Defines plugin version and Moodle requirements.
- `settings.php` - Plugin-specific settings via Moodle administration.
- `db/caches.php` - Moodle Universal Cache (MUC) definitions for performance.
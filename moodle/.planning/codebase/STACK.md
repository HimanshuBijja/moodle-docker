# Technology Stack

**Analysis Date:** 2025-02-14

## Languages

**Primary:**
- PHP 8.1+ - Core application logic, templating (Mustache), and API endpoints.

**Secondary:**
- JavaScript (ES6+) - Frontend interactivity, implemented via AMD modules and YUI (legacy).
- SCSS/CSS - Styling for themes and components.
- SQL - Database queries (supporting multiple dialects via Moodle DML).

## Runtime

**Environment:**
- PHP 8.1 (as seen in `docker-compose.yml` and `moodle/composer.json`)
- Apache (Web Server)
- Node.js >= 22.11.0 < 23 (Build-time environment for JS/CSS assets)

**Package Manager:**
- Composer - PHP dependency management (`moodle/composer.json`)
- npm - JavaScript dependency management (`moodle/package.json`)
- Lockfile: `moodle/composer.lock` and `moodle/npm-shrinkwrap.json` present.

## Frameworks

**Core:**
- Moodle Core - A bespoke PHP framework for learning management.
- Mustache - Templating engine used for UI components.

**Testing:**
- PHPUnit ^9.6.18 - Unit testing for PHP code.
- Behat 3.14.* - Behavioral testing (E2E) using Gherkin.
- Symfony Http-Client/Process - Supporting libraries for testing and integrations.

**Build/Dev:**
- Grunt ^1.6.1 - Task runner for building JS/CSS assets.
- Babel - Transpiling ES6 JS.
- Rollup - Bundling JS modules.
- Sass - Compiling SCSS to CSS.
- Stylelint/ESLint - Code quality and linting.

## Key Dependencies

**Critical:**
- `phpunit/phpunit` - Core testing framework.
- `behat/behat` - Functional testing framework.
- `symfony/process` - Managing sub-processes.
- `@babel/core` - JS transpilation.

**Infrastructure:**
- MariaDB 10.11.6 - Primary database in Docker setup.
- Redis 7 - Session and MUC (Moodle Universal Cache) storage.

**Charting & Visualization (Plugins):**
- Plotly.js 1.35.2 - Used in `local_learning_analytics` (`js/plotly.min.js`).
- Highcharts 10.1.0 / Highstock - Used in `local_edudashboard` and `block_analytics_graphs`.
- ApexCharts 3.37.2 - Used in `local_edudashboard`.
- Chart.js 4.4.0 - Used in `local_edudashboard`.

**UI Components (Plugins):**
- DataTables - Used in `local_kopere_dashboard`.
- Dropzone.js - Used in `local_kopere_dashboard`.
- jQuery / jQuery UI - Used in legacy-style plugins like `block_analytics_graphs`.
- Highslide JS - Used for image/content popups in `block_analytics_graphs`.
- Bootstrap 4 - Used in `local_kopere_dashboard` and `local_edudashboard`.

## Configuration

**Environment:**
- Configured via `moodle/config.php` (generated from `moodle/config-dist.php`).
- Key configs include `$CFG->dbtype`, `$CFG->dbname`, `$CFG->wwwroot`, `$CFG->dataroot`.

**Build:**
- `moodle/Gruntfile.js` - Main build configuration.
- `moodle/package.json` - Node scripts and dependencies.
- `moodle/composer.json` - PHP dependencies.

## Platform Requirements

**Development:**
- Docker and Docker Compose (provided in project root).
- Node.js (matching version in `.nvmrc`).

**Production:**
- Web server (Apache/Nginx) with PHP 8.1+.
- Supported Database (MySQL, MariaDB, PostgreSQL, MSSQL, or Oracle).
- Cron task for background processing (`moodle/admin/cli/cron.php`).

---

*Stack analysis: 2025-02-14*

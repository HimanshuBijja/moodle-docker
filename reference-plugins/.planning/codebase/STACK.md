# Technology Stack

**Analysis Date:** 2024-02-14

## Languages

**Primary:**
- PHP 7.x/8.x - Core plugin logic and Moodle integration. Used in all plugins (e.g., `reference-plugins/analysis-dashboard/extracted/block_analytics_graphs_moodle51_2024100201/analytics_graphs/block_analytics_graphs.php`)

**Secondary:**
- JavaScript (ES6+ / AMD) - Client-side interactions and data visualization. Used in `amd/src/` directories (e.g., `reference-plugins/analysis-dashboard/extracted/local_edudashboard_moodle45_2025042400/edudashboard/amd/src/main.js`)
- CSS/SCSS - Styling for dashboards and widgets (e.g., `reference-plugins/analysis-dashboard/extracted/local_kopere_dashboard_moodle51_2025110600 (1)/kopere_dashboard/assets/css/dashboard.scss`)
- Mustache - Templating engine for UI components (e.g., `reference-plugins/analysis-dashboard/extracted/report_lmsace_reports_moodle50_2025060200/lmsace_reports/templates/lmsace_reports.mustache`)

## Runtime

**Environment:**
- Moodle Platform (supports Moodle 4.x and 5.x as per plugin names)

**Package Manager:**
- Composer - Used for dependency management and installers.
- Lockfile: missing (standard for Moodle plugins unless using a full development environment)

## Frameworks

**Core:**
- Moodle Framework - Providing DB, Auth, Cache, and UI components.

**Testing:**
- PHPUnit - Unit testing (e.g., `reference-plugins/analysis-dashboard/extracted/report_lmsace_reports_moodle50_2025060200/lmsace_reports/tests/`)
- Behat - E2E testing (e.g., `reference-plugins/analysis-dashboard/extracted/report_lmsace_reports_moodle50_2025060200/lmsace_reports/tests/behat/reports_management.feature`)

**Build/Dev:**
- Grunt - Task runner for compiling JS/CSS (e.g., `reference-plugins/analysis-dashboard/extracted/local_learning_analytics_moodle50_2025052600/learning_analytics/Gruntfile.js`)
- GitHub Actions - CI/CD workflows (e.g., `reference-plugins/analysis-dashboard/extracted/block_analyticswidget_moodle50_2022062108/analyticswidget/.github/workflows/ci.yml`)

## Key Dependencies

**Visualization:**
- Highcharts JS (v5.0.2 - v10.1.0) - Advanced charting (used in `block_analytics_graphs`, `local_edudashboard`)
- Chart.js (v4.4.0) - Simple and flexible charting (used in `gradereport_quizanalytics`, `report_lmsace_reports`)
- ApexCharts (v3.37.2) - Modern interactive charts (used in `local_edudashboard`)
- Plotly.js (v1.35.2) - Scientific charting (used in `local_learning_analytics`)

**Data Management:**
- DataTables (v1.10.15) - Enhanced HTML tables with sorting and filtering (used in `local_kopere_dashboard`, `report_lmsace_reports`)
- JSZip (v3.10.1) - Client-side zip generation for exports.

**UI Components:**
- VvvebJs (v2.0.1) - Drag and drop website builder/editor (used in `local_kopere_dashboard`)
- TinyMCE - Rich text editing.
- Dropzone.js (v4.1.0) - Drag and drop file uploads.
- jQuery UI (v1.12.1) - UI interactions and widgets.

## Configuration

**Environment:**
- Configured via Moodle's `settings.php` and stored in Moodle's `config_plugins` table.

**Build:**
- `Gruntfile.js` for asset compilation in some plugins.
- `thirdpartylibs.xml` for tracking external library versions.

## Platform Requirements

**Development:**
- PHP, Node.js (for Grunt), Moodle development environment.

**Production:**
- Moodle-compatible web server (Apache/Nginx), Database (MySQL/PostgreSQL), PHP.

---

*Stack analysis: 2024-02-14*

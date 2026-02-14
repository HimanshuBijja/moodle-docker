# Architecture Analysis

**Analysis Date:** 2024-02-14

## Overall Patterns

- **Modular Report System (Local Plugins):** `local_learning_analytics` uses a subplugin-like architecture where individual reports are stored in a `reports/` directory. It employs a central `router.php` (`classes/router.php`) to handle requests and dispatch to specific report classes.
- **Traditional Block Structure:** `block_analytics_graphs` follows the classic Moodle block pattern with a main block class (`block_analytics_graphs.php`) and multiple top-level PHP files (e.g., `grades_chart.php`, `hits.php`) for specific views/functionalities.
- **Output & Templating:** Modern plugins (like `local_learning_analytics`) heavily use Mustache templates (`templates/`) and Renderers (`classes/output/renderer.php`), while older or more complex graphing plugins (`block_analytics_graphs`) often include Javascript and HTML generation logic within PHP files.

## Key Integration Patterns

- **External Libraries:** Plugins often bundle external JS libraries (e.g., `plotly.min.js` in `local_learning_analytics/js`, `highcharts.js` in `block_analytics_graphs/externalref`) for data visualization.
- **Events & Privacy:** Standard Moodle integration via `classes/event/` for logging and `classes/privacy/` for GDPR compliance.

## Key Abstractions

- **Router:** Dispatching requests to internal sub-components.
- **Renderers:** Separation of data processing and UI display.

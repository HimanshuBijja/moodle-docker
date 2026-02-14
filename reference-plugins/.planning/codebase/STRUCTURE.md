# Codebase Structure

**Analysis Date:** 2024-02-14

## Directory Layout (Common Patterns)

- `classes/`: Autoloaded PHP classes, often following Moodle's `[component]\[subsystem]` namespace convention.
- `db/`: Plugin metadata, including `access.php` (capabilities), `install.xml` (schema), and `upgrade.php`.
- `lang/`: Internationalization files organized by language code (e.g., `en`, `de`).
- `templates/`: `.mustache` files for UI components.
- `amd/`: Asynchronous Module Definition (AMD) Javascript files.
- `reports/` (in `local_learning_analytics`): Contains specialized report logic, each with its own `classes`, `lang`, and `version.php`.

## Key File Locations

- `version.php`: Plugin version and dependency information.
- `lib.php`: Standard Moodle hook implementations (cron, navigation, etc.).
- `settings.php`: Admin configuration settings.
- `index.php`: Main entry point for local plugins.

## Naming Conventions

- **Namespaces:** `[component]\[subsystem]` (e.g., `local_learning_analyticsouter`).
- **Templates:** Component-prefixed names.

## Where to Add New Code

- **New Report:** For `local_learning_analytics`, add a new directory under `reports/` following the existing sub-component pattern.
- **New Visualizer:** Add a class extending `output_base` in `classes/local/outputs/`.
- **New Block Feature:** Add a specific PHP file for the view and link it in `block_analytics_graphs.php`.

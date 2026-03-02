# Codebase Structure

## Directory Overview

- `amd/`: Contains JavaScript source and minified files for frontend functionality.
  - `src/`: Source JS modules (e.g., `dashboard.js`, `widget_renderer.js`).
  - `build/`: Minified JS for Moodle production use.
- `classes/`: Core PHP logic using Moodle's autoloading.
  - `external/`: External functions for AJAX calls (e.g., `get_widget_data.php`).
  - `local/`: Internal logic and widget implementations.
    - `widgets/`: Individual widget classes.
  - `output/`: Page renderers and output classes.
  - `task/`: Scheduled tasks for data aggregation.
- `db/`: Database definitions, scheduled tasks, and mobile configuration.
- `lang/`: Language strings for internationalization.
- `templates/`: Mustache templates for the UI.
- `tests/`: Behat and unit tests.

## Key Components

- **Widget System**: Managed by `classes/local/widget_registry.php` and `classes/local/base_widget.php`.
- **Frontend Rendering**: Uses `amd/src/widget_renderer.js` and `templates/widget_card.mustache`.
- **Data Aggregation**: Scheduled tasks in `classes/task/` populate caches or summary tables.
- **Entry Points**: `index.php`, `managerdashboard.php`, and `studentdashboard.php` serve as the main UI pages.
# Architecture

## High-Level Overview
Moodle is built on a highly modular, plugin-based architecture. Almost every piece of functionality is a plugin of a specific type (e.g., activity, block, theme, authentication method).

## Plugin-Based System
- **Plugin Types**: Over 50 different plugin types (activities, enrolment, repository, theme, etc.).
- **Directory Convention**: Each plugin type follows a strict naming convention and internal structure:
  - `classes/`: PSR-4 autoloaded PHP classes.
  - `db/`: Database schema (install.xml), events (events.php), and tasks (tasks.php).
  - `lang/`: Language strings for internationalization.
  - `templates/`: Mustache templates for the UI.
  - `amd/`: Client-side AMD (RequireJS) modules.
  - `tests/`: PHPUnit and Behat tests.
- **Independence**: Plugins are designed to be self-contained but can interact via core APIs.

## Core APIs & Subsystems
Moodle defines several core APIs that abstract common tasks:
- **DML (Data Manipulation Layer)**: Database interaction via the `$DB` object.
- **DDL (Data Definition Layer)**: Programmatic database schema management.
- **Access API**: Role-based access control (RBAC) via capabilities (`has_capability()`).
- **File API**: Unified management of user-uploaded and system files.
- **Output API**: Centralized rendering using Renderers and Mustache templates.
- **Events & Hooks**: A pub/sub system for inter-plugin communication.

## Execution Model
- **Bootstrap Phase**: `lib/setup.php` initializes global state (`$CFG`, `$DB`, `$USER`, `$PAGE`, `$OUTPUT`).
- **Global Variables**: Heavy reliance on these core objects for application state and services.
- **Requests**: Handled by specific PHP entry points (e.g., `index.php`, `view.php`) which call into core APIs and plugin code.

## Data Flow
1. **Request**: A PHP file is requested (e.g., `mod/assign/view.php?id=123`).
2. **Setup**: The script requires `config.php`, which bootstraps the environment.
3. **Logic**: The script uses core APIs to fetch data, check permissions, and process business logic.
4. **Output**: The script passes data to a Renderer, which uses a Mustache template to produce the HTML.

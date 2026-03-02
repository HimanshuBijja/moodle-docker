# Codebase Structure

## Core Directories
- **`admin/`**: Site administration tools, settings, and CLI scripts (`admin/cli/`).
- **`lib/`**: Core libraries, base classes, and bundled third-party libraries (e.g., `lib/aws-sdk/`).
- **`theme/`**: Layouts and styles for different user interfaces (e.g., `boost`, `classic`).
- **`lang/`**: Core language strings.
- **`pix/`**: Core icons and images.
- **`user/`**: User profile and management code.

## Plugin Repositories
- **`mod/`**: Course activities (e.g., `assign`, `forum`, `quiz`).
- **`blocks/`**: UI components that appear on the side of pages.
- **`auth/`**: Authentication methods.
- **`enrol/`**: User enrolment methods.
- **`repository/`**: External storage and media integrations.
- **`filter/`**: Content processing and transformations (e.g., `mathjaxloader`, `emoticon`).
- **`report/`**: Global reporting plugins.
- **`local/`**: Custom local modifications or utility plugins.
- **`question/`**: Question types and question bank functionality.

## Subsystems & APIs
- **`cache/`**: Cache definitions and store implementations.
- **`grade/`**: Gradebook logic and export formats.
- **`message/`**: Internal messaging and notification systems.
- **`backup/`**: Backup and restore functionality.
- **`analytics/`**: Machine learning and analysis framework.
- **`ai/`**: Placement and providers for AI services.

## Development & Build Tools
- **`.grunt/`**: Grunt task configurations.
- **`.upgradenotes/`**: Notes for developers during major upgrades.
- **`node_modules/`**: Node.js dependencies (installed via `npm`).
- **`vendor/`**: PHP dependencies (installed via `composer`).

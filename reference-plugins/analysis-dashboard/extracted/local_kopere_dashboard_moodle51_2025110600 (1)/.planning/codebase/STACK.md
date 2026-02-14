# Technology Stack

**Analysis Date:** 2025-02-15

## Languages

**Primary:**
- PHP 7.4+ - Backend logic, routing, and Moodle integration. Optimized for Moodle 5.1.

**Secondary:**
- JavaScript (ES5/ES6) - Frontend functionality using AMD/RequireJS modules.
- Mustache - Templating engine for PHP-to-HTML rendering.
- SCSS/CSS - Styling for the dashboard and embedded editor.

## Runtime

**Environment:**
- Moodle 3.11+ (2021041900) - Required Moodle version.
- Linux Environment - Required for some performance monitoring features (uses `/proc/meminfo`, `top`, `uptime`).

**Package Manager:**
- Moodle Plugin Manager - Installed via `local/kopere_dashboard`.
- Third-party libraries are bundled directly (vendor-in-repo).

## Frameworks

**Core:**
- Moodle Framework - Plugin architecture, DB API, Output API.

**Frontend:**
- RequireJS (AMD) - Module management.
- jQuery 3.x - DOM manipulation and AJAX.
- DataTables 1.10.15 - Advanced table management and reporting.

**Build/Dev:**
- Grunt (implicit) - Standard Moodle JS/CSS compilation.
- Travis CI - Configuration present in `.travis.yml`.
- GitHub Actions - Workflows in `.github/workflows/`.

## Key Dependencies

**Critical:**
- `VvvebJs` 2.0.1 - Embedded web page builder in `_editor/VvvebJs`.
- `DataTables` - Core for all reporting views in `amd/src/dataTables.js`.
- `TinyMCE` - Used within the VvvebJs editor.
- `jszip` 3.10.1 - Used for exporting DataTables to Excel.

**Infrastructure:**
- `Dropzone` 4.1.0 - File upload handling in `assets/dropzone`.
- `Bootstrap` - Embedded within the VvvebJs editor for layout components.

## Configuration

**Environment:**
- Moodle `config.php` - Standard environment settings.
- Plugin Settings - Managed via `settings.php` and `classes/settings.php`.

**Build:**
- `version.php` - Plugin metadata and requirements.
- `thirdpartylibs.xml` - Tracking of external libraries.

## Platform Requirements

**Development:**
- PHP 7.4+
- Moodle development environment.

**Production:**
- Linux-based hosting (recommended for performance monitor features).
- `shell_exec` PHP function enabled (optional, but required for server stats).

---

*Stack analysis: 2025-02-15*

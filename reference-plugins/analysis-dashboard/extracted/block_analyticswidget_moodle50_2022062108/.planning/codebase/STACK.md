# Technology Stack

**Analysis Date:** 2025-01-24

## Languages

**Primary:**
- PHP 7.3+ - Backend logic and Moodle integration
- JavaScript (ES6) - Frontend chart rendering via RequireJS

**Secondary:**
- Mustache - Templating engine for HTML output
- CSS - Styling for the block and widgets

## Runtime

**Environment:**
- Moodle 3.8+ (Required version: 2019111809)

**Package Manager:**
- Composer (implied by `moodle-plugin-ci` usage in `.github/workflows/ci.yml`)

## Frameworks

**Core:**
- Moodle Block API - Plugin structure and lifecycle
- Moodle Mobile API - Support for the Moodle App

**Testing:**
- moodle-plugin-ci - CI tool for Moodle plugins
- PHPUnit/Behat - Referenced in CI but no tests found in codebase

**Build/Dev:**
- Grunt - Task runner for linting and JS compilation (standard Moodle)

## Key Dependencies

**Critical:**
- `core/chartjs` - Moodle's wrapper for Chart.js for data visualization
- `jquery` - Standard Moodle dependency for DOM manipulation

**Infrastructure:**
- Moodle Cache API (MUC) - Used for caching statistics (`awstat` definition)

## Configuration

**Environment:**
- Moodle site configuration via `settings.php`
- Role-based configuration for teacher/student role IDs

**Build:**
- `.github/workflows/ci.yml`: GitHub Actions configuration
- `version.php`: Plugin metadata and requirements

## Platform Requirements

**Development:**
- PHP, Moodle dev environment, Grunt

**Production:**
- Moodle 3.8 or higher with Mobile App support enabled

---

*Stack analysis: 2025-01-24*

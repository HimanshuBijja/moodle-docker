# Codebase Structure

**Analysis Date:** 2024-02-14

## Directory Layout

```
moodle/
├── admin/          # Site administration pages and CLI scripts
├── ai/             # AI subsystem (introduced in newer Moodle versions)
├── analytics/      # Learning analytics core API
├── auth/           # Authentication plugins
├── blocks/         # UI blocks (widgets)
├── cache/          # Moodle Universal Cache (MUC)
├── course/         # Course management logic and UI
├── db/             # Core database schema and install scripts
├── enrol/          # Enrolment plugins
├── grade/          # Gradebook logic and report plugins
├── lang/           # Core language strings
├── lib/            # Core libraries and third-party dependencies
├── local/          # General-purpose local plugins
├── mod/            # Activity modules (Assign, Quiz, Forum, etc.)
├── pix/            # Core icons and images
├── report/         # Site-wide report plugins
├── theme/          # UI themes
└── webservice/     # Web service protocols and core logic
```

## Directory Purposes

**mod/:**
- Purpose: Contains all interactive activities.
- Contains: Folders for each activity type (e.g., `mod/assign`, `mod/quiz`).
- Key files: `lib.php` (hooks), `version.php` (metadata), `view.php` (main entry).

**lib/:**
- Purpose: Core utility functions and base classes.
- Contains: Subsystems like `dml`, `ddl`, `filestorage`, and third-party libraries.
- Key files: `moodlelib.php`, `weblib.php`, `datalib.php`.

**local/:**
- Purpose: Site-specific customizations or plugins that don't fit elsewhere.
- Contains: Custom plugin folders.
- Key files: `local_learning_analytics` (from reference plugins).

**admin/cli/:**
- Purpose: Command-line interface scripts.
- Contains: Scripts for cron, upgrade, database maintenance.

## Key File Locations

**Entry Points:**
- `index.php`: Site home page.
- `config.php`: Site configuration (generated from `config-dist.php`).
- `admin/cli/cron.php`: Background task runner.

**Configuration:**
- `version.php`: Core Moodle version.
- `lib/db/install.xml`: Core database schema.

**Core Logic:**
- `lib/moodlelib.php`: The "kitchen sink" of core functions.
- `lib/dml/moodle_database.php`: Base class for database interactions.

**Testing:**
- `lib/phpunit/`: PHPUnit framework integration.
- `lib/behat/`: Behat (E2E) framework integration.

## Naming Conventions

**Files:**
- `lib.php`: Standard hook entry point for plugins.
- `db/access.php`: Capability definitions.
- `db/events.php`: Event observers.
- `classes/*.php`: Autoloaded classes (Namespaced).

**Directories:**
- `classes/`: PSR-4-like autoloaded classes.
- `db/`: Database and metadata.
- `lang/en/`: English language strings.
- `amd/src/`: JavaScript ES6 source files.

## Where to Add New Code

**New Feature (Activity):**
- Primary code: `mod/[pluginname]/`
- Tests: `mod/[pluginname]/tests/`

**New Block:**
- Implementation: `blocks/[blockname]/`

**New API/Service:**
- Web Service: `[plugin]/externallib.php`
- Local Plugin: `local/[pluginname]/`

**Utilities:**
- Shared helpers: `lib/classes/[subsystem]/`

## Special Directories

**.planning/:**
- Purpose: GSD-specific documentation and project state.
- Generated: Yes
- Committed: Yes

**node_modules/:**
- Purpose: Build tools and JS dependencies.
- Generated: Yes (via `npm install`)
- Committed: No

---

*Structure analysis: 2024-02-14*

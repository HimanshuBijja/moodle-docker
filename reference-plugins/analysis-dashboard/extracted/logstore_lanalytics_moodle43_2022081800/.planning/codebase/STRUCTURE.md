# Codebase Structure

**Analysis Date:** 2025-02-15

## Directory Layout

```
lanalytics/
├── classes/          # Autoloaded PHP classes
│   ├── log/          # Log store implementation
│   ├── privacy/      # Privacy API implementation
│   ├── task/         # Scheduled tasks
│   └── devices.php   # Device detection logic
├── cli/              # Command-line interface scripts
├── db/               # Database schema and plugin metadata
├── lang/             # Language strings (i18n)
├── settings.php      # Admin settings definition
└── version.php       # Plugin version and requirements
```

## Directory Purposes

**classes/:**
- Purpose: Contains the core logic of the plugin using Moodle's PSR-4 like namespacing.
- Key files: `classes/log/store.php`, `classes/devices.php`.

**cli/:**
- Purpose: Maintenance and utility scripts for administrators.
- Key files: `cli/import.php` (legacy log import), `cli/test-devices.php` (UA testing).

**db/:**
- Purpose: Defines the database structure, scheduled tasks, and upgrade steps.
- Key files: `db/install.xml`, `db/tasks.php`.

**lang/en/:**
- Purpose: English language strings for the UI and admin settings.
- Key files: `lang/en/logstore_lanalytics.php`.

## Key File Locations

**Entry Points:**
- `lanalytics/classes/log/store.php`: Main logging implementation.

**Configuration:**
- `lanalytics/settings.php`: Defines settings visible in Moodle site admin.

**Core Logic:**
- `lanalytics/classes/devices.php`: Logic for device and OS detection.

## Naming Conventions

**Files:**
- Lowercase with underscores for standard Moodle files (e.g., `settings.php`, `version.php`).
- Namespaced class files follow Moodle's autoloading rules (e.g., `classes/log/store.php` for `logstore_lanalytics\log\store`).

**Directories:**
- Moodle standard directory structure for plugins.

## Where to Add New Code

**New Feature:**
- Add logic in `classes/`.
- Add settings in `settings.php`.
- Add strings in `lang/en/logstore_lanalytics.php`.

**New Task:**
- Implementation: `classes/task/`
- Declaration: `db/tasks.php`

**Utilities:**
- Add to `classes/` or as a new CLI script in `cli/`.

---

*Structure analysis: 2025-02-15*

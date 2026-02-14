# Codebase Structure

**Analysis Date:** 2024-05-24

## Directory Layout

```
moodle/
├── admin/          # System administration logic
├── auth/           # Authentication plugins
├── course/         # Course management and display logic
├── lib/            # Core libraries and autoloaded classes
├── mod/            # Activity modules (Forum, Quiz, etc.)
├── theme/          # UI Themes (Boost, Classic)
├── db/             # Core database definitions (install.xml)
└── lang/           # Language translations
```

## Directory Purposes

**moodle/lib/:**
- Purpose: Core infrastructure.
- Contains: `classes/` (PSR-compliant autoloaded classes), `moodlelib.php` (legacy common functions).

**moodle/mod/:**
- Purpose: Modular learning activities.
- Each subdirectory (e.g., `moodle/mod/assign`) is a self-contained plugin.

## Key File Locations

**Entry Points:**
- `moodle/index.php`: Site front page.
- `moodle/course/view.php`: Course home page.

**Configuration:**
- `moodle/config.php`: Site-specific configuration (database, paths).
- `moodle/version.php`: Core versioning information.

## Naming Conventions

**Classes:**
- PSR-4 namespaces corresponding to directory structure (e.g., `core_course\output` in `moodle/course/classes/output/`).

**Plugins:**
- Short, lowercase directory names.

## Where to Add New Code

**New Activity:** `moodle/mod/[pluginname]/`
**System Tool:** `moodle/admin/tool/[pluginname]/`
**Shared Logic:** `moodle/lib/classes/` or a local plugin `moodle/local/[pluginname]/`

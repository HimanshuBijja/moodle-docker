# Codebase Structure

**Analysis Date:** 2025-01-24

## Directory Layout

```
analyticswidget/
├── classes/                # Autoloaded PHP classes
│   ├── output/             # Renderers and mobile output
│   ├── widgets/            # Analytics widget implementations
│   │   ├── my/             # Dashboard widgets for students/users
│   │   └── teacher/        # Analytics for teachers
│   ├── cache.php           # Cache helper logic (if used)
│   └── widget.php          # Main widget manager and interface
├── db/                     # Moodle system integrations
│   ├── access.php          # Capabilities and permissions
│   ├── caches.php          # MUC definitions
│   ├── events.php          # Event handlers (if any)
│   ├── install.php         # Post-install logic
│   └── mobile.php          # Moodle Mobile App registration
├── lang/                   # Localization files
│   └── en/                 # English translations
├── pix/                    # Plugin icons and images
├── templates/              # Mustache templates
│   ├── my/                 # Templates for user widgets
│   └── teacher/            # Templates for teacher widgets
├── .github/                # CI/CD workflows
├── block_analyticswidget.php # Main block class
├── settings.php            # Global admin settings
├── styles.css              # Custom styling
└── version.php             # Plugin metadata
```

## Directory Purposes

**classes/widgets/:**
- Purpose: Contains the core logic for different statistics.
- Contains: PHP classes implementing `widgetfacade`.
- Key files: `classes/widgets/my/course_stats.php`, `classes/widgets/teacher/activity_stats.php`.

**templates/:**
- Purpose: Defines the HTML and JS structure for the widgets.
- Contains: Mustache files.
- Key files: `templates/widget.mustache`, `templates/my/course_stats.mustache`.

**db/:**
- Purpose: Configures the plugin's integration with Moodle core services.
- Contains: Configuration arrays for caches, mobile app, and permissions.

## Key File Locations

**Entry Points:**
- `block_analyticswidget.php`: Main entry point for the block.
- `classes/output/mobile.php`: Entry point for Moodle App requests.

**Configuration:**
- `settings.php`: Admin settings for roles and visibility.
- `version.php`: Versioning and dependency requirements.

**Core Logic:**
- `classes/widget.php`: Orchestrates data collection from widgets.

**Testing:**
- (Not detected in codebase, only referenced in CI config)

## Naming Conventions

**Files:**
- [snake_case.php]: Standard for Moodle files (e.g., `course_stats.php`).

**Directories:**
- [snake_case]: Standard for Moodle subdirectories.

## Where to Add New Code

**New Feature (Statistic):**
- Primary code: Create a new class in `classes/widgets/my/` or `classes/widgets/teacher/`.
- Template: Create a corresponding mustache file in `templates/my/` or `templates/teacher/`.

**New Component/Module:**
- Implementation: Add a new namespace under `classes/`.

**Utilities:**
- Shared helpers: `classes/` (e.g., `classes/utils.php`).

## Special Directories

**.github/workflows/:**
- Purpose: CI/CD pipeline definition.
- Generated: No.
- Committed: Yes.

---

*Structure analysis: 2025-01-24*

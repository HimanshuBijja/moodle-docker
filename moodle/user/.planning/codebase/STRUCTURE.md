# Codebase Structure

**Analysis Date:** 2025-02-12

## Directory Layout

```
moodle/user/
├── amd/                # Client-side JavaScript (AMD/ESM modules)
│   └── src/            # Source JS files for user-related UI components
├── classes/            # Modern autoloaded namespaced PHP classes
│   ├── external/       # Data Exporters and modern external API functions
│   ├── form/           # Form definitions for user preferences and profiles
│   ├── hook/           # Hook implementations for extending user functionality
│   ├── output/         # Renderable and templatable classes for the UI layer
│   └── table/          # Table builders (e.g., participants list)
├── filters/            # Filtering logic for user search and listing
├── profile/            # Custom user profile field system
│   └── field/          # Subdirectories for each field type (checkbox, text, etc.)
├── templates/          # Mustache templates for UI components
├── tests/              # Unit and Behat tests
└── [root]              # Legacy PHP controllers and core library functions
```

## Directory Purposes

**amd/:**
- Purpose: Contains modern JavaScript modules for client-side interactivity.
- Contains: ESM source files (`src/`) that are transpiled into AMD modules (`build/`).
- Key files: `user/amd/src/status_field.js` (manages enrolment status interaction).

**classes/:**
- Purpose: Modern OO layer for user-related logic.
- Contains: Various subdirectories for specialized tasks, all using the `core_user` namespace.
- Key files: `user/classes/fields.php` (field management), `user/classes/external/user_summary_exporter.php` (DSO/Exporter).

**profile/:**
- Purpose: Extensible framework for defining custom user fields.
- Contains: Core profile field API and implementations for specific field types.
- Key files: `user/profile/lib.php` (base class for fields), `user/profile/field/text/field.class.php` (text field implementation).

**filters/:**
- Purpose: Logic for filtering users in listings.
- Contains: Classes for different filter types (text, cohort, role).
- Key files: `user/filters/lib.php` (filter manager).

**templates/:**
- Purpose: Mustache templates for UI rendering.
- Contains: HTML templates with data placeholders.
- Key files: `user/templates/status_field.mustache`, `user/templates/participantsfilter.mustache`.

## Key File Locations

**Entry Points:**
- `user/view.php`: Main profile page for a specific user.
- `user/index.php`: Main participants listing page.
- `user/edit.php`: User profile editing interface.
- `user/profile.php`: Redirect and entry point for profile operations.

**Configuration:**
- `user/version.php`: Version information (standard Moodle plugin file).

**Core Logic:**
- `user/lib.php`: Legacy centralized procedural functions for CRUD.
- `user/editlib.php`: Library functions specifically for the profile editor.
- `user/profile/lib.php`: Framework for custom profile fields.

**Testing:**
- `user/tests/`: PHPUnit tests for core logic.
- `user/tests/behat/`: Feature files and step definitions for automated browser testing.

## Naming Conventions

**Files:**
- Legacy: `snake_case.php` (e.g., `user/externallib.php`).
- Modern Classes: `snake_case.php` matching the class name inside namespaced subdirectories (e.g., `user/classes/fields.php`).

**Directories:**
- Plural names for categories (e.g., `user/classes/`, `user/templates/`).

## Where to Add New Code

**New Feature:**
- Business logic: `user/classes/[feature_name].php`.
- Shared logic: `user/lib.php` (if it needs to be procedural) or `user/classes/`.
- Tests: `user/tests/[feature_name]_test.php`.

**New Component/Module:**
- Templatable class: `user/classes/output/[component_name].php`.
- Template: `user/templates/[component_name].mustache`.
- Client-side logic: `user/amd/src/[component_name].js`.

**Utilities:**
- Helper functions: `user/lib.php` or a static utility class in `user/classes/`.

## Special Directories

**amd/build/:**
- Purpose: Transpiled and minified JavaScript files.
- Generated: Yes.
- Committed: Yes (standard Moodle practice).

**tests/fixtures/:**
- Purpose: Mock data and files for tests.
- Generated: No.
- Committed: Yes.

---

*Structure analysis: 2025-02-12*

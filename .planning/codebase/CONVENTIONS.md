# Coding Conventions

## PHP Standards
- **Enforcement**: Strict Moodle-specific coding standards enforced via `PHPCS` (configuration in `phpcs.xml.dist`).
- **Autoloading**: Follows PSR-4 conventions for classes in the `classes/` directory. Namespace format: `[plugintype]_[pluginname]`.
- **Global Scope Avoidance**: While core relies on globals, new code is encouraged to use core APIs and injected dependencies where possible.
- **Naming**:
  - Functions/Variables: `lowercase_with_underscores`.
  - Classes: `lowercase_with_underscores` (historically) or `PascalCase` within namespaced `classes/` directories.
  - Constants: `UPPERCASE_WITH_UNDERSCORES`.
- **Boilerplate**: Every file must start with a standard Moodle license header (`COPYING.txt`).

## Javascript Standards
- **Enforcement**: `ESLint` (configuration in `.eslintrc`).
- **Module Pattern**: AMD (Asynchronous Module Definition) using `define()` and `require()`.
- **Modernization**: Use of ES6+ features (transpiled by Babel).
- **Placement**: AMD modules reside in `[plugin]/amd/src/` and are built to `[plugin]/amd/build/`.

## CSS/UI Standards
- **Styling**: `SASS` is the preferred way to write styles.
- **Framework**: Heavily based on Bootstrap (specifically the `Boost` theme).
- **Templates**: `Mustache` templates are used for all new UI components.
- **Icons**: FontAwesome icons are standard.

## Database Conventions
- **XML Schema**: Schema must be defined in `db/install.xml` using Moodle's XML format.
- **Upgrades**: Schema changes are implemented procedurally in `db/upgrade.php`.
- **Prefixes**: All table names are prefixed (usually `mdl_`).

## Language/Internationalization
- **Strings**: No hardcoded UI strings. All strings must be defined in `lang/[lang]/[plugin].php` and accessed via `get_string()`.
- **Encoding**: UTF-8 is mandatory.

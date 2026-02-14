# Technology Stack

**Analysis Date:** 2025-02-15

## Languages

**Primary:**
- PHP 7.0+ - Used for all plugin logic and Moodle integration.

## Runtime

**Environment:**
- Moodle 3.4+ (based on `requires = 2017111302` in `version.php`)
- PHP Runtime (Standard Moodle requirement)

**Package Manager:**
- Moodle Plugin Manager - Standard Moodle plugin installation.
- No separate `composer.json` or `package.json` in the plugin root.

## Frameworks

**Core:**
- Moodle Logging Framework (`tool_log`) - The plugin implements the `	ool_log\log\writer` interface.

**Build/Dev:**
- CLI Scripts - Custom scripts in `cli/` for maintenance and testing.

## Key Dependencies

**Critical:**
- `tool_log` - Base framework for logging in Moodle.
- `local_learning_analytics` - The companion plugin mentioned in `README.md` for the UI.

## Configuration

**Environment:**
- Moodle Site Administration - Configured via `settings.php`.

**Key configs required:**
- `logstore_lanalytics/log_scope`: Determines which courses are logged.
- `logstore_lanalytics/course_ids`: IDs for inclusion/exclusion.
- `logstore_lanalytics/tracking_roles`: Whitelist of roles.
- `logstore_lanalytics/nontracking_roles`: Blacklist of roles.

## Platform Requirements

**Development:**
- Moodle development environment.

**Production:**
- Moodle server with write access to the database.

---

*Stack analysis: 2025-02-15*

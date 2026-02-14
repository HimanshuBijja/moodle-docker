# Codebase Concerns

**Analysis Date:** 2025-02-15

## Tech Debt

**Custom Autoloader & Routing:**
- Issue: The plugin uses a custom autoloader and a `view.php` based routing system instead of standard Moodle `output` classes and flat PHP files or standard routes.
- Files: `autoload.php`, `view.php`, `view-ajax.php`.
- Impact: Makes it harder for standard Moodle developers to follow the flow; might conflict with future Moodle core autoloading improvements.
- Fix approach: Migrate to standard Moodle 4.x/5.x output components and external APIs.

**Legacy Class Aliases:**
- Issue: `autoload.php` contains hardcoded `class_alias` for renamed classes.
- Files: `autoload.php`.
- Impact: Maintenance burden and potential confusion.
- Fix approach: Perform a global search and replace for legacy class names and remove aliases.

## Known Bugs

**Shell Execution Dependency:**
- Issue: Performance monitoring depends on `shell_exec`.
- Symptoms: Dashboard stats (CPU, Memory, Disk) will show "Function 'shell_exec' disabled by hosting" on many production environments.
- Files: `classes/server/performancemonitor.php`.
- Trigger: Shared hosting or hardened VPS environments.

## Security Considerations

**Embedded Editor Save Logic:**
- Risk: `_editor/save.php` performs manual sanitization of HTML and filenames.
- Files: `_editor/save.php`.
- Current mitigation: Basic string replacement and extension checks.
- Recommendations: Replace with Moodle's `clean_text()` or a more robust security library; ensure all file operations use Moodle File API exclusively.

**Direct Database Modification by Editor:**
- Risk: Editor saves content directly to DB without thorough validation.
- Files: `_editor/save.php`.

## Performance Bottlenecks

**Disk Space Calculation:**
- Problem: `du -h` on the entire `moodledata` directory can be extremely slow and resource-intensive for large sites.
- Files: `classes/server/performancemonitor.php` (`disk_moodledata` method).
- Cause: Synchronous (or background shell) execution of `du`.
- Improvement path: Use Moodle's internal file size tracking or run this as a scheduled task and cache the result.

## Fragile Areas

**Routing system:**
- Files: `autoload.php`, `view.php`.
- Why fragile: Any typo in `classname` or `method` URL parameters results in a white screen or a standard PHP error if not carefully handled.
- Safe modification: Always test new controller methods via the full `view.php` routing path.

## Scaling Limits

**Dashboard Polling:**
- Current capacity: Multiple AJAX requests triggered on dashboard load.
- Limit: On very high-traffic sites, simultaneous dashboard access could spike DB and PHP-FPM usage.
- Scaling path: Increase cache TTL for dashboard stats in MUC.

## Dependencies at Risk

**VvvebJs & Embedded Libraries:**
- Risk: The plugin bundles a large amount of third-party JS/CSS (`_editor/VvvebJs`).
- Impact: Significant increase in plugin size; potential security vulnerabilities in old versions of bundled libraries (e.g., Bootstrap, TinyMCE versions within the editor).
- Migration plan: Consider using Moodle's built-in TinyMCE or Atto editors if possible, or move external libraries to a proper dependency management system.

## Test Coverage Gaps

**Unit Tests:**
- What's not tested: Core logic classes, utility functions, and routing.
- Files: Entire `classes/` directory.
- Risk: Regressions during upgrades (especially relevant for Moodle core updates).
- Priority: High.

---

*Concerns audit: 2025-02-15*

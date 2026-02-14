# Testing Patterns

**Analysis Date:** 2025-02-15

## Test Framework

**Runner:**
- Custom CLI Scripts.
- Moodle PHPUnit (implied, though no tests are currently present in the plugin itself).

**Run Commands:**
```bash
php cli/test-devices.php    # Run device detection tests
```

## Test File Organization

**Location:**
- CLI tests in `cli/`.

**Naming:**
- `test-*.php`.

## Test Structure

**Suite Organization:**
```php
// From cli/test-devices.php
$ualist = [
    ['Browser', 'OS', 'User-Agent-String'],
    // ...
];
foreach ($ualist as $triple) {
    // Detect and assert
}
```

## Mocking

**Framework:** Manual global state manipulation (e.g., `$_SERVER['HTTP_USER_AGENT']`).

**Patterns:**
```php
$_SERVER['HTTP_USER_AGENT'] = $ua;
$detectedbrowserid = devices::get_browser();
```

## Fixtures and Factories

**Test Data:**
- Static array of User-Agent strings in `cli/test-devices.php`.

## Coverage

**Requirements:** None enforced.

## Test Types

**Manual Integration Tests:**
- `cli/import.php` can be used for testing the import logic on a dev database.

**Unit Tests:**
- The logic in `devices.php` is tested via `cli/test-devices.php`.

## Common Patterns

**Async Testing:**
- N/A.

**Error Testing:**
- N/A.

---

*Testing analysis: 2025-02-15*

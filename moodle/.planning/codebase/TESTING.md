# Testing Patterns

**Analysis Date:** 2025-02-14

## Test Framework

**Runner:**
- **PHPUnit:** 9.6.18+ (Config: `phpunit.xml.dist`). Used for unit and integration testing.
- **Behat:** 3.14.* (Config: `behat.yml.dist`). Used for E2E browser testing.

**Assertion Library:**
- PHPUnit built-in assertions (`assertEquals`, `assertInstanceOf`, `assertCount`, etc.).

**Run Commands:**
```bash
# PHPUnit
vendor/bin/phpunit --testsuite [component_name]  # Run tests for a specific component
vendor/bin/phpunit path/to/file_test.php        # Run a specific test file

# Behat
# Requires a running selenium/chromedriver and initialized behat environment
php admin/tool/behat/cli/init.php
vendor/bin/behat path/to/feature.feature
```

## Test File Organization

**Location:**
- **PHPUnit:** `[component]/tests/` for core tests. For plugins, always use a `tests/` directory.
- **Behat:** `[component]/tests/behat/`.

**Naming:**
- **PHPUnit:** `[name]_test.php`. The class name must match the filename and end in `_test`.
- **Behat:** `[description].feature`.

**Structure:**
```
[plugin_root]/
├── tests/
│   ├── unit_test.php       # Unit tests
│   ├── integration_test.php # Integration tests
│   ├── generator/          # Custom data generators
│   └── behat/
│       └── basic_flow.feature # Behat features
```

## Test Structure

**Suite Organization:**
- Use `advanced_testcase` for most tests (enables DB and global state management).
- Use `basic_testcase` for pure unit tests that don't need Moodle's environment.

```php
namespace local_analytics;

defined('MOODLE_INTERNAL') || die();

final class processor_test extends \advanced_testcase {
    public function test_processing_logic(): void {
        $this->resetAfterTest(); // Essential for any DB changes
        $user = $this->getDataGenerator()->create_user();
        
        $processor = new \local_analytics\processor();
        $result = $processor->process_user($user->id);
        
        $this->assertEquals('expected', $result);
    }
}
```

## Mocking

**Framework:** PHPUnit built-in Mock Objects.

**Patterns:**
- Mock external services and network calls.
- Avoid mocking `$DB`. Instead, use `resetAfterTest()` and let it write to the test database.
- Use `this->setUser($user)` to mock the current global `$USER`.

## Fixtures and Factories

**Data Generators:**
- Access via `$this->getDataGenerator()`.
- Standard methods: `create_user()`, `create_course()`, `create_category()`, `create_module('assign', [...])`.
- Plugins can define custom generators in `tests/generator/lib.php`.

## Coverage

**Current State:**
- Moodle Core: High coverage in critical areas (lib, admin, auth).
- Reference Plugins: **Very low coverage.** Many analyzed plugins (`local_learning_analytics`, `block_analytics_graphs`) lack a `tests/` directory entirely.
- **Requirement:** New features in the dashboard should include PHPUnit tests covering the data processing logic.

## Test Types

**Unit Tests:**
- Test individual classes in isolation.
- No DB access if possible.

**Integration Tests:**
- Most common in Moodle.
- Verify interactions with the database and other Moodle subsystems.

**Behat (E2E):**
- Verify the UI is rendered correctly.
- Test AJAX interactions.
- Tag with `@javascript` if JS is required.

## Common Patterns

**Database State:**
- Always call `$this->resetAfterTest()` at the beginning of a test or in `setUp()`.

**Output Suppression:**
- Moodle tests often need to suppress output from functions that call `echo` or `print`.

**Expect Exceptions:**
```php
$this->expectException(\moodle_exception::class);
$this->expectExceptionMessageMatches('/invalid/i');
```

---

*Testing analysis: 2025-02-14*

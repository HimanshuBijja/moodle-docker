# Testing Patterns

**Analysis Date:** 2025-03-05

## Test Framework

**Runner:**
- PHPUnit for unit/integration tests: `advanced_testcase`.
- Behat for acceptance/E2E tests: `behat_base`.

**Assertion Library:**
- PHPUnit: PHPUnit internal assertions (e.g., `assertEquals`, `assertGreaterThanOrEqual`).
- Behat: Mink assertions and Gherkin step matching.

**Run Commands:**
```bash
# Run PHPUnit tests for the plugin
vendor/bin/phpunit --group local_analysis_dashboard

# Run Behat tests for the plugin
vendor/bin/behat --tags=@local_analysis_dashboard
```

## Test File Organization

**Location:**
- PHPUnit: `tests/` directory (e.g., `tests/widget_performance_test.php`).
- Behat: `tests/behat/` directory for `.feature` and step definitions.

**Naming:**
- PHPUnit: `[name]_test.php` (e.g., `widget_performance_test.php`).
- Behat: `[name].feature` (e.g., `dashboard_access.feature`).
- Step Definitions: `behat_local_analysis_dashboard.php`.

## Test Structure

**Suite Organization:**
```php
class widget_performance_test extends advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        // Additional setup (e.g., reset registry)
    }

    public function test_something(): void {
        // Given
        // When
        // Then
    }
}
```

**Patterns:**
- **Setup:** Use `setUp()` with `resetAfterTest(true)` to ensure isolation.
- **Teardown:** Usually handled by Moodle's `resetAfterTest`.
- **Assertion:** Direct assertions on data structures and object states.

## Mocking

**Framework:** PHPUnit Mocking (sparingly).

**Patterns:**
- Moodle's `advanced_testcase` provides methods like `getDataGenerator()` to create real objects (users, courses, cohorts) rather than mocking them.

**What to Mock:**
- External services/APIs if necessary.

**What NOT to Mock:**
- Moodle core components (use real ones with `resetAfterTest`).
- Database calls (use real database with `resetAfterTest`).

## Fixtures and Factories

**Test Data:**
```php
$user = $this->getDataGenerator()->create_user();
$course = $this->getDataGenerator()->create_course();
```

**Location:**
- `getDataGenerator()` is built into Moodle's `advanced_testcase`.
- Custom generators can be defined in `tests/generator/lib.php` (if needed).

## Coverage

**Requirements:**
- Targeted coverage for core logic and widget data retrieval.
- Focus on contract validation for widget outputs.

**View Coverage:**
```bash
vendor/bin/phpunit --group local_analysis_dashboard --coverage-html coverage/
```

## Test Types

**Unit Tests:**
- Test individual widget `get_data` and logic in isolation where possible.

**Integration Tests:**
- Test `widget_registry` and interaction with MUC (Moodle Universal Cache).
- See `tests/widget_performance_test.php`.

**Acceptance (Behat) Tests:**
- Test UI and access control in a headless browser.
- Uses `@javascript` for AJAX-heavy pages.
- See `tests/behat/`.

## Common Patterns

**Async Testing (Behat):**
- Use `@javascript` tag.
- Assertions like `should exist` wait for the element to appear.

**Error Testing:**
- `expectException(moodle_exception::class)` in PHPUnit.

**Widget Performance/Contract Validation:**
- Validate widget output against expected JSON structures.
- Ensure all registered widgets have valid names and types.

---

*Testing analysis: 2025-03-05*

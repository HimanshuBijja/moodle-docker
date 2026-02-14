# Testing Patterns

**Analysis Date:** 2025-02-14

## Test Framework

**Runner:**
- **PHPUnit:** 9.6.18+ (Config: `phpunit.xml.dist`)
- **Behat:** 3.14.* (Config: `behat.yml.dist`)

**Assertion Library:**
- PHPUnit built-in assertions.

**Run Commands:**
```bash
# PHPUnit
vendor/bin/phpunit                         # Run all tests
vendor/bin/phpunit path/to/test.php        # Run specific test

# Behat
php admin/tool/behat/cli/util.php --enable # Enable Behat
vendor/bin/behat                           # Run Behat tests

# Grunt (for linting)
grunt gherkinlint                          # Lint feature files
```

## Test File Organization

**Location:**
- PHPUnit: Co-located in `tests/` directories within components, e.g., `lib/tests/`, `admin/tests/`.
- Behat: Located in `tests/behat/` directories within components, e.g., `lib/tests/behat/`.

**Naming:**
- PHPUnit: `*_test.php`, e.g., `accesslib_test.php`.
- Behat: `*.feature`, e.g., `action_menu.feature`.

**Structure:**
```
[component]/
├── tests/
│   ├── *_test.php          # PHPUnit tests
│   ├── fixtures/           # Test fixtures
│   └── behat/
│       └── *.feature       # Behat features
```

## Test Structure

**Suite Organization:**
```php
final class accesslib_test extends advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    public function test_functionality(): void {
        // ...
    }
}
```

**Patterns:**
- **Setup:** `setUp()` method often calls `$this->resetAfterTest()` to ensure a clean database state.
- **Data Generation:** Use of `$this->getDataGenerator()` to create courses, users, and modules.
- **Mocking:** PHPUnit built-in mocking or Moodle's own test doubles.

## Mocking

**Framework:** PHPUnit built-in `createMock()`.

**Patterns:**
```php
$mock = $this->createMock(some_class::class);
$mock->method('some_method')->willReturn('some_value');
```

**What to Mock:**
- External APIs.
- Complex subsystems that are not the focus of the unit test.

**What NOT to Mock:**
- Data structures and simple value objects.
- The Database (Moodle prefers integration tests with a real test database).

## Fixtures and Factories

**Test Data:**
```php
$user = $this->getDataGenerator()->create_user();
$course = $this->getDataGenerator()->create_course();
```

**Location:**
- Fixtures are often located in `tests/fixtures/` within the component.

## Coverage

**Requirements:** High coverage is expected for new core features, though not strictly enforced by a global threshold in the repo.

**View Coverage:**
```bash
vendor/bin/phpunit --coverage-html report/
```

## Test Types

**Unit Tests:**
- Focus on individual functions or classes.
- Fast execution.

**Integration Tests:**
- Most PHPUnit tests in Moodle are integration tests as they interact with the database.
- Use `advanced_testcase` to manage state.

**E2E Tests:**
- Behat is used for browser-based testing.
- Tests user interactions and UI workflows.

## Common Patterns

**Async Testing:**
- Behat uses `@javascript` tag for tests requiring a browser with JS support.

**Error Testing:**
```php
$this->expectException(moodle_exception::class);
// ... code that throws exception
```

---

*Testing analysis: 2025-02-14*

# Testing Strategy

## Frameworks
- **PHPUnit**: Core framework for unit and integration testing.
- **Behat**: Functional/acceptance testing using Gherkin scenarios.
- **PHPCS**: Coding standards enforcement (static analysis).
- **ESLint/Stylelint**: JS and CSS linting (static analysis).

## Test Location
- **PHPUnit**: Tests are located in `[plugin]/tests/` and class names must end in `_test.php`.
- **Behat**: Scenarios are located in `[plugin]/tests/behat/` in `.feature` files.
- **Javascript**: JS tests (if present) are typically in `[plugin]/amd/tests/`.

## Running Tests
- **Environment Setup**: Requires a dedicated testing database and `dataroot` (specified in `config.php`).
- **Initialization**: 
  - PHPUnit: `php admin/tool/phpunit/cli/init.php`
  - Behat: `php admin/tool/behat/cli/init.php`
- **Execution**:
  - PHPUnit: `vendor/bin/phpunit` or via the CLI wrapper `php admin/tool/phpunit/cli/util.php`.
  - Behat: `vendor/bin/behat`.
  - Static Analysis: `npm run lint` (JS/CSS) or `vendor/bin/phpcs` (PHP).

## Test Data
- **Data Generators**: Moodle provides a comprehensive `data_generator` class to create mock courses, users, and activities for tests.
- **Database States**: Each PHPUnit test runs in a transaction that is rolled back after the test, ensuring a clean state.

## Core Suites
- Core tests are run for all core APIs (`lib/`, `user/`, `admin/`, etc.).
- Each plugin is responsible for its own test suite.

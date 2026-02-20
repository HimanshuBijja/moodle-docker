@local @local_analysis_dashboard
Feature: Dashboard access control
  As different user roles I expect appropriate access to dashboards

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | manager1 | Manager   | User     | manager1@example.com |
      | teacher1 | Teacher   | User     | teacher1@example.com |
    And the following "system role assigns" exist:
      | user     | role    |
      | manager1 | manager |

  Scenario: Admin can access the site dashboard
    Given I log in as "admin"
    When I am on the "local_analysis_dashboard > index" page
    Then I should see "Analysis Dashboard"

  Scenario: Admin can access the manager dashboard
    Given I log in as "admin"
    When I am on the "local_analysis_dashboard > managerdashboard" page
    Then I should see "Manager Analytics Dashboard"

  Scenario: Manager can access the manager dashboard
    Given I log in as "manager1"
    When I am on the "local_analysis_dashboard > managerdashboard" page
    Then I should see "Manager Analytics Dashboard"

  @javascript
  Scenario: Admin dashboard shows widget cards
    Given I log in as "admin"
    When I am on the "local_analysis_dashboard > index" page
    Then ".analysis-dashboard-widget" "css_element" should exist

@local @local_analysis_dashboard
Feature: Student personal dashboard
  As a student I can view my personal analytics

  Background:
    Given the following "courses" exist:
      | fullname    | shortname |
      | Test Course | TC1       |
    And the following "users" exist:
      | username | firstname | lastname | email              |
      | student1 | Student   | User     | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | TC1    | student |

  Scenario: Student can access personal dashboard
    Given I log in as "student1"
    When I am on the "local_analysis_dashboard > student" page
    Then I should see "My Analytics"

  @javascript
  Scenario: Student dashboard shows widget cards
    Given I log in as "student1"
    When I am on the "local_analysis_dashboard > student" page
    Then ".analysis-dashboard-widget" "css_element" should exist

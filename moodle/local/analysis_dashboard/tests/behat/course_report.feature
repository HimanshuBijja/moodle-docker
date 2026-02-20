@local @local_analysis_dashboard
Feature: Course report access
  As a teacher I can view the course analytics report for my course

  Background:
    Given the following "courses" exist:
      | fullname    | shortname |
      | Test Course | TC1       |
    And the following "users" exist:
      | username | firstname | lastname | email              |
      | teacher1 | Teacher   | User     | teacher1@example.com |
      | student1 | Student   | User     | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | TC1    | editingteacher |
      | student1 | TC1    | student        |

  Scenario: Teacher can access course report
    Given I log in as "teacher1"
    When I am on the "local_analysis_dashboard > coursereport" page with "id" "TC1"
    Then I should see "Course Report"

  @javascript
  Scenario: Course report shows widget cards
    Given I log in as "teacher1"
    When I am on the "local_analysis_dashboard > coursereport" page with "id" "TC1"
    Then ".analysis-dashboard-widget" "css_element" should exist

@local @local_analysis_dashboard
Feature: Dashboard Accessibility
  In order to view user statistics
  As an admin
  I need to be able to access the analysis dashboard

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | testuser | Test | User | testuser@example.com |

  @javascript
  Scenario: Admin can view the dashboard and use filters
    Given I log in as "admin"
    And I am on "local/analysis_dashboard/index.php" page
    Then I should see "Users per District"
    And I should see "Filter by Year"
    And "div#district-chart" should exist

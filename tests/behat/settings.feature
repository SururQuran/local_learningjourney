@local @local_learningjourney
Feature: Learning Journey administration pages are available
  In order to configure the post quiz experience
  As an administrator
  I need to reach the Learning Journey settings pages

  Background:
    Given I log in as "admin"

  Scenario: The general settings page loads
    When I navigate to "Plugins > Local plugins > Learning Journey > General" in site administration
    Then I should see "Enable Learning Journey"
    And I should see "Fallback pass mark"

  Scenario: The display and scoring settings page loads
    When I navigate to "Plugins > Local plugins > Learning Journey > Display and scoring" in site administration
    Then I should see "Star thresholds"

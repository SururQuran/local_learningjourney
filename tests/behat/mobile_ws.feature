@local @local_learningjourney @webservice
Feature: The read only result web service is available and protected
  In order to support the mobile app
  As a developer
  I need a web service that respects the same access rules as the web page

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | student1 | Sam       | Learner  | sam@example.com   |
      | teacher1 | Tara      | Teach    | tara@example.com  |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name        | course | idnumber | section | grade | attempts |
      | quiz     | Unit 1 quiz | C1     | quiz1    | 1       | 100   | 3        |
      | page     | Lesson two  | C1     | page2    | 2       |       |          |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name | questiontext   |
      | Test questions   | truefalse | TF1  | Is this true?  |
    And quiz "Unit 1 quiz" contains the following questions:
      | question | page |
      | TF1      | 1    |

  Scenario: The external function is registered
    Given I log in as "admin"
    When I navigate to "Server > Web services > API Documentation" in site administration
    Then I should see "local_learningjourney_get_result"

  Scenario: The mobile addon is declared
    Given I log in as "admin"
    When I navigate to "Plugins > Plugins overview" in site administration
    Then I should see "Learning Journey"

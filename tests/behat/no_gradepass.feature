@local @local_learningjourney
Feature: Quizzes without a pass mark use the configurable site default
  In order to give a meaningful verdict everywhere
  As an administrator
  I need a fallback pass mark that I can change without touching code

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

  @javascript
  Scenario: The shipped default pass mark of 60 percent is applied
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "60.0%"
    And I should see "Passed"

  @javascript
  Scenario: An administrator raises the pass mark to 80 percent
    Given the following config values are set as admin:
      | fallbackgradepass | 80 | local_learningjourney |
    And I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "80.0%"
    And I should see "Passed"

  Scenario: The pass mark setting rejects a value outside the permitted range
    Given I log in as "admin"
    When I navigate to "Plugins > Local plugins > Learning Journey > General" in site administration
    And I set the field "Fallback pass mark" to "150"
    And I press "Save changes"
    Then I should see "Enter a whole number between 0 and 100"

  Scenario: Turning the fallback off withholds the verdict
    Given the following config values are set as admin:
      | usefallbackgradepass | 0 | local_learningjourney |
    And I log in as "admin"
    When I navigate to "Plugins > Local plugins > Learning Journey > Quizzes without a pass mark" in site administration
    Then I should see "Unit 1 quiz"

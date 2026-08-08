@local @local_learningjourney
Feature: A learner who does not pass is encouraged rather than discouraged
  In order to keep going
  As a learner
  I need supportive feedback and the actions that are actually available

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
  Scenario: Failing a quiz shows the encouragement page with a retry
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "False" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Keep going"
    And I should see "Pass mark"
    And I should see "Attempts remaining"
    And I should see "Try again"
    And I should not see "Congratulations"

  @javascript
  Scenario: A learner with no attempts left is not offered a retry
    Given the following config values are set as admin:
      | fallbackgradepass | 60 | local_learningjourney |
    And I am on the "Unit 1 quiz" "quiz activity" page logged in as "admin"
    And I navigate to "Settings" in current page administration
    And I set the field "Attempts allowed" to "1"
    And I press "Save and return to course"
    And I log out
    And I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "False" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Keep going"
    And I should not see "Try again"
    And I should see "Return to course"

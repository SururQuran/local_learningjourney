@local @local_learningjourney
Feature: A learner result is visible only to its owner and permitted staff
  In order to protect learner data
  As a site administrator
  I need result pages to enforce ownership

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
  Scenario: A teacher may view a learner result
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    And I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Congratulations"

  Scenario: An unknown attempt identifier is refused politely
    Given I log in as "student1"
    When I am on the "999999" "local_learningjourney > Result" page
    Then I should see "That quiz attempt could not be found"

@local @local_learningjourney
Feature: A learner who passes a quiz is shown the Learning Journey page
  In order to know where to go next
  As a learner
  I need to see my result and a route onward immediately after submitting

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
  Scenario: Passing a quiz shows the celebration page and the next activity
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Congratulations"
    And I should see "Final score"
    And I should see "Passed"
    And I should see "Continue to Lesson two"
    And I should not see "Try again"

  @javascript
  Scenario: The continue button leads to the next activity
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I click on "Continue to Lesson two" "link"
    Then I should see "Lesson two"

  @javascript
  Scenario: The standard Moodle review page remains reachable
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I click on "Review quiz" "link"
    Then I should see "Is this true?"

  @javascript
  Scenario: Revisiting an old review page is not intercepted
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    And I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    When I am on the "Unit 1 quiz" "quiz activity" page
    And I follow "Review"
    Then I should see "Is this true?"
    And I should not see "Congratulations"

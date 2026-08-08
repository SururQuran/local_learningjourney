@local @local_learningjourney
Feature: An attempt awaiting marking is never given a verdict
  In order not to be told I failed an unmarked attempt
  As a learner
  I need a neutral page until marking is complete

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email           |
      | student1 | Sam       | Learner  | sam@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | name       | course | idnumber | grade |
      | quiz     | Essay quiz | C1     | quiz1    | 100   |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name   | questiontext        |
      | Test questions   | essay | Essay1 | Explain your answer |
    And quiz "Essay quiz" contains the following questions:
      | question | page |
      | Essay1   | 1    |

  @javascript
  Scenario: An essay answer produces the pending page rather than a fail
    Given I am on the "Essay quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I set the field "Answer" to "My answer"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "Answers received"
    And I should not see "Not yet passed"
    And I should not see "Congratulations"

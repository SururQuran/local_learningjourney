@local @local_learningjourney @accessibility
Feature: The Learning Journey page meets accessibility requirements
  In order to use the plugin with assistive technology
  As a learner
  I need semantic structure, keyboard access and no automatic movement

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
  Scenario: The pass page passes automated accessibility tests
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then the page should meet accessibility standards
    And "Continue to Lesson two" "link" should be visible

  @javascript
  Scenario: The fail page passes automated accessibility tests
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "False" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then the page should meet accessibility standards

  @javascript
  Scenario: No automatic movement happens by default
    Given I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I wait "15" seconds
    Then I should see "Congratulations"
    And I should not see "Stay on this page"

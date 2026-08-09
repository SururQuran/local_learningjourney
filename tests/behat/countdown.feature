@local @local_learningjourney
Feature: The automatic redirect stays cancellable
  In order not to be moved on before I am ready
  As a learner
  I need a visible countdown I can stop

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
  Scenario: The countdown appears only when enabled and can be cancelled
    Given the following config values are set as admin:
      | autoredirect  | 1  | local_learningjourney |
      | redirectdelay | 30 | local_learningjourney |
    And I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I follow "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then I should see "Stay on this page"
    When I press "Stay on this page"
    And I wait "5" seconds
    Then I should see "Congratulations"
    And I should not see "Stay on this page"

  Scenario: A countdown shorter than the accessible minimum is rejected
    Given I log in as "admin"
    When I navigate to "Plugins > Local plugins > Learning Journey > General" in site administration
    And I set the field "Redirect delay" to "3"
    And I press "Save changes"
    Then I should see "The countdown must be at least 10 seconds"

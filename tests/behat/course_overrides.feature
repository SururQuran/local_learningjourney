@local @local_learningjourney
Feature: A course may customise or switch off its Learning Journey
  In order to tailor the experience per course
  As a teacher
  I need to override site settings for my course only

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

  Scenario: A teacher sets a custom success message for one course
    Given I am on the "Course 1" course page logged in as "teacher1"
    When I navigate to "Learning Journey" in current page administration
    And I set the field "Use site default" in the "Success heading" "fieldset" to "0"
    And I set the field "Success heading" to "Ma sha Allah"
    And I press "Save changes"
    Then I should see "Changes saved"

  @javascript
  Scenario: The learner sees the course specific message
    Given I am on the "Course 1" course page logged in as "teacher1"
    And I navigate to "Learning Journey" in current page administration
    And I set the field "Use site default" in the "Success heading" "fieldset" to "0"
    And I set the field "Success heading" to "Ma sha Allah"
    And I press "Save changes"
    And I log out
    And I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I follow "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then I should see "Ma sha Allah"
    And I should not see "Congratulations"

  @javascript
  Scenario: A course may switch the plugin off entirely
    Given I am on the "Course 1" course page logged in as "teacher1"
    And I navigate to "Learning Journey" in current page administration
    And I set the field "Use site default" in the "Enable Learning Journey" "fieldset" to "0"
    And I set the field "Enable Learning Journey" to "0"
    And I press "Save changes"
    And I log out
    And I am on the "Unit 1 quiz" "quiz activity" page logged in as "student1"
    When I press "Attempt quiz"
    And I click on "True" "radio"
    And I follow "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then I should not see "Congratulations"
    And I should see "Is this true?"

  Scenario: A learner cannot reach the course override form
    Given I log in as "student1"
    When I am on the "Course 1" course page
    Then I should not see "Learning Journey"

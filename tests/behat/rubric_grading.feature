@mod @mod_quiz @quizaccess @quizaccess_essayrubric @qtype_essayrubric
Feature: Grade Essay with rubric questions in a quiz
  As a teacher
  In order to grade essays consistently
  I need to grade with the rubric, and control what students see of it

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher  | Terry     | Teacher  |
      | student  | Sam       | Student  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype       | name       | template | questiontext                |
      | Test questions   | essayrubric | Frog story | editor   | Write a story about a frog. |
    And the following "activities" exist:
      | activity | name   | course | essayrubric_alwaysshowdefinition | essayrubric_showremarksstudent |
      | quiz     | Quiz 1 | C1     | 1                                | 1                              |
      | quiz     | Quiz 2 | C1     | 0                                | 0                              |
    And quiz "Quiz 1" contains the following questions:
      | question   | page | maxmark |
      | Frog story | 1    | 3       |
    And quiz "Quiz 2" contains the following questions:
      | question   | page | maxmark |
      | Frog story | 1    | 3       |

  Scenario: Students preview the rubric if the quiz allows it
    When I am on the "Quiz 1" "mod_quiz > View" page logged in as "student"
    And I press "Attempt quiz"
    Then I should see "Rubric used for grading"
    And I should see "Argument"
    And I should see "2 points"

  Scenario: Students do not see the rubric before grading if the quiz does not allow it
    When I am on the "Quiz 2" "mod_quiz > View" page logged in as "student"
    And I press "Attempt quiz"
    Then I should see "Write a story about a frog."
    And I should not see "Rubric used for grading"
    And I should not see "Argument"

  @javascript
  Scenario: A teacher grades with the rubric and the student reviews the grading
    Given user "student" has attempted "Quiz 1" with responses:
      | slot | response                       |
      | 1    | Once upon a time, a frog sang. |
    When I am on the "Quiz 1" "mod_quiz > Manual grading report" page logged in as "teacher"
    And I click on "Grade" "link" in the "Frog story" "table_row"
    Then I should see "Select a level for every criterion to calculate the mark."
    And I click on "Strong" "radio"
    And I click on "Poor" "radio"
    And I should see "Rubric score: 2 / 3"
    And I should see "Mark: 2.00 out of 3.00"
    And I set the field "Remark for criterion 2" to "Check your apostrophes."
    And I set the field "Comment" to "A lovely story."
    And I press "Save and show next"
    And I should see "All selected attempts have been graded. Returning to the list of questions."
    And I am on the "Quiz 1 > student > Attempt 1" "mod_quiz > Attempt review" page logged in as "student"
    And I should see "2.00 out of 3.00"
    And I should see "Strong" in the ".mode-review .level.selected" "css_element"
    And I should see "Check your apostrophes."
    And I should see "A lovely story."
    And I should not see "Rubric used for grading"

  @javascript
  Scenario: Remarks are hidden from students if the quiz does not show them
    Given user "student" has attempted "Quiz 2" with responses:
      | slot | response                       |
      | 1    | Once upon a time, a frog sang. |
    When I am on the "Quiz 2" "mod_quiz > Manual grading report" page logged in as "teacher"
    And I click on "Grade" "link" in the "Frog story" "table_row"
    And I click on "Strong" "radio"
    And I click on "Good" "radio"
    And I set the field "Remark for criterion 2" to "Check your apostrophes."
    And I press "Save and show next"
    And I am on the "Quiz 2 > student > Attempt 1" "mod_quiz > Attempt review" page logged in as "student"
    Then I should see "3.00 out of 3.00"
    And I should see "Strong" in the ".mode-review .level.selected" "css_element"
    And I should not see "Check your apostrophes."

  @javascript @_switch_window
  Scenario: A teacher grades with the rubric from the attempt review page
    Given user "student" has attempted "Quiz 1" with responses:
      | slot | response                       |
      | 1    | Once upon a time, a frog sang. |
    When I am on the "Quiz 1 > student > Attempt 1" "mod_quiz > Attempt review" page logged in as "teacher"
    And I follow "Make comment or override mark"
    And I switch to "commentquestion" window
    And I click on "Adequate" "radio"
    And I click on "Good" "radio"
    And I press "Save" and switch to main window
    Then I should see "Graded with rubric 2.00 (Argument: Adequate (1); Spelling: Good (1)) with comment:"
    And I should see "Adequate" in the ".mode-review .level.selected" "css_element"

  @javascript
  Scenario: Rubric settings are saved from the quiz settings form
    # Some sites add required fields to all activity forms, e.g. local_assess_type. Turn it off (harmless if absent).
    Given the following config values are set as admin:
      | config  | value | plugin            |
      | enabled | 0     | local_assess_type |
    When I am on the "Quiz 1" "quiz activity editing" page logged in as "teacher"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Allow students to preview the rubric (otherwise it will only be displayed after grading) | 0                              |
      | Allow grader to add text remarks for each criterion                                      | 0                              |
      | Sort order for levels                                                                    | Descending by number of points |
    And I press "Save and display"
    And I am on the "Quiz 1" "quiz activity editing" page
    And I expand all fieldsets
    Then the following fields match these values:
      | Allow students to preview the rubric (otherwise it will only be displayed after grading) | 0                              |
      | Display points for each level to those being graded                                      | 1                              |
      | Allow grader to add text remarks for each criterion                                      | 0                              |
      | Sort order for levels                                                                    | Descending by number of points |
    And I should not see "Show remarks to those being graded"

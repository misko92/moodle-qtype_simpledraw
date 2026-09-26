@qtype @qtype_simpledraw @javascript
Feature: Students answer a Simple drawing question with a pen only
  In order to keep drawing questions simple for students on tablets
  As a student
  I need a canvas with just a fixed pen, eraser and undo/redo

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | student1 | S1        | Student1 |
      | teacher1 | T1        | Teacher1 |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name   | course |
      | quiz     | Quiz 1 | C1     |
    And the following "question categories" exist:
      | contextlevel    | reference | name           |
      | Activity module | Quiz 1    | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype      | name | template |
      | Test questions   | simpledraw | D1   | plain    |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | D1       | 1    |

  Scenario: A student only sees the pen, eraser and undo/redo
    Given I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    When I press "Attempt quiz"
    Then the simple drawing tool "tool_fhpath" should be "visible"
    And the simple drawing tool "tool_eraser" should be "visible"
    And the simple drawing tool "tool_undo" should be "visible"
    And the simple drawing tool "tool_redo" should be "visible"
    And the simple drawing tool "tool_select" should be "hidden"
    And the simple drawing tool "tool_text" should be "hidden"
    And the simple drawing tool "tool_highlighter" should be "hidden"
    And the simple drawing tool "tool_line" should be "hidden"
    And the simple drawing tool "tool_rect" should be "hidden"
    And the simple drawing tool "tool_ellipse" should be "hidden"
    And the simple drawing tool "fastcolorpicks" should be "hidden"
    And the simple drawing tool "preset_sizes_panel_id" should be "hidden"

  Scenario: A student's drawing is saved and the teacher can annotate it
    Given I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    When I draw a stroke on the simple drawing canvas
    Then the simple drawing canvas should contain a stroke of colour "#000000" and width "4"
    And I follow "Finish attempt ..."
    And I should see "Answer saved"
    And I press "Submit all and finish"
    And I click on "Submit" "button" in the "Submit all your answers and finish?" "dialogue"
    And I log out
    And I am on the "Quiz 1 > student1 > Attempt 1" "mod_quiz > Attempt review" page logged in as "teacher1"
    And the simple drawing review should show a student stroke of colour "#000000" and width "4"
    And the simple drawing tool "tool_saveannotation" should be "visible"
    And the simple drawing tool "tool_text" should be "visible"
    And the simple drawing tool "fastcolorpicks" should be "visible"

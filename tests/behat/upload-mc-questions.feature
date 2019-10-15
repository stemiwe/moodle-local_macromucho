@local @local_macromucho
  Feature: in order to simplify question bank creation,
            as an editingteacher
            i need to upload a list of multiple choice questions in one go.

    Background:
      Given the following "courses" exist:
        | fullname | shortname | category | groupmode |
        | Course 1 | C1        | 0        | 1         |
      And the following "users" exist:
        | username | firstname | lastname | email               |
        | teacher  | tea       | cher     | teacher@example.com |
      And the following "course enrolments" exist:
        | user    | course | role           |
        | teacher | C1     | editingteacher |

    Scenario: I find the macromucho tab
      When I log in as "teacher"
      And I am on "Course 1" course homepage
      And I navigate to "Question bank" in current page administration
      Then I should see "Mass creation of multiple choice questions"

    Scenario: I successfully upload some sample questions
      When I log in as "teacher"
      And I am on "Course 1" course homepage
      And I navigate to "Question bank" in current page administration
      And I follow "Mass creation of multiple choice questions"
      And I press "Submit"
      And I wait "3" seconds
      Then I should see "Successfully imported questions:"
      And I should see "Errors: 0"
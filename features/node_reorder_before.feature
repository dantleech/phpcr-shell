Feature: Reorder a node
    In order to change the order of a node
    As a user that is logged into the shell
    I should be able to run a command which does that

    Background:
        Given that I am logged in as "testuser"
        And the "session_data.xml" fixtures are loaded

    Scenario: Reorder a node
        Given the cnp is "/tests_general_base"
        And I execute the "node:order-before unversionable" command
        Then the command should not fail
        And there should exist a node at "/tests_general_base/simpleVersioned" before "/tests_general_base/unversionable"

Feature: Test payment results

  Scenario: Pay with Masterpass and display time
    Given I open "/" on my server
    And I select payment method Masterpass
    And I click on the pay button
    And I focus on MasterPass_frame
    And I click on the demo-success button
    And I wait 2 seconds
    Given I open "/result" on my server
    Then I see a payment with the current time


  Scenario: Payments are sorted by creation (desc)
    Given I open "/result" on my server
    Then I see the payments sorted by creation

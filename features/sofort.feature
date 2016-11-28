Feature: Test payment method Sofortueberweisung
  Scenario: Showing sofortuberweisung as selectable payment method
    When I open "/" on my server
    Then Payment method Sofort is visible

  Scenario: Send sofortueberweisung payment request with default parameters
    Given I open "/" on my server
    When  I select payment method Sofort
    And I enter the external confirmUrl
    And I click on the pay button
    And I wait 2 seconds
    And I click on the button with caption Success
    Then I get redirected to "/success"
Feature: Test payment method CreditCard
  Scenario: Showing CreditCard as selectable payment method
    When I open "/" on my server
    Then Payment method CreditCard is visible

  Scenario: Opens CreditCard modal
    When I open "/" on my server
    And I select payment method CreditCard
    And I click on the show-ccard-fields button
    Then I see the modal ds-fields

  Scenario: Showing error due to wrong credit card data
    When I open "/" on my server
    And I select payment method CreditCard
    And I click on the show-ccard-fields button
    And I wait 3 seconds
    And I fill the form with
      | field            | content          |
      | pan              | 9500000000000001 |
      | cardHolderName   | Joe Doe          |
      | expirationMonth  | 01               |
      | expirationYear   | 2000             |
      | cardVerifyCode   | 666              |
    And I click on the pay button
    Then An alert pops up

  Scenario: Successfull payment with minimum data
    When I open "/" on my server
    And I select payment method CreditCard
    And I enter the external confirmUrl
    And I click on the show-ccard-fields button
    And I wait 3 seconds
    And I fill the form with
      | field            | content          |
      | pan              | 9500000000000002 |
      | cardHolderName   | Joe Doe          |
      | expirationMonth  | 01               |
      | expirationYear   | 2020             |
      | cardVerifyCode   | 666              |
    And I click on the pay button
    And I wait 3 seconds
    Then I get redirected to "/success"
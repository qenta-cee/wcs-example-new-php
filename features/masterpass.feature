Feature: Test payment method Masterpass
  Scenario: Showing Masterpass as selectable payment method
    When I open "/" on my server
    Then Payment method Masterpass is visible

  Scenario: Add three basket items to the request
    When I open "/" on my server
    And I select payment method Masterpass
    And I click on basket item plus button
    And I click on basket item plus button
    And I click on basket item plus button
    Then 3 basket items are visible

  Scenario: Send pay request with minimal parameters
    When I open "/" on my server
    And I select payment method Masterpass
    And I fill the form with
      | field                             | content       |
      | inputTotalAmount                  | 1.00          |
      | inputTotalAmountCurrency          | EUR           |
    And I click on the pay button
    And I focus on MasterPass_frame
    And I click on the demo-success button
    And I wait 2 seconds
    Then I get redirected to "/callback" with
      | parameter                         | value         |
      | status                            | SUCCESS       |

  Scenario: Send pay request with two basket items
    When I open "/" on my server
    And I select payment method Masterpass
    And I click on basket item plus button
    And I click on basket item plus button
    And I fill the form with
      | field                             | content       |
      | inputTotalAmount                  | 2.00          |
      | inputTotalAmountCurrency          | EUR           |
      | inputArticleNumber0               | 23            |
      | inputName0                        | FirstItem     |
      | inputDescription0                 | Short desc    |
      | inputQuantity0                    | 2             |
      | inputUnitGrossAmount0             | 0.50          |
      | inputUnitGrossAmountCurrency0     | EUR           |
      | inputUnitNetAmount0               | 0.42          |
      | inputUnitNetAmountCurrency0       | EUR           |
      | inputUnitTaxAmount0               | 0.08          |
      | inputUnitTaxAmountCurrency0       | EUR           |
      | inputUnitTaxRate0                 | 19            |
      | inputArticleNumber1               | 42            |
      | inputName1                        | SecondItem    |
      | inputDescription1                 | Short descipt |
      | inputQuantity1                    | 1             |
      | inputUnitGrossAmount1             | 1.50          |
      | inputUnitGrossAmountCurrency1     | EUR           |
      | inputUnitNetAmount1               | 0.96          |
      | inputUnitNetAmountCurrency1       | EUR           |
      | inputUnitTaxAmount1               | 0.54          |
      | inputUnitTaxAmountCurrency1       | EUR           |
      | inputUnitTaxRate1                 | 40            |
    And I click on the pay button
    Then I should see MasterPass_frame
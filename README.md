# Wirecard Checkout Seamless Integration Example
[![license](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://github.com/wirecard/wcs-example-new-php/blob/master/LICENSE)
[![PHP v5.6](https://img.shields.io/badge/php-5.6-yellow.svg)](http://www.php.net)
This project demonstrates the implementation of the Wirecard Checkout Seamless API.

## Installation:
`composer install` installs all required php dependencies.
`npm install` installs all required nodejs dependencies.

## Coding Standards for PHP:
Code in this module should follow the coding standards defined in PSR-1 and PSR-2 (http://www.php-fig.org/psr/).

### PHPCS
`composer cs-check` checks for fulfillment of coding standards.

`composer cs-fix` fixes code according to coding standards.

## Test suites:
### PHPUnit
`composer test` runs full testsuite.

`composer test-reports` runs full testsuite and creates coverage reports.

### Jasmine
`npm test` runs full testsuite and creates coverage reports.

### Cucumber Selenium suits
 `npm run guitest` runs full test suits on local machine.
 
 `gulp guitest` runs allows you to configure the selenium run.
 
 Following parameters are allowed
 - **--baseUri**: the baseUri ob the test subject (e.G. http://localhost:8080)
 - **--browser**: the browser used for testing (allowed: firefox, chrome)
 - **--seleniumHost**: the host for a remote selenium host (e.G. http://localhost:4444/wd/hub)
 
 **Full example:**
 `gulp guitest --baseUri=http://localhost:8080 --browser=firefox --seleniumHost=http://localhost:4444/wd/hub`

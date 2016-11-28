var assert = require('assert');
var webdriver = require('selenium-webdriver');
var until = webdriver.until;
var by = webdriver.By;
var domurl = require('domurl');

module.exports = function () {
    this.When(/^I open "([^"]*)" on my server$/, function (path) {
        return this.driver.get(this.getUrl(path));
    });

    this.Then(/^Payment method ([^"]*) is visible$/, function (paymentMethod) {
        return this.driver.findElement({id: 'paymentMethod' + paymentMethod});
    });

    this.When(/^I click on basket item plus button$/, function () {
        this.wait(500);
        return this.driver.findElement({id: 'addItem'}).then(function (element) {
            return element.click();
        });
    });

    this.Then(/^([^"]*) basket items are visible$/, function (count) {
        this.driver.findElement({id: 'items'}).findElements({className: 'panel'}).then(function (elements) {
            return assert.equal(elements.length, count);
        });
    });

    this.When(/^I fill the form with$/, function (fields) {
        var rows = fields.hashes();
        for (var i = 0; i < rows.length; i++) {
            this.driver.findElement({id: rows[i].field}).clear();
            this.driver.findElement({id: rows[i].field}).sendKeys(rows[i].content);
        }
    });

    this.When(/^I click on the ([^"]*) button$/, function (button) {
        this.wait(500);
        return this.driver.findElement({id: button}).then(function (element) {
            return element.click();
        })
    });

    this.When(/^I click on the button with caption ([^"]*)$/, function (button) {
        return this.driver.findElement({linkText: button}).then(function (element) {
            return element.click();
        })
    });

    this.When(/^I wait ([^"]*) seconds$/, function (time) {
        this.wait(time * 1000);
    });

    this.Then(/^I see the modal ([^"]*)$/, function (element) {
        this.wait(2500);
        return this.driver.findElement({id: element}).getAttribute('class').then(function (classes) {
            assert.equal(classes, 'modal fade in')
        })
    });

    this.Then(/^I see a payment with the current time$/, function () {
        return this.driver.findElement(by.xpath("//table//tr//td[2]")).getAttribute('innerHTML').then(
            function(element) {
                var currentdate = new Date();
                var searchstring = currentdate.getFullYear() + "-" + (currentdate.getMonth()+1) + "-"
                    + ("0"+currentdate.getDate()).slice(-2) + " " + ("0"+currentdate.getHours()).slice(-2) + ":"
                    + ("0"+currentdate.getMinutes()).slice(-2);
                assert.equal(element, searchstring) });
    });

    this.Then(/^I see the payments sorted by creation$/, function () {
        return this.driver.findElements(by.xpath("//table//tr//td[2]")).then(function(elements) {
            var dateArray = [];
            for (var i = 0; i < elements.length; i++) {
                elements[i].getText().then(function(text) {
                    dateArray.push(text);
                    if(dateArray.length == elements.length ) {
                        var sortedDateArray = dateArray.slice(0);
                        sortedDateArray.sort().reverse();
                        assert.equal(dateArray.toString(), sortedDateArray.toString());
                    }
                })
            }
        });
    });


    this.Then(/^I should see ([^"]*)$/, function (element) {
        return this.driver.wait(until.elementLocated(by.id(element)), 9000);
    });

    this.Then(/^An alert pops up$/, function () {
        this.wait(500);

        var found = false;
        try {
            this.driver.switchTo().alert();
            found = true;
        }
        catch (err) {
            found = false;
        }

        assert.equal(found, true);
    });

    this.Then(/^I get redirected to "([^"]*)" with$/, function (link, getParameter) {
        return this.driver.getCurrentUrl().then(function (url) {
            return new domurl(url);
        }).then(function (url) {
            assert.equal(link, url.path);
            return url;
        }).then(function (url) {
            getParameter = getParameter.hashes();
            for (var i = 0; i < getParameter.length; i++) {
                assert.equal(url.query[getParameter[i].parameter], getParameter[i].value);
            }
            return url;
        })
    });

    this.Then(/^I get redirected to "([^"]*)"$/, function (link) {
        return this.driver.getCurrentUrl().then(function (url) {
            return new domurl(url);
        }).then(function (url) {
            assert.equal(link, url.path);
            return url;
        });
    });

    this.When(/^I focus on ([^"]*)$/, function (element) {
        var driver = this.driver;
        return this.driver.wait(until.elementLocated(by.id(element)), 9000).then(function () {
            driver.switchTo().frame(element)
        });
    });

    this.When(/^I select payment method ([^"]*)$/, function (paymentMethod) {
        return this.driver.findElement({id: 'paymentMethod' + paymentMethod}).then(function (element) {
            element.click();
        });
    });

    this.When(/^I enter the external confirmUrl$/, function () {
        this.driver.findElement({id: 'confirmUrl'}).clear();
        this.driver.findElement({id: 'confirmUrl'}).sendKeys(this.getExternalUrl('/confirm'));
    });
};
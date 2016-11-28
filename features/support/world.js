var webdriver = require('selenium-webdriver');
var chrome = require('selenium-webdriver/chrome');

function CustomWorld(options) {
    options = options || {};
    if (options.baseUri == undefined) {
        var baseUri = 'http://localhost:8080';
    } else {
        var baseUri = options.baseUri;
    }


    if(options.externalBaseUri == undefined) {
        var externalBaseUri = baseUri;
    } else {
        var externalBaseUri = options.externalBaseUri;
    }

    this.getUrl = function (path) {
        return baseUri + path;
    };

    this.getExternalUrl = function(path) {
        return externalBaseUri + path;
    };

    var builder = new webdriver.Builder();
    switch (options.browser) {
        case 'firefox':
        case 'chrome':
            builder.forBrowser(options.browser);
            break;
        default:
            builder.forBrowser('chrome');
            break;
    }

    if (options.seleniumHost != undefined) {
        builder.usingServer(options.seleniumHost);
    }
    var driver = builder.build();
    this.driver = driver;

    this.wait = function(timeout) {
        var date = new Date();
        var curDate = null;
        do { curDate = new Date(); }
        while(curDate-date < timeout);
    };
}

module.exports = function () {
    this.World = CustomWorld;
    this.setDefaultTimeout(15*1000);
};
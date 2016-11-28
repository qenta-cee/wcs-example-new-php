'use strict';
describe("Init Form", function() {
    var mockSubmitFormEvent;

    beforeEach(function() {
        mockSubmitFormEvent = new Event('submit');
        spyOn(mockSubmitFormEvent, 'preventDefault');
        spyOn($, 'post').and.callFake(function () {
            var d = $.Deferred();
            d.resolve('{"fake":"data"}');
            return d.promise();
        });
    });

    describe("submits", function() {
        beforeEach(function () {
            spyOn(app, 'paymentInitiation');
            app.submitHandler(mockSubmitFormEvent);
        });

        it("prevents form submission", function() {
            expect(mockSubmitFormEvent.preventDefault).toHaveBeenCalled();
        });

        it("calls request data post", function() {
            expect($.post).toHaveBeenCalledWith('/ajax/process-data', '');
        });

        it("calls paymentInitation method", function() {
            expect(app.paymentInitiation).toHaveBeenCalledWith('{"fake":"data"}');
        });
    });

    describe("adds item", function() {
        var mockClickItemEvent;

        beforeEach(function() {
            mockClickItemEvent = new Event('click');
            spyOn(mockClickItemEvent, 'preventDefault');

            $(document.body).html('<div style="display: block; height: 1000px;">Hi</div><div id="item__index"><p>item__index</p></div><div id="items"></div>');
        });

        it("prevents from click", function() {
            app.itemAddHandler(mockClickItemEvent);
            expect(mockClickItemEvent.preventDefault).toHaveBeenCalled();
        });

        it("appends to dom with replaced __index", function() {
            app.itemAddHandler(mockClickItemEvent);

            expect($(document.body).html()).toBe('<div style="display: block; height: 1000px;">Hi</div><div id="item__index"><p>item__index</p></div><div id="items"><div id="item1" class="panel panel-default"><p>item1</p></div></div>');
        });

        it("scrolls to item", function() {
            spyOn($.fn, 'animate');
            app.itemAddHandler(mockClickItemEvent);

            expect($.fn.animate).toHaveBeenCalledWith({ scrollTop: 1060 });
        });
    });

    describe("deletes item", function() {
        var mockClickItemEvent;

        beforeEach(function() {
            mockClickItemEvent = new Event('click');
            spyOn(mockClickItemEvent, 'preventDefault');
        });

        it("prevents from click", function() {
            app.itemDeleteHandler(mockClickItemEvent);
            expect(mockClickItemEvent.preventDefault).toHaveBeenCalled();
        });

        it("removes from dom", function() {
            spyOn($.fn, 'remove');
            app.itemDeleteHandler(mockClickItemEvent);

            expect($.fn.remove).toHaveBeenCalled();
        });
    });

    describe("paymentInitiation", function () {
        it ("calls checkoutMasterpass", function () {
            spyOn(app, "checkoutMasterpass");
            app.paymentInitiation({"paymentMethod":"MASTERPASS","data" : {"a" : "1", "b": "2"}});

            expect(app.checkoutMasterpass).toHaveBeenCalledWith( {"a": "1", "b" : "2"} );
        });

        it ("calls loadDataStorage", function () {
            spyOn(app, "loadDataStorage");
            app.paymentInitiation({"paymentMethod":"CCARD","data" : {"javascriptUrl" : "http://www.example.com", "storageId": "123"}});

            expect(app.loadDataStorage).toHaveBeenCalledWith( {"javascriptUrl" : "http://www.example.com", "storageId": "123"} );
        });

        it ("calls redirect", function () {
            spyOn(app, "redirect");
            app.paymentInitiation({"paymentMethod":"SOFORTUEBERWEISUNG","data" : {"redirectUrl" : "http://www.example.com"}});

            expect(app.redirect).toHaveBeenCalledWith( 'http://www.example.com' );
        });

        it("displays an error", function () {
           spyOn(window, 'alert');
           app.paymentInitiation({"errorCode":"42","errorMessage" : "Error occurred."});

            expect(window.alert).toHaveBeenCalled();
        });
    });


    describe("checkoutMasterpass", function() {

        beforeEach( function() {
            var WirecardCheckout = { MasterPassClient: { checkout: function( data ) { return data; }  } };
            window.WirecardCheckout = WirecardCheckout;
        });

        it ("adds callback functions", function() {
            spyOn(window.WirecardCheckout.MasterPassClient, 'checkout').and.callThrough();
            var mockData = { data: "content" };
            app.checkoutMasterpass(mockData);
            expect ( window.WirecardCheckout.MasterPassClient.checkout ).toHaveBeenCalledWith( {
                    data: "content",
                    successCallback: app.masterpassSuccessCallback,
                    cancelCallback: app.masterpassCancelCallback,
                    failureCallback: app.masterpassFailureCallback
                } );
        });

        it ("calls checkout", function () {
            spyOn(WirecardCheckout.MasterPassClient, 'checkout');
            app.checkoutMasterpass( {} );
            expect(WirecardCheckout.MasterPassClient.checkout).toHaveBeenCalled();
        });
    });

    describe("callbackFunctions", function() {
        beforeEach( function() {
            app.settings.baseUrl = "http://www.example.com";
        });

        it ("redirect successfull requests", function() {
            spyOn(app, "redirect");
            app.masterpassSuccessCallback( { walletId: 123, status: "SUCCESS"} );
            expect(app.redirect).toHaveBeenCalledWith( 'http://www.example.com/callback?walletId=123&status=SUCCESS' );
        });

        it ("redirect failed requests", function() {
            spyOn(app, "redirect");
            app.masterpassFailureCallback( { walletId: 123, status: "FAILURE"} );
            expect(app.redirect).toHaveBeenCalledWith( 'http://www.example.com/callback?walletId=123&status=FAILURE' );
        });

        it ("redirect cancel requests", function() {
            spyOn(app, "redirect");
            app.masterpassCancelCallback( { walletId: 123, status: "CANCEL"} );
            expect(app.redirect).toHaveBeenCalledWith( 'http://www.example.com/callback?walletId=123&status=CANCEL' );
        });

    });



    describe("loadDataStorage", function () {
        beforeEach(function() {
            $(document.body).html('<input id="storageId" value="">');
        });

        it ("sets storageId", function () {
            spyOn($, "getScript");
            app.loadDataStorage({"javascriptUrl" : "http://www.example.com", "storageId": "123"});

            expect($('#storageId').val()).toBe('123');
        });

        it ("calls getScript", function () {
            spyOn($, "getScript");
            app.loadDataStorage({"javascriptUrl" : "http://www.example.com", "storageId": "123"});

            expect($.getScript).toHaveBeenCalledWith( 'http://www.example.com', app.initDataStorage );
        });
    });

    describe("initDataStorage", function () {
        var mockDataStorage;
        beforeEach(function () {
            mockDataStorage = { shopId: 'test' };
            window.WirecardCEE_DataStorage = function() {
                return mockDataStorage;
            }

            $.fn.modal = function() {
                return '';
            }
        });

        it ("sets storageId", function () {

            app.initDataStorage();

            expect(app.dataStorage).toBe(mockDataStorage);
        });

        it ("opens modal", function () {

            spyOn($.fn, 'modal');

            app.initDataStorage();

            expect($.fn.modal).toHaveBeenCalledWith('show');
        });
    });

    describe("cCardSubmitHandler", function () {
        beforeEach(function () {
            $(document.body).html('<input id="pan" value="9500000000000001"><input id="expirationMonth" value="01">' +
                '<input id="expirationYear" value="2020"><input id="cardHolderName" value="Joe Test">' +
                '<input id="cardVerifyCode" value="666">');

            app.dataStorage = {
                storeCreditCardInformation: function(a, b) {
                }
            };

            spyOn(app.dataStorage, 'storeCreditCardInformation');

            app.cCardSubmitHandler(mockSubmitFormEvent);
        });

        it("prevents form submission", function() {
            expect(mockSubmitFormEvent.preventDefault).toHaveBeenCalled();
        });

        it("call storeCreditCardInformation", function() {
            expect(app.dataStorage.storeCreditCardInformation).toHaveBeenCalledWith({ pan: '9500000000000001',
                expirationMonth: '01', expirationYear: '2020', cardholdername: 'Joe Test', cardverifycode: '666' },
                app.dataStorageHandler);
        });
    });

    describe("dataStorageHandler", function () {
        it("calls form submit", function() {
            var response = {
                getStatus: function() {
                    return 0;
                }
            };

            spyOn($.fn, 'submit');

            app.dataStorageHandler(response);

            expect($.fn.submit).toHaveBeenCalled();
        });

        it("calls form submit", function() {
            var response = {
                getStatus: function() {
                    return 1;
                },
                getErrors: function() {
                    return [
                        {
                            message: 'test message 1',
                            errorCode: 100
                        },
                        {
                            message: 'test message 2',
                            errorCode: 200
                        }
                    ];
                }
            };

            spyOn(window, 'alert');
            app.dataStorageHandler(response);

            expect(window.alert).toHaveBeenCalled();
        });
    });

    describe("at payment method switch", function () {
        beforeEach(function() {
            $(document.body).html('<ul id="paymentSelection">' +
                '<li><a href="#"><input type="radio" id="a" checked="checked">a</a></li>' +
                '<li><a href="#" id="click-target"><input type="radio" id="b">b</a></li>' +
                '</ul><div id="payment-method-form">change this</div>' +
                '<div id="b-template">to this</div>');

            $('#paymentSelection').find('li').on('click', app.paymentMethodSelectHandler);
        });

        it("changes the selected button with click on link", function() {
            $('#click-target').click();

            expect($('#b').is(':checked')).toBe(true);
        });

        it("changes the selected button with click on input", function() {
            $('#b').click();

            expect($('#b').is(':checked')).toBe(true);
        });

        it("alters the payment method form", function() {
            $('#click-target').click();

            expect($(document.body).html()).toBe('<ul id="paymentSelection"><li><a href="#">' +
                '<input type="radio" id="a">a</a></li><li><a href="#" id="click-target">' +
                '<input type="radio" id="b">b</a></li></ul><div id="payment-method-form">' +
                '<div>to this</div></div><div id="b-template">to this</div>');
        });
    });
});
'use strict';
var app = {
    settings: {
        baseUrl: ''
    },
    basketItemIndex: 0,
    dataStorage: null,

    submitHandler: function (event) {
        event.preventDefault();

        $.post(app.settings.baseUrl + "/ajax/process-data", $(event.target).serialize())
            .done(app.paymentInitiation);
    },

    paymentInitiation: function (responseObject) {
        if (responseObject.hasOwnProperty('errorCode')) {
            alert("Error code: " + responseObject.errorCode + "\nError message: " + responseObject.errorMessage);
            return;
        }

        if (responseObject.paymentMethod === 'MASTERPASS') {
            app.checkoutMasterpass(responseObject.data);
        } else if (responseObject.data.hasOwnProperty('javascriptUrl')) {
            app.loadDataStorage(responseObject.data);
        } else {
            app.redirect(responseObject.data.redirectUrl);
        }
    },

    loadDataStorage: function(data) {
        $('#storageId').val(data.storageId);

        $.getScript(data.javascriptUrl, app.initDataStorage)
    },

    initDataStorage: function() {
        app.dataStorage = new WirecardCEE_DataStorage();

        $('#ds-fields').modal('show');
    },

    cCardSubmitHandler: function(event) {
        event.preventDefault();

        var paymentInformation = {
            pan: $('#pan').val(),
            expirationMonth: $('#expirationMonth').val(),
            expirationYear: $('#expirationYear').val(),
            cardholdername: $('#cardHolderName').val(),
            cardverifycode: $('#cardVerifyCode').val()
        };

        app.dataStorage.storeCreditCardInformation(paymentInformation, app.dataStorageHandler);
    },

    dataStorageHandler: function(response) {
        if (response.getStatus() == 0) {
            $('#paymentPageForm').submit();
        } else {
            var errorString = '';
            var errors = response.getErrors();
            var error;

            for (error in errors) {
                errorString += "Error " + error + ": " + errors[error].message + " (Error Code: " + errors[error].errorCode + ")\n";
            }

            alert(errorString);
        }
    },

    redirect: function(url) {
        window.location.href = url;
    },

    checkoutMasterpass: function (data) {
        data['successCallback'] = app.masterpassSuccessCallback;
        data['cancelCallback'] = app.masterpassCancelCallback;
        data['failureCallback'] = app.masterpassFailureCallback;
        WirecardCheckout.MasterPassClient.checkout( data );
    },

    masterpassSuccessCallback: function (data) {
        app.redirect(app.settings.baseUrl + "/callback?walletId=" + data['walletId'] + "&status=" + data['status']);
    },

    masterpassCancelCallback: function (data) {
        app.redirect(app.settings.baseUrl + "/callback?walletId=" + data['walletId'] + "&status=" + data['status']);
    },

    masterpassFailureCallback: function (data) {
        app.redirect(app.settings.baseUrl + "/callback?walletId=" + data['walletId'] + "&status=" + data['status']);
    },

    itemAddHandler: function (event) {
        event.preventDefault();

        var itemAsString = $('#item__index').clone().html();

        var indexReplacementRegex = new RegExp('__index', 'g');
        itemAsString = itemAsString.replace(indexReplacementRegex, app.basketItemIndex.toString());

        var $item = $('<div></div>').attr('id', 'item' + app.basketItemIndex).addClass('panel panel-default').html(itemAsString);

        $('html,body').animate({
            scrollTop: $('#items').append($item).offset().top
        });
        app.basketItemIndex++;
    },

    itemDeleteHandler: function (event) {
        event.preventDefault();

        $(event.target).closest('.panel').remove();
    },

    paymentMethodSelectHandler: function(event) {
        var $target = $(event.target);
        var $radio = null;

        if ($target.is('input')) {
            $radio = $target;
        } else {
            $('#paymentSelection').find('input[type="radio"]').removeAttr('checked');
            $radio = $target.find('input').first().prop('checked', true);
        }

        $('#payment-method-form').html( $('#' + $radio.attr('id') + '-template').clone().removeAttr('id').removeClass('hidden') );

        $('#addItem').on('click', app.itemAddHandler);
        $('#items').on('click', '.delete-item', app.itemDeleteHandler);
        $('#ccard-fields').on('submit', app.cCardSubmitHandler);
    }
};
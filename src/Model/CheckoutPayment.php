<?php

namespace Wirecard\WcsIntegrationExample\Model;

use Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister;
use WirecardCEE_QMore_ReturnFactory;

/**
 * Class for communication with the checkout-client-library.
 *
 * Class CheckoutPayment
 * @package Wirecard\WcsIntegrationExample\Model
 */
class CheckoutPayment
{
    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var PaymentDataPersister
     */
    protected $paymentDataPersister;

    /**
     * SofortPayment constructor.
     *
     * @param PaymentDataPersister $paymentDataPersister
     */
    public function __construct($clientSecret, PaymentDataPersister $paymentDataPersister = null)
    {
        $this->clientSecret = $clientSecret;
        $this->paymentDataPersister = isset($paymentDataPersister) ? $paymentDataPersister : new PaymentDataPersister();
    }

    /**
     * Validates a request and generates a confirm response string.
     *
     * If any problems occur (exception or validation error),
     * this method will return a confirm response string with content,
     * which the checkout-client-library will evaluate as error.
     *
     * If no problems occur,
     * it will return an empty confirm response string,
     * which the checkout-client-library will evaluate as success.
     *
     * @param $requestData
     * @return string
     */
    public function confirm($requestData)
    {
        try {
            $returnFactory = WirecardCEE_QMore_ReturnFactory::getInstance($requestData, $this->clientSecret);
        } catch (\Exception $e) {
            return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString($e->getMessage());
        }

        $responseString = null;
        try {
            if (!$returnFactory->validate()) {
                $requestData['validationState'] = false;
                $responseString = 'Validation error: invalid response';
            }
        } catch (\Exception $e) {
            return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString($e->getMessage());
        }

        $requestData['validationState'] = true;

        $this->paymentDataPersister->saveCheckoutPayment($requestData);
        return WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString($responseString);
    }

}
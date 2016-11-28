<?php

namespace Wirecard\WcsIntegrationExample\Service;

use DateTime;
use WirecardCEE_QMore_FrontendClient;
use WirecardCEE_QMore_Response_Initiation;
use WirecardCEE_Stdlib_ConsumerData;
use WirecardCEE_Stdlib_ConsumerData_Address;

/**
 * Class CheckoutPaymentService
 *
 * This class handles the parsing and initiation of the FrontendClient
 *
 * @package Wirecard\WcsIntegrationExample\Service
 */
class CheckoutPaymentService
{
    /**
     * @var array
     */
    protected $clientCredentials;

    /**
     * @var WirecardCEE_QMore_FrontendClient
     */
    protected $frontendClient;

    /**
     * @var string
     */
    protected $paymentMethod;

    /**
     * CheckoutPaymentService constructor.
     * @param $clientCredentials
     * @param null $frontendClient
     */
    public function __construct($clientCredentials, $frontendClient = null)
    {
        $this->clientCredentials = $clientCredentials;
        $this->frontendClient = $frontendClient;
    }

    /**
     * This method handles the FrontendClient initiation
     *
     * It first parses the requestData and sets the FrontendClient object with it. Then it will try to send the
     * initiate request.
     *
     * If the request failed or an error returned, it will return an array containing the error.
     *
     * If the request was successful, then an array with all data will be returned.
     * @param $requestData
     * @return array
     */
    public function setupPayment($requestData)
    {
        $this->parse($requestData);

        try {
            $response = $this->frontendClient->initiate();
        } catch (\Exception $e) {
            return array(
                'errorCode' => $e->getCode(),
                'errorMessage' => $e->getMessage()
            );
        }

        if ($response->getStatus() === WirecardCEE_QMore_Response_Initiation::STATE_FAILURE) {
            $errorCode = array();
            $errorMessage = array();

            foreach($response->getErrors() as $error)
            {
                $errorCode[] = $error->getErrorCode();
                $errorMessage[] = html_entity_decode($error->getConsumerMessage());
            }

            return array(
                'errorCode' => implode('| ', $errorCode),
                'errorMessage' => implode('| ', $errorMessage)
            );
        }

        return array(
            'paymentMethod' => $this->paymentMethod,
            'data' => array(
                'redirectUrl' => $response->getRedirectUrl()
            )
        );
    }

    /**
     * This method parses the requestData and fills with it the FrontendClient object
     *
     * @param $requestData
     */
    protected function parse($requestData)
    {
        $this->paymentMethod = $requestData['paymentMethod'];

        $this->initiateFrontendClient($requestData['language']);
        $this->setGeneral($requestData);
        $this->setOrder($requestData);
        $this->setConsumer($requestData);
    }

    /**
     * This method initiates a new FrontendClient if none is yet set
     *
     * @param $language
     */
    protected function initiateFrontendClient($language)
    {
        if ($this->frontendClient === null) {
            $settings = array(
                'CUSTOMER_ID' => $this->clientCredentials['customerId'],
                'SHOP_ID' => $this->clientCredentials['shopId'],
                'SECRET' => $this->clientCredentials['secret'],
                'LANGUAGE' => $language
            );

            $this->frontendClient = new WirecardCEE_QMore_FrontendClient($settings);
        }
    }

    /**
     * This method sets all general fields of the frontendClient
     *
     * @param $requestData
     */
    protected function setGeneral($requestData)
    {
        if (array_key_exists('storageId', $requestData)) {
            $this->frontendClient->setStorageId($requestData['storageId']);
        }

        $this->frontendClient->setConfirmUrl($requestData['confirmUrl'])
            ->setSuccessUrl($requestData['successUrl'])
            ->setCancelUrl($requestData['cancelUrl'])
            ->setFailureUrl($requestData['failureUrl'])
            ->setPendingUrl($requestData['pendingUrl'])
            ->setConfirmMail($requestData['confirmMail'])
            ->setServiceUrl($requestData['serviceUrl'])
            ->setPaymentType($requestData['paymentMethod']);
    }

    /**
     * This method sets all order related fields of the frontendClient
     *
     * @param $requestData
     */
    protected function setOrder($requestData)
    {
        $this->frontendClient->setAmount($requestData['amount'])
            ->setCurrency($requestData['currency'])
            ->setOrderDescription($requestData['orderDescription'])
            ->setCustomerStatement($requestData['customerStatement'])
            ->setOrderReference($requestData['orderReference'])
            ->setAutoDeposit(array_key_exists('autoDeposit', $requestData))
            ->setTransactionIdentifier($requestData['transactionIdentifier']);

        if (array_key_exists('orderIdent',$requestData)) {
            $this->frontendClient->setOrderIdent($requestData['orderIdent']);
        }
    }

    /**
     * This method sets all consumer related fields of the frontendClient
     *
     * @param $requestData
     */
    protected function setConsumer($requestData)
    {
        $consumerData = new WirecardCEE_Stdlib_ConsumerData();
        $consumerData->setEmail($requestData['consumerEmail'])
            ->setBirthDate(new DateTime($requestData['consumerBirthDate']))
            ->setIpAddress($requestData['consumerIpAddress'])
            ->setUserAgent($requestData['consumerUserAgent']);

        $consumerBilling = $this->createConsumerBilling($requestData);
        $consumerData->addAddressInformation($consumerBilling);

        $consumerShipping = $this->createConsumerShipping($requestData);
        $consumerData->addAddressInformation($consumerShipping);

        $this->frontendClient->setConsumerData($consumerData);
    }

    /**
     * This method creates a new consumerBilling object and sets its field from the $requestData
     *
     * @param $requestData
     * @return WirecardCEE_Stdlib_ConsumerData_Address
     */
    protected function createConsumerBilling($requestData)
    {
        $consumerBilling = new WirecardCEE_Stdlib_ConsumerData_Address(WirecardCEE_Stdlib_ConsumerData_Address::TYPE_BILLING);
        $consumerBilling->setFirstname($requestData['consumerBillingFirstname'])
            ->setFirstname($requestData['consumerBillingFirstname'])
            ->setLastname($requestData['consumerBillingLastname'])
            ->setAddress1($requestData['consumerBillingAddress1'])
            ->setAddress2($requestData['consumerBillingAddress2'])
            ->setCity($requestData['consumerBillingCity'])
            ->setState($requestData['consumerBillingState'])
            ->setCountry($requestData['consumerBillingCountry'])
            ->setZipCode($requestData['consumerBillingZipCode'])
            ->setPhone($requestData['consumerBillingPhone'])
            ->setFax($requestData['consumerBillingFax']);

        return $consumerBilling;
    }

    /**
     * This method creates a new consumerShipping object and sets its field from the $requestData
     *
     * @param $requestData
     * @return WirecardCEE_Stdlib_ConsumerData_Address
     */
    protected function createConsumerShipping($requestData)
    {
        $consumerShipping = new WirecardCEE_Stdlib_ConsumerData_Address(WirecardCEE_Stdlib_ConsumerData_Address::TYPE_SHIPPING);
        $consumerShipping->setFirstname($requestData['consumerShippingFirstname'])
            ->setFirstname($requestData['consumerShippingFirstname'])
            ->setLastname($requestData['consumerShippingLastname'])
            ->setAddress1($requestData['consumerShippingAddress1'])
            ->setAddress2($requestData['consumerShippingAddress2'])
            ->setCity($requestData['consumerShippingCity'])
            ->setState($requestData['consumerShippingState'])
            ->setCountry($requestData['consumerShippingCountry'])
            ->setZipCode($requestData['consumerShippingZipCode'])
            ->setPhone($requestData['consumerShippingPhone'])
            ->setFax($requestData['consumerShippingFax']);

        return $consumerShipping;
    }
}
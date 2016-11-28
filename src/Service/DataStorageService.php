<?php

namespace Wirecard\WcsIntegrationExample\Service;


use WirecardCEE_QMore_DataStorage_Response_Initiation;
use WirecardCEE_QMore_DataStorageClient;

/**
 * Class DataStorageService
 *
 * This class handles the parsing and initiation of the DataStorage
 *
 * @package Wirecard\WcsIntegrationExample\Service
 */
class DataStorageService
{
    /**
     * @var array
     */
    protected $clientCredentials;

    /**
     * @var WirecardCEE_QMore_DataStorageClient
     */
    protected $dataStorageClient;

    /**
     * DataStorageService constructor.
     * @param $clientCredentials
     * @param WirecardCEE_QMore_DataStorageClient|null $dataStorageClient
     */
    public function __construct($clientCredentials, WirecardCEE_QMore_DataStorageClient $dataStorageClient = null)
    {
        $this->clientCredentials = $clientCredentials;
        $this->dataStorageClient = $dataStorageClient;
    }

    /**
     * This method handles the DataStorage initiation
     *
     * It first parses the requestData and sets the DataStorageClient object with it. Then it will try to send the
     * initiate request.
     *
     * If the request failed or returned an error, it will return an array containing the error.
     *
     * If the request was successful, then an array with all data will be returned.
     *
     * @param $requestData
     * @return array
     */
    public function setup($requestData)
    {
        $this->initiateDataStorageClient($requestData['language']);
        $this->dataStorageClient->setReturnUrl($requestData['returnUrl'])
            ->setOrderIdent($requestData['orderIdent']);

        try {
            $response = $this->dataStorageClient->initiate();
            if ($response->getStatus() === WirecardCEE_QMore_DataStorage_Response_Initiation::STATE_FAILURE) {
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
        } catch (\Exception $e) {
            return array(
                'errorCode' => $e->getCode(),
                'errorMessage' => $e->getMessage()
            );
        }

        return array(
            'paymentMethod' => $requestData['paymentMethod'],
            'data' => array(
                'javascriptUrl' => $response->getJavascriptUrl(),
                'storageId' => $response->getStorageId()
            )
        );
    }

    /**
     * This method initiates a new DataStorageClient if none is yet set
     *
     * @param $language
     */
    protected function initiateDataStorageClient($language)
    {
        if ($this->dataStorageClient === null) {
            $settings = array(
                'CUSTOMER_ID' => $this->clientCredentials['customerId'],
                'SHOP_ID' => $this->clientCredentials['shopId'],
                'SECRET' => $this->clientCredentials['secret'],
                'LANGUAGE' => $language
            );

            $this->dataStorageClient = new WirecardCEE_QMore_DataStorageClient($settings);
        }
    }
}
<?php

namespace WirecardTest\WcsIntegrationExample\Service;


use Wirecard\WcsIntegrationExample\Service\DataStorageService;
use WirecardCEE_QMore_DataStorage_Response_Initiation;
use WirecardCEE_QMore_DataStorageClient;
use WirecardCEE_QMore_Error;
use WirecardCEE_Stdlib_Client_Exception_InvalidResponseException;

class DataStorageServiceITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataStorageService
     */
    protected $dataStorageService;

    /**
     * @var WirecardCEE_QMore_DataStorageClient
     */
    protected $dataStorageClient;

    /**
     * @var WirecardCEE_QMore_DataStorage_Response_Initiation
     */
    protected $responseInitiation;

    public function setUp()
    {
        $clientCredentials = array(
            'customerId' => 'D200001',
            'shopId' => '',
            'secret' => 'mypersonalsecret'
        );

        $this->dataStorageClient = $this->createPartialMock('\WirecardCEE_QMore_DataStorageClient', array('initiate'));

        $this->responseInitiation = $this->createMock('WirecardCEE_QMore_DataStorage_Response_Initiation');
        $this->dataStorageService = new DataStorageService($clientCredentials, $this->dataStorageClient);
    }

    public function testSetupSuccess()
    {
        $requestData = $this->getRequestData();

        $this->responseInitiation->method('getStatus')->willReturn(0);
        $this->responseInitiation->method('getJavascriptUrl')->willReturn('http://www.wirecard.at');
        $this->responseInitiation->method('getStorageId')->willReturn('abc123');
        $this->dataStorageClient->method('initiate')->willReturn($this->responseInitiation);

        $result = array(
            'paymentMethod' => $requestData['paymentMethod'],
            'data' => array(
                'javascriptUrl' => 'http://www.wirecard.at',
                'storageId' => 'abc123'
            )
        );

        $this->assertEquals($result, $this->dataStorageService->setup($this->getRequestData()));
    }

    public function testSetupInitiationError()
    {
        $this->dataStorageClient->method('initiate')->will(
            $this->throwException(new WirecardCEE_Stdlib_Client_Exception_InvalidResponseException('bad luck', 666))
        );

        $result = array(
            'errorCode' => 666,
            'errorMessage' => 'bad luck'
        );

        $this->assertEquals($result, $this->dataStorageService->setup($this->getRequestData()));
    }

    public function testSetupReturnedError()
    {
        $error1 = new WirecardCEE_QMore_Error(100, 'first error');
        $error1->setConsumerMessage('first error');

        $error2 = new WirecardCEE_QMore_Error(200, 'second error');
        $error2->setConsumerMessage('second error');

        $errors = array(
            $error1,
            $error2,
        );
        $this->responseInitiation->method('getErrors')->willReturn($errors);
        $this->responseInitiation->method('getStatus')->willReturn(1);
        $this->dataStorageClient->method('initiate')->willReturn($this->responseInitiation);

        $result = array(
            'errorCode' => '100| 200',
            'errorMessage' => 'first error| second error'
        );

        $this->assertEquals($result ,$this->dataStorageService->setup($this->getRequestData()));
    }

    protected function getRequestData()
    {
        return array(
            'language' => 'en',
            'returnUrl' => 'http://www.example.com',
            'orderIdent' => '123MyOrder',
            'paymentMethod' => 'CCARD'
        );
    }
}

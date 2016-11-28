<?php

namespace WirecardTest\WcsIntegrationExample\Service;


use Wirecard\WcsIntegrationExample\Service\CheckoutPaymentService;
use WirecardCEE_QMore_Error;
use WirecardCEE_QMore_FrontendClient;
use WirecardCEE_QMore_Response_Initiation;
use WirecardCEE_Stdlib_Client_Exception_InvalidResponseException;

class CheckoutPaymentServiceITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CheckoutPaymentService
     */
    protected $checkoutPaymentService;

    /**
     * @var WirecardCEE_QMore_FrontendClient
     */
    protected $frontendClient;

    /**
     * @var WirecardCEE_QMore_Response_Initiation
     */
    protected $responseInitiation;

    public function setUp()
    {
        $clientCredentials = array(
            'customerId' => 'D200001',
            'shopId' => '',
            'secret' => 'mypersonalsecret'
        );

        $this->frontendClient = $this->createPartialMock('\WirecardCEE_QMore_FrontendClient', array('initiate'));

        $this->responseInitiation = $this->createMock('\WirecardCEE_QMore_Response_Initiation');

        $this->checkoutPaymentService = new CheckoutPaymentService($clientCredentials, $this->frontendClient);
    }

    public function testSetupPaymentSetsAllFields()
    {
        $this->responseInitiation->method('getStatus')->willReturn(0);
        $this->frontendClient->method('initiate')->willReturn($this->responseInitiation);

        $this->checkoutPaymentService->setupPayment($this->getRequestData());
        $this->assertEquals($this->getRequestData(true), $this->frontendClient->getRequestData());
    }

    public function testSetupPaymentSuccess()
    {
        $this->responseInitiation->method('getStatus')->willReturn(0);
        $this->responseInitiation->method('getRedirectUrl')->willReturn('http://www.wirecard.at');
        $this->frontendClient->method('initiate')->willReturn($this->responseInitiation);

        $result = array(
            'paymentMethod' => 'SOFORTUEBERWEISUNG',
            'data' => array(
                'redirectUrl' => 'http://www.wirecard.at'
            )
        );

        $this->assertEquals($result ,$this->checkoutPaymentService->setupPayment($this->getRequestData()));
    }

    public function testSetupPaymentInitiationError()
    {
        $this->frontendClient->method('initiate')->will(
            $this->throwException(new WirecardCEE_Stdlib_Client_Exception_InvalidResponseException('bad luck', 666))
        );

        $result = array(
            'errorCode' => 666,
            'errorMessage' => 'bad luck'
        );

        $this->assertEquals($result ,$this->checkoutPaymentService->setupPayment($this->getRequestData()));
    }

    public function testSetupPaymentReturnedError()
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
        $this->frontendClient->method('initiate')->willReturn($this->responseInitiation);

        $result = array(
            'errorCode' => '100| 200',
            'errorMessage' => 'first error| second error'
        );

        $this->assertEquals($result ,$this->checkoutPaymentService->setupPayment($this->getRequestData()));
    }

    protected function getRequestData($compare = false)
    {
        $requestData = array(
            'language' => 'en',
            'paymentMethod' => 'SOFORTUEBERWEISUNG',
            'confirmUrl' => 'http://www.example.com/confirm',
            'successUrl' => 'http://www.example.com/success',
            'cancelUrl' => 'http://www.example.com/cancel',
            'failureUrl' => 'http://www.example.com/failure',
            'pendingUrl' => 'http://www.example.com/pending',
            'confirmMail' => 'joe.test@example.com',
            'serviceUrl' => 'http://www.example.com/imprint',
            'amount' => '1.0',
            'currency' => 'EUR',
            'orderDescription' => 'My desc',
            'customerStatement' => 'my statement',
            'orderReference' => '12345',
            'orderIdent' => '12345',
            'autoDeposit' => true,
            'transactionIdentifier' => 'SINGLE',
            'consumerEmail' => 'joe.doe@example.com',
            'consumerBirthDate' => '1991-01-01',
            'consumerIpAddress' => '127.0.0.1',
            'consumerUserAgent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36',
            'consumerBillingFirstname' => 'Joe',
            'consumerBillingLastname' => 'Doe',
            'consumerBillingAddress1' => 'Examplestreet',
            'consumerBillingAddress2' => '13b',
            'consumerBillingCity' => 'Vienna',
            'consumerBillingState' => 'Vienna',
            'consumerBillingCountry' => 'AT',
            'consumerBillingZipCode' => '1000',
            'consumerBillingPhone' => '00433165145688',
            'consumerBillingFax' => '00433165145688',
            'consumerShippingFirstname' => 'Joe',
            'consumerShippingLastname' => 'Doe',
            'consumerShippingAddress1' => 'Examplestreet',
            'consumerShippingAddress2' => '13b',
            'consumerShippingCity' => 'Vienna',
            'consumerShippingState' => 'Vienna',
            'consumerShippingCountry' => 'AT',
            'consumerShippingZipCode' => '1000',
            'consumerShippingPhone' => '00433165145688',
            'consumerShippingFax' => '00433165145688',
            'storageId' => 'abc123'
        );

        if ($compare) {
            $requestData['paymentType'] = $requestData['paymentMethod'];

            if ($requestData['autoDeposit']) {
                $requestData['autoDeposit'] = 'yes';
            } else {
                unset($requestData['autoDeposit']);
            }

            unset($requestData['language'], $requestData['paymentMethod']);
        }

        return $requestData;
    }
}

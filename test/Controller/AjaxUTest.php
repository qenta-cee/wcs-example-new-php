<?php

namespace WirecardTest\WcsIntegrationExample\Controller;

use Wirecard\WcsIntegrationExample\Model\MasterpassPayment;
use Wirecard\WcsIntegrationExample\Service\CheckoutPaymentService;
use Wirecard\WcsIntegrationExample\Service\DataStorageService;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Wirecard\WcsIntegrationExample\Controller\Ajax;
use WirecardCheckoutApiClient\Entity\MasterPass\Wallet;
use WirecardCheckoutApiClient\Exception\RuntimeException;
use WirecardCheckoutApiClient\Service\MasterPass\WalletResourceService;

class AjaxUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Ajax
     */
    protected $ajax;

    /**
     * @var MasterpassPayment
     */
    protected $masterpassPayment;

    /**
     * @var CheckoutPaymentService
     */
    protected $checkoutPaymentService;

    /**
     * @var DataStorageService
     */
    protected $dataStorageService;

    public function setUp()
    {
        $this->masterpassPayment = $this->createMock('Wirecard\WcsIntegrationExample\Model\MasterpassPayment');
        $this->checkoutPaymentService = $this->createMock('Wirecard\WcsIntegrationExample\Service\CheckoutPaymentService');
        $this->dataStorageService = $this->createMock('Wirecard\WcsIntegrationExample\Service\DataStorageService');
        $this->ajax = new Ajax($this->masterpassPayment, $this->checkoutPaymentService, $this->dataStorageService);
    }

    public function testPostRequestDataWithMasterpass()
    {
        $emptyResponse = new \Slim\Http\Response();
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $requestData = [
            'paymentMethod' => 'MASTERPASS',
            'callbackUrl' => 'bla',
            'shippingLocationProfile' => 'DACH',
            'allowedCardTypes' => []
        ];
        $request->method('getParsedBody')->willReturn($requestData);

        $expectedSetupResult = [
            'paymentMethod' => 'MASTERPASS',
            'data' => [
                'walletId' => 'abc',
                'other_param' => 'whatever'
            ]
        ];

        $this->masterpassPayment
            ->expects($this->once())
            ->method('setupPayment')
            ->with($requestData)
            ->willReturn($expectedSetupResult);

        $this->ajax->postRequestData($request, $emptyResponse);
    }

    public function testPostRequestDataWithDataStoragePaymentMethod()
    {
        $emptyResponse = new \Slim\Http\Response();
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $requestData = [
            'paymentMethod' => 'CCARD',
            'storageId' => ''
        ];
        $request->method('getParsedBody')->willReturn($requestData);

        $expectedSetupResult = [
            'paymentMethod' => 'CCARD',
            'data' => [
                'javascriptUrl' => 'http://www.example.com',
                'storageId' => 'abc123'
            ]
        ];

        $this->dataStorageService
            ->expects($this->once())
            ->method('setup')
            ->with($requestData)
            ->willReturn($expectedSetupResult);

        $this->ajax->postRequestData($request, $emptyResponse);
    }

    public function testPostRequestDataWithOtherPaymentMethod()
    {
        $emptyResponse = new \Slim\Http\Response();
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $requestData = [
            'paymentMethod' => 'SOFORTUEBERWEISUNG'
        ];
        $request->method('getParsedBody')->willReturn($requestData);

        $expectedSetupResult = [
            'paymentMethod' => 'sth_stupid',
            'data' => [
                'redirectUrl' => 'http://www.example.com'
            ]
        ];

        $this->checkoutPaymentService
            ->expects($this->once())
            ->method('setupPayment')
            ->with($requestData)
            ->willReturn($expectedSetupResult);

        $this->ajax->postRequestData($request, $emptyResponse);
    }


    /**
     * @param $expectedContent
     * @param $request
     */
    private function expectResponseWithContent($expectedContent, $request)
    {
        $emptyResponse = new \Slim\Http\Response();
        $expectedResponse = $emptyResponse->withJson($expectedContent);

        $response = $this->ajax->postRequestData($request, $emptyResponse);

        $this->assertEquals($expectedResponse, $response);
    }

}

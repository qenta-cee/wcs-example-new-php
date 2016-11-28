<?php

namespace WirecardTest\WcsIntegrationExample\Model;

use Wirecard\WcsIntegrationExample\Model\MasterpassPayment;
use Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister;
use Wirecard\WcsIntegrationExample\Service\PaymentService;
use Wirecard\WcsIntegrationExample\Service\WalletService;

class MasterpassPaymentUTest extends \PHPUnit_Framework_TestCase
{
    const WALLET_ID = 'wallet-55-dummy';
    /**
     * @var MasterpassPayment
     */
    protected $masterpassPayment;

    /**
     * @var WalletService
     */
    protected $walletService;

    /**
     * @var PaymentDataPersister
     */
    protected $paymentDataPersister;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    public function setUp()
    {
        $this->walletService = $this->createMock('Wirecard\WcsIntegrationExample\Service\WalletService');
        $this->paymentService = $this->createMock('Wirecard\WcsIntegrationExample\Service\PaymentService');
        $this->paymentDataPersister = $this->createMock('Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister');
        $this->masterpassPayment = new MasterpassPayment($this->walletService, $this->paymentService, $this->paymentDataPersister);
    }

    public function testSetupPaymentSuppressShippingAddressEnabledFalse()
    {
        $this->setupPaymentWithSuppressShippingAddressEnabled(false);
    }

    public function testSetupPaymentSuppressShippingAddressEnabledTrue()
    {
        $this->setupPaymentWithSuppressShippingAddressEnabled(true);
    }

    public function testSetupPaymentSuppressShippingAddressEnabledNotSet()
    {
        $requestData = [
            'callbackUrl' => 'bla',
            'shippingLocationProfile' => 'DACH',
            'allowedCardTypes' => ['Visa']
        ];
        $expectedResult = [
            'paymentMethod' => 'MASTERPASS',
            'data' => [
                'walletId' => $this::WALLET_ID,
                'callbackUrl' => 'bla',
                'shippingLocationProfile' => 'DACH',
                'suppressShippingAddressEnabled' => false,
                'allowedCardTypes' => ['Visa']
            ]
        ];
        $this->setupPaymentWithParameters($requestData, $expectedResult);
    }

    public function testSetupPaymentCreateThrowsException()
    {
        $requestData = [
            'callbackUrl' => 'bla',
            'shippingLocationProfile' => 'DACH',
            'allowedCardTypes' => ['Visa']
        ];

        $this->walletService
            ->method('create')
            ->will($this->throwException(new \Exception('test error message', 55)));

        $result = $this->masterpassPayment->setupPayment($requestData);

        $this->assertEquals([
                'errorCode' => 55,
                'errorMessage' => 'test error message'
            ],
            $result
        );
    }

    public function testPayWithWalletHappyPath()
    {
        $this->walletService
            ->method('check')
            ->with($this::WALLET_ID)
            ->willReturn([]);

        $paymentData = [];
        $this->paymentDataPersister
            ->method('read')
            ->with($this::WALLET_ID)
            ->willReturn($paymentData);

        $successfulResult = ['processingState' => 'SUCCESS'];
        $this->paymentService
            ->method('pay')
            ->with(
                $this::WALLET_ID,
                $paymentData
            )
            ->willReturn($successfulResult);

        $result = $this->masterpassPayment->payWithWallet($this::WALLET_ID);

        $this->assertTrue($result->isSuccessful());
    }

    public function testPayWithWalletPaymentReturnsFailure()
    {
        $this->walletService
            ->method('check')
            ->with($this::WALLET_ID)
            ->willReturn([]);

        $paymentData = [];
        $this->paymentDataPersister
            ->method('read')
            ->with($this::WALLET_ID)
            ->willReturn($paymentData);

        $failureResult = ['processingState' => 'FAILURE'];
        $this->paymentService
            ->method('pay')
            ->with(
                $this::WALLET_ID,
                $paymentData
            )
            ->willReturn($failureResult);

        $result = $this->masterpassPayment->payWithWallet($this::WALLET_ID);

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals(['errorTitle' => "Payment is in status FAILURE"], $result->getData());
    }

    public function testPayWithWalletPaymentThrowsException()
    {
        $this->walletService
            ->method('check')
            ->with($this::WALLET_ID)
            ->willReturn([]);

        $paymentData = [];
        $this->paymentDataPersister
            ->method('read')
            ->with($this::WALLET_ID)
            ->willReturn($paymentData);


        $this->paymentService
            ->method('pay')
            ->with(
                $this::WALLET_ID,
                $paymentData
            )
            ->will($this->throwException(new \Exception('test exception', 42)));

        $result = $this->masterpassPayment->payWithWallet($this::WALLET_ID);

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals([
            'errorCode' => 42,
            'errorMessage' => 'test exception',
            'errorTitle' => 'Error at executing the payment'
        ], $result->getData());
    }

    public function testPayWithWalletPersisterThrowsException()
    {
        $this->walletService
            ->method('check')
            ->with($this::WALLET_ID)
            ->willReturn([]);

        $this->paymentDataPersister
            ->method('read')
            ->with($this::WALLET_ID)
            ->will($this->throwException(new \Exception('test exception', 42)));

        $result = $this->masterpassPayment->payWithWallet($this::WALLET_ID);

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals([
            'errorCode' => 42,
            'errorMessage' => 'test exception',
            'errorTitle' => 'Error at reading the payment data from the persistent storage.'
        ], $result->getData());
    }

    public function testPayWithWalletCheckThrowsException()
    {
        $this->walletService
            ->method('check')
            ->with($this::WALLET_ID)
            ->will($this->throwException(new \Exception('test exception', 42)));

        $result = $this->masterpassPayment->payWithWallet($this::WALLET_ID);

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals([
            'errorCode' => 42,
            'errorMessage' => 'test exception',
            'errorTitle' => 'Error at reading the wallet'
        ], $result->getData());
    }

    public function testPayWithWalletCheckFindsError()
    {
        $walletError = ['errorTitle' => 'The wallet is invalid.'];
        $this->walletService
            ->method('check')
            ->with($this::WALLET_ID)
            ->willReturn($walletError);

        $result = $this->masterpassPayment->payWithWallet($this::WALLET_ID);

        $this->assertFalse($result->isSuccessful());
        $this->assertEquals([
            'errorTitle' => 'The wallet is invalid.'
        ], $result->getData());
    }

    private function setupPaymentWithParameters($requestData, $expectedResult)
    {
        $this->walletService
            ->method('create')
            ->willReturn($this::WALLET_ID);

        $this->paymentDataPersister
            ->expects($this->once())
            ->method('saveWallet');

        $result = $this->masterpassPayment->setupPayment($requestData);

        $this->assertEquals($expectedResult, $result);
    }

    private function setupPaymentWithSuppressShippingAddressEnabled($value)
    {
        $requestData = [
            'callbackUrl' => 'bla',
            'shippingLocationProfile' => 'DACH',
            'suppressShippingAddressEnabled' => $value,
            'allowedCardTypes' => ['Visa', 'Mastercard']
        ];
        $expectedResult = [
            'paymentMethod' => 'MASTERPASS',
            'data' => [
                'walletId' => $this::WALLET_ID,
                'callbackUrl' => 'bla',
                'shippingLocationProfile' => 'DACH',
                'suppressShippingAddressEnabled' => $value,
                'allowedCardTypes' => ['Visa,Mastercard']
            ]
        ];
        $this->setupPaymentWithParameters($requestData, $expectedResult);
    }
}

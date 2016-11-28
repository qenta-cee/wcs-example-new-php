<?php


namespace WirecardTest\WcsIntegrationExample\Service;

use Wirecard\WcsIntegrationExample\Service\CheckoutPaymentService;
use WirecardCheckoutApiClient\Entity\MasterPass\Wallet;
use Wirecard\WcsIntegrationExample\Service\PaymentService;
use WirecardCheckoutApiClient\Service\MasterPass\PaymentResourceService;
use Zend\Stdlib\Hydrator\HydratorInterface;

class PaymentServiceUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var PaymentResourceService
     */
    protected $resourceService;

    public function setUp()
    {
        $this->hydrator = $this->createMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $this->resourceService = $this->createMock('WirecardCheckoutApiClient\Service\MasterPass\PaymentResourceService');
        $this->paymentService = new PaymentService($this->hydrator, $this->resourceService);
    }

    public function testPayProcessedImmediately()
    {
        $walletId = 'abc-123';
        $totalAmount = ['amount' => 5, 'currency' => 'EUR'];
        $paymentData = [ 'basket' => ['totalAmount' => $totalAmount]];

        $hydratedPayment = $this->createMock('WirecardCheckoutApiClient\Entity\MasterPass\Wallet\Payment');
        $this->hydrator
            ->method('hydrate')
            ->with($paymentData)
            ->willReturn($hydratedPayment);

        $hydratedPayment
            ->method('setTotalAmount')
            ->with($totalAmount);

        $pendingPayment = $this->createPayment('PENDING');
        $successfulPayment = $this->createPayment('SUCCESS');

        $this->resourceService->expects($this->at(0))
            ->method('setWalletId');

        $this->resourceService->expects($this->at(1))
            ->method('create')
            ->willReturn($pendingPayment);

        $this->resourceService->expects($this->at(2))
            ->method('get')
            ->willReturn($successfulPayment);

        $extractedPayment = [];
        $this->hydrator
            ->method('extract')
            ->with($successfulPayment)
            ->willReturn($extractedPayment);

        $result = $this->paymentService->pay($walletId, $paymentData);

        $this->assertEquals($extractedPayment, $result);
    }

    private function createPayment($state)
    {
        $pendingPayment = new Wallet\Payment();
        $pendingPayment->setProcessingState($state);
        return $pendingPayment;
    }


}

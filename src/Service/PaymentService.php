<?php

namespace Wirecard\WcsIntegrationExample\Service;

use WirecardCheckoutApiClient\Entity\MasterPass\Wallet;
use WirecardCheckoutApiClient\Service\MasterPass\PaymentResourceService;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class PaymentService
 *
 * This class is used for payment requests for payment methods using checkout api client
 * Every communication with a PaymentResourceService should happen in this class.
 *
 * @package Wirecard\WcsIntegrationExample\Service
 */
class PaymentService
{
    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var PaymentResourceService
     */
    protected $resourceService;

    /**
     * PaymentService constructor.
     * @param HydratorInterface $hydrator
     * @param PaymentResourceService $resourceService
     */
    public function __construct(HydratorInterface $hydrator, PaymentResourceService $resourceService)
    {
        $this->hydrator = $hydrator;
        $this->resourceService = $resourceService;
    }

    /**
     * This method issues a payment request for a given wallet
     *
     * It creates a new wallet payment object and fills it with the given paymentData. Then it issues the payment
     * request which always returns a status 'pending'.
     * Afterwards it checks using the waitUntilProcessed method for a final payment state.
     *
     * The result of the payment is returned
     *
     * @param $walletId
     * @param $paymentData
     * @return array The payment data as array.
     */
    public function pay($walletId, $paymentData)
    {
        $payment = new Wallet\Payment();
        $payment = $this->hydrator->hydrate($paymentData, $payment);
        $payment->setTotalAmount($paymentData['basket']['totalAmount']);
        $this->resourceService->setWalletId($walletId);

        $resultPayment = $this->resourceService->create($payment);
        $resultPayment = $this->waitUntilProcessed($resultPayment);

        return $this->hydrator->extract($resultPayment);
    }

    /**
     * This method returns the payment with its final state
     *
     * It takes a payment and refreshes it for a maximum of 20 seconds until a final state was returned.
     * Then it returns the current state of the payment.
     *
     * @param $resultPayment
     * @return Wallet\Payment
     */
    private function waitUntilProcessed($resultPayment)
    {
        $attemptCount = 0;
        while ($resultPayment->getProcessingState() === 'PENDING' && $attemptCount < 20000) {
            $resultPayment = $this->resourceService->get($resultPayment);
            $attemptCount += 1;
            usleep(1000);
        }
        return $resultPayment;
    }

}
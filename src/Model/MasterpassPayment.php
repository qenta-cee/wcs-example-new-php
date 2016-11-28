<?php

namespace Wirecard\WcsIntegrationExample\Model;

use Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister;
use Wirecard\WcsIntegrationExample\Service\PaymentService;
use Wirecard\WcsIntegrationExample\Service\WalletService;

/**
 * Class to execute payments with Masterpass.
 *
 * Class MasterpassPayment
 * @package Wirecard\WcsIntegrationExample\Model
 */
class MasterpassPayment
{
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

    /**
     * PaymentLogic constructor.
     * @param WalletService $walletService
     * @param PaymentService $paymentService
     * @param PaymentDataPersister|null $paymentDataPersister
     */
    public function __construct(WalletService $walletService, PaymentService $paymentService, PaymentDataPersister $paymentDataPersister = null)
    {
        $this->walletService = $walletService;
        $this->paymentService = $paymentService;
        $this->paymentDataPersister = isset($paymentDataPersister) ? $paymentDataPersister : new PaymentDataPersister();
    }

    /**
     * Creates a wallet required to invoke the Masterpass lightbox, saves the wallet and
     *returns the data provided from the request and from the wallet which is necessary for the Masterpass lightbox
     *
     * @param $requestData
     * @return array with the data provided from the request and from the wallet
     */
    public function setupPayment($requestData)
    {
        try {
            $walletId = $this->walletService->create($requestData);
        } catch (\Exception $e) {
            return [
                'errorCode' => $e->getCode(),
                'errorMessage' => $e->getMessage()
            ];
        }

        $persistedData = $requestData;
        $persistedData['walletId'] = $walletId;
        $this->paymentDataPersister->saveWallet($persistedData);

        $allowedCardTypes = implode(',',$requestData['allowedCardTypes']);
        $data = array(
            'paymentMethod' => 'MASTERPASS',
            'data' => [
                'walletId' => $walletId,
                'callbackUrl' => $requestData['callbackUrl'],
                'shippingLocationProfile' => $requestData['shippingLocationProfile'],
                'suppressShippingAddressEnabled' => isset($requestData['suppressShippingAddressEnabled']) && $requestData['suppressShippingAddressEnabled'],
                'allowedCardTypes' => [$allowedCardTypes]
            ]
        );

        return $data;
    }

    /**
     * Executes a payment with a given wallet.
     *
     * First this method checks whether the wallet is valid and if it can be used for payments.
     * If both conditions are complied with the payment is executed.
     *
     * @param $walletId
     * @return PaymentResult
     */
    public function payWithWallet($walletId)
    {
        $walletErrors = null;
        try {
            $walletErrors = $this->walletService->check($walletId);
        } catch (\Exception $e) {
            return PaymentResult::createExceptionResult($e, 'Error at reading the wallet');
        }

        if (empty($walletErrors)) {
            return $this->createPayment($walletId);
        } else {
            return PaymentResult::createFailureResult($walletErrors);
        }
    }

    private function createPayment($walletId)
    {
        try {
            $paymentData = $this->paymentDataPersister->read($walletId);
        }catch (\Exception $e) {
            return PaymentResult::createExceptionResult($e, 'Error at reading the payment data from the persistent storage.');
        }

        try {
            $paymentResult = $this->paymentService->pay($walletId, $paymentData);
        } catch (\Exception $e) {
            return PaymentResult::createExceptionResult($e, 'Error at executing the payment');
        }

        if ($paymentResult['processingState'] === 'SUCCESS') {
            $result = PaymentResult::createSuccessfulResult(['payment' => json_encode($paymentResult)]);
            $this->paymentDataPersister->saveMasterpassPaymentResult($result->getData());

            return $result;
        } else {
            $status = $paymentResult['processingState'];
            return PaymentResult::createFailureResult(['errorTitle' => "Payment is in status $status"]);
        }
    }

}
<?php

namespace Wirecard\WcsIntegrationExample\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response;
use Wirecard\WcsIntegrationExample\Service\CheckoutPaymentService;
use Wirecard\WcsIntegrationExample\Model\MasterpassPayment;
use Wirecard\WcsIntegrationExample\Service\DataStorageService;

/**
 * Class Ajax
 * @package Wirecard\WcsIntegrationExample\Controller
 *
 * Controller used for initializing a payment.
 */
class Ajax
{
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

    /**
     * Ajax constructor.
     * @param MasterpassPayment $masterpassPayment
     * @param CheckoutPaymentService $checkoutPaymentService
     * @param DataStorageService $dataStorageService
     */
    public function __construct(MasterpassPayment $masterpassPayment, CheckoutPaymentService $checkoutPaymentService, DataStorageService $dataStorageService)
    {
        $this->masterpassPayment = $masterpassPayment;
        $this->checkoutPaymentService = $checkoutPaymentService;
        $this->dataStorageService = $dataStorageService;
    }

    /**
     * Determines the payment method and initialize the payment correspondingly.
     *
     * If the consumer has to enter payment data in the online shop (e.g. credit card number),
     * this data is stored in the Wirecard data storage and the payment is afterwards initiated
     * using the DataStorageService.
     *
     * If the consumer does not enter payment data (e.g. sofort),
     * the CheckoutPaymentService is used to initiate the payment.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     *
     */
    public function postRequestData(Request $request, Response $response)
    {
        $requestData = $request->getParsedBody();

        if ($requestData['paymentMethod'] === 'MASTERPASS') {
            $result = $this->masterpassPayment->setupPayment($requestData);
        } elseif (array_key_exists('storageId', $requestData) && $requestData['storageId'] === '') {
            $result = $this->dataStorageService->setup($requestData);
        } else {
            $result = $this->checkoutPaymentService->setupPayment($requestData);
        }

        return $response->withJson($result);
    }

}
<?php

namespace Wirecard\WcsIntegrationExample\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Wirecard\WcsIntegrationExample\Model\MasterpassPayment;
use Wirecard\WcsIntegrationExample\Model\CheckoutPayment;

/**
 * Class Callback
 * @package Wirecard\WcsIntegrationExample\Controller
 *
 * Controller used after the payment has been finished.
 */
class Callback
{
    /**
     * @var Twig
     */
    protected $view;

    /**
     * @var MasterpassPayment
     */
    protected $masterpassPayment;

    /**
     * @var CheckoutPayment
     */
    protected $checkoutPayment;

    /**
     * Callback constructor.
     * @param Twig $view
     * @param MasterpassPayment $masterpassPayment
     * @param CheckoutPayment $checkoutPayment
     */
    public function __construct(Twig $view, MasterpassPayment $masterpassPayment, CheckoutPayment $checkoutPayment)
    {
        $this->view = $view;
        $this->masterpassPayment = $masterpassPayment;
        $this->checkoutPayment = $checkoutPayment;
    }

    /**
     * In case of Masterpass only one callback URL can be configured.
     * This method is called after the consumer has entered payment card data and shipping address at Masterpass.
     * If the status is SUCCESS and the wallet is vaild, the payment can be executed.
     *
     * After the payment has been executed, the consumer is forwarded to a success or to a failure URL,
     * depending on the result of the payment.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function masterpassCallback(Request $request, Response $response)
    {
        $status = $_GET['status'];
        if ($status === 'SUCCESS') {
            $walletId = $_GET['walletId'];
            $paymentResult = $this->masterpassPayment->payWithWallet($walletId);
            if ($paymentResult->isSuccessful()) {
                return $this->view->render($response, 'success.html', $paymentResult->getData());
            } else {
                return $this->view->render($response, 'failure.html', $paymentResult->getData());
            }
        }

        return $this->view->render($response, 'failure.html', [
            'errorTitle' => 'Wrong status',
            'errorMessage' => "The status of init was $status . A payment can be started only if the status is SUCCESS."
        ]);
    }

    /**
     * This method is intended to be used as a success URL.
     *
     * @param Request $request
     * @param Response $response
     * @return Response The consumer gets forwarded to a success page.
     */
    public function success(Request $request, Response $response)
    {
        return $this->view->render($response, 'success.html');
    }

    /**
     * This method is intended to be used as a failure URL.
     *
     * @param Request $request
     * @param Response $response
     * @return Response The consumer gets forwarded to a failure page.
     */
    public function failure(Request $request, Response $response)
    {
        $requestData = $request->getParsedBody();
        return $this->view->render($response, 'failure.html', ['errorMessage' => json_encode($requestData)]);
    }

    /**
     * This method is intended to be used as a cancel URL.
     *
     * @param Request $request
     * @param Response $response
     * @return Response The consumer gets forwarded to a cancel page.
     */
    public function cancel(Request $request, Response $response)
    {
        return $this->view->render($response, 'cancel.html');
    }

    /**
     * This method is intended to be used as a pending URL.
     *
     * @param Request $request
     * @param Response $response
     * @return Response The consumer gets forwarded to a pending page.
     * On this page you can inform the consumers,
     * when and how they'll learn the final status of the payment.
     */
    public function pending(Request $request, Response $response)
    {
        return $this->view->render($response, 'pending.html');
    }

    /**
     * This method is intended to be used as a confirmation URL.
     *
     * @param Request $request
     * @param Response $response
     * @return string
     */
    public function confirm(Request $request, Response $response)
    {
        $requestData = $request->getParsedBody();
        return $this->checkoutPayment->confirm($requestData);
    }


}
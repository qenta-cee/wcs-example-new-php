<?php


namespace WirecardTest\WcsIntegrationExample\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use Wirecard\WcsIntegrationExample\Controller\Callback;
use Wirecard\WcsIntegrationExample\Model\CheckoutPayment;
use Wirecard\WcsIntegrationExample\Model\MasterpassPayment;
use Wirecard\WcsIntegrationExample\Model\PaymentResult;

class CallbackUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Callback
     */
    protected $callback;

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
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function setUp()
    {
        $this->view = $this->createMock('Slim\Views\Twig');
        $this->masterpassPayment = $this->createMock('Wirecard\WcsIntegrationExample\Model\MasterpassPayment');
        $this->checkoutPayment = $this->createMock('Wirecard\WcsIntegrationExample\Model\CheckoutPayment');
        $this->callback = new Callback($this->view, $this->masterpassPayment, $this->checkoutPayment);

        $this->request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $this->response = $this->createMock('Psr\Http\Message\ResponseInterface');
    }

    public function testMasterpassCallbackPaymentSuccessful()
    {
        $_GET['status'] = 'SUCCESS';
        $_GET['walletId'] = 'abc123';

        $successfulPaymentContent = ['paymentId' => '42', 'param1' => 'whatever'];
        $successfulPaymentResult = PaymentResult::createSuccessfulResult($successfulPaymentContent);
        $this->masterpassPayment
            ->method('payWithWallet')
            ->willReturn($successfulPaymentResult);

        $renderResult = $this->createMock('Psr\Http\Message\ResponseInterface');
        $this->view
            ->method('render')
            ->with(
                $this->response,
                'success.html',
                $successfulPaymentContent
            )
            ->willReturn($renderResult);


        $result = $this->callback->masterpassCallback($this->request, $this->response);

        $this->assertEquals($renderResult, $result);
    }

    public function testMasterpassCallbackPaymentUnsuccessful()
    {
        $_GET['status'] = 'SUCCESS';
        $_GET['walletId'] = 'abc123';

        $failurePaymentContent = ['paymentId' => '42', 'param1' => 'whatever'];
        $failureResult = PaymentResult::createFailureResult($failurePaymentContent);
        $this->masterpassPayment
            ->method('payWithWallet')
            ->willReturn($failureResult);

        $renderResult = $this->createMock('Psr\Http\Message\ResponseInterface');
        $this->view
            ->method('render')
            ->with(
                $this->response,
                'failure.html',
                $failurePaymentContent
            )
            ->willReturn($renderResult);


        $result = $this->callback->masterpassCallback($this->request, $this->response);

        $this->assertEquals($renderResult, $result);
    }

    public function testMasterpassCallbackStatusIsNotSuccess()
    {
        $_GET['status'] = 'sth else';

        $renderResult = $this->createMock('Psr\Http\Message\ResponseInterface');
        $this->view
            ->method('render')
            ->with(
                $this->response,
                'failure.html',
                [
                    'errorTitle' => 'Wrong status',
                    'errorMessage' => "The status of init was sth else . A payment can be started only if the status is SUCCESS."
                ]
            )
            ->willReturn($renderResult);


        $result = $this->callback->masterpassCallback($this->request, $this->response);

        $this->assertEquals($renderResult, $result);
    }

    public function testSuccess()
    {
        $renderResult = $this->expectRenderView('success.html');

        $result = $this->callback->success($this->request, $this->response);

        $this->assertEquals($renderResult, $result);
    }

    public function testFailure()
    {
        $renderResult = $this->expectRenderView('failure.html');

        $result = $this->callback->failure($this->request, $this->response);

        $this->assertEquals($renderResult, $result);
    }

    public function testCancel()
    {
        $renderResult = $this->expectRenderView('cancel.html');

        $result = $this->callback->cancel($this->request, $this->response);

        $this->assertEquals($renderResult, $result);
    }

    public function testPending()
    {
        $renderResult = $this->expectRenderView('pending.html');

        $result = $this->callback->pending($this->request, $this->response);

        $this->assertEquals($renderResult, $result);
    }

    public function testConfirm()
    {
        $parsedRequest = [];
        $this->request
            ->method('getParsedBody')
            ->willReturn($parsedRequest);

        $checkoutPaymentResult = 'whatever';
        $this->checkoutPayment
            ->method('confirm')
            ->with($parsedRequest)
            ->willReturn($checkoutPaymentResult);

        $result = $this->callback->confirm($this->request, $this->response);

        $this->assertEquals($checkoutPaymentResult, $result);
    }

    /**
     * @return ResponseInterface
     */
    private function expectRenderView($viewName)
    {
        $renderResult = $this->createMock('Psr\Http\Message\ResponseInterface');
        $this->view
            ->method('render')
            ->with(
                $this->response,
                $viewName
            )
            ->willReturn($renderResult);
        return $renderResult;
    }

}

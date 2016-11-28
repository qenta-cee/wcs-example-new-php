<?php
/**
 * Created by IntelliJ IDEA.
 * User: horst.fickel
 * Date: 25.10.2016
 * Time: 15:38
 */

namespace WirecardTest\WcsIntegrationExample\Controller;


use Slim\Collection;
use Slim\Views\Twig;
use Wirecard\WcsIntegrationExample\Controller\Result;
use Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister;


class ResultUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Result
     */
    protected $result;

    /**
     * @var Collection
     */
    protected $settings;

    /**
     * @var Twig
     */
    protected $view;

    /**
     * @var PaymentDataPersister
     */
    protected $paymentDataPersister;



    public function setUp()
    {
        $this->view = $this->createMock('Slim\Views\Twig');
        $this->settings = $this->createMock('Slim\Collection');
        $this->paymentDataPersister = $this->createMock('Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister');

        $this->result = new Result($this->settings, $this->view, $this->paymentDataPersister);
    }



    public function testDispatchGetResultsCallsPersister()
    {
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->method('getParsedBody')->willReturn(array());
        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $this->paymentDataPersister->expects($this->once())->method('getResultArray');

        $this->result->dispatchGetResults($request, $response, array() );
    }


    public function testDispatchGetResultsCallsRender()
    {
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->method('getParsedBody')->willReturn(array());
        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $this->view->expects($this->once())->method('render')->with($response, 'results.html',
            array('results' => null ));

        $this->result->dispatchGetResults($request, $response, array());
    }

    public function testDispatchGetCallsPersister()
    {
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->method('getParsedBody')->willReturn(array());
        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $orderNumber = "123456";

        $this->paymentDataPersister->expects($this->once())->method('getResult')->with( $orderNumber );

        $this->result->dispatchGet($request, $response, array("orderNumber" => $orderNumber) );
    }

    public function testDispatchGetCallsRender()
    {
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->method('getParsedBody')->willReturn(array());
        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $orderNumber = "123456";

        $this->view->expects($this->once())->method('render')->with($response, 'result.html',
            array('parameters' => null ));

        $this->result->dispatchGet($request, $response, array("orderNumber" => $orderNumber) );
    }


}

<?php
/**
 * Created by IntelliJ IDEA.
 * User: horst.fickel
 * Date: 14.11.2016
 * Time: 13:15
 */

namespace WirecardTest\WcsIntegrationExample\Controller;


use Slim\Views\Twig;
use Wirecard\WcsIntegrationExample\Controller\Fallback;

class FallbackUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Fallback
     */
    protected $fallback;

    /**
     * @var Twig
     */
    protected $view;

    protected function setUp()
    {
        $this->view = $this->createMock('Slim\Views\Twig');
        $this->fallback = new Fallback($this->view);
    }

    public function testDispatchPostWithEmptyResponse()
    {
        $response = $this->createMock('Psr\Http\Message\ResponseInterface');
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->method('getParsedBody')->willReturn(array());

        $this->view->expects($this->once())->method('render')->with($response, 'fallback.html', array('response' => ''));

        $this->fallback->dispatchPost($request, $response, array());
    }

    public function testDispatchPostResponse()
    {
        $responseString = 'test';

        $response = $this->createMock('Psr\Http\Message\ResponseInterface');
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->method('getParsedBody')->willReturn(array('response' => $responseString));

        $this->view->expects($this->once())->method('render')->with($response, 'fallback.html', array('response' => $responseString));

        $this->fallback->dispatchPost($request, $response, array());
    }
}

<?php
namespace WirecardTest\WcsIntegrationExample\Slim\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Wirecard\WcsIntegrationExample\Slim\Middleware\RealPort;

class RealPortUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RealPort
     */
    private $middleware;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var callable
     */
    private $next;

    public function setUp()
    {
        $this->middleware = new RealPort();
        $this->response = $this->createMock(ResponseInterface::class);
        $this->next = function ($request, $response) {
            return $response;
        };
        parent::setUp();
    }

    public function testInvokeWillReturnUnchangedResponseObject()
    {
        $middleware = $this->middleware;
        $this->assertEquals($this->response, $middleware($this->getMockRequest(), $this->response, $this->next));
    }

    /**
     * @param $header
     * @param $expectedPort
     * @dataProvider requestHeaderProvider
     */
    public function testInvokeWillSetNewUriObjectWithExpectedPort($header, $expectedPort)
    {
        $request = $this->getMockRequest($header, $expectedPort);
        $middleware = $this->middleware;
        $middleware($request, $this->response, $this->next);
    }

    public function testInvokeWithHttpsPortWillSetNewUriObjectWithExpectedPortAndHttps()
    {
        $expectedPort = 443;
        $header = [
            'HTTP_X_FORWARDED_FOR' => 'example.com',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ];
        $requestUri = $this->createMock(UriInterface::class);
        $requestUri->expects($this->once())->method('withScheme')->with($this->equalTo('https'))->willReturnSelf();
        $requestUri->method('withPort')->with($this->equalTo($expectedPort))->willReturnSelf();
        $request = $this->getMockRequest($header, $expectedPort, $requestUri);

        $middleware = $this->middleware;
        $middleware($request, $this->response, $this->next);
    }

    public function requestHeaderProvider()
    {
        return [
            [
                ['SERVER_PORT' => 1234],
                1234
            ],
            [
                ['HTTP_X_FORWARDED_FOR' => 'example.com'],
                80
            ],
            [
                ['HTTP_X_FORWARDED_FOR' => 'example.com', 'HTTP_X_FORWARDED_PROTO' => 'https'],
                443
            ],
            [
                ['HTTP_X_FORWARDED_FOR' => 'example.com', 'HTTP_X_FORWARDED_PORT' => 42],
                42
            ],
        ];
    }

    private function getMockRequest(array $params = [], $expectedPort = 80, $requestUri = null)
    {
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $request->method('getServerParams')->willReturn($params);
        $request->expects($this->once())->method('withUri')->willReturnSelf();
        if ($requestUri === null) {
            $requestUri = $this->createMock(UriInterface::class);
            $requestUri->method('withPort')->with($this->equalTo($expectedPort))->willReturnSelf();
            $requestUri->method('withScheme')->willReturnSelf();
        }
        $request->method('getUri')->willReturn($requestUri);
        return $request;
    }
}

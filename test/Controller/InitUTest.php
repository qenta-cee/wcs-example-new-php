<?php

namespace WirecardTest\WcsIntegrationExample\Controller;

use Slim\Collection;
use Slim\Views\Twig;
use Wirecard\WcsIntegrationExample\Controller\Init;

class InitUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Init
     */
    protected $init;

    /**
     * @var Collection
     */
    protected $settings;

    /**
     * @var Twig
     */
    protected $view;

    public function setUp()
    {
        $this->view = $this->createMock('Slim\Views\Twig');
        $this->settings = $this->createMock('Slim\Collection');
        $this->settings->method('get')->willReturn(array(
            'originUrl' => '{BASE}',
            'callbackUrl' => '{BASE}/callback',
            'confirmUrl' => '{BASE}/confirm',
            'successUrl' => '{BASE}/success',
            'cancelUrl' => '{BASE}/cancel',
            'failureUrl' => '{BASE}/failure',
            'pendingUrl' => '{BASE}/pending',
            'serviceUrl' => '{BASE}/pending',
            'returnUrl' => '{BASE}/fallback',
            'consumerIpAddress' => null,
            'consumerUserAgent' => null
        ));

        $this->init = new Init($this->settings, $this->view);
    }

    public function testDispatchGetCallsSettingsWithAllowedCardTypes()
    {
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $uri = $this->createMock('Psr\Http\Message\UriInterface');
        $uri->method('getHost')->willReturn('example.com');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getPort')->willReturn('80');
        $request->method('getUri')->willReturn($uri);

        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $this->settings->expects($this->at(0))->method('get')->with('allowedCardTypes');

        $this->init->dispatchGet($request, $response, array());
    }

    public function testDispatchGetCallsSettingsWithDefaultValues()
    {
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $uri = $this->createMock('Psr\Http\Message\UriInterface');
        $uri->method('getHost')->willReturn('example.com');
        $uri->method('getScheme')->willReturn('http');
        $uri->method('getPort')->willReturn('80');
        $request->method('getUri')->willReturn($uri);

        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $this->settings->expects($this->at(1))->method('get')->with('defaultValues');

        $this->init->dispatchGet($request, $response, array());
    }

    public function testDispatchGetCallsRender()
    {
        $request = $this->createMock('Psr\Http\Message\ServerRequestInterface');
        $uri = $this->createMock('Psr\Http\Message\UriInterface');
        $uri->method('withPath')->willReturn('http://example.com/');
        $request->method('getUri')->willReturn($uri);

        $response = $this->createMock('Psr\Http\Message\ResponseInterface');

        $this->view->expects($this->once())->method('render')->with($response, 'init.html',
            array(
                'defaultData' => array(
                    'originUrl' => 'http://example.com',
                    'callbackUrl' => 'http://example.com/callback',
                    'confirmUrl' => 'http://example.com/confirm',
                    'successUrl' => 'http://example.com/success',
                    'cancelUrl' => 'http://example.com/cancel',
                    'failureUrl' => 'http://example.com/failure',
                    'pendingUrl' => 'http://example.com/pending',
                    'serviceUrl' => 'http://example.com/pending',
                    'returnUrl' => 'http://example.com/fallback',
                    'consumerIpAddress' => null,
                    'consumerUserAgent' => null
                ),
                'shippingLocationProfiles' => array('default', 'WDCEDACH'),
                'allowedCardTypes' => array(
                    'originUrl' => '{BASE}',
                    'callbackUrl' => '{BASE}/callback',
                    'confirmUrl' => '{BASE}/confirm',
                    'successUrl' => '{BASE}/success',
                    'cancelUrl' => '{BASE}/cancel',
                    'failureUrl' => '{BASE}/failure',
                    'pendingUrl' => '{BASE}/pending',
                    'serviceUrl' => '{BASE}/pending',
                    'returnUrl' => '{BASE}/fallback',
                    'consumerIpAddress' => null,
                    'consumerUserAgent' => null
                )));

        $this->init->dispatchGet($request, $response, array());
    }
}

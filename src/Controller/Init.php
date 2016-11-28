<?php

namespace Wirecard\WcsIntegrationExample\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Collection;
use Slim\Views\Twig;

/**
 * Class Init
 * @package Wirecard\WcsIntegrationExample\Controller
 *
 * The entry point of a payment.
 */
class Init
{
    /**
     * @var Collection
     */
    protected $settings;

    /**
     * @var Twig
     */
    protected $view;

    /**
     * Init constructor.
     *
     * @param Collection $settings
     * @param Twig $view
     */
    public function __construct(Collection $settings, Twig $view)
    {
        $this->settings = $settings;
        $this->view = $view;
    }

    /**
     * Displays a view where the consumer can enter payment data
     * depending on the selected payment method.
     * The default values for the input are also set.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function dispatchGet(Request $request, Response $response, array $args)
    {
        $allowedCardTypes = $this->settings->get('allowedCardTypes');

        $defaultData = $this->settings->get('defaultValues');

        try {
            $base = substr((string)$request->getUri()->withPath(''), 0, -1);

            $defaultData['returnUrl'] = str_replace('{BASE}', $base, $defaultData['returnUrl']);
            $defaultData['originUrl'] = str_replace('{BASE}', $base, $defaultData['originUrl']);
            $defaultData['callbackUrl'] = str_replace('{BASE}', $base, $defaultData['callbackUrl']);
            $defaultData['confirmUrl'] = str_replace('{BASE}', $base, $defaultData['confirmUrl']);
            $defaultData['successUrl'] = str_replace('{BASE}', $base, $defaultData['successUrl']);
            $defaultData['cancelUrl'] = str_replace('{BASE}', $base, $defaultData['cancelUrl']);
            $defaultData['failureUrl'] = str_replace('{BASE}', $base, $defaultData['failureUrl']);
            $defaultData['pendingUrl'] = str_replace('{BASE}', $base, $defaultData['pendingUrl']);
            $defaultData['serviceUrl'] = str_replace('{BASE}', $base, $defaultData['serviceUrl']);
        } catch (\RuntimeException $e) {
            trigger_error('Route does not exist to generate redirectUrl', E_USER_WARNING);
        } catch (\InvalidArgumentException $e) {
            trigger_error('Required data missing to generate redirectUrl', E_USER_WARNING);
        }

        $defaultData['consumerIpAddress'] = $request->getServerParams()['REMOTE_ADDR'];
        $defaultData['consumerUserAgent'] = $request->getServerParams()['HTTP_USER_AGENT'];

        /**
         * ToDo - Read shipping location profiles from api
         */
        $shippingLocationProfiles = array('default', 'WDCEDACH');

        return $this->view->render($response, 'init.html', array(
            'defaultData' => $defaultData,
            'shippingLocationProfiles' => $shippingLocationProfiles,
            'allowedCardTypes' => $allowedCardTypes
        ));
    }
}
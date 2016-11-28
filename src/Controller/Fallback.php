<?php

namespace Wirecard\WcsIntegrationExample\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

/**
 * Class Fallback
 * @package Wirecard\WcsIntegrationExample\Controller
 *
 * Controller used as fallback,
 * if the consumer's browser can't support CORS.
 */
class Fallback
{
    /**
     * @var Twig
     */
    protected $view;

    public function __construct(Twig $view)
    {
        $this->view = $view;
    }

    /**
     * This method is intended to be used as return URL for payment methods which use data storage.
     *
     * The method is called, if the consumer's browser is not capable
     * of fully supporting CORS (Cross Origin Resource Sharing).
     * In that case the communication between the HTML page and
     * the Wirecard Checkout Server has to occur in an iframe,
     * where the anonymized payment data are returned to JavaScript objects.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function dispatchPost(Request $request, Response $response, array $args)
    {
        $responseParam = '';
        $requestData = $request->getParsedBody();

        if (array_key_exists('response', $requestData)) {
            $responseParam = $requestData['response'];
        }
        return $this->view->render($response, 'fallback.html', array(
            'response' => addslashes($responseParam)
        ));
    }
}
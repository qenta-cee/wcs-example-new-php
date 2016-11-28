<?php

namespace Wirecard\WcsIntegrationExample\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Collection;
use Slim\Views\Twig;
use Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister;

/**
 * Class Result
 * @package Wirecard\WcsIntegrationExample\Controller
 *
 * Shows the result of already finished payments.
 */
class Result
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
     * @var PaymentDataPersister
     */
    protected $paymentDataPersister;


    /**
     * Result constructor.
     *
     * @param Collection $settings
     * @param Twig $view
     * @param PaymentDataPersister|null $paymentDataPersister
     */
    public function __construct(Collection $settings, Twig $view, PaymentDataPersister $paymentDataPersister = null)
    {
        $this->settings = $settings;
        $this->view = $view;
        $this->paymentDataPersister = isset($paymentDataPersister) ? $paymentDataPersister : new PaymentDataPersister();
    }


    /**
     * Displays an overview over all previous payments.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function dispatchGetResults(Request $request, Response $response, array $args)
    {
        $results = $this->paymentDataPersister->getResultArray();

        return $this->view->render($response, 'results.html', array('results' => $results));
    }

    /**
     * Displays payment data details for a specific order number.
     *
     * @param Request $request
     * @param Response $response
     * @param array $args Has to contain an orderNumber.
     * @return Response
     */
    public function dispatchGet( Request $request, Response $response, array $args )
    {
        $result = $this->paymentDataPersister->getResult( $args['orderNumber'] );

        return $this->view->render($response, 'result.html', array('parameters' => $result) );
    }

}
<?php

/**
 * @var $container \Slim\Container
 */
$container = $app->getContainer();

// Register Twig View helper
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../view/', [
        'cache' => false
    ]);

    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $ext = new Slim\Views\TwigExtension($c['router'], $basePath);
    $view->addExtension($ext);

    return $view;
};

$zfConfig = [
    // This should be an array of module namespaces used in the application.
    'modules' => array(
        'WirecardCheckoutApiClient'
    ),
    'module_listener_options' => array(
        'module_paths' => array(
            './module',
            './vendor',
        ),
    ),
];

$serviceManager = new \Zend\ServiceManager\ServiceManager(new \Zend\Mvc\Service\ServiceManagerConfig());
$serviceManager->setService('ApplicationConfig', $zfConfig);
$serviceManager->get('ModuleManager')->loadModules();
$serviceManager->get('Application')->bootstrap();

$container['ServiceManager'] = function($c) use ($serviceManager) {
    return $serviceManager;
};

// init services
$container['Wirecard\WcsIntegrationExample\Service\WalletService'] = function ($c) {
    $walletHydrator = $c->get('ServiceManager')->get('WirecardCheckoutApiClient\\Entity\\MasterPass\\WalletHydrator' );

    $clientCredentials = $c->get('settings')['apiClientCredentials'];
    $walletResourceService = $c->get('ServiceManager')->get('WCAPI\\MasterPass\\WalletService');
    $walletResourceService->setAuthenticationCredentials($clientCredentials['merchantId'], $clientCredentials['merchantSecret']);

    return new \Wirecard\WcsIntegrationExample\Service\WalletService($walletResourceService, $walletHydrator);
};

$container['Wirecard\WcsIntegrationExample\Service\PaymentService'] = function ($c) {
    $paymentHydrator = $c->get('ServiceManager')->get('WirecardCheckoutApiClient\\Entity\\MasterPass\\Wallet\\PaymentHydrator' );

    $clientCredentials = $c->get('settings')['apiClientCredentials'];
    $paymentResourceService = $c->get('ServiceManager')->get('WCAPI\\MasterPass\\PaymentService');
    $paymentResourceService->setAuthenticationCredentials($clientCredentials['merchantId'], $clientCredentials['merchantSecret']);

    return new \Wirecard\WcsIntegrationExample\Service\PaymentService($paymentHydrator, $paymentResourceService);
};

// init model
$container['Wirecard\WcsIntegrationExample\Model\MasterpassPayment'] = function ($c) {
    $walletService = $c['Wirecard\WcsIntegrationExample\Service\WalletService'];
    $paymentService = $c['Wirecard\WcsIntegrationExample\Service\PaymentService'];

    return new \Wirecard\WcsIntegrationExample\Model\MasterpassPayment($walletService, $paymentService);
};

$container['Wirecard\WcsIntegrationExample\Service\CheckoutPaymentService'] = function ($c) {
    $clientCredentials = $c->get('settings')['checkoutClientCredentials'];

    return new \Wirecard\WcsIntegrationExample\Service\CheckoutPaymentService($clientCredentials);
};

$container['Wirecard\WcsIntegrationExample\Service\DataStorageService'] = function ($c) {
    $clientCredentials = $c->get('settings')['checkoutClientCredentials'];

    return new \Wirecard\WcsIntegrationExample\Service\DataStorageService($clientCredentials);
};

$container['Wirecard\WcsIntegrationExample\Model\CheckoutPayment'] = function ($c) {
    $clientSecret = $c->get('settings')['checkoutClientCredentials']['secret'];
    return new \Wirecard\WcsIntegrationExample\Model\CheckoutPayment($clientSecret);
};

// init controllers
$container['Wirecard\WcsIntegrationExample\Controller\Init'] = function ($c) {
    return new \Wirecard\WcsIntegrationExample\Controller\Init($c->get('settings'), $c->get('view'));
};
$container['Wirecard\WcsIntegrationExample\Controller\Ajax'] = function ($c) {
    $masterpassPayment = $c['Wirecard\WcsIntegrationExample\Model\MasterpassPayment'];
    $checkoutPayment = $c['Wirecard\WcsIntegrationExample\Service\CheckoutPaymentService'];
    $dataStorage = $c['Wirecard\WcsIntegrationExample\Service\DataStorageService'];

    return new \Wirecard\WcsIntegrationExample\Controller\Ajax($masterpassPayment, $checkoutPayment, $dataStorage);
};

// callback controllers
$container['Wirecard\WcsIntegrationExample\Controller\Callback'] = function ($c) {
    $masterpassPayment = $c['Wirecard\WcsIntegrationExample\Model\MasterpassPayment'];
    $checkoutPayment = $c['Wirecard\WcsIntegrationExample\Model\CheckoutPayment'];
    return new \Wirecard\WcsIntegrationExample\Controller\Callback($c->get('view'), $masterpassPayment, $checkoutPayment);
};

// result controller
$container['Wirecard\WcsIntegrationExample\Controller\Result'] = function ($c) {
    return new \Wirecard\WcsIntegrationExample\Controller\Result($c->get('settings'), $c->get('view'));
};

$container['Wirecard\WcsIntegrationExample\Controller\Fallback'] = function ($c) {
    return new \Wirecard\WcsIntegrationExample\Controller\Fallback($c->get('view'));
};
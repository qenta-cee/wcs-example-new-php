<?php

//routes
$app->get('/', 'Wirecard\WcsIntegrationExample\Controller\Init:dispatchGet')->setName('getInit');
$app->post('/ajax/process-data', 'Wirecard\WcsIntegrationExample\Controller\Ajax:postRequestData');
$app->get('/callback', 'Wirecard\WcsIntegrationExample\Controller\Callback:masterpassCallback');
$app->get('/success', 'Wirecard\WcsIntegrationExample\Controller\Callback:success');
$app->get('/failure', 'Wirecard\WcsIntegrationExample\Controller\Callback:failure');
$app->get('/cancel', 'Wirecard\WcsIntegrationExample\Controller\Callback:cancel');
$app->get('/pending', 'Wirecard\WcsIntegrationExample\Controller\Callback:pending');
$app->post('/confirm', 'Wirecard\WcsIntegrationExample\Controller\Callback:confirm');
$app->get('/result', 'Wirecard\WcsIntegrationExample\Controller\Result:dispatchGetResults')->setName('getResults');
$app->get('/result/{orderNumber}', 'Wirecard\WcsIntegrationExample\Controller\Result:dispatchGet')->setName('getResult');
$app->post('/fallback', 'Wirecard\WcsIntegrationExample\Controller\Fallback:dispatchPost');
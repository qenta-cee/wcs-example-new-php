<?php

namespace Wirecard\WcsIntegrationExample\Service;

use WirecardCheckoutApiClient\Service\MasterPass\WalletResourceService;
use WirecardCheckoutApiClient\Entity\MasterPass\Wallet;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * Class WalletService
 *
 * Used to create and verify a wallet.
 * Every communication with a WalletResourceService should happen in this class.
 *
 * @package Wirecard\WcsIntegrationExample\Service
 */
class WalletService
{
    /**
     * @var WalletResourceService
     */
    protected $resourceService;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * WalletService constructor.
     * @param WalletResourceService $resourceService
     * @param HydratorInterface $hydrator
     */
    public function __construct(WalletResourceService $resourceService, HydratorInterface $hydrator)
    {
        $this->resourceService = $resourceService;
        $this->hydrator = $hydrator;
    }

    /**
     * This method creates the wallet
     *
     * It takes the request post parameters, fills with it a new wallet object and then it tries to create
     * the wallet with the properties of the wallet object. This is done using the WalletResourceService.
     *
     * If the creation was successful, we will return the walletId. Otherwise it will throw an error.
     *
     * @param $data
     * @return string
     */
    public function create($data)
    {
        $wallet = $this->hydrator->hydrate($data, new Wallet());
        $wallet = $this->resourceService->create($wallet);
        return $wallet->getId();
    }

    /**
     * This method checks a wallet if it can be used to execute a payment.
     *
     * It takes the walletId and sets it in a new wallet object which will be used to perform a get on the wallet
     * resource. This is necessary to get the latest version of the wallet.
     *
     * Then if it finds an error (e.g. it contains no credit card information),
     * it will return an array with an error.
     *
     * If no errors are found,
     * it will return an empty array
     *
     * @param $walletId
     * @return array containing the errors found. An empty array, if no errors have been found.
     */
    public function check($walletId)
    {
        $req = new Wallet();
        $req->setId($walletId);
        $walletResponse = $this->resourceService->get($req);

        if ($walletResponse->getCard() === null) {
            return [
                'errorTitle' => 'Error at checking this wallet',
                'errorMessage' => 'The wallet does not contain card data and can not be used to pay.'
            ];
        }

        return [];
    }


}
<?php


namespace WirecardTest\WcsIntegrationExample\Service;


use Wirecard\WcsIntegrationExample\Service\WalletService;
use WirecardCheckoutApiClient\Entity\MasterPass\Wallet;
use WirecardCheckoutApiClient\Service\MasterPass\WalletResourceService;
use Zend\Stdlib\Hydrator\HydratorInterface;

class WalletServiceUTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WalletService
     */
    protected $walletService;

    /**
     * @var WalletResourceService
     */
    protected $resourceService;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    public function setUp()
    {
        $this->hydrator = $this->createMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $this->resourceService = $this->createMock('WirecardCheckoutApiClient\Service\MasterPass\WalletResourceService');
        $this->walletService = new WalletService($this->resourceService, $this->hydrator);
    }

    public function testCreate()
    {
        $data = [];

        $hydratedWallet = new Wallet();
        $this->hydrator
            ->method('hydrate')
            ->with($data)
            ->willReturn($hydratedWallet);

        $walletId = 'wallet123';
        $createdWallet = $this->createWalletWithId($walletId);
        $this->resourceService
            ->method('create')
            ->with($hydratedWallet)
            ->willReturn($createdWallet);

        $result = $this->walletService->create($data);

        $this->assertEquals($walletId, $result);
    }

    public function testCheckNoErrorFound()
    {
        $walletId = 'wallet123';
        $wallet = $this->createWalletWithId($walletId);
        $wallet->setId($walletId);

        $resultWallet = $this->createWalletWithId($walletId);
        $resultWallet->setCard(new Wallet\Card());

        $this->resourceService
            ->method('get')
            ->with($wallet)
            ->willReturn($resultWallet);

        $result = $this->walletService->check($walletId);

        $this->assertEquals([], $result);
    }

    public function testCheckNoCardDataFound()
    {
        $walletId = 'wallet123';
        $wallet = $this->createWalletWithId($walletId);
        $wallet->setId($walletId);

        $resultWallet = $this->createWalletWithId($walletId);
        // No card data for result wallet.

        $this->resourceService
            ->method('get')
            ->with($wallet)
            ->willReturn($resultWallet);

        $result = $this->walletService->check($walletId);

        $this->assertEquals([
            'errorTitle' => 'Error at checking this wallet',
            'errorMessage' => 'The wallet does not contain card data and can not be used to pay.'
        ],
            $result);
    }

    /**
     * @param $walletId
     * @return Wallet
     */
    private function createWalletWithId($walletId)
    {
        $result = new Wallet();
        $result->setId($walletId);
        return $result;
    }

}

<?php

namespace WirecardTest\WcsIntegrationExample\Persistence;

use Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister;

class PaymentDataPersisterWalletITest extends \PHPUnit_Framework_TestCase
{
    const WALLET_ID = 'wallet-55-dummy';

    /**
     * @var PaymentDataPersister
     */
    protected $paymentDataPersister;

    /**
     * @var array
     */
    protected $walletData;

    public function setUp()
    {
        $this->paymentDataPersister = new PaymentDataPersister();
        $this->walletData = [
            'walletId' => $this::WALLET_ID,
            'orderDescription' => 'dummy order description'
        ];
    }

    public function testSaveWallet()
    {
        $result = $this->paymentDataPersister->saveWallet($this->walletData);

        $this->assertTrue($result > 0);
    }

    public function testSaveResult()
    {
        $data = array(
            'payment' => json_encode(array(
                    'orderNumber' => '000000'
                ))
        );
        $result = $this->paymentDataPersister->saveMasterpassPaymentResult($data);

        $this->assertTrue($result > 0);
    }

    /**
     * @depends testSaveWallet
     */
    public function testRead()
    {
        $result = $this->paymentDataPersister->read($this::WALLET_ID);

        $this->assertEquals($this->walletData, $result);
    }

}

<?php

namespace WirecardTest\WcsIntegrationExample\Persistence;

use Wirecard\WcsIntegrationExample\Persistence\PaymentDataPersister;

class PaymentDataPersisterCheckoutITest extends \PHPUnit_Framework_TestCase
{
    const ORDER_NUMBER = '000000';

    /**
     * @var PaymentDataPersister
     */
    protected $paymentDataPersister;

    /**
     * @var array
     */
    protected $checkoutData;

    public function setUp()
    {
        $this->paymentDataPersister = new PaymentDataPersister();
        $this->checkoutData = [
            'orderNumber' => $this::ORDER_NUMBER,
            'orderDescription' => 'dummy order description'
        ];
    }

    public function testSaveCheckoutPayment()
    {
        $result = $this->paymentDataPersister->saveCheckoutPayment($this->checkoutData);

        $this->assertTrue($result > 0);
    }

    /**
     * @depends testSaveCheckoutPayment
     */
    public function testGetResult()
    {
        $this->assertEquals($this->checkoutData, $this->paymentDataPersister->getResult($this::ORDER_NUMBER));
    }

    public function testGetResultArrayIfResultsIsEmpty()
    {
        $this->paymentDataPersister = $this->getMockBuilder(PaymentDataPersister::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResultsArray'])
            ->getMock();
        $this->assertEquals(array(), $this->paymentDataPersister->getResultArray() );
    }

    public function testGetResultArray()
    {
        $results = $this->paymentDataPersister->getResultArray();

        foreach ($results as $result) {
            $this->assertTrue(is_numeric($result['order']), $result['order']);
            $this->assertTrue(is_numeric($result['time']), $result['time']);
        }
    }

    public function testSaveToFileIfMkdirFails()
    {
        $this->paymentDataPersister = $this->getMockBuilder(PaymentDataPersister::class)
            ->disableOriginalConstructor()
            ->setMethods(['saveToFile'])
            ->getMock();
        $reflection = new \ReflectionClass(get_class($this->paymentDataPersister));
        $method = $reflection->getMethod('saveToFile');
        $method->setAccessible(true);
        $this->assertEquals(false, $method->invokeArgs( $this->paymentDataPersister, array('', '') ) );
    }


}

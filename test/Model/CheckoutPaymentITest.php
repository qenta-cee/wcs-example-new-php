<?php


namespace WirecardTest\WcsIntegrationExample\Model;


use Wirecard\WcsIntegrationExample\Model\CheckoutPayment;

class CheckoutPaymentITest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CheckoutPayment
     */
    protected $checkoutPayment;

    public function setUp()
    {
        $this->checkoutPayment = new CheckoutPayment('B8AKTPWBRMNBV455FG6M2DANE99WU2');
    }

    public function testConfirmWithValidParamsReturnsOK()
    {
        $validRequest = $this::createValidRequest();
        $result = $this->checkoutPayment->confirm($validRequest);

        $this->assertOKResult($result);
    }

    public function testConfirmWithEmptyParamsReturnsNOK()
    {
        $result = $this->checkoutPayment->confirm([]);

        $this->assertNOKResult($result);
    }

    public function testConfirmWithBadFingerprintReturnsNOK()
    {
        $req = $this::createValidRequest();
        $req['responseFingerprint'] = 'dummy';
        $result = $this->checkoutPayment->confirm($req);

        $this->assertNOKResult($result);
    }

    public function testConfirmWithoutFingerprintReturnsNOK()
    {
        $req = $this::createValidRequest();
        $req['responseFingerprint'] = null;
        $result = $this->checkoutPayment->confirm($req);

        $this->assertNOKResult($result);
    }

    private static function createValidRequest()
    {
        return[
            'paymentType' => 'SOFORTUEBERWEISUNG',
            'paymentState' => 'SUCCESS',
            'orderNumber' => 11,
            'responseFingerprintOrder' => 'paymentType,paymentState,secret,responseFingerprintOrder',
            'responseFingerprint' => '24220f278cb629fe5bde029c773f426d8a019439ff75ee542da66d4c4444fb4e8e2a7a01f1ab8ffa312b707bf3230e6fa872069f886d1d711baed59101806721'
        ];
    }

    /**
     * @param $result
     */
    private function assertNOKResult($result)
    {
        $this->assertTrue(strpos($result, 'result="NOK"') !== false);
    }

    /**
     * @param $result
     */
    private function assertOKResult($result)
    {
        $this->assertTrue(strpos($result, 'result="OK"') !== false);
    }
}


   
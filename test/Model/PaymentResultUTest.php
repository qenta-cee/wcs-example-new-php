<?php


namespace WirecardTest\WcsIntegrationExample\Model;


use Wirecard\WcsIntegrationExample\Model\PaymentResult;

class PaymentResultUTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSuccessfulResultWithoutData()
    {
        $okResult =PaymentResult::createSuccessfulResult();

        $this->assertTrue($okResult->isSuccessful());
        $this->assertEquals([], $okResult->getData());
    }

    public function testCreateSuccessfulResultWithData()
    {
        $paymentData = ['paymentId' => 'dummy5', 'acquirer' => 'test acquirer'];
        $okResult =PaymentResult::createSuccessfulResult($paymentData);

        $this->assertTrue($okResult->isSuccessful());
        $this->assertEquals($paymentData, $okResult->getData());
    }

    public function testCreateFailureResultWithData()
    {
        $paymentData = ['paymentId' => 'dummy5', 'acquirer' => 'test acquirer', 'errorMessage' => 'not allowed'];
        $failureResult =PaymentResult::createFailureResult($paymentData);

        $this->assertFalse($failureResult->isSuccessful());
        $this->assertEquals($paymentData, $failureResult->getData());
    }

    public function testCreateExceptionResultWithoutTitle()
    {
        $e = new \Exception('internal error', 502);
        $exceptionResult = PaymentResult::createExceptionResult($e);

        $this->assertFalse($exceptionResult->isSuccessful());
        $this->assertEquals(['errorCode' => 502, 'errorMessage' => 'internal error'], $exceptionResult->getData());
    }

    public function testCreateExceptionResultWithTitle()
    {
        $e = new \Exception('internal error', 502);
        $exceptionResult = PaymentResult::createExceptionResult($e, 'Arbitrary error title');

        $this->assertFalse($exceptionResult->isSuccessful());
        $this->assertEquals([
            'errorCode' => 502,
            'errorMessage' => 'internal error',
            'errorTitle' => 'Arbitrary error title'
        ], $exceptionResult->getData());
    }

}

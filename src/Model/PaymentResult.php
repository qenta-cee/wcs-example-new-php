<?php

namespace Wirecard\WcsIntegrationExample\Model;

/**
 * The result of a payment.
 *
 * At the moment it's only used by Masterpass,
 * but it's generic and could be used by other payment methods as well.
 *
 * The class should be instantiated via the static factory methods starting with create.
 *
 * Class PaymentResult
 * @package Wirecard\WcsIntegrationExample\Model
 */
class PaymentResult
{
    /**
     * @var boolean
     */
    protected $successful;

    /**
     * @var array
     */
    protected $data;

    /**
     * PaymentResult constructor.
     * @param $successful
     * @param $data
     */
    private function __construct($successful, $data)
    {
        $this->successful = $successful;
        $this->data = $data;
    }

    /**
     * Creates a successful payment result
     * with optional payment data.
     *
     * @param array $data
     * @return PaymentResult
     */
    public static function createSuccessfulResult(array $data = [])
    {
        return new PaymentResult(true, $data);
    }

    /**
     * Creates a failure payment result.
     * Some data has to be provided e.g. with errorCode, errorMessage, errorTitle
     *
     * @param $data
     * @return PaymentResult
     */
    public static function createFailureResult($data)
    {
        return new PaymentResult(false, $data);
    }

    /**
     * Creates a failure payment result based on an exception.
     * The properties code and message of the exception will be mapped to payment data.
     *
     * @param $e
     * @param null $title
     * @return PaymentResult
     */
    public static function createExceptionResult($e, $title = null)
    {
        $data = [
            'errorCode' => $e->getCode(),
            'errorMessage' => $e->getMessage()
        ];
        if (isset($title)) {
            $data['errorTitle'] = $title;
        }

        return new PaymentResult(false, $data);
    }

    /**
     * Shows whether the payment was successful.
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        return $this->successful;
    }

    /**
     * Returns additional data about the payment.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }


}
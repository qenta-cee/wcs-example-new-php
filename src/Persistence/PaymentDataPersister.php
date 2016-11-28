<?php

namespace Wirecard\WcsIntegrationExample\Persistence;

/**
 * Class used to read and write payment data from and to the file system.
 *
 * Class PaymentDataPersister
 * @package Wirecard\WcsIntegrationExample\Persistence
 */
class PaymentDataPersister
{

    /**
     * Directory used to store the wallet and payment results in.
     *
     * @var string
     */
    protected $resultsDir;

    public function __construct()
    {
        $this->resultsDir = __DIR__ . '/../../results/';
    }

    /**
     * Stores data about a Masterpass wallet.
     * The file is named after the wallet ID, which is unique.
     *
     * @param $data
     * @return int
     */
    public function saveWallet($data)
    {
        return $this->saveToFile(json_encode($data), $data['walletId']);
    }


    /**
     * Stores the result of a Masterpass payment.
     *
     * @param $data
     * @return bool|int
     */
    public function saveMasterpassPaymentResult($data)
    {
        $orderNumber = json_decode($data['payment'])->{'orderNumber'};

        return $this->saveToFile($data, $orderNumber);
    }

    /**
     * Read a file from the results-directory.
     * @param $walletId string
     * @return mixed
     */
    public function read($walletId)
    {
        $fileName = $this->paymentFileName($walletId);
        $content = file_get_contents($fileName);
        return json_decode($content, true);
    }


    /**
     * Saves the result of a payment.
     *
     * @param $data
     * @return bool|int
     */
    public function saveCheckoutPayment($data)
    {
        $orderNumber = $data['orderNumber'];

        return $this->saveToFile(json_encode($data), $orderNumber);
    }

    /**
     * Returns the filename including its directory.
     *
     * @param $filename string
     * @return string
     */
    private function paymentFileName($filename)
    {
        $fileName = $this->resultsDir . '/' . $filename . '.json';
        return $fileName;
    }


    /**
     * Returns a list of successfully executed payments which are stored in the results-directory.
     * Includes the timestamp of the creation of the file as time reference for the order.
     *
     * @return array|mixed
     */
    public function getResultArray()
    {
        if(!is_dir($this->resultsDir)) {
            return array();
        }

        $files = scandir($this->resultsDir);
        $orders = preg_grep('/^[0-9]{1,9}\.json$/', $files);
        $orders = preg_replace('/^([0-9]{1,9})\.json$/', '\\1', $orders);

        $results = array();

        foreach( $orders as $order ) {
            $fileName = $this->paymentFileName($order);
            if (file_exists( $fileName )) {
                $results []= array ('time' => filectime( $fileName ), 'order' => $order );
            }
        }

        arsort($results);

        return $results;
    }

    /**
     * Returns the contents of the file of a specific order number. This file should contain the
     * corresponding payment data.
     *
     * @param $orderNumber  string
     * @return mixed
     */
    public function getResult($orderNumber) {
        $fileName = $this->paymentFileName($orderNumber);
        $content = file_get_contents($fileName);

        return json_decode($content, true);
    }

    /**
     * Saves the given data to the file. The filename is difined as id.
     *
     * @param $data mixed   Data to store
     * @param $id   string  Filename to use
     * @return bool|int
     */
    private function saveToFile($data, $id)
    {
        if (!@mkdir($this->resultsDir) && !is_dir($this->resultsDir)) {
            return false;
        }
        $fileName = $this->paymentFileName($id);
        return file_put_contents($fileName, $data);
    }

}
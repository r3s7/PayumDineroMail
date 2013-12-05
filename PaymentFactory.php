<?php
namespace App;

App\Payum\Action\CaptureAction;
App\Payum\Action\StatusAction;
use Payum\Payment;
use Payum\Request\CaptureRequest;
use Payum\Request\BinaryMaskStatusRequest;

abstract class PaymentFactory
{
    /**
     * @param $model
     * @param $gatewayUsername for sandbox you can use 'TEST-TEST-TEST-TEST-TEST'
     * @param $gatewayPassword for sandbox you can use 'TEST'
     * @param $encryption for sandbox you can use false here
     * @param $sandbox if you want use sandbox you need this in true.
     * @internal param \App\Api $api
     *
     * @return Payment
     */
    public static function create($model, $gatewayUsername, $gatewayPassword, $encryption, $sandbox)
    {

        /* MODEL EXAMPLE
        $model = new ArrayObject(array(
            //Merchant transaction ID, String used for IPN 3.0 requests and IPN 3.0 notifications
            'MerchantTransactionId' => '1234534',
            //Subject of the transaction (String)
            'Subject' => '',
            //Message about the transaction (String)
            'Message' => '',
            //Buyer data *All required
            'Name' => 'John',
            'LastName' => 'Doe',
            'Address' => 'Lombard Street',
            'City' => 'United States',
            'Country' => 'San Francisco',
            'Email' => 'john@doe.com',
            'Phone' => '45556565',
            //array of items *All required
            'Items' => array(
                0 => array(
                    'Code' => 'A001',
                    'Name' => 'LCD MONITOR',
                    'Description' => 'this is a LCD Monitor',
                    'Quantity' => 2,
                    'Amount' => 10.40,
                    // Possible values of the Currency: ARS, BRL, MXN, CLP, USD
                    'Currency' => 'USD',
                )
            ),

        ));
         END DATA EXAMPLE */

        $payment = new Payment;
        // CaptureAction(ApiUser[Slugified String], ApiPassword[String], Encryption[boolean], SanBox[boolean])
        $payment->addAction(new CaptureAction($gatewayUsername, $gatewayPassword, $encryption, $sandbox));
        $payment->addAction(new StatusAction);


        $payment->execute(new CaptureRequest($model));
        $payment->execute($status = new BinaryMaskStatusRequest($model));

        if ($status->isPending()) {
            echo "We purchase successfully this is your barcode {$model['result']['BarcodeImageUrl']}, please
            print your barcode and go to the Bank.";
        } else if ($status->isFailed()) {
            echo 'An error occurred';
        } else {
            echo 'Something went wrong but we don`t know the exact status';
        }

        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
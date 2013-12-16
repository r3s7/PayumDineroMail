<?php
namespace Payum\DineroMail;

use Payum\DineroMail\Action\CaptureAction;
use Payum\Core\Payment;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\BinaryMaskStatusRequest;


abstract class PaymentFactory
{
    /**
     * @param $api
     * @internal param $model
     * @internal param \Payum\DineroMail\for $gatewayUsername sandbox you can use 'TEST-TEST-TEST-TEST-TEST'
     * @internal param \Payum\DineroMail\for $gatewayPassword sandbox you can use 'TEST'
     * @internal param \Payum\DineroMail\for $encryption sandbox you can use false here
     * @internal param \Payum\DineroMail\if $sandbox you want use sandbox you need this in true.
     * @internal param \App\Api $api
     *
     * @return Payment
     */
    public static function create( Api $api)
    {


        /* first, I need an instance of Payment */
        $payment = new Payment;

        /* in second place, I need append an instance of Api to the Payment */
        $payment->addApi($api);

        /* in third place, I need append an instance of the action DoPaymentWithReference to the payment */
        $payment->addAction(new CaptureAction);



         /*
        // CaptureAction(ApiUser[Slugified String], ApiPassword[String], Encryption[boolean], SanBox[boolean])
        $payment->addAction(new CaptureAction($gatewayUsername, $gatewayPassword, $encryption, $sandbox));
        $payment->addAction(new StatusAction);




        if ($status->isPending()) {
            echo "We purchase successfully this is your barcode {$model['result']['BarcodeImageUrl']}, please
            print your barcode and go to the Bank.";
        } else if ($status->isFailed()) {
            echo 'An error occurred';
        } else {
            echo 'Something went wrong but we don`t know the exact status';
        }
*/
        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
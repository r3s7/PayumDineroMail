<?php
namespace Payum\DineroMail;

//Payum core namespaces
use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\Core\Payment;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\BinaryMaskStatusRequest;

//Internal namespaces
use Payum\DineroMail\Action\PaymentWithCreditCardCaptureAction;
use Payum\DineroMail\Action\PaymentWithCreditCardStatusAction;
use Payum\DineroMail\Action\PaymentWithReferenceCaptureAction;
use Payum\DineroMail\Action\PaymentWithReferenceStatusAction;
use Payum\DineroMail\Action\PaymentWithPayButtonCaptureAction;
use Payum\DineroMail\Action\PaymentWithPayButtonStatusAction;



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

        $payment->addExtension(new EndlessCycleDetectorExtension);

        // in the future, we'll work on figuring out from our config with type of actions we want to use here


        if($api instanceof DoPaymentWithPayButtonApi)
            $payment->addAction(new PaymentWithPayButtonCaptureAction());

        if($api instanceof DoPaymentWithCreditCardApi)
            $payment->addAction(new PaymentWithCreditCardCaptureAction());

        if($api instanceof DoPaymentWithReferenceApi)
            $payment->addAction(new PaymentWithReferenceCaptureAction());

        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);


//        $payment->addAction(new PaymentWithReferenceCaptureAction);
//        $payment->addAction(new PaymentWithReferenceStatusAction);

         /*
        // CaptureAction(ApiUser[Slugified String], ApiPassword[String], Encryption[boolean], SanBox[boolean])
        $payment->addAction(new CaptureAction($gatewayUsername, $gatewayPassword, $encryption, $sandbox));





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
<?php
namespace Payum\DineroMail;

use Payum\Core\Action\ExecuteSameRequestWithModelDetailsAction;
use Payum\Core\Payment;
use Payum\Core\Extension\EndlessCycleDetectorExtension;
use Payum\DineroMail\Action\CaptureAction;
use Payum\DineroMail\Action\StatusAction;

abstract class PaymentFactory
{
    /**
     * @param Api $api
     * 
     * @return Payment
     */
    public static function create(Api $api)
    {
        $payment = new Payment;
        
        $payment->addApi($api);

        $payment->addExtension(new EndlessCycleDetectorExtension);

        $payment->addAction(new CaptureAction);
        $payment->addAction(new StatusAction);
        $payment->addAction(new ExecuteSameRequestWithModelDetailsAction);
        
        return $payment;
    }

    /**
     */
    private function __construct()
    {
    }
}
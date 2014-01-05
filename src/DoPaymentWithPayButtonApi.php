<?php

namespace Payum\DineroMail;

use Payum\DineroMail\Api;
use Payum\DineroMail\Request\HttpGet\CheckOutUrl;
use Payum\DineroMail\Request\HttpGet\Objects\Buyer;
use Payum\DineroMail\Request\HttpGet\Objects\Merchant;
use Payum\DineroMail\Request\HttpGet\Objects\Item;

class DoPaymentWithPayButtonApi extends Api
{

    protected $_merchant;

    const DINEROMAIL_ROOT_CHECKOUT_URL                = "https://checkout.dineromail.com/CheckOut?";
    const DINEROMAIL_DEFAULT_PAYMENT_METHOD_AVAILABLE = "all";
    const DINEROMAIL_DEFAULT_COUNTRY_ID               = "chl";

    public function __construct($config)
    {

        $this->_merchant = new Merchant($config['merchantId']);
    }

    public function setMerchant(Merchant $merchant){

        $this->_merchant = $merchant;
    }

    public function getMerchant(){

        return $this->_merchant;
    }
    /**
     * encapsulates the call to the DineroMail web service invoking the method
     * doPaymentWithReference
     * @link https://api.dineromail.com/dmapi.asmx?WSDL
     *
     * @param \Payum\DineroMail\Request\HttpGet\Objects\Buyer contains the buyer information
     * @param array of \Payum\DineroMail\Request\HttpGet\Objects\Item to create the payment
     * @param \Payum\DineroMail\Request\HttpGet\Objects\Merchant $merchant
     * @param $okUrl
     * @param $errorUrl
     * @param $pendingUrl
     * @param string $countryId
     * @param string $paymentMethodAvailable
     * @internal param string $transactionId an unique TX id
     * @internal param $message
     * @internal param $subject
     * @return mixed
     */
    public function doPaymentWithPayButton(
        Buyer $buyer,
        Array $items,
        $merchant,
        $okUrl,
        $errorUrl,
        $pendingUrl,
        $countryId = self::DINEROMAIL_DEFAULT_COUNTRY_ID,
        $paymentMethodAvailable = self::DINEROMAIL_DEFAULT_PAYMENT_METHOD_AVAILABLE
    ) {

        //@TODO we should use $hash in the future with the Dineromail Advanced Integration.

        // we need Yii here
        \Yii::app()->request->redirect(
            new CheckOutUrl(
                $buyer,
                $items,
                $merchant,
                self::DINEROMAIL_ROOT_CHECKOUT_URL,
                $okUrl,
                $errorUrl,
                $pendingUrl,
                $countryId,
                $paymentMethodAvailable
            )
        );


    }

}
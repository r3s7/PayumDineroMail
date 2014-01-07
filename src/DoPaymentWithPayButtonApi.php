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
    protected $_countryId;
    protected $_paymentMethodAvailable;

    const DINEROMAIL_ROOT_CHECKOUT_URL                = "https://checkout.dineromail.com/CheckOut?";
    const DINEROMAIL_DEFAULT_PAYMENT_METHOD_AVAILABLE = "all";
    const DINEROMAIL_DEFAULT_COUNTRY_ID               = "3";

    public function __construct($config)
    {

        $this->_merchant = new Merchant($config['merchantId']);
        $this->_countryId = self::DINEROMAIL_DEFAULT_COUNTRY_ID;
        $this->_paymentMethodAvailable = '1';

    }

    public function setMerchant(Merchant $merchant){

        $this->_merchant = $merchant;
    }

    public function setCountryId($countryId){

        $this->_countryId = (string) $countryId;

    }

    public function setPaymentMethodAvailable($paymentMethodAvailable){

        $this->_paymentMethodAvailable = (string) $paymentMethodAvailable;

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
     * @param array $items of \Payum\DineroMail\Request\HttpGet\Objects\Item to create the payment
     * @param \Payum\DineroMail\Request\HttpGet\Objects\Merchant $merchant
     * @param $okUrl
     * @param $errorUrl
     * @param $pendingUrl
     * @internal param string $countryId
     * @internal param string $paymentMethodAvailable
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
        $pendingUrl
    ) {

        /* @TODO we should use $hash in the future with the Dineromail Advanced Integration, @see the manual
         * https://cl.dineromail.com/content/integracion.zip
         */

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
                $this->_countryId,
                $this->_paymentMethodAvailable
            )
        );


    }

}
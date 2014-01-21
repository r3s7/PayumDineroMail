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

        $this->_merchant                = new Merchant($config['MerchantId']);
        $this->_countryId               = $config['CountryId'];
        Item::setCurrencyCode($config['CurrencyCode']);
        Item::setNeedsReconversion($config['CurrencyReconversion']);
        Item::setReconversionFee($config['CurrencyReconversionFee']);
        $this->_paymentMethodAvailable  = self::DINEROMAIL_DEFAULT_PAYMENT_METHOD_AVAILABLE;

    }

    public function setMerchant(Merchant $merchant)
    {

        $this->_merchant = $merchant;
    }

    public function setCountryId($countryId)
    {

        $this->_countryId = (string)$countryId;

    }

    public function setPaymentMethodAvailable($paymentMethodAvailable)
    {

        $this->_paymentMethodAvailable = (string)$paymentMethodAvailable;

    }

    public function getMerchant()
    {

        return $this->_merchant;
    }

    protected function hash(
        Buyer $buyer,
        Array $items,
        $merchantTransactionId
    ) {

        $string = '';
        $string .= (string)$buyer;
        $string .= Item::concatenateItems($items);
        $string .= $this->_merchant;
        $string .= "&payment_method_available=" . $this->_paymentMethodAvailable;
        $string .= "&transaction_id =" . $merchantTransactionId;
        $string .= $this->_merchant->getPassword();

        return md5($string);
    }

    /**
     * encapsulates the call to the DineroMail web service invoking the method
     * doPaymentWithReference
     * @link https://api.dineromail.com/dmapi.asmx?WSDL
     *
     * @param \Payum\DineroMail\Request\HttpGet\Objects\Buyer contains the buyer information
     * @param array $items of \Payum\DineroMail\Request\HttpGet\Objects\Item to create the payment
     * @param \Payum\DineroMail\Request\HttpGet\Objects\Merchant $merchant
     * @param $merchantTransactionId
     * @param $okUrl
     * @param $pendingUrl
     * @param $errorUrl
     * @internal param string $countryId
     * @internal param string $paymentMethodAvailable
     * @return mixed
     */

    public function doPaymentWithPayButton(
        Buyer $buyer,
        Array $items,
        $merchant,
        $merchantTransactionId,
        $okUrl,
        $pendingUrl,
        $errorUrl
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
                $this->_countryId,
                $this->_paymentMethodAvailable,
                $merchantTransactionId,
                $this->hash($buyer, $items, $merchantTransactionId),
                $okUrl,
                $pendingUrl,
                $errorUrl
            )
        );


    }

}
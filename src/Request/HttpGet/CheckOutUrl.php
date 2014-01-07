<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Franchesco Fonseca
 * Email: <franchesco@webmized.com>
 * Date: 04/01/14
 * Time: 12:56 AM
 *
 * Represents a CheckOut Url containing all the information related
 * to the transaction
 * @see the @manual integration_en.pdf pages 5 to 7
 * @manual https://cl.dineromail.com/content/integracion.zip
 */

namespace Payum\DineroMail\Request\HttpGet;

use Payum\DineroMail\Api;
use Payum\DineroMail\DoPaymentWithPayButtonApi;
use Payum\DineroMail\Request\HttpGet\Objects\Buyer;
use Payum\DineroMail\Request\HttpGet\Objects\Item;
use Payum\DineroMail\Request\HttpGet\Objects\Merchant;

class CheckOutUrl
{


    protected $_buyer;
    protected $_items;
    protected $_merchant;
    protected $_countryId;
    protected $_paymentMethodAvailable;
    protected $_checkOutUrl;
    protected $_okUrl;
    protected $_errorUrl;
    protected $_pendingUrl;

    public function __construct(
        Buyer $buyer,
        Array $items,
        Merchant $merchant,
        $checkOutUrl,
        $okUrl,
        $errorUrl,
        $pendingUrl,
        $countryId,
        $paymentMethodAvailable
    )
    {

        $this->_buyer = $buyer;
        $this->_items = $items;
        $this->_merchant = $merchant;
        $this->_checkOutUrl = $checkOutUrl;
        $this->_okUrl = urlencode($okUrl);
        $this->_errorUrl = urlencode($errorUrl);
        $this->_pendingUrl = urlencode($pendingUrl);
        $this->_countryId = $countryId;
        $this->_paymentMethodAvailable = $paymentMethodAvailable;
    }

    protected function concatenateItems(Array $items)
    {

        $string = '';
        foreach ($items as $item) {

          $string .= $item;
        }

        return $string;

    }

    /**
     * @return String
     *
     * something like this:
     * https://checkout.dineromail.com/CheckOut?merchant=1721561&country_id=chl
     * &payment_method_available=all&item_name_1=Example+DVD&item_quantity_1=1&item_ammount_1=12050
     * &item_name_2=Example2+DVD&item_quantity_1=3&item_ammount_2=14050
     */
    public function __toString()
    {


        $string = '';
        $string .= $this->_checkOutUrl;
        $string .= $this->_merchant;
        $string .= "&country_id={$this->_countryId}";
        $string .= "&payment_method_available={$this->_paymentMethodAvailable}";
        $string .= $this->concatenateItems($this->_items);
        //@TODO we need figure out how we can get the status notification, (Dineromail sucks)
        //$string .= "&ok_url={$this->_okUrl}";
        //$string .= "&error_url={$this->_errorUrl}";
        //$string .= "&pending_url={$this->_pendingUrl}";
        $string .= "&currency=clp";


        return $string;

    }

}
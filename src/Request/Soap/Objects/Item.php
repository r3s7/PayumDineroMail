<?php

/**
 * Represents a Item object containing all the information related
 * to the item to be purchased.
 * @see Vendor_DineroMail_Object_Object
 */

namespace Payum\DineroMail\Request\Soap\Objects;

use Payum\DineroMail\Api;

class Item extends SoapObject
{

    protected $_amount = '';
    protected $_code = '';
    protected $_description = '';
    protected $_name = '';
    protected $_quantity = 1;

    static protected $_currencyCode = Api::DINEROMAIL_DEFAULT_CURRENCY;
    static protected $_needsReconversion;
    static protected $_reconversionFee;

    public function setAmount($amount)
    {
        $this->_amount = strval($amount);
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    static public function setCurrencyCode($currencyCode)
    {
        self::$_currencyCode = $currencyCode;
    }

    static public function setNeedsReconversion($bool)
    {
        self::$_needsReconversion = $bool;
    }

    static public function setReconversionFee($fee)
    {
        self::$_reconversionFee = $fee;
    }

    public function setDescription($description)
    {
        $this->_description = $description;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setQuantity($quantity)
    {
        $this->_quantity = $quantity;
    }

    public function asSoapObject()
    {

        return new \SOAPVar(array('Amount' => $this->_amount,
                'Code' => $this->_code,
                'Currency' => $this->_currency,
                'Description' => $this->_description,
                'Name' => $this->_name,
                'Quantity' => $this->_quantity),
            SOAP_ENC_OBJECT,
            'Item',
            $this->getGateway()->getNameSpace());
    }

    public function __toString()
    {

        return $this->_amount .
        $this->_code .
        $this->_currency .
        $this->_description .
        $this->_name .
        $this->_quantity;
    }

}
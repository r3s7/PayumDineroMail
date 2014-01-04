<?php

/**
 * Represents a Item object containing all the information related
 * to the item to be purchased.
 * @see Vendor_DineroMail_Object_Object
 */

namespace Payum\DineroMail\Request\Api\Objects;

use Payum\DineroMail\Request\Api;

class Item
{

    protected $_amount = '';
    protected $_code = '';
    protected $_description = '';
    protected $_name = '';
    protected $_quantity = 1;
    protected $_currency = Api::DINEROMAIL_DEFAULT_CURRENCY;


    public function setAmount($amount)
    {
        $this->_amount = strval($amount);
    }

    public function setCode($code)
    {
        $this->_code = $code;
    }

    public function setCurrency($currency)
    {
        $this->_currency = $currency;
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
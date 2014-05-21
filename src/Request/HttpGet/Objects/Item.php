<?php

/**
 * Represents a Item object containing all the information related
 * to the Items of the transaction
 * @see the @manual integration_en.pdf at "basic integration"
 * @manual https://cl.dineromail.com/content/integracion.zip
 */

namespace Payum\DineroMail\Request\HttpGet\Objects;

use Payum\DineroMail\Api;

class Item
{

    public static $itemsInstancesAmount = 0;

    protected $_itemNumber;
    protected $_ammount = '';
    protected $_quantity = 1;
    protected $_name = '';
    protected $_currencyCode = '';

    /**
     * Please note that DineroMail require suffixes for the item positions
     *
     * Example, 1 item url should be something like this:
     * item_ammount_1=12050&item_name_1=Item+Name&item_quantity_1=2
     *
     * Example, 2 item should be something like this:
     * item_ammount_1=12050&item_name_1=Item+Name&item_quantity_1=2&item_ammount_2=14050&item_name_2=Item2+Name&item_quantity_2=1
     */
    public function __construct()
    {

        self::$itemsInstancesAmount++;
        $this->_itemNumber = self::$itemsInstancesAmount;
    }

    /**
     * Please note that DineroMail recognize the two last digits of an Amount as Decimals
     *
     * for example: 12050 is equal to 120.50
     */

    public function setAmount($ammount)
    {
        $this->_ammount = (string)number_format($ammount,2,'.','');

    }

    public function setCurrencyCode($cc)
    {
        $this->_currencyCode = (string)$cc;

    }

    public function setName($name)
    {
        $this->_name = urlencode($name);
    }

    public function setQuantity($quantity)
    {
        $this->_quantity = (string)$quantity;
    }


    public function getAmount()
    {
        return $this->_ammount;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getQuantity()
    {
        return $this->_quantity;
    }

    public static function concatenateItems(Array $items)
    {

        $string = '';
        foreach ($items as $item) {

            $string .= $item;
        }

        return $string;
    }

    public function __toString()
    {
        $string       = '';
        $string .= "&item_ammount_{$this->_itemNumber}={$this->_ammount}";
        $string .= "&item_name_{$this->_itemNumber}={$this->_name}";
        $string .= "&item_quantity_{$this->_itemNumber}={$this->_quantity}";
        $string .= "&item_currency_{$this->_itemNumber}={$this->_currencyCode}";

        return $string;
    }

}
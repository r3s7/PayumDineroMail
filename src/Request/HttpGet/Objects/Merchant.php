<?php

/**
 * Represents a Merchant object containing all the information related
 * to the Merchant owner of the DineroMail account
 * @see the @manual integration_en.pdf pages 14 to 16
 * @manual https://cl.dineromail.com/content/integracion.zip
 */

namespace Payum\DineroMail\Request\HttpGet\Objects;

class Merchant
{

    protected $_merchantId    = '';
    protected $_password      = '';

    public function __construct($id, $password)
    {
        $this->_merchantId    = (string)$id;
        $this->_password      = (string)$password;
    }

    public function setMerchantId($id)
    {
        $this->_merchantId = (string)$id;
    }

    public function setPassword($password)
    {
        $this->_password = (string)$password;
    }

    public function getMerchantId()
    {
        return $this->_merchantId;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * @return String
     *
     * something like this:
     * merchant=12345678 or merchant=richard%40gonzalez.com
     */
    public function __toString()
    {
        return "merchant={$this->_merchantId}";

    }

}

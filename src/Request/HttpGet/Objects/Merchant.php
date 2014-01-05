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

    protected $_merchantId = '';
    protected $_merchantEmail = '';

    public function __construct($id, $email = '')
    {
        $this->_merchantId    = (int)$id;
        $this->_merchantEmail = urlencode((string)$email);
    }

    public function setMerchantId($id)
    {
        $this->_merchantId = (int)$id;
    }

    public function setMerchantEmail($email)
    {
        $this->_merchantEmail = urlencode((string)$email);
    }

    public function getMerchantId()
    {
        return $this->_merchantId;
    }

    public function getMerchantEmail()
    {
        return urldecode($this->_merchantEmail);
    }

    /**
     * @return String
     *
     * something like this:
     * merchant=12345678 or merchant=richard%40gonzalez.com
     */
    public function __toString()
    {

        if (!empty($this->_merchantId)) {
        }
        return "merchant={$this->_merchantId}";

        if (empty($this->_merchantId)) {
            return "merchant={$this->_merchantEmail}";
        }
    }

}

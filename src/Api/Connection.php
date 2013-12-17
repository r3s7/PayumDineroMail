<?php

namespace Payum\DineroMail\Api;

/**
 * Represents a connection with the webservice
 *
 * @see Vendor_DineroMail_Credentials
 * @see Vendor_DineroMail_Gateway_Abstract
 */
class Connection
{

    protected $_credentials = null;
    protected $_gateway = null;
    protected $_crypt = false;


    public function __construct(Credentials $credentials,
                                Gateway $gateway, $crypt = false)
    {
        $this->_credentials = $credentials;
        $this->_gateway = $gateway;
        $this->_crypt = $crypt;
    }

    public function getCredentials()
    {
        return $this->_credentials;
    }

    public function getGateway()
    {
        return $this->_gateway;
    }

}

<?php

namespace Payum\DineroMail\Request\Soap;

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


    public function __construct(Credentials $credentials,
                                Gateway $gateway)
    {
        $this->_credentials = $credentials;
        $this->_gateway = $gateway;
    }

    public function getCredentials()
    {
        return $this->_credentials;
    }

    public function getGateway()
    {
        return $this->_gateway;
    }

    /**
     * @return SoapClient
     */
    public function getClient()
    {
        return new \SoapClient($this->getGateway()->getWdsl(),
            array('trace' => 1,
                'exceptions' => 1));
    }
}

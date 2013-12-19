<?php

namespace Payum\DineroMail\Api;

/**
 * Represents a connection with the webservice
 *
 * @see Vendor_DineroMail_Credentials
 * @see Vendor_DineroMail_Gateway_Abstract
 */
class ReferenceConnection extends Connection
{
    /**
     * @return DMSoapClient
     */
    public function getClient()
    {
        return new ReferenceClient($this->getGateway()->getWdsl(),
            array('trace' => 1,
                'exceptions' => 1));
    }
}

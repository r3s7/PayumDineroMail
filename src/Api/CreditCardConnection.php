<?php

namespace Payum\DineroMail\Api;

/**
 * Represents a connection with the webservice
 *
 * @see Vendor_DineroMail_Credentials
 * @see Vendor_DineroMail_Gateway_Abstract
 */
class CreditCardConnection extends Connection
{
    /**
     * @return CreditCardClient
     */
    public function getClient()
    {
        return new CreditCardClient($this->getGateway()->getWdsl(),
            array('trace' => 1,
                'exceptions' => 1));
    }
}

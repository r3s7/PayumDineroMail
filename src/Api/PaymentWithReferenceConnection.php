<?php

namespace Payum\DineroMail\Api;

/**
 * Represents a connection with the webservice
 *
 * @see Vendor_DineroMail_Credentials
 * @see Vendor_DineroMail_Gateway_Abstract
 */
class PaymentWithReferenceConnection extends Connection
{
    protected $_crypt = false;


    public function __construct(Credentials $credentials,
                                Gateway $gateway, $crypt = false)
    {
        parent::__construct($credentials, $gateway);
        $this->_crypt = $crypt;
    }
}

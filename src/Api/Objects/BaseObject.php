<?php

namespace Payum\DineroMail\Api\Objects;

use Payum\DineroMail\Api\Gateway;
use Payum\DineroMail\Api;
abstract class BaseObject
{
    protected $_gateway = null;


    public final function __construct(Api $dineroMailAction)
    {
        $gateway = new Gateway(
            $dineroMailAction->getConnection()->getGateway()->getNameSpace(),
            $dineroMailAction->getConnection()->getGateway()->getWdsl()
        );
        $this->_gateway = $gateway;
    }

    public function getGateway()
    {
        return $this->_gateway;
    }

    /**
     * Represents and object as SOAPVar
     *
     * @return SOAPVar the SOAPVar object containing all the required data
     */
    public abstract function asSoapObject();


}
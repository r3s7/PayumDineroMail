<?php

namespace Payum\DineroMail\Api\Objects;

abstract class BaseObject
{
    protected $_gateway = null;


    public final function __construct(DineroMailAction $dineroMailAction)
    {
        $gateway = new DineroMailGateway(
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
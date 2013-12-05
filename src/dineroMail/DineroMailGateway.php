<?php

class DineroMailGateway
{

    protected $_nameSpace;
    protected $_wdsl;

    public function __construct($nameSpace, $wdsl)
    {
        $this->_nameSpace = $nameSpace;
        $this->_wdsl = $wdsl;
    }

    public function getNameSpace()
    {
        return $this->_nameSpace;
    }

    public function getWdsl()
    {
        return $this->_wdsl;
    }

}
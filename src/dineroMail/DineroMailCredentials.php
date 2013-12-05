<?php

/**
 * Contains the credentials required by Vendor_DineroMail_Connection
 *
 * @see Vendor_DineroMail_Connection
 */
class DineroMailCredentials
{

    protected $_username = null;
    protected $_password = null;


    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    public function getUserName()
    {
        return $this->_username;
    }

    public function getPassword()
    {
        return $this->_password;
    }

}
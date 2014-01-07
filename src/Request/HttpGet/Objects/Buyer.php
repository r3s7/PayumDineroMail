<?php

/**
 * Represents a Buyer object containing all the information related
 * to the buyer which is going to do the purchase
 * @see the @manual integration_en.pdf at "basic integration"
 * @manual https://cl.dineromail.com/content/integracion.zip
 */

namespace Payum\DineroMail\Request\HttpGet\Objects;

class Buyer
{

    protected $_documentNumber = '';
    protected $_documentType ='';
    protected $_email = '';
    protected $_lastName = '';
    protected $_name = '';
    protected $_phone = '';
    protected $_sex = '';


    //set methods
    public function setDocumentNumber($documentNumber)
    {
        $this->_documentNumber = urlencode($documentNumber);
    }

    public function setDocumentType($documentType)
    {
        $this->_documentType = urlencode($documentType);
    }

    public function setEmail($email)
    {
        $this->_email = urlencode($email);
    }

    public function setLastName($lastName)
    {
        $this->_lastName = urlencode($lastName);
    }

    public function setName($name)
    {
        $this->_name = urlencode($name);
    }

    public function setPhone($phone)
    {
        $this->_phone = urlencode($phone);
    }

    public function setSex($sex)
    {
        $this->_sex = urlencode(strtolower($sex));
    }

    //get methods
    public function getDocumentNumber()
    {
        return $this->_documentNumber;
    }

    public function getDocumentType()
    {
        return $this->_documentType;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function getLastName()
    {
        return $this->_lastName;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getPhone()
    {
        return $this->_phone;
    }

    public function getSex()
    {
        return $this->_sex;
    }

    /**
     * @return String
     *
     * something like this:
     * &buyer_document_number=12345678&buyer_document_type=dni&buyer_email=john%40doe.com&buyer_lastname=Doe
     * &buyer_name=John&buyer_phone=55543256&buyer_sex=m
     */
    public function __toString()
    {

        $string = '';

        if(!empty($this->_documentNumber))
            $string .= "&buyer_document_number=".$this->_documentNumber;

        if(!empty($this->_documentType))
            $string .= "&buyer_document_type=".$this->_documentType;

        if(!empty($this->_email))
            $string .= "&buyer_email=".$this->_email;

        if(!empty($this->_lastName))
            $string .= "&buyer_lastname=".$this->_lastName;

        if(!empty($this->_name))
            $string .= "&buyer_name=".$this->_name;

        if(!empty($this->_phone))
            $string .= "&buyer_phone=".$this->_phone;

        if(!empty($this->_sex))
            $string .= "&buyer_sex=".$this->_sex;

        return trim($string);
    }

}
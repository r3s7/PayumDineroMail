<?php

/**
 * Represents a Buyer object containing all the information related
 * to the buyer which is going to do the purchase
 * @see Vendor_DineroMail_Object_Object
 */

namespace Payum\DineroMail\Api\Objects;

class Buyer extends BaseObject
{

    protected $_address = '';
    protected $_city = '';
    protected $_country = '';
    protected $_email = '';
    protected $_lastName = '';
    protected $_name = '';
    protected $_phone = '';


    public function setAddress($address)
    {
        $this->_address = $address;
    }

    public function setCity($city)
    {
        $this->_city = $city;
    }

    public function setCountry($country)
    {
        $this->_country = $country;
    }

    public function setEmail($email)
    {
        $this->_email = $email;
    }

    public function setLastName($lastName)
    {
        $this->_lastName = $lastName;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setPhone($phone)
    {
        $this->_phone = $phone;
    }

    public function asSoapObject()
    {

        return new SOAPVar(array('Address' => $this->_address,
                'City' => $this->_city,
                'Country' => $this->_country,
                'Email' => $this->_email,
                'LastName' => $this->_lastName,
                'Name' => $this->_name,
                'Phone' => $this->_phone),
            SOAP_ENC_OBJECT,
            'Buyer',
            $this->getGateway()->getNameSpace());
    }

    public function __toString()
    {

        return $this->_name .
        $this->_lastName .
        $this->_email .
        $this->_address .
        $this->_phone .
        $this->_country .
        $this->_city;
    }

}
<?php

/**
 * Represents a CreditCard object containing all the information related
 * to the credit card which the buyer is using
 * @see Vendor_DineroMail_Object_Object
 */

namespace Payum\DineroMail\Api\Objects;

use Payum\Core\Security\SensitiveValue;

class CreditCard extends BaseObject
{

    /*
     * installment - required
     * creditCardNumber - required
     * holder - required
     * expirationDate - required
     * securityCode - required
     * documentNumber - required - holder's document number, not sensitive info
     *
     * the below are optional
     * address
     * addressNumber
     * addressComplement
     * zipCode
     * neighborhood
     * city
     * state
     * country
     */

    // required fields per API docs (Dec 19, 2013)
    protected $_installment         = '';
    protected $_creditCardNumber    = '';
    protected $_holder              = '';
    protected $_expirationDate      = '';
    protected $_securityCode        = '';
    protected $_documentNumber      = '';

    // optional fields per API docs (Dec 19, 2013)
    protected $_address             = '';
    protected $_addressNumber       = '';
    protected $_addressComplement   = '';
    protected $_zipCode             = '';
    protected $_neighborhood        = '';
    protected $_city                = '';
    protected $_state               = '';
    protected $_country             = '';

    // set our required values
    public function setInstallment($installment)
    {
        $this->_installment = $installment;
    }

    public function setCreditCardNumber(SensitiveValue $creditCardNumber)
    {
        $this->_creditCardNumber = $creditCardNumber;
    }

    public function setHolder($holder)
    {
        $this->_holder = $holder;
    }

    public function setExpirationDate(SensitiveValue $expirationDate)
    {
        $this->_expirationDate = $expirationDate;
    }

    public function setSecurityCode(SensitiveValue $securityCode)
    {
        $this->_securityCode = $securityCode;
    }

    public function setDocumentNumber($documentNumber)
    {
        $this->_documentNumber = $documentNumber;
    }

    // set optional values
    public function setAddress($address)
    {
        $this->_address = $address;
    }

    public function setAddressNumber($addressNumber)
    {
        $this->_addressNumber = $addressNumber;
    }

    public function setAddressComplement($addressComplement)
    {
        $this->_addressComplement = $addressComplement;
    }

    public function setZipCode($zipCode)
    {
        $this->_zipCode = $zipCode;
    }

    public function setNeighborhood($neighborhood)
    {
        $this->_neighborhood = $neighborhood;
    }

    public function setCity($city)
    {
        $this->_city = $city;
    }

    public function setState($state)
    {
        $this->_state = $state;
    }

    public function setCountry($country)
    {
        $this->_country = $country;
    }

    public function asSoapObject()
    {
        return new \SOAPVar(array(
                'Installment'           => $this->_installment,
                'CreditCardNumber'      => $this->_creditCardNumber, // @TODO: sensitive, won't come out right
                'Holder'                => $this->_holder,
                'ExpirationDate'        => $this->_expirationDate, // @TODO: sensitive, won't come out right
                'SecurityCode'          => $this->_securityCode, // @TODO: sensitive, won't come out right
                'DocumentNumber'        => $this->_documentNumber,

                'Address'               => $this->_address,
                'AddressNumber'         => $this->_addressNumber,
                'AddressComplement'     => $this->_addressComplement,
                'ZipCode'               => $this->_zipCode,
                'Neighborhood'          => $this->_neighborhood,
                'City'                  => $this->_city,
                'State'                 => $this->_state,
                'Country'               => $this->_country,
            ),
            SOAP_ENC_OBJECT,
            'CreditCard',
            $this->getGateway()->getNameSpace());
    }

    public function __toString()
    {

        // @TODO: make sure we retrieve the CC Number that is sensitive (and stored in SensitiveValue) properly
        // otherwise it will get erased before we can use it

        return
            $this->_installment .
            $this->_creditCardNumber . // @TODO: sensitive, won't come out right
            $this->_holder .
            $this->_expirationDate . // @TODO: sensitive, won't come out right
            $this->_securityCode . // @TODO: sensitive, won't come out right
            $this->_documentNumber .

            $this->_address .
            $this->_addressNumber .
            $this->_addressComplement .
            $this->_zipCode .
            $this->_neighborhood .
            $this->_city .
            $this->_state .
            $this->_country;
    }

}
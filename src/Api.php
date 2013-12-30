<?php
namespace Payum\DineroMail;

use Payum\DineroMail\Api\Credentials;
use Payum\DineroMail\Api\Gateway;
use Payum\DineroMail\Api\Objects\Buyer;
use Payum\DineroMail\Api\Objects\CreditCard;
use Payum\DineroMail\Api\ReferenceConnection;
use Payum\DineroMail\Api\CreditCardConnection;
use Payum\DineroMail\Api\Connection;
use Payum\DineroMail\Api\DMSoapClient;
/**
 * Represents and contains all logic required to call the DineroMail
 * web service
 *
 * @see DineroMailConnection, DineroMailException, DineroMailCredentials,
 * DineroMailGateway and DineroMail objects.
 */

abstract class Api
{

    //Possible values:  ARS, BRL, MXN, CLP, USD
    const DINEROMAIL_DEFAULT_CURRENCY = "USD";

    const DINEROMAIL_NS_GATEWAY_SANDBOX = "https://sandboxapi.dineromail.com/";
    const DINEROMAIL_WDSL_GATEWAY_SANDBOX = "https://sandboxapi.dineromail.com/dmapi.asmx?WSDL";
    const DINEROMAIL_NS_GATEWAY = "https://api.dineromail.com/";
    const DINEROMAIL_WDSL_GATEWAY = "https://api.dineromail.com/dmapi.asmx?WSDL";


    protected $_currency;
    protected $_provider;

    protected $_testModeSettings = '';
    protected $_sandboxMode;

    protected $_connection = null;
    protected $_client = null;

    protected $_isLive;



    public function setConnection(Connection $connection)
    {
        return $this->_connection = $connection;
    }

    /**
     * Returns a setup connection
     * @return Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    public function setCurrency($currency)
    {
        return $this->_currency = $currency;
    }

    public function getCurrency()
    {
        return $this->_currency;
    }

    public function setProvider($provider)
    {
        return $this->_provider = $provider;
    }

    public function getProvider()
    {
        return $this->_provider;
    }

    public function setTestModeSettings($settings)
    {
        return $this->_testModeSettings = $settings;
    }

    public function getTestModeSettings()
    {
        return $this->_testModeSettings;
    }

    public function getSandboxMode()
    {
        return $this->_sandboxMode;
    }

    public function getClient()
    {
        return $this->getConnection()->getClient();
    }

    /**
     * Whether we're in live mode or not
     * @return bool
     */
    public function isLive()
    {
        return $this->_isLive;
    }

    /**
     * Returns the soap credential object
     *
     * @return SOAPVar the soap object
     */
    protected function credentialsObject()
    {


        return new \SOAPVar(array('APIUserName' => $this->getConnection()->getCredentials()->getUserName(),
                                 'APIPassword' => $this->getConnection()->getCredentials()->getPassword()),
            SOAP_ENC_OBJECT,
            'APICredential',
            $this->getConnection()->getGateway()->getNameSpace());
    }

    /**
     * makes the raw call to the service using the SoapClient
     * @see Vendor_DineroMail_Exception
     *
     * @param $function string function to call
     * @param $parameters array contains the parameters to send to the webservice
     * @return stdClass raw webservice response
     * @throws DineroMailException in case some error
     */
    protected function call($function, array $parameters)
    {

        try {
            $response = $this->getClient()->$function($parameters);
            return $response;
        } catch (SoapFault $ex) {
            throw new DineroMailException($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Returns an unique id for each service call
     *
     * @param void
     * @return string al simple call to the microtime function
     */
    protected function uniqueId()
    {

        return (string)time();
    }

    /**
     * Returns a md5 hash of all given parameters
     *
     * @param 1..n parameters to hash
     * @return string containing the md5
     */
    protected function hash( /* polimorphic */)
    {

        $args = func_get_args();
        return md5(implode("", $args));
    }

    /**
     * Returns an items chain string for our hash function
     * @param $items
     * @return string
     */
    protected function getItemsChain($items)
    {
        $itemsChain = '';
        foreach ($items as $item) {
            $itemsChain .= $item;
        }

        return $itemsChain;
    }

    /**
     * Returns our items in a way we can send in a request
     * @param $items
     * @return array
     */
    protected function getSoapItems($items)
    {
        $oitems = array();
        foreach ($items as $item) {
            $oitems[] = $item->asSoapObject();
        }

        return $oitems;
    }

}
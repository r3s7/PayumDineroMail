<?php

/**
 * Represents and contains all logic required to call the DineroMail
 * web service
 *
 * @see DineroMailConnection, DineroMailException, DineroMailCredentials,
 * DineroMailGateway and DineroMail objects.
 */
require("DineroMailDumper.php");
require("DineroMailException.php");
require("DineroMailGateway.php");
require("DineroMailCredentials.php");
require("DineroMailConnection.php");
require("Objects/DineroMailObject.php");
require("Objects/DineroMailBuyer.php");
require("Objects/DineroMailItem.php");


class DineroMailAction
{

    //Possible values:  ARS, BRL, MXN, CLP, USD
    const DINEROMAIL_DEFAULT_CURRENCY = "USD";

    const DINEROMAIL_NS_GATEWAY_SANDBOX = "https://sandboxapi.dineromail.com/";
    const DINEROMAIL_WDSL_GATEWAY_SANDBOX = "https://sandboxapi.dineromail.com/dmapi.asmx?WSDL";

    const DINEROMAIL_NS_GATEWAY = "https://sandboxapi.dineromail.com/";
    const DINEROMAIL_WDSL_GATEWAY = "https://sandboxapi.dineromail.com/dmapi.asmx?WSDL";

    //Possible values: rapipago, pagofacil, bapro, cobroexpress
    const DINEROMAIL_DEFAULT_PROVIDER = "pagofacil";


    protected $_currency;
    protected $_provider;

    protected $_connection = null;
    protected $_client = null;


    public function __construct(
        $gatewayUsername,
        $gatewayPassword,
        $encryption,
        $sandbox,
        $defaultProvider = DINEROMAIL_DEFAULT_PROVIDER,
        $defaultCurrency = DINEROMAIL_DEFAULT_CURRENCY
    )
    {

        $credentials = new DineroMailCredentials($gatewayUsername, $gatewayPassword);

        if ($sandbox == true) {

            $gateway = new DineroMailGateway(DINEROMAIL_NS_GATEWAY_SANDBOX, DINEROMAIL_WDSL_GATEWAY_SANDBOX);
        } else {

            $gateway = new DineroMailGateway(DINEROMAIL_NS_GATEWAY, DINEROMAIL_WDSL_GATEWAY);
        }

        $this->_connection = new DineroMailConnection($credentials, $gateway, $encryption);
        
        $this->setupClient();
    }

    public function setConnection(DineroMailConnection $connection)
    {
        return $this->_connection = $connection;
    }

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

    protected function getClient()
    {
        return $this->_client;
    }

    /**
     * Setups the soap client object
     *
     * @return SoapClient the soap object
     */
    protected function setupClient()
    {

        $this->_client = new SoapClient($this->getConnection()->getGateway()->getWdsl(),
            array('trace' => 1,
                'exceptions' => 1));
    }

    /**
     * Returns the soap credential object
     *
     * @return SOAPVar the soap object
     */
    protected function credentialsObject()
    {


        return new SOAPVar(array('APIUserName' => $this->getConnection()->getCredentials()->getUserName(),
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
     * encapsulates the call to the DineroMail web service invoking the method
     * doPaymentWithReference
     * @link https://api.dineromail.com/dmapi.asmx?WSDL
     *
     * @param array $items items to create the payment
     * @param DineroMailBuyer $buyer contains the buyer information
     * @param string $transactionId an unique TX id
     */
    public function doPaymentWithReference(array $items, DineroMailBuyer $buyer, $transactionId, $message, $subject)
    {

        $messageId = $this->uniqueId();
        $itemsChain = '';
        $oitems = array();

        foreach ($items as $item) {
            $itemsChain .= $item;
            $oitems[] = $item->asSoapObject();
        }


        $hash = $this->hash($transactionId,
            $messageId,
            $itemsChain,
            $buyer,
            $this->getProvider(),
            $subject,
            $message,
            $this->getConnection()->getCredentials()->getPassword());


        $request = array('Credential' => $this->credentialsObject(),
            'Crypt' => false,
            'MerchantTransactionId' => $transactionId,
            'UniqueMessageId' => $messageId,
            'Provider' => $this->getProvider(),
            'Message' => $message,
            'Subject' => $subject,
            'Items' => $oitems,
            'Buyer' => $buyer->asSoapObject(),
            'Hash' => $hash);


        $result = $this->call("DoPaymentWithReference", $request);

        return $result->DoPaymentWithReferenceResult;

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


}
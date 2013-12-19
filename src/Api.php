<?php
namespace Payum\DineroMail;

use Payum\DineroMail\Api\Credentials;
use Payum\DineroMail\Api\Gateway;
use Payum\DineroMail\Api\Objects\Buyer;
use Payum\DineroMail\Api\PaymentWithReferenceConnection;
use Payum\DineroMail\Api\Connection;
use Payum\DineroMail\Api\DMSoapClient;
/**
 * Represents and contains all logic required to call the DineroMail
 * web service
 *
 * @see DineroMailConnection, DineroMailException, DineroMailCredentials,
 * DineroMailGateway and DineroMail objects.
 */

class Api
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
        $config,
        $defaultCurrency = self::DINEROMAIL_DEFAULT_CURRENCY
    )
    {

        if(!empty($config['provider'])){

            $this->setProvider($config['provider']);
        } else{
            $this->setProvider(self::DINEROMAIL_DEFAULT_PROVIDER);
        }

        $credentials = new Credentials($config['username'],$config['password']);

        if ($config['sandbox'] == true) {

            $gateway = new Gateway(self::DINEROMAIL_NS_GATEWAY_SANDBOX, self::DINEROMAIL_WDSL_GATEWAY_SANDBOX);
        } else {

            $gateway = new Gateway(self::DINEROMAIL_NS_GATEWAY, self::DINEROMAIL_WDSL_GATEWAY);
        }

        // we'll want to use the appropriate type of connection here depending on which type of payment we're making
//        $this->_connection = new PaymentWithReferenceConnection($credentials, $gateway, $config['encryption']);
        $this->_connection = new Connection($credentials, $gateway);

        $this->setupClient();
    }

    public function setConnection(Connection $connection)
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

    public function getClient()
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

        $this->_client = new DMSoapClient($this->getConnection()->getGateway()->getWdsl(),
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
     * encapsulates the call to the DineroMail web service invoking the method
     * doPaymentWithReference
     * @link https://api.dineromail.com/dmapi.asmx?WSDL
     *
     * @param array $items items to create the payment
     * @param DineroMailBuyer $buyer contains the buyer information
     * @param string $transactionId an unique TX id
     */
    public function doPaymentWithReference(array $items, Buyer $buyer, $transactionId, $message, $subject)
    {
        $messageId = $this->uniqueId();

        $hash = $this->hash($transactionId,
            $messageId,
            $this->getItemsChain($items),
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
                         'Items' => $this->getSoapItems($items),
                         'Buyer' => $buyer->asSoapObject(),
                         'Hash' => $hash);

        $result = $this->call("DoPaymentWithReference", $request);

        return $result->DoPaymentWithReferenceResult;

    }

    /**
     * encapsulates the call to the DineroMail web service invoking the method
     * DoPaymentWithCreditCard
     * @link https://api.dineromail.com/dmapi.asmx?WSDL
     *
     * @param array $items items to create the payment
     * @param DineroMailBuyer $buyer contains the buyer information
     * @param string $transactionId an unique TX id
     * @param string $message the API says this is optional, although we aren't currently treating it that way in our code here
     * @param string $subject the API says this is optional, although we aren't currently treating it that way in our code here
     */
    public function doPaymentWithCreditCard(array $items, Buyer $buyer, CreditCard $creditCard, $transactionId, $message, $subject)
    {
        $messageId = $this->uniqueId();

        $hash = $this->hash($transactionId,
            $messageId,
            $this->getItemsChain($items),
            $buyer,
            $creditCard,
            $this->getProvider(),
            $subject,
            $message,
            $this->getConnection()->getCredentials()->getPassword());


        $request = array(
            'Credential'                => $this->credentialsObject(),
            'Crypt'                     => false,
            'MerchantTransactionId'     => $transactionId,
            'UniqueMessageId'           => $messageId,
            'Provider'                  => $this->getProvider(),
            'Message'                   => $message,
            'Subject'                   => $subject,
            'Items'                     => $this->getSoapItems($items),
            'Buyer'                     => $buyer->asSoapObject(),
            'CreditCard'                => $creditCard->asSoapObject(),
            'Hash'                      => $hash
        );

        $result = $this->call("DoPaymentWithCreditCard", $request);

        return $result->DoPaymentWithCreditCardResult;

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
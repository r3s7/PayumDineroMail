<?php

namespace Payum\DineroMail;

use Payum\DineroMail\Api;
use Payum\DineroMail\Api\Credentials;
use Payum\DineroMail\Api\Gateway;
use Payum\DineroMail\Api\Objects\Buyer;
use Payum\DineroMail\Api\Objects\CreditCard;
use Payum\DineroMail\Api\ReferenceConnection;
use Payum\DineroMail\Api\CreditCardConnection;
use Payum\DineroMail\Api\Connection;
use Payum\DineroMail\Api\DMSoapClient;


class DoPaymentWithReferenceApi extends Api{


    //Possible values: rapipago, pagofacil, bapro, cobroexpress
    const DINEROMAIL_DEFAULT_PROVIDER = "servipag";


    public function __construct(
        $config,
        $defaultCurrency = self::DINEROMAIL_DEFAULT_CURRENCY
    )
    {

        $this->setCurrency($defaultCurrency);

        if(!empty($config['provider'])){

            $this->setProvider($config['provider']);
        } else{
            $this->setProvider(self::DINEROMAIL_DEFAULT_PROVIDER);
        }

        if (!empty($config['test_mode_settings'])) {
            $this->setTestModeSettings($config['test_mode_settings']);
        }
        $this->_sandboxMode = ($config['sandbox'] ? true : false);

        $credentials = new Credentials($config['username'],$config['password']);

        if ($config['sandbox'] == true) {
            $this->_isLive = false;
            $gateway = new Gateway(self::DINEROMAIL_NS_GATEWAY_SANDBOX, self::DINEROMAIL_WDSL_GATEWAY_SANDBOX);
        } else {
            $this->_isLive = true;
            $gateway = new Gateway(self::DINEROMAIL_NS_GATEWAY, self::DINEROMAIL_WDSL_GATEWAY);
        }

        // we may want to use the appropriate type of connection here depending on which type of payment we're making
        // but for now, looks like we can get by with just one
        $this->setConnection(new Connection($credentials, $gateway, $config['encryption']));
    }


    /**
     * encapsulates the call to the DineroMail web service invoking the method
     * doPaymentWithReference
     * @link https://api.dineromail.com/dmapi.asmx?WSDL
     *
     * @param array $items items to create the payment
     * @param \Buyer|\Payum\DineroMail\Api\Objects\Buyer|\Payum\DineroMail\DineroMailBuyer $buyer contains the buyer information
     * @param string $transactionId an unique TX id
     * @param $message
     * @param $subject
     * @return mixed
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

}
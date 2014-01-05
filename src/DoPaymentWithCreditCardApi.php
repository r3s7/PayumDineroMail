<?php

namespace Payum\DineroMail;

use Payum\DineroMail\Api;
use Payum\DineroMail\Request\Soap\Credentials;
use Payum\DineroMail\Request\Soap\Gateway;
use Payum\DineroMail\Request\Soap\Objects\Buyer;
use Payum\DineroMail\Request\Soap\Objects\CreditCard;
use Payum\DineroMail\Request\Soap\Connection;

class DoPaymentWithCreditCardApi extends Api{

    //Possible values: rapipago, pagofacil, bapro, cobroexpress
    const DINEROMAIL_DEFAULT_PROVIDER = "ALL";


    public function __construct(
        $config,
        $defaultCurrency = self::DINEROMAIL_DEFAULT_CURRENCY
    )
    {

        $this->setCurrency($defaultCurrency);

        $this->setProvider(self::DINEROMAIL_DEFAULT_PROVIDER);

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
     * DoPaymentWithCreditCard
     * @link https://api.dineromail.com/dmapi.asmx?WSDL
     *
     * @param array $items items to create the payment
     * @param \Buyer|\DineroMailBuyer|\Payum\DineroMail\Api\Objects\Buyer $buyer contains the buyer information
     * @param CreditCard $creditCard
     * @param string $transactionId an unique TX id
     * @param string $message the API says this is optional, although we aren't currently treating it that way in our code here
     * @param string $subject the API says this is optional, although we aren't currently treating it that way in our code here
     * @return mixed
     */
    public function doPaymentWithCreditCard(array $items, Buyer $buyer, CreditCard $creditCard, $transactionId, $message, $subject)
    {
        $messageId = $this->uniqueId();

        $hash = $this->hash(
            $transactionId,
            $messageId,
            $this->getItemsChain($items),
            $buyer,
            $creditCard,
            $this->getProvider(),
            $subject,
            $message,
            $this->getConnection()->getCredentials()->getPassword()
        );


        $request = array(
            'Credential'                => $this->credentialsObject(),
            'Crypt'                     => false,
            'MerchantTransactionId'     => $transactionId,
            'Items'                     => $this->getSoapItems($items),
            'Buyer'                     => $buyer->asSoapObject(),
            'Provider'                  => $this->getProvider(),
            'CreditCard'                => $creditCard->asSoapObject(),
            'Subject'                   => $subject,
            'Message'                   => $message,
            'UniqueMessageId'           => $messageId,
            'Hash'                      => $hash
        );

        $result = $this->call("DoPaymentWithCreditCard", $request);

        return $result->DoPaymentWithCreditCardResult;

    }


}
<?php

namespace Payum\DineroMail\Api;

use Payum\DineroMail\Api\ClientInterface;

/*
 * is the same SoapClient but with especial methods for retrieve information about DineroMail requests and responses
 * */
abstract class DMSoapClient extends \SoapClient implements ClientInterface
{

}
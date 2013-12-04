<?php

define("DINEROMAIL_API_PWD", "TEST-TEST-TEST-TEST-TEST");
define("DINEROMAIL_API_USER", "TEST");

/*
* Possible values for DINEROMAIL_NS_GATEWAY AND DINEROMAIL_WDSL_GATEWAY
*
* Production NS: https://sandboxapi.dineromail.com/
* Production WDSL: https://sandboxapi.dineromail.com/dmapi.asmx?WSDL
*
* or
*
* Development NS: https://api.dineromail.com/
* Development WDSL: https://api.dineromail.com/dmapi.asmx?WSDL
*
*/

define("DINEROMAIL_NS_GATEWAY", "https://sandboxapi.dineromail.com/");
define("DINEROMAIL_WDSL_GATEWAY", "https://sandboxapi.dineromail.com/dmapi.asmx?WSDL");

define("DINEROMAIL_CONNECTION_ENCRYPTION", true);

/* Possible values:
*  ARS, BRL, MXN, CLP, USD
*/
define("DINEROMAIL_DEFAULT_CURRENCY", "USD");

/* Possible values:
*  rapipago, pagofacil, bapro, cobroexpress
*/
define("DINEROMAIL_DEFAULT_PROVIDER", "pagofacil");



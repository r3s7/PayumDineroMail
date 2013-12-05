<?php
/*
 * is the same SoapClient but with especial methods for retrieve information about DineroMail requests and responses
 * */
class DineroMailSoapClient extends SoapClient
{

    public function getDineroMailLastResponse()
    {
        return simplexml_load_string(
            str_replace(
                "soap:",
                "",
                $this->__last_response))->Body->DoPaymentWithReferenceResponse->DoPaymentWithReferenceResult;
    }

    public function getDineroMailLastRequest()
    {
        return simplexml_load_string(
            str_replace(
                "ns1:",
                "",
                str_replace("SOAP-ENV:", "", $this->__last_request)))->Body->DoPaymentWithReference;
    }


}
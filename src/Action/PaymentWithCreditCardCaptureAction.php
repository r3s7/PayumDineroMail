<?php
/*
 * See the documentation about this in:
 * http://payum.forma-dev.com/documentation/master/Payum/develop-payment-gateway-with-payum
 *
 * */
namespace Payum\DineroMail\Action;

use Payum\DineroMail\Action\PaymentCaptureAction;
use Payum\DineroMail\Api\DineroMailException;
use Payum\DineroMail\Api;
use Payum\DineroMail\Api\Objects\Buyer;
use Payum\DineroMail\Api\Objects\CreditCard;
use Payum\DineroMail\Api\Objects\Item;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Model\ArrayObject;
use Payum\YiiExtension\Model\PaymentDetailsActiveRecordWrapper;

class PaymentWithCreditCardCaptureAction extends PaymentCaptureAction
{
    protected $creditCard;
    protected $possibleStatuses = array('PENDING','COMPLETED','DENIED','ERROR');


    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = $request->getModel();

        $getPayment = $request->getPayment();

        $this->model = $getPayment->getPaymentDetails();

        if (
            isset($this->model['CurrencyCode']) &&
            isset($this->model['MerchantTransactionId']) &&
            isset($this->model['Name']) &&
            isset($this->model['LastName']) &&
            isset($this->model['Email']) &&
            isset($this->model['PaymentProvider']) &&
            isset($this->model['Installment']) &&
            isset($this->model['Holder']) &&
            isset($this->model['DocumentNumber'])
        ) {

            $getDineroMailConfig = \DineroMailCreditCardConfig::model()->findByPk($getPayment->payment_method_id);

            /* @var $Api Api */
            // get back our new DineroMailAction instance
            $Api = $getDineroMailConfig->getApi();

            // set our currency code for our API
            // this will be set on a per item basis later on
            $Api->setCurrency($this->model['CurrencyCode']);

            $Api->setProvider($this->model['PaymentProvider']);

            $this->prepareToPay($request, $Api);


            //set as 1 for COMPLETED status, 2 for PENDING status (other values cause DENIED status)
            if ($Api->getSandboxMode() && $Api->getTestModeSettings() != '') {
                $this->model['MerchantTransactionId'] = $Api->getTestModeSettings();
            }

            try {
                // send off our DineroMail transaction to the doPaymentWithCreditCard function
                $result = $Api->doPaymentWithCreditCard(
                    $this->items,
                    $this->buyer,
                    $this->creditCard,
                    $this->model['MerchantTransactionId'],
                    $this->model['Message'],
                    $this->model['Subject']
                );

                $this->updatePaymentStatus($getPayment,$result);

                $this->logPaymentTransaction($result,$getDineroMailConfig);


            } catch (DineroMailException $e) {
                $getPayment->status = 'ERROR';
                $getPayment->save();

                $variables = array(
                    'ipAddress'          => \Yii::app()->request->userHostAddress,
                    'User'               => (isset(\Yii::app()->user->id)) ? Yii::app()->user->id : null,
                    'submissionId'       => (isset($getDineroMailConfig->submissionId)) ? $getDineroMailConfig->submissionId : null,
                    'message'            => $e,
                );
                \Yii::app()->applog->log("dineromail-unknown-error", null, $variables);

                throw new \CHttpException(400, \Yii::t('app', 'unknow error'));
            }


        } else {

            $variables = array(
                'ipAddress'          => \Yii::app()->request->userHostAddress,
                'User'               => (isset(\Yii::app()->user->id)) ? Yii::app()->user->id : null,
            );
            \Yii::app()->applog->log("dineromail-bad-request", null, $variables);

            throw new \CHttpException(400, \Yii::t('app', 'bad request'));
        }
    }

    public function prepareToPay($request, $Api)
    {
        parent::prepareToPay($request, $Api);

        // @TODO: instead of passing in the whole api model, let's just pass the namespace?
        // seems to be all we're using in there
        $this->creditCard = new CreditCard($Api);

        $this->creditCard->setInstallment($this->model['Installment']);
        $this->creditCard->setCreditCardNumber($this->model['CreditCardNumber']);
        $this->creditCard->setHolder($this->model['Holder']);
        $this->creditCard->setExpirationDate($this->model['ExpirationDate']);
        $this->creditCard->setSecurityCode($this->model['SecurityCode']);
        $this->creditCard->setDocumentNumber($this->model['DocumentNumber']);


    }

    protected function updatePaymentStatus($payment,$result)
    {
        if(!in_array($result->Status,$this->possibleStatuses))
            throw new \CHttpException(400, \Yii::t('app', 'bad request'));

        $payment->status = $result->Status ;
        $payment->unique_message_id = $result->UniqueMessageId;
        $payment->save();

    }

    protected function logPaymentTransaction($result,$paymentMethodConfig)
    {

        if(!in_array($result->Status,$this->possibleStatuses))
            throw new \CHttpException(400, \Yii::t('app', 'bad request'));

        $lowerCaseStatus = strtolower($result->Status);

        $variables = array(
            'ipAddress'             => \Yii::app()->request->userHostAddress,
            'User'                  => (isset(\Yii::app()->user->id)) ? Yii::app()->user->id : null,
            'submissionId'          => (isset($paymentMethodConfig->submissionId)) ? $paymentMethodConfig->submissionId : null,
            'message'               => $result->Message,
            'uniqueMessageId'       => $result->UniqueMessageId,
            'merchantTransactionId' => $result->MerchantTransactionId,
            'status'                => $result->Status
        );
        \Yii::app()->applog->log("dineromail-{$lowerCaseStatus}", null, $variables);
    }


    public function supports($request)
    {
        $paymentName = explode('-',$request->getModel()->activeRecord->paymentName);
        $paymentMethod = $paymentName[0];

        if($paymentMethod == 'DineroMailCreditCard'){
            return true;

        }else{
            return false;
        }
    }
}
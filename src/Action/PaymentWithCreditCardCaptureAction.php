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
            isset($this->model['MerchantTransactionId']) &&
            isset($this->model['Name']) &&
            isset($this->model['LastName']) &&
            isset($this->model['Email']) &&
            isset($this->model['Installment']) &&
            isset($this->model['Holder']) &&
            isset($this->model['DocumentNumber'])
        ) {

            $getDineroMailConfig = \DineroMailCreditCardConfig::model()->findByPk($getPayment->payment_method_id);

            /* @var $Api Api */
            // get back our new DineroMailAction instance
            $Api = $getDineroMailConfig->getApi();

            $this->prepareToPay($getPayment->order_id, $Api);

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


                if ($result->Status == "PENDING") {

                    $model['status'] = 'PENDING';

                    // @TODO: think we don't need this any more, so should we just leave it out?
                    // do we want to add anything else in instead?
                    // $getPayment->bank_transfer_reference = $result->BarcodeImageUrl;
                    $getPayment->save();
                }

                /* I have doubts here, I think this payment method never gets the COMPLETED status immediately
                /* (I think this thing applies only for sandbox tests)
                 * */
                if ($result->Status == "COMPLETED") {

                    $model['status'] = 'COMPLETED';

                    $getPayment->status = 'COMPLETED';
                    // @TODO: think we don't need this any more, so should we just leave it out?
                    // do we want to add anything else in instead?
                    // $getPayment->bank_transfer_reference = $result->BarcodeImageUrl;
                    $getPayment->save();
                }


                if ($result->Status == "DENIED") {

                    $model['status'] = 'DENIED';

                    if($getPayment->status !== 'COMPLETED'){
                        $getPayment->status = 'DENIED';
                        $getPayment->save();
                    }
                }

                if ($result->Status == "ERROR") {
                    $model['status'] = 'ERROR';

                    if($getPayment->status !== 'COMPLETED'){
                        $getPayment->status = 'ERROR';
                        $getPayment->save();
                    }
                }


            } catch (DineroMailException $e) {

                $model['status'] = 'ERROR';

                if($getPayment->status !== 'COMPLETED'){
                    $getPayment->status = 'ERROR';
                    $getPayment->save();
                }
            }


        } else {
            throw new \CHttpException(400, \Yii::t('app', 'bad request'));
        }
    }

    public function prepareToPay($orderId, $Api)
    {
        parent::prepareToPay($orderId, $Api);

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

    public function supports($request)
    {
        return
            $request instanceof \WDCustomSecuredCaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
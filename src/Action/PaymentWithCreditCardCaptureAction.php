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
use Payum\DineroMail\Api\Objects\Item;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Model\ArrayObject;
use Payum\YiiExtension\Model\PaymentDetailsActiveRecordWrapper;

class PaymentWithCreditCardCaptureAction extends PaymentCaptureAction
{
    public function execute($request)
    {
//        $getPayumPaymentDetails = PaymentDetailsActiveRecordWrapper::findModelById(
//            'payum_payment',
//            $request->getModel()->getDetails()->getId()
//        );
//
//        $model = unserialize($getPayumPaymentDetails->activeRecord->attributes['_details']);
//
//        if (
//            array_key_exists('MerchantTransactionId', $model) &&
//            array_key_exists('Name', $model) &&
//            array_key_exists('LastName', $model) &&
//            array_key_exists('Address', $model) &&
//            array_key_exists('City', $model) &&
//            array_key_exists('Country', $model) &&
//            array_key_exists('Email', $model) &&
//            array_key_exists('Phone', $model)
//        ) {
//
            // @TODO: work out how we handle getting this model, but it is *definitely NOT* from a saved place in the DB :-)
            $this->model = ''; // ??? :-)

            // @TODO: let's clean this up down the road, but not right now

            $unSuglify = explode('-', $this->model['MerchantTransactionId']);

            $getPayment = \Payment::model()->findByPk($unSuglify[0]);

            $getDineroMailConfig = \DineroMailConfig::model()->findByPk($getPayment->payment_method_id);

            /* @var $Api Api */
            // get back our new DineroMailAction instance
            $Api = $getDineroMailConfig->getApi();

            $this->prepareToPay($getPayment->order_id, $Api);

            try {
                // send off our DineroMail transaction to the doPaymentWithCreditCard function
                $Api->doPaymentWithCreditCard(
                    $this->items,
                    $this->buyer,
                    $creditCard, // @TODO: oh wait, where did this come from? ;-)
                    $this->model['MerchantTransactionId'],
                    $this->model['Message'],
                    $this->model['Subject']
                );


                if ($Api->getClient()->getDineroMailLastResponse()->Status == "PENDING") {

                    $getPayment->status = 'PENDING';
                    $getPayment->bank_transfer_reference = $Api->getClient()->getDineroMailLastResponse()->BarcodeImageUrl;
                    $getPayment->save();
                    \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);
                }

                /* I have doubts here, I think this payment method never gets the COMPLETED status immediately
                /* (I think this thing applies only for sandbox tests)
                 * */
                if ($Api->getClient()->getDineroMailLastResponse()->Status == "COMPLETED") {

                    $getPayment->status = 'COMPLETED';
                    $getPayment->bank_transfer_reference = $Api->getClient()->getDineroMailLastResponse()->BarcodeImageUrl;
                    $getPayment->save();
                    \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);
                }


                if ($Api->getClient()->getDineroMailLastResponse()->Status == "DENIED") {

                    if($getPayment->status !== 'COMPLETED'){
                        $getPayment->status = 'DENIED';
                        $getPayment->save();
                    }
                    \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);
                }

                if ($Api->getClient()->getDineroMailLastResponse()->Status == "ERROR") {

                    if($getPayment->status !== 'COMPLETED'){
                        $getPayment->status = 'ERROR';
                        $getPayment->save();
                    }
                    \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);

                }


            } catch (DineroMailException $e) {

                if($getPayment->status !== 'COMPLETED'){
                    $getPayment->status = 'ERROR';
                    $getPayment->save();
                }
                \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);
            }


//        } else {
//
//
//            throw new \CHttpException(400, \Yii::t('app', 'bad request'));
//        }
    }

    public function supports($request)
    {

        $paymentName = explode('-',$request->getModel()->activeRecord->paymentName);
        $paymentMethod = $paymentName[0];

        if($paymentMethod == 'DineroMail'){
            return true;

        }else{
            return false;
        }

    }
}
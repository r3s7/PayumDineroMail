<?php
/*
 * See the documentation about this in:
 * http://payum.forma-dev.com/documentation/master/Payum/develop-payment-gateway-with-payum
 *
 * */
namespace Payum\DineroMail\Action;

use Payum\DineroMail\Action\PaymentCaptureAction;
use Payum\DineroMail\Api\DineroMailException;
use Payum\DineroMail\Api\Objects\Buyer;
use Payum\DineroMail\Api\Objects\Item;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Model\ArrayObject;
use Payum\YiiExtension\Model\PaymentDetailsActiveRecordWrapper;

class PaymentWithReferenceCaptureAction extends PaymentCaptureAction
{
    public function execute($request)
    {

        $getPayumPaymentDetails = PaymentDetailsActiveRecordWrapper::findModelById(
            'payum_payment',
            $request->getModel()->getDetails()->getId()
        );

        $model = unserialize($getPayumPaymentDetails->activeRecord->attributes['_details']);

        if (
            array_key_exists('MerchantTransactionId', $model) &&
            array_key_exists('Name', $model) &&
            array_key_exists('LastName', $model) &&
            array_key_exists('Address', $model) &&
            array_key_exists('City', $model) &&
            array_key_exists('Country', $model) &&
            array_key_exists('Email', $model) &&
            array_key_exists('Phone', $model)
        ) {

            $model['Message'] = 'This is a payment of '. $model['MerchantTransactionId'];
            $model['Subject'] = 'Payment of '. $model['MerchantTransactionId'];

            $unSuglify = explode('-', $model['MerchantTransactionId']);

            $getPayment = \Payment::model()->findByPk($unSuglify[0]);

            $getDineroMailConfig = \DineroMailConfig::model()->findByPk($getPayment->payment_method_id);

            $getOrder = \Order::model()->findByPk($getPayment->order_id);
            $Api = $getDineroMailConfig->getApi();

            $this->prepareToPay($getPayment->order_id, $Api);

            //uncomment this if you want a successfully transaction in sandbox
            //set in 1 for COMPLETED status and 2 for PENDING status (other values retrieves DENIED status)
            //$model['MerchantTransactionId'] ='1';

            /* Execute the transaction */

            try {
                //trying to execute the DineroMail transaction through the doPaymentWithReference function
                $Api->doPaymentWithReference(
                    $items,
                    $buyer,
                    $model['MerchantTransactionId'],
                    $model['Message'],
                    $model['Subject']
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


        } else {


            throw new \CHttpException(400, \Yii::t('app', 'bad request'));
        }


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
<?php
/*
 * See the documentation about this in:
 * http://payum.forma-dev.com/documentation/master/Payum/develop-payment-gateway-with-payum
 *
 * */
namespace Payum\DineroMail\Action;

use Payum\DineroMail\Api\DineroMailException;
use Payum\DineroMail\Api\Objects\Buyer;
use Payum\DineroMail\Api\Objects\Item;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Model\ArrayObject;
use Payum\YiiExtension\Model\PaymentDetailsActiveRecordWrapper;

class PaymentWithReferenceCaptureAction extends PaymentAwareAction
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

            $getOrderItems = $getOrder->orderItems();

            //do purchase call to the payment gateway using username and password.


            //new DineroMailAction instance
            $Api = $getDineroMailConfig->getApi();

            /* Capture Buyer information, all information are required */

            /* You need pass the reference of the related DineroMailAction instance to the DineroMailBuyer instance,
             * the DineroMailBuyer instance need the Gateway information stored in DineroMailAction instance,
             * because each parent of the abstract class DineroMailObject needs the Gateway attributes.
             * */
            $buyer = new Buyer($Api);
            $buyer->setName($model['Name']);
            $buyer->setLastName($model['LastName']);
            $buyer->setAddress($model['Address']);
            $buyer->setCity($model['City']);
            $buyer->setCountry($model['Country']);
            $buyer->setEmail($model['Email']);
            $buyer->setPhone($model['Phone']);

            /* Capture Items information, all information are required except Quantity and Currency
             * remember: you set the default currency and provider in the DineroMailAction Constructor.
             */

            $items = array();
            foreach ($getOrderItems as $item) {

                /* You need pass the reference of the related DineroMailAction instance to the DineroMailItem instance,
                 * the Dine \CVarDumper::dump($items,10,true);
                exit();roMailItem instance need the Gateway information stored in DineroMailAction instance,
                 * because each parent of the abstract class DineroMailObject needs the Gateway attributes.
                 * */
                $currentItem = null;
                $currentItem = new Item($Api);
                $currentItem->setCode($item->type .'-'. $item->id);
                $currentItem->setName($item->name);
                $currentItem->setDescription($item->name);

                $currentItem->setAmount($item->amount);

                if (isset($model['Items']['Currency'])) {
                    $currentItem->setCurrency($model['Items']['Currency']);
                }

                $items[] = $currentItem;
            }

            //uncomment this if you want a successfully transaction in sandbox
            //set in 1 for COMPLETED status and 2 for PENDING status (other values retrieves DENIED status)
            //$model['MerchantTransactionId'] ='1';

            /* Execute the transaction */

            if ($Api->getSandboxMode() && $Api->getTestModeSettings() != '') {
                $model['MerchantTransactionId'] = $Api->getTestModeSettings();
            }

            $model['Message'] = 'This is a payment of ' . $model['MerchantTransactionId'];
            $model['Subject'] = 'Payment of ' . $model['MerchantTransactionId'];


            try {
                //trying to execute the DineroMail transaction through the doPaymentWithReference function
                $result = $Api->doPaymentWithReference(
                    $items,
                    $buyer,
                    $model['MerchantTransactionId'],
                    $model['Message'],
                    $model['Subject']
                );


                if ($result->Status == "PENDING") {

                    $getPayment->status = $result->Status;
                    $getPayment->bank_transfer_reference = $result->BarcodeImageUrl;
                    $getPayment->save();
                    \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);
                }

                /* I have doubts here, I think this payment method never gets the COMPLETED status immediately
                /* (I think this thing applies only for sandbox tests)
                 * */
                if ($result->Status == "COMPLETED") {

                    $getPayment->status = $result->Status;
                    $getPayment->bank_transfer_reference = $result->BarcodeImageUrl;
                    $getPayment->save();
                    \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);
                }


                if ($result->Status == "DENIED") {

                    if($getPayment->status !== 'COMPLETED'){
                        $getPayment->status = $result->Status;
                        $getPayment->save();
                    }
                    \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);
                }

                if ($result->Status == "ERROR") {

                    if($getPayment->status !== 'COMPLETED'){
                        $getPayment->status = $result->Status;
                        $getPayment->save();
                    }
                    \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);

                }


            } catch (DineroMailException $e) {

                if($getPayment->status !== 'COMPLETED'){
                    $getPayment->status = $result->Status;
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
<?php
/*
 * See the documentation about this in:
 * http://payum.forma-dev.com/documentation/master/Payum/develop-payment-gateway-with-payum
 *
 * */
namespace Payum\DineroMail\Action;

//Payum core namespaces
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Model\ArrayObject;

// Payum Yii extension namespaces
use Payum\YiiExtension\Model\PaymentDetailsActiveRecordWrapper;

//Internal namespaces
use Payum\DineroMail\Request\Common\DineroMailException;
use Payum\DineroMail\Request\HttpGet\Objects\Buyer;
use Payum\DineroMail\Request\HttpGet\Objects\Item;
use Payum\DineroMail\Request\HttpGet\Objects\Merchant;

class PaymentWithPayButtonCaptureAction extends PaymentAwareAction
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
            array_key_exists('DocumentNumber', $model) &&
            array_key_exists('DocumentType', $model) &&
            array_key_exists('Name', $model) &&
            array_key_exists('LastName', $model) &&
            array_key_exists('Email', $model) &&
            array_key_exists('Phone', $model) &&
            array_key_exists('Sex', $model)
        ) {


            $unSuglify = explode('-', $model['MerchantTransactionId']);

            $getPayment = \Payment::model()->findByPk($unSuglify[0]);

            $getDineroMailConfig = \DineroMailPayButtonConfig::model()->findByPk($getPayment->payment_method_id);

            $getOrder = \Order::model()->findByPk($getPayment->order_id);

            $getOrderItems = $getOrder->orderItems();

            //get Api
            $Api = $getDineroMailConfig->getApi();

            if ($Api->getSandboxMode() && $Api->getTestModeSettings() != '') {
                //@TODO we need Merchant in 1721561 and country_id = 1 (note that 1=ar  2=br 3=cl 4=mx)
                $Api->setMerchant = new Merchant($Api->getTestModeSettings());
                $Api->setCountryId = '1';
            }

            /* Capture Buyer information, all information are required */

            /* You need pass the reference of the related DineroMailAction instance to the DineroMailBuyer instance,
             * the DineroMailBuyer instance need the Gateway information stored in DineroMailAction instance,
             * because each parent of the abstract class DineroMailObject needs the Gateway attributes.
             * */
            $buyer = new Buyer($Api);
            $buyer->setDocumentNumber($model['DocumentNumber']);
            $buyer->setDocumentType($model['DocumentType']);
            $buyer->setName($model['Name']);
            $buyer->setLastName($model['LastName']);
            $buyer->setEmail($model['Email']);
            $buyer->setPhone($model['Phone']);
            $buyer->setSex($model['Sex']);


            /* Capture Items information, all information are required except Quantity and Currency
             * remember: you set the default currency and provider in the DineroMailAction Constructor.
             */

            $items = array();
            foreach ($getOrderItems as $item) {

                $currentItem = null;
                $currentItem = new Item();
                $currentItem->setName($item->name);
                //setQuantity is not needed
                $currentItem->setAmount($item->amount);

                $items[] = $currentItem;
            }


            try {
                //this method redirects to the DineroMail checkOut page
                //\CVarDumper::dump($items,10,true); \Yii::app()->end();
                $Api->doPaymentWithPayButton(
                    $buyer,
                    $items,
                    $Api->getMerchant(),
                    $request->getModel()->activeRecord->_after_url,
                    $request->getModel()->activeRecord->_after_url,
                    $request->getModel()->activeRecord->_after_url
                );
                //@TODO in medium-term we need here CountryId and PaymentMethodAvailable


            } catch (DineroMailException $e) {

                \Yii::app()->request->redirect($request->getModel()->activeRecord->_after_url);
            }


        } else {


            throw new \CHttpException(400, \Yii::t('app', 'bad request'));
        }


    }

    public function supports($request)
    {

        $paymentName   = explode('-', $request->getModel()->activeRecord->_payment_name);
        $paymentMethod = $paymentName[0];

        if ($paymentMethod == 'DineroMailPayButton') {
            return true;

        } else {
            return false;
        }

    }
}
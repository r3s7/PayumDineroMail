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

        // @TODO check why DocumentNumber, DocumentType, Phone, and Sex are in blank
        if (
            array_key_exists('MerchantTransactionId', $model) &&
            array_key_exists('CurrencyCode', $model) &&
            array_key_exists('Name', $model) &&
            array_key_exists('LastName', $model) &&
            array_key_exists('Email', $model)
        ) {

            $getPayment          = \Payment::model()->findByPk($model['PaymentId']);
            $getDineroMailConfig = \DineroMailPayButtonConfig::model()->findByPk($getPayment->payment_method_id);
            $getOrder            = \Order::model()->findByPk($getPayment->order_id);
            $getOrderItems       = $getOrder->orderItems();

            //get Api
            $Api = $getDineroMailConfig->getApi();

            $buyer = new Buyer($Api);
            $buyer->setDocumentNumber(isset($model['DocumentNumber']) ? $model['DocumentNumber'] : null);
            $buyer->setDocumentType(isset($model['DocumentType']) ? $model['DocumentType'] : null);
            $buyer->setName($model['Name']);
            $buyer->setLastName($model['LastName']);
            $buyer->setEmail($model['Email']);
            $buyer->setPhone(isset($model['Phone']) ? $model['Phone'] : null);
            $buyer->setSex(isset($model['Sex']) ? $model['Sex'] : null);


            /* Capture Items information, all information are required except Quantity and Currency
             * remember: you set the default currency and provider in the DineroMailAction Constructor.
             */

            $items = array();
            foreach ($getOrderItems as $item) {
                $currentItem = null;
                $currentItem = new Item();
                $currentItem->setName($item->name);
                //setQuantity is not needed in our system
                $currentItem->setAmount($item->amount);
                $currentItem->setCurrencyCode($model['CurrencyCode']);
                $items[] = $currentItem;
            }

            try {
                //this method redirects to the DineroMail checkOut page
                $Api->doPaymentWithPayButton(
                    $buyer,
                    $items,
                    $Api->getMerchant(),
                    $model['MerchantTransactionId'],
                    $request->getModel(
                    )->activeRecord->_after_url . "&dm_transaction_status=COMPLETED&dm_transaction_id={$model['MerchantTransactionId']}",
                    $request->getModel(
                    )->activeRecord->_after_url . "&dm_transaction_status=PENDING&dm_transaction_id={$model['MerchantTransactionId']}",
                    $request->getModel(
                    )->activeRecord->_after_url . "&dm_transaction_status=ERROR&dm_transaction_id={$model['MerchantTransactionId']}"
                );
                //@TODO in medium-term we need here CountryId and PaymentMethodAvailable

            } catch (DineroMailException $e) {

                \Yii::app()->request->redirect(
                    $request->getModel(
                    )->activeRecord->_after_url . "&dm_transaction_status=ERROR&dm_transaction_id={$model['MerchantTransactionId']}"
                );
            }

        } else {

            throw new \CHttpException(400, \Yii::t('app', 'bad request'));
        }

    }

    public function supports($request)
    {

        $getPayumPaymentDetails = PaymentDetailsActiveRecordWrapper::findModelById(
            'payum_payment',
            $request->getModel()->getDetails()->getId()
        );

        $model = unserialize($getPayumPaymentDetails->activeRecord->attributes['_details']);

        if ($model['PaymentMethod'] == \PaymentMethodConfig::DINEROMAIL_PB) {

            return true;

        } else {

            return false;
        }

    }
}
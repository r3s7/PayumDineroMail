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

class CaptureAction extends PaymentAwareAction
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

            /* Execute the transaction */

            try {
                //trying to execute the DineroMail transaction through the doPaymentWithReference function
                $Api->doPaymentWithReference(
                    $items,
                    $buyer,
                    $model['MerchantTransactionId'], //change this value to "1" in sandbox
                    $model['Message'],
                    $model['Subject']
                );



                if ($Api->getClient()->getDineroMailLastResponse()->Status == "PENDING") {

                  /*$model['status'] = 'PENDING';
                    $model['result'] = array(
                        'VoucherUrl'            => '',
                        'BarcodeImageUrl'       =>
                        $Api->getClient()->getDineroMailLastResponse()->BarcodeImageUrl,
                        'MerchantTransactionId' =>
                        $Api->getClient()->getDineroMailLastResponse()->MerchantTransactionId,
                        'Message'               =>
                        $Api->getClient()->getDineroMailLastResponse()->Message,
                        'UniqueMessageId'       =>
                        $Api->getClient()->getDineroMailLastResponse()->UniqueMessageId,
                        'Status'                =>
                        $Api->getClient()->getDineroMailLastResponse()->Status,
                    );*/

                    $getPayment->bank_transfer_reference = array(
                        'BarcodeImageUrl' => $Api->getClient()->getDineroMailLastResponse()->BarcodeImageUrl
                    );
                    $getPayment->bank_transfer_reference = serialize($getPayment->bank_transfer_reference);
                    $getPayment->save();

                    header("location{$request->getModel()->activeRecord->_after_url}");
                }

                /* I have doubts here, I think this payment method never gets the COMPLETED status immediately
                /* (I think this thing applies only for IPN)
                 * */
                if ($Api->getClient()->getDineroMailLastResponse()->Status == "COMPLETED") {

                  /* $model['status'] = 'COMPLETED';
                    $model['result'] = array(
                        'VoucherUrl'            =>
                        $Api->getClient()->getDineroMailLastResponse()->VoucherUrl,
                        'BarcodeImageUrl'       =>
                        $Api->getClient()->getDineroMailLastResponse()->BarcodeImageUrl,
                        'MerchantTransactionId' =>
                        $Api->getClient()->getDineroMailLastResponse()->MerchantTransactionId,
                        'Message'               =>
                        $Api->getClient()->getDineroMailLastResponse()->Message,
                        'UniqueMessageId'       =>
                        $Api->getClient()->getDineroMailLastResponse()->UniqueMessageId,
                        'Status'                =>
                        $Api->getClient()->getDineroMailLastResponse()->Status,
                    );*/

                    $getPayment->bank_transfer_reference = array(
                        'BarcodeImageUrl' => $Api->getClient()->getDineroMailLastResponse()->BarcodeImageUrl,
                        'VoucherUrl'      => $Api->getClient()->getDineroMailLastResponse()->VoucherUrl

                    );
                    $getPayment->bank_transfer_reference = serialize($getPayment->bank_transfer_reference);
                    $getPayment->save();

                    header("location{$request->getModel()->activeRecord->_after_url}");
                }


                if ($Api->getClient()->getDineroMailLastResponse()->Status == "DENIED") {
                    //$model['status'] = 'DENIED';
                    $getPayment->status = 'failed';
                    $getPayment->save();
                }

                if ($Api->getClient()->getDineroMailLastResponse()->Status == "ERROR") {
                  //$model['status'] = 'ERROR';
                    $getPayment->status = 'failed';
                    $getPayment->save();
                }


            } catch (DineroMailException $e) {

                //$model['status'] = 'ERROR';
                $getPayment->status = 'failed';
                $getPayment->save();
            }


        } else {

            //$model['status'] = 'ERROR';
            throw new \CHttpException(400, \Yii::t('app', 'bad request'));
        }

\CVarDumper::dump($request->getModel()->activeRecord->_after_url,10,true);
       //$request->setModel($model);
    }

    public function supports($request)
    {

        return true;
        /*  $request instanceof CaptureRequest &&
          $request->getModel() instanceof \ArrayAccess;*/
    }
}
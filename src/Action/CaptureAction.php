<?php
/*
 * See the documentation about this in:
 * http://payum.forma-dev.com/documentation/master/Payum/develop-payment-gateway-with-payum
 *
 * */
namespace Payum\DineroMail\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\CaptureRequest;

class CaptureAction extends PaymentAwareAction
{


    public function execute($request)
    {
        \CVarDumper::dump($request,10,true); exit();
        $model = $request->getModel();

        if (
            isset($model['Name']) &&
            isset($model['LastName']) &&
            isset($model['Address']) &&
            isset($model['City']) &&
            isset($model['Country']) &&
            isset($model['Email']) &&
            isset($model['Phone']) &&
            is_array($model['Items'])
        ) {

            //do purchase call to the payment gateway using username and password.


            //new DineroMailAction instance
            $dineroMailAction = new DineroMailAction(
                $this->gatewayUsername,
                $this->gatewayPassword,
                $this->encryption,
                $this->sandbox,
                $this->provider,
                $this->currency
            );

            /* Capture Buyer information, all information are required */

            /* You need pass the reference of the related DineroMailAction instance to the DineroMailBuyer instance,
             * the DineroMailBuyer instance need the Gateway information stored in DineroMailAction instance,
             * because each parent of the abstract class DineroMailObject needs the Gateway attributes.
             * */
            $buyer = new DineroMailBuyer($dineroMailAction);
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

            foreach ($model['Items'] as $item) {

                /* You need pass the reference of the related DineroMailAction instance to the DineroMailItem instance,
                 * the DineroMailItem instance need the Gateway information stored in DineroMailAction instance,
                 * because each parent of the abstract class DineroMailObject needs the Gateway attributes.
                 * */
                $currentItem = new DineroMailItem($dineroMailAction);
                $currentItem->setCode($item['Code']);
                $currentItem->setName($item['Name']);
                $currentItem->setDescription($item['Description']);

                if (isset($item['Quantity']))
                    $currentItem->setQuantity($item['Quantity']);

                $currentItem->setAmount($item['Amount']);

                if (isset($item['Currency']))
                    $currentItem->setCurrency($item['Currency']);

                $items[] = $currentItem;
            }

            /* Execute the transaction */

            try {
                //trying to execute the DineroMail transaction through the doPaymentWithReference function
                $dineroMailAction->doPaymentWithReference(
                    $items,
                    $buyer,
                    $model['MerchantTransactionId'],
                    $model['Message'],
                    $model['Subject']
                );


                if ($dineroMailAction->getClient()->getDineroMailLastResponse()->Status == "PENDING") {

                    $model['status'] = 'PENDING';
                    $model['result'] = array(
                        'VoucherUrl' => '',
                        'BarcodeImageUrl' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->BarcodeImageUrl,
                        'MerchantTransactionId' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->MerchantTransactionId,
                        'Message' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->Message,
                        'UniqueMessageId' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->UniqueMessageId,
                        'Status' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->Status,
                    );
                }

                /* I have doubts here, I think this payment method never gets the COMPLETED status immediately
                /* (I think this thing applies only for IPN)
                 * */
                if ($dineroMailAction->getClient()->getDineroMailLastResponse()->Status == "COMPLETED") {

                    $model['status'] = 'COMPLETED';
                    $model['result'] = array(
                        'VoucherUrl' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->VoucherUrl,
                        'BarcodeImageUrl' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->BarcodeImageUrl,
                        'MerchantTransactionId' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->MerchantTransactionId,
                        'Message' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->Message,
                        'UniqueMessageId' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->UniqueMessageId,
                        'Status' =>
                        $dineroMailAction->getClient()->getDineroMailLastResponse()->Status,
                    );
                }


                if ($dineroMailAction->getClient()->getDineroMailLastResponse()->Status == "DENIED")
                    $model['status'] = 'DENIED';

                if ($dineroMailAction->getClient()->getDineroMailLastResponse()->Status == "ERROR")
                    $model['status'] = 'ERROR';


            } catch (DineroMailException $e) {

                $model['status'] = 'ERROR';
            }


        } else {

            $model['status'] = 'ERROR';
        }

        $request->setModel($model);
    }

    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
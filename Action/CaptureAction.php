<?php
namespace App\Payum\Action;

use Payum\Action\ActionInterface;
use Payum\Request\CaptureRequest;

class CaptureAction implements ActionInterface
{
    protected $gatewayUsername;
    protected $gatewayPassword;
    protected $encryption;
    protected $sandbox;
    protected $provider;
    protected $currency;


    public function __construct($gatewayUsername, $gatewayPassword, $encryption, $sandbox, $provider, $currency)
    {
        $this->gatewayUsername = $gatewayUsername;
        $this->gatewayPassword = $gatewayPassword;
        $this->encryption = $encryption;
        $this->sandbox = $sandbox;
        $this->provider = $provider;
        $this->currency = $currency;
    }

    public function execute($request)
    {
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

            /* You need pass the reference of the related DineroMailAction instance to the DineroMailBuyer instance,             *
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

                /* You need pass the reference of the related DineroMailAction instance to the DineroMailItem instance,             *
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
                $dineroMailAction->doPaymentWithReference($items, $buyer, $model['MerchantTransactionId'], $model['Message'], $model['Subject']);

                if($dineroMailAction->getClient()->getDineroMailLastResponse()->Status == "COMPLETED")
                $model['status'] = 'success';

                if($dineroMailAction->getClient()->getDineroMailLastResponse()->Status == "DENIED")
                    $model['status'] = 'DENIED';

                if($dineroMailAction->getClient()->getDineroMailLastResponse()->Status == "ERROR")
                    $model['status'] = 'ERROR';


            } catch (DineroMailException $e) {

                $model['status'] = 'ERROR';
            }


        } else {

            $model['status'] = 'ERROR';
        }
    }

    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
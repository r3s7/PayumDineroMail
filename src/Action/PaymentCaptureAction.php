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

abstract class PaymentCaptureAction extends PaymentAwareAction
{
    /**
     * @var Buyer
     */
    protected $buyer;

    /**
     * @var array of items we're buying
     */
    protected $items;

    /**
     * Not sure what type of model this is
     * TODO: improve this PHPDoc
     * @var
     */
    protected $model;

    abstract function execute($request);

    abstract function supports($request);

    protected function prepareToPay($orderId, $Api)
    {
        $getOrder = \Order::model()->findByPk($orderId);

        $getOrderItems = $getOrder->orderItems();

        $this->model['Message'] = 'This is a payment of ' . $this->model['MerchantTransactionId'];
        $this->model['Subject'] = 'Payment of ' . $this->model['MerchantTransactionId'];

        /* Capture Buyer information, all information are required */

        /* You need pass the reference of the related DineroMailAction instance to the DineroMailBuyer instance,
         * the DineroMailBuyer instance need the Gateway information stored in DineroMailAction instance,
         * because each parent of the abstract class DineroMailObject needs the Gateway attributes.
         * */

        // @TODO: instead of passing in the whole api model, let's just pass the namespace?
        // seems to be all we're using in there
        $this->buyer = new Buyer($Api);
        $this->buyer->setName($this->model['Name']);
        $this->buyer->setLastName($this->model['LastName']);
        $this->buyer->setAddress($this->model['Address']);
        $this->buyer->setCity($this->model['City']);
        $this->buyer->setCountry($this->model['Country']);
        $this->buyer->setEmail($this->model['Email']);
        $this->buyer->setPhone($this->model['Phone']);

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

            // TODO: same question goes here, couldn't we just pass in the namespace instead?
            $currentItem = new Item($Api);
            $currentItem->setCode($item->type . '-' . $item->id);
            $currentItem->setName($item->name);
            $currentItem->setDescription($item->name);

            $currentItem->setAmount($item->amount);

            if (isset($model['Items']['Currency'])) {
                $currentItem->setCurrency($model['Items']['Currency']);
            }

            $items[] = $currentItem;
        }

        $this->items = $items;
    }
}
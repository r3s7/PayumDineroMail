<?php
namespace Payum\DineroMail\Action;

//Payum core namespaces
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\StatusRequestInterface;
use Payum\Core\Request\BinaryMaskStatusRequest;
use Payum\Core\Bridge\Spl\ArrayObject;

class PaymentWithCreditCardStatusAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = new ArrayObject($request->getModel());

        // used by the CC system
        if (null === $model['status']) {
            $request->markNew();
            return;
        }

        if('PENDING' === $model['status']) {
            $request->markPending();

            return;
        }

        /* I have doubts here, I think this payment method never gets the COMPLETED status immediately
        /* (I think this thing applies only for IPN)
         * */
        if ('COMPLETED' == $model['status']) {
            $request->markSuccess();

            return;
        }

        if ('DENIED' == $model['status']) {
            $request->markFailed();

            return;
        }

        if ('ERROR' == $model['status']) {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    public function supports($request)
    {
        return
            $request instanceof BinaryMaskStatusRequest &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
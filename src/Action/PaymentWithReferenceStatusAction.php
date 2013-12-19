<?php
namespace Payum\DineroMail\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\StatusRequestInterface;

class PaymentWithReferenceStatusAction implements ActionInterface
{
    public function execute($request)
    {
        $model = $request->getModel();

        // @TODO: used by the CC system, maybe move this into a different class and take it out here?
        if ('PENDING' == isset($model['status'])) {
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
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
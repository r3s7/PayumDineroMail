<?php
namespace App\Payum\Action;

use Payum\Action\ActionInterface;
use Payum\Request\StatusRequestInterface;

class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        $model = $request->getModel();

        if ('PENDING' == isset($model['status'])) {
            $request->markNew();

            return;
        }

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
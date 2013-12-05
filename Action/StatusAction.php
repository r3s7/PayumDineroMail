<?php
namespace App\Payum\Action;

use Payum\Action\ActionInterface;
use Payum\Request\StatusRequestInterface;

class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        $model = $request->getModel();

        if (false == isset($model['status'])) {
            $request->markNew();

            return;
        }

        if ('success' == $model['status']) {
            $request->markSuccess();

            return;
        }

        if ('error' == $model['status']) {
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
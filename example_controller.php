<?php

class example_controller{

public function prepareAction(Request $request)
{
    $paymentName = 'dineroMail';

    $form = $this->createPurchaseForm();
    $form->handleRequest($request);
    if ($form->isValid()) {
        $data = $form->getData();

        $storage = $this->getPayum()->getStorageForClass(
            'Acme\PaymentBundle\Model\PaymentDetails',
            $paymentName
        );

        /** @var PaymentDetails */
        $paymentDetails = $storage->createModel();
        //be2bill amount format is cents: for example:  100.05 (EUR). will be 10005.
        $paymentDetails['AMOUNT'] = $data['amount'] * 100;
        $paymentDetails['CLIENTEMAIL'] = 'user@email.com';
        $paymentDetails['CLIENTUSERAGENT'] = $request->headers->get('User-Agent', 'Unknown');
        $paymentDetails['CLIENTIP'] = $request->getClientIp();
        $paymentDetails['CLIENTIDENT'] = 'payerId';
        $paymentDetails['DESCRIPTION'] = 'Payment for digital stuff';
        $paymentDetails['ORDERID'] = 'orderId';
        $paymentDetails['CARDCODE'] = new SensitiveValue($data['card_number']);
        $paymentDetails['CARDCVV'] = new SensitiveValue($data['card_cvv']);
        $paymentDetails['CARDFULLNAME'] = new SensitiveValue($data['card_holder']);
        $paymentDetails['CARDVALIDITYDATE'] = new SensitiveValue($data['card_expiration_date']);
        $storage->updateModel($paymentDetails);

        $captureToken = $this->getTokenFactory()->createCaptureToken(
            $paymentName,
            $paymentDetails,
            'acme_payment_details_view'
        );

        return $this->forward('PayumBundle:Capture:do', array(
            'payum_token' => $captureToken,
        ));
    }

    return $this->render('AcmePaymentBundle:SimplePurchaseBe2Bill:prepare.html.twig', array(
        'form' => $form->createView()
    ));
}

}
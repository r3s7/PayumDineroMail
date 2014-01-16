<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gallopinto
 * Date: 1/14/14
 * Time: 2:52 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Payum\DineroMail\Request\HttpGet;

use \Payum\DineroMail\Request\HttpGet\Objects\Buyer;
use Payum\DineroMail\Request\HttpGet\Objects\Item;
use Payum\DineroMail\Request\HttpGet\Objects\Merchant;

class Payment
{

    protected $_buyer;
    protected $_items;
    protected $_merchant;
    protected $_merchantTransactionId;
    protected $_paymentCompletedUrl;
    protected $_paymentPendingUrl;
    protected $_paymentErrorUrl;
    protected $_countryId;
    protected $_paymentMethodAvailable;
    protected $_rootCheckOutUrl;

    protected $concatenatedItems;
    protected $_checkOutUrl;
    protected $_isValid;

    public function __construct($config)
    {
        $this->buyer                   = $config['Buyer'];
        $this->items                   = $config['Items'];
        $this->merchant                = $config['Merchant'];
        $this->_paymentMethodAvailable = $config['PaymentMethodAvailable'];
        $this->_countryId              = $config['CountryId'];
        $this->_rootCheckOutUrl        = $config['rootCheckOutUrl'];
        $this->merchantTransactionId   = $config['MerchantTransactionId'];
        $this->paymentCompletedUrl     = $config['PaymentCompletedUrl'];
        $this->paymentPendingUrl       = $config['PaymentPendingUrl'];
        $this->paymentErrorUrl         = $config['PaymentErrorUrl'];
        $this->checkOutUrl             = $this->prepareCheckOutUrl();

        //validate attributes
        $this->isValid = $this->validate();

        //before validate
        if ($this->isValid) {
            $this->encodeUrls();
            $this->prepareItems();
            $this->prepareCheckOutUrl();
        }


    }

    protected function prepareItems()
    {
        $this->_items = Item::concatenateItems($this->_items);
    }

    protected function encodeUrls()
    {
        $this->paymentCompletedUrl = urlencode($this->paymentCompletedUrl);
        $this->paymentPendingUrl   = urlencode($this->paymentPendingUrl);
        $this->paymentErrorUrl     = urlencode($this->paymentErrorUrl);
    }

    protected function prepareCheckOutUrl()
    {

        $string = '';
        $string .= $this->_rootCheckOutUrl;
        //@TODO we would the the Buyer object in the Future for the advanced integration with Hash
        //$string .= $this->_buyer; Buyer is not used for the moment
        $string .= $this->_merchant;
        $string .= "&country_id={$this->_countryId}";
        $string .= Item::concatenateItems($this->_items);
        $string .= "&payment_method_available={$this->_paymentMethodAvailable}";
        $string .= "&transaction_id={$this->_merchantTransactionId}";
        //I think this should works for payment status notification
        $string .= "&ok_url={$this->paymentCompletedUrl}";
        $string .= "&pending_url={$this->paymentPendingUrl}";
        $string .= "&error_url={$this->paymentErrorUrl}";
        $string .= "&url_redirect_enabled=1";
        $string .= "&buyer_message=1";


        $this->_checkOutUrl = $string;

    }

    protected function validate()
    {

        //array of items to be validated

        $valid            = false;
        $somethingIsWrong = false;

        $validate = array(
            'Buyer'                  => false,
            'Items'                  => false,
            'Merchant'               => false,
            'MerchantTransactionId'  => false,
            'PaymentCompletedUrl'    => false,
            'PaymentPendingUrl'      => false,
            'PaymentErrorUrl'        => false,
            'CheckOutUrl'            => false,
            'RootCheckOutUrl'        => false,
            'countryId'              => false,
            'PaymentMethodAvailable' => false
        );

        //Buyer validation
        if ($this->_buyer instanceof Buyer) {
            $validate['Buyer'] = true;
        }

        //Items validation
        foreach ($this->_items as $item) {

            if ($item instanceof Item && $item->getItemNumber() == 1) {
                $validate['Items'] = true;
            }

            if (!$item instanceof Item) {
                $validate['Items'] = false;
            }

        }

        //Merchant validation
        if ($this->_merchant instanceof Merchant) {
            $validate['Merchant'] = true;
        }

        //MerchantTransactionId validation
        if (is_numeric($this->_merchantTransactionId)) {
            $$validate['MerchantTransactionId'] = true;
        }


        //Completed, Pending and Error Url validation
        if (filter_var($this->_paymentCompletedUrl, FILTER_VALIDATE_URL)) {
            $validate['PaymentCompletedUrl'] = true;
        }
        if (filter_var($this->_paymentPendingUrl, FILTER_VALIDATE_URL)) {
            $validate['PaymentPendingUrl'] = true;
        }
        if (filter_var($this->_paymentPendingUrl, FILTER_VALIDATE_URL)) {
            $$validate['PaymentPendingUrl'] = true;
        }

        //validate DineroMail root checkout url
        if (filter_var($this->_rootCheckOutUrl, FILTER_VALIDATE_URL)) {
            $validate['RootCheckOutUrl'] = true;
        }

        //CountryId validation
        if (is_numeric($this->_countryId)) {
            $validate['CountryId'] = true;
        }

        //if all is valid return true and the payment isValid
        foreach ($validate as $validation) {

            //something wrong detected
            if (!$validation && $valid) {
                $somethingIsWrong = true;
                $valid            = false;
            }

            //something wrong detected
            if (!$validation && !$valid) {
                $somethingIsWrong = true;
                $valid            = false;
            }

            //if validation is Ok and valid is false and
            if ($validation && !$valid && !$somethingIsWrong) {
                $valid = true;
            }


        }

        return $valid;

    }


}
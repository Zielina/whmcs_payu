<?php
require(dirname(__FILE__) . '/callback/payu/lib/openpayu.php');
//include('C:/wamp64/www/sklep/vhmcs/whmcs/modules/gateways/callback/payu/lib/openpayu.php');


function payu_MetaData()
{
    return array(
        'DisplayName' => 'PayU Payment  Module',
        'APIVersion' => '1.1', // Use API Version 1.1
    );
}

function payu_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'PayU',
        ),
        // a text field type allows for single line text input
        'POS' => array(
            'FriendlyName' => 'POS ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your account ID here',
        ),
        'FirstKey' => array(
            'FriendlyName' => 'FirstKey',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter First secret key here',
        ),
        // a password field type allows for masked text input
        'SecondKey' => array(
            'FriendlyName' => 'Second Key',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter Second secret key here',
        ),
    );
}


function payu_link($params)
{

    $pos = $params['POS'];
    $key = $params['SecondKey'];
    $firstKey=$params['FirstKey'];
    $version=payu_MetaData();
	
OpenPayU_Configuration::setEnvironment('secure');
OpenPayU_Configuration::setMerchantPosId($pos);
OpenPayU_Configuration::setSignatureKey($key);
    if (!empty($firstKey)) {
        OpenPayU_Configuration::setOauthClientId($pos);
        OpenPayU_Configuration::setOauthClientSecret($params[FirstKey]);

    }

    OpenPayU_Configuration::setSender('Plugin ver ' . $version[APIVersion]);

   $order = array();
   // var_dump($params);
    $order['notifyUrl'] = $params['systemurl'] . '/modules/gateways/callback/payu/payu.php' ;
    $order['continueUrl'] = $params['systemurl'];

    $order['customerIp'] = $_SERVER['REMOTE_ADDR'];
    $order['merchantPosId'] = $pos;
    $order['description'] = $params["description"];
    $order['currencyCode'] = 'PLN';
    //$order['currencyCode'] =$params['currency'];
    $order['totalAmount'] = $params['amount'] * 100;
    $order['additionalDescription'] = $params["invoiceid"];


    $order['products'][0]['name'] = $params["description"];
    $order['products'][0]['unitPrice'] = $params['amount'] * 100;;
    $order['products'][0]['quantity'] = 1;


    $order['buyer']['email'] = $params['clientdetails']['email'];
    $order['buyer']['phone'] = $params['clientdetails']['phonenumber'];
    $order['buyer']['firstName'] = $params['clientdetails']['firstname'];
    $order['buyer']['lastName'] = $params['clientdetails']['lastname'];
    $order['settings']['invoiceDisabled'] = true;

//Add delivery informations
    $order['buyer']['delivery']['recipientName'] = $params['clientdetails']['fullname'];
    $order['buyer']['delivery']['recipientEmail'] = $params['clientdetails']['email'];
    $order['buyer']['delivery']['recipientPhone'] = $params['clientdetails']['phonenumber'];
    $order['buyer']['delivery']['street'] = $params['clientdetails']['address1'].' '.$params['clientdetails']['address2'];
    $order['buyer']['delivery']['postalBox'] = $params['clientdetails']['city'];
    $order['buyer']['delivery']['postalCode'] = $params['clientdetails']['postcode'];
    $order['buyer']['delivery']['city'] = $params['clientdetails']['city'];
    $order['buyer']['delivery']['state'] = $params['clientdetails']['state'];
    $order['buyer']['delivery']['countryCode'] = $params['clientdetails']['countrycode'];

//$payu_code=OpenPayU_Order::hostedOrderForm($order);
    $payu_code=OpenPayU_Order::create($order);
    $respond=$payu_code->getResponse()->redirectUri;

    if ($payu_code->getStatus() == 'SUCCESS') {
       // header($respond);
        die('<script type="text/javascript">window.location.href="' . $respond . '";</script>');

    }

}





<?php
ini_set('xdebug.overload_var_dump', 0);
include("C:/wamp64/www/sklep/vhmcs/whmcs/init.php");
require_once 'lib/openpayu.php';
include("C:/wamp64/www/sklep/vhmcs/whmcs/includes/functions.php");
include("C:/wamp64/www/sklep/vhmcs/whmcs/includes/gatewayfunctions.php");
include("C:/wamp64/www/sklep/vhmcs/whmcs/includes/invoicefunctions.php");
//$patch=$_SERVER['DOCUMENT_ROOT'].'/sklep/vhmcs/whmcs/includes/gatewayfunctions.php';
////echo ($patch);
//require_once __DIR__ . '/../../../init.php';
//require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
//require_once __DIR__ . '/../../../includes/invoicefunctions.php';

//require_once __DIR__ . '/../../../init.php';
//require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
//require_once __DIR__ . '/../../../includes/invoicefunctions.php';
//require_once 'invoicefunctions.php';
//require_once 'gatewayfunctions.php';

//include('C:/wamp64/www/sklep/vhmcs/whmcs/modules/gateways/payu.php');


$gatewaymodule = "payu";

$GATEWAY = getGatewayVariables($gatewaymodule);
$secretKey = $GATEWAY['SecondKey'];
OpenPayU_Configuration::setSignatureKey($secretKey);

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

$body = file_get_contents('php://input');
$data = trim($body);
 try {
     $response = OpenPayU_Order::consumeNotification($data);
     var_dump($response);

     $invoiceid = $response->getResponse()->order->additionalDescription;
     $status = $response->getResponse()->order->status;
     $transid = $response->getResponse()->order->orderId;
     $amount = $response->getResponse()->order->totalAmount;

     if ($status == "COMPLETED") {
         echo($status);
         # Successful
         addInvoicePayment($invoiceid, $transid, $amount, 0, 'payu');
         logTransaction('payu', $_POST, "Successful");
         echo("OK");

     } else {
         # Unsuccessful
         logTransaction($GATEWAY["name"], $response, "Unsuccessful");
     }
 } catch(Exception $e){
     echo 'Exceptions', $e->getMessage();
 }


}





?>
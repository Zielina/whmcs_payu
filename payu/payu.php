<?php
include("../../../init.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");
require_once 'lib/openpayu.php';

$gatewaymodule = "PayU";

$GATEWAY = getGatewayVariables($gatewaymodule);

//checkCbTransID($transid); -> może się zdażyć, że wyślemy kilka completed


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $body = file_get_contents('php://input');
    $data = stripslashes(trim($body));
    $response = OpenPayU_Order::consumeNotification($data);
    $invoiceid =  $response->getResponse()->order->additionalDescription;
    $status = $response->getResponse()->order->status;
    $transid = $response->getResponse()->order->orderId;
    $amount = $response->getResponse()->order->totalAmount;

    if ($status=="COMPLETED") {
        # Successful
        addInvoicePayment($invoiceid,$transid,$amount,0,$gatewaymodule);
        logTransaction($GATEWAY["name"],$_POST,"Successful");
        var_dump("OK");
    } else {
        # Unsuccessful
        logTransaction($GATEWAY["name"],$_POST,"Unsuccessful");
    }


    header("HTTP/1.1 200 OK");

}
?>
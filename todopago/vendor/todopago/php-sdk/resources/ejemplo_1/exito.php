<?php 
use TodoPago\Connector;

//importo archivo con SDK
include_once '../../vendor/autoload.php';

define('MERCHANT', 305);
define('SECURITY', '1234567890ABCDEF1234567890ABCDEF');
$rk = $_COOKIE['RequestKey'];
$ak = $_GET['Answer'];
$operationid = $_GET['operationid'];

$optionsGAA = array (     
        'Security'   => SECURITY,      
        'Merchant'   => MERCHANT,     
        'RequestKey' => $rk,       
        'AnswerKey'  => $ak // *Importante     
);  

//común a todas los métodos
$wsdl['Authorize'] = "https://50.19.97.101:8243/services/Authorize?wsdl";
$wsdl['PaymentMethods'] = "https://50.19.97.101:8243/services/PaymentMethods?wsdl";
$wsdl['Operations'] = "https://50.19.97.101:8243/services/Operations?wsdl";
$http_header = array('Authorization'=>'PRISMA 912EC803B2CE49E4A541068D495AB570');
define('END_POINT', "https://50.19.97.101:8243/services/");

//creo instancia de la clase TodoPago
$connector = new Connector($http_header, $wsdl, END_POINT);

$rta2 = $connector->getAuthorizeAnswer($optionsGAA);

if ($rta2['StatusCode']== -1){
	echo "<h2>OPERACION :".$_GET['operationid']." exitosa</h2>";
}else{
	header("location: error.php?operationid=$operationid");
}
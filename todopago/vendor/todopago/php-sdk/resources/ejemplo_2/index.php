<?php
use TodoPago\Sdk;

//importo archivo con SDK
include_once '../../vendor/autoload.php';

//común a todas los métodos
$wsdl['Authorize'] = "https://50.19.97.101:8243/services/Authorize?wsdl";
$wsdl['PaymentMethods'] = "https://50.19.97.101:8243/services/PaymentMethods?wsdl";
$wsdl['Operations'] = "https://50.19.97.101:8243/services/Operations?wsdl";
$http_header = array('Authorization'=>'PRISMA 912EC803B2CE49E4A541068D495AB570',
 'user_agent' => 'PHPSoapClient');
define('END_POINT', "https://50.19.97.101:8243/services/");
 	
//opciones para el método sendAuthorizeRequest
$optionsSAR_comercio = array (
	'Security'=>'1234567890ABCDEF1234567890ABCDEF',
	'EncodingMethod'=>'XML',
	'Merchant'=>305,
	'URL_OK'=>'localhost:8888/sdk-php/ejemplo/success.php',
	'URL_ERROR'=>'localhost:8888/sdk-php/ejemplo/fail.php'
);

$optionsSAR_operacion = array (
	'MERCHANT'=> "305",
	'OPERATIONID'=>"01",
	'CURRENCYCODE'=> 032,
	'AMOUNT'=>"54"
	);
 	
//opciones para el método getAuthorizeAnswer
$optionsGAA = array(	
	'Security' => '1234567890ABCDEF1234567890ABCDEF', 
	'Merchant' => "305",
	'RequestKey' => '8496472a-8c87-e35b-dcf2-94d5e31eb12f',
	'AnswerKey' => '8496472a-8c87-e35b-dcf2-94d5e31eb12f'
	);
	
//opciones para el método getAllPaymentMethods
$optionsGAMP = array("MERCHANT"=>305);
	
//opciones para el método getStatus 
$optionsGS = array('MERCHANT'=>'305', 'OPERATIONID'=>'141120084707');
	
//creo instancia de la clase TodoPago
$connector = new Sdk($http_header, $wsdl, END_POINT);
	
//ejecuto los métodos
$rta = $connector->sendAuthorizeRequest($optionsSAR_comercio, $optionsSAR_operacion);
$rta2 = $connector->getAuthorizeAnswer($optionsGAA);
$rta3 = $connector->getAllPaymentMethods($optionsGAMP);
$rta4 = $connector->getStatus($optionsGS);    
   	 
//imprimo respuestas
echo "<h3>var_dump de la respuesta de Send Authorize Request</h3>";
var_dump($rta);
echo "<h3>var_dump de la respuesta de Get Authorize Answer</h3>";
var_dump($rta2);
echo "<h3>var_dump de la respuesta de Get All Payment Methods</h3>";
var_dump($rta3);
echo "<h3>var_dump de la respuesta de Get Status</h3>";
var_dump($rta4);
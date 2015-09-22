<?php
//importo archivo con SDK
include_once dirname(__FILE__)."/../TodoPago/lib/Sdk.php";
use TodoPago\Sdk;
	
//común a todas los métodos

$http_header = array('Authorization'=>'PRISMA f3d8b72c94ab4a06be2ef7c95490f7d3',
 'user_agent' => 'PHPSoapClient');
 	
//opciones para el método sendAuthorizeRequest
$optionsSAR_comercio = array (
	'Security'=>'1234567890ABCDEF1234567890ABCDEF',
	'EncodingMethod'=>'XML',
	'Merchant'=>2153,
	'URL_OK'=>'localhost:8888/sdk-php/ejemplo/success.php',
	'URL_ERROR'=>'localhost:8888/sdk-php/ejemplo/fail.php'
);

$optionsSAR_operacion = array (
	'MERCHANT'=> "2153",
	'OPERATIONID'=>"50",
	'CURRENCYCODE'=> 032,
	'AMOUNT'=>"54",
	//Datos ejemplos CS
	'CSBTCITY'=> "Villa General Belgrano",
	'CSSTCITY'=> "Villa General Belgrano",
	
	'CSBTCOUNTRY'=> "AR",
	'CSSTCOUNTRY'=> "AR",
	
	'CSBTEMAIL'=> "todopago@hotmail.com",
	'CSSTEMAIL'=> "todopago@hotmail.com",
	
	'CSBTFIRSTNAME'=> "Juan",
	'CSSTFIRSTNAME'=> "Juan",      
	
	'CSBTLASTNAME'=> "Perez",
	'CSSTLASTNAME'=> "Perez",
	
	'CSBTPHONENUMBER'=> "541160913988",     
	'CSSTPHONENUMBER'=> "541160913988",     
	
	'CSBTPOSTALCODE'=> " 1010",
	'CSSTPOSTALCODE'=> " 1010",
	
	'CSBTSTATE'=> "B",
	'CSSTSTATE'=> "B",
	
	'CSBTSTREET1'=> "Cerrito 740",
	'CSSTSTREET1'=> "Cerrito 740",
	
	'CSBTCUSTOMERID'=> "453458",
	'CSBTIPADDRESS'=> "192.0.0.4",       
	'CSPTCURRENCY'=> "ARS",
	'CSPTGRANDTOTALAMOUNT'=> "125.38",
	'CSMDD7'=> "",     
	'CSMDD8'=> "Y",       
	'CSMDD9'=> "",       
	'CSMDD10'=> "",      
	'CSMDD11'=> "",
	'CSMDD12'=> "",     
	'CSMDD13'=> "",
	'CSMDD14'=> "",
	'CSMDD15'=> "",        
	'CSMDD16'=> "",
	'CSITPRODUCTCODE'=> "electronic_good#chocho",
	'CSITPRODUCTDESCRIPTION'=> "NOTEBOOK L845 SP4304LA DF TOSHIBA#chocho",     
	'CSITPRODUCTNAME'=> "NOTEBOOK L845 SP4304LA DF TOSHIBA#chocho",  
	'CSITPRODUCTSKU'=> "LEVJNSL36GN#chocho",
	'CSITTOTALAMOUNT'=> "1254.40#10.00",
	'CSITQUANTITY'=> "1#1",
	'CSITUNITPRICE'=> "1254.40#15.00"
	);
 	
//opciones para el método getAuthorizeAnswer
$optionsGAA = array(	
	'Security' => '1234567890ABCDEF1234567890ABCDEF', 
	'Merchant' => "2153",
	'RequestKey' => '710268a7-7688-c8bf-68c9-430107e6b9da',
	'AnswerKey' => '693ca9cc-c940-06a4-8d96-1ab0d66f3ee6'
	);
	
//opciones para el método getAllPaymentMethods
$optionsGAMP = array("MERCHANT"=>2153);
	
//opciones para el método getStatus 
$optionsGS = array('MERCHANT'=>'2153', 'OPERATIONID'=>'02');
	
//creo instancia de la clase TodoPago
$connector = new Sdk($http_header, "test");
	
//ejecuto los métodos
$rta = $connector->sendAuthorizeRequest($optionsSAR_comercio, $optionsSAR_operacion);
$rta2 = $connector->getAuthorizeAnswer($optionsGAA);
$rta3 = $connector->getStatus($optionsGS);
$rta4 = $connector->getAllPaymentMethods($optionsGAMP);

//Print values
echo "<h3>var_dump de la respuesta de Send Authorize Request</h3>";
var_dump($rta);
echo "<h3>var_dump de la respuesta de Get Authorize Answer</h3>";
var_dump($rta2);
echo "<h3>var_dump de la respuesta de Get Status</h3>";
var_dump($rta3);
echo "<h3>var_dump de la respuesta de GetAllPaymentMethods</h3>";
var_dump($rta4);

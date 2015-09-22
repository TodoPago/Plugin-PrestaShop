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


//datos constantes
define('CURRENCYCODE', 032);
define('MERCHANT', 305);
define('ENCODINGMETHOD', 'XML');
define('SECURITY', '1234567890ABCDEF1234567890ABCDEF');

//id de la operacion
$operationid = rand(0, 99999999);

//opciones para el método sendAuthorizeRequest (datos propios del comercio)
$optionsSAR_comercio = array (
	'Security'=> SECURITY,
	'EncodingMethod'=>ENCODINGMETHOD,
	'Merchant'=>MERCHANT,
	'URL_OK'=>"localhost:8888/sdk-php-master/ejemplo_1/exito.php?operationid=$operationid",
	'URL_ERROR'=>"localhost:8888/sdk-php-master/ejemplo_1/error.php?operationid=$operationid"
);

// + opciones para el método sendAuthorizeRequest (datos propios de la operación) 
$optionsSAR_operacion = $_POST;
$optionsSAR_operacion['MERCHANT'] = MERCHANT;
/*$optionsSAR_operacion = array (
	'MERCHANT'=> MERCHANT, //dato fijo (número identificador del comercio)
	'OPERATIONID'=>'13333456', //número único que identifica la operación
	'CURRENCYCODE'=> CURRENCYCODE, //por el momento es el único tipo de moneda aceptada
	'AMOUNT'=>$_POST['amount'],
	);
*/

//creo instancia de la clase TodoPago
$connector = new Sdk($http_header, $wsdl, END_POINT);

$rta = $connector->sendAuthorizeRequest($optionsSAR_comercio, $optionsSAR_operacion);
setcookie('RequestKey',$rta["RequestKey"],  time() + (86400 * 30), "/");
var_dump($rta);
header("location: ".$rta["URL_Request"]);

	
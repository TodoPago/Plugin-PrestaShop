<?php 
namespace TodoPago;

define('TODOPAGO_VERSION','1.1.0');
define('TODOPAGO_ENDPOINT_TEST','https://developers.todopago.com.ar/services/t/1.1/');
define('TODOPAGO_ENDPOINT_PROD','https://apis.todopago.com.ar/services/t/1.1/');

define('TODOPAGO_WSDL_AUTHORIZE',dirname(__FILE__).'/Authorize.wsdl');
define('TODOPAGO_WSDL_OPERATIONS',dirname(__FILE__).'/Operations.wsdl');
define('TODOPAGO_WSDL_PAYMENTMETHODS',dirname(__FILE__).'/PaymentMethods.wsdl');

class Sdk
{
	private $host = NULL;
	private $port = NULL;
	private $user = NULL;
	private $pass = NULL;
	private $connection_timeout = NULL;
	private $local_cert = NULL;
	private $end_point = TODOPAGO_ENDPOINT_TEST;
	
	public function __construct($header_http_array, $mode = "test"){
		$this->wsdl = array("Authorize" => TODOPAGO_WSDL_AUTHORIZE, "Operations" => TODOPAGO_WSDL_OPERATIONS, "PaymentMethods" => TODOPAGO_WSDL_PAYMENTMETHODS);
		
		if($mode == "test") {
			$this->end_point = TODOPAGO_ENDPOINT_TEST;
		} elseif ($mode == "prod") {
			$this->end_point = TODOPAGO_ENDPOINT_PROD;	
		}
		
		$this->header_http = $this->getHeaderHttp($header_http_array);
	
	}

	private function getHeaderHttp($header_http_array){
		$header = "";
		foreach($header_http_array as $key=>$value){
			$header .= "$key : $value\r\n";
		}
		
		return $header;
	}
	/*
	* configuraciones
	/

	/**
	* Setea parametros en caso de utilizar proxy
	* ejemplo:
	* $todopago->setProxyParameters('199.0.1.33', '80', 'usuario','contrasenya');
	*/
	public function setProxyParameters($host = null, $port = null, $user = null, $pass = null){
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
	}
	
	/**
	* Setea time out (deaulft=NULL)
	* ejemplo:
	* $todopago->setConnectionTimeout(1000);
	*/
	public function setConnectionTimeout($connection_timeout){
		$this->connection_timeout = $connection_timeout;
	}
	
	/**
	* Setea ruta del certificado .pem (deaulft=NULL)
	* ejemplo:
	* $todopago->setLocalCert('c:/miscertificados/decidir.pem');
	*/	
	public function setLocalCert($local_cert){
		$this->local_cert= file_get_contents($local_cert);
	}
	

	/*
	* GET_PAYMENT_VALUES
	*/

	public function sendAuthorizeRequest($options_comercio, $options_operacion){
		// parseo de los valores enviados por el e-commerce/custompage
		$authorizeRequest = $this->parseToAuthorizeRequest($options_comercio, $options_operacion);
		
		$authorizeRequestResponse = $this->getAuthorizeRequestResponse($authorizeRequest);

		//devuelve el formato de array el resultado de de la operación SendAuthorizeRequest
		$authorizeRequestResponseValues = $this->parseAuthorizeRequestResponseToArray($authorizeRequestResponse);

		return $authorizeRequestResponseValues;
	}

	private function parseToAuthorizeRequest($options_comercio, $options_operacion){
		$authorizeRequest = (object)$options_comercio;
		$authorizeRequest->Payload = $this->getPayload($options_operacion);
		return $authorizeRequest;
	}

	private function getClientSoap($typo){
		$local_wsdl = $this->wsdl["$typo"];
		$local_end_point = $this->end_point."$typo";
		$context = array('http' =>
			array(
				'header'  => $this->header_http
							
			)
		);
         
		$clientSoap = new \SoapClient($local_wsdl, array(
				
				'stream_context' => stream_context_create($context),
				'local_cert'=>($this->local_cert), 
				'connection_timeout' => $this->connection_timeout,
				'location' => $local_end_point,
				'encoding' => 'UTF-8',
				'proxy_host' => $this->host,
				'proxy_port' => $this->port,
				'proxy_login' => $this->user,
				'proxy_password' => $this->pass
			));

		return $clientSoap;
	}

	private function getAuthorizeRequestResponse($authorizeRequest){
		$clientSoap = $this->getClientSoap('Authorize');

		$authorizeRequestResponse = $clientSoap->SendAuthorizeRequest($authorizeRequest);

		return $authorizeRequestResponse;
	}

	private function parseAuthorizeRequestResponseToArray($authorizeRequestResponse){
		$authorizeRequestResponseOptions = json_decode(json_encode($authorizeRequestResponse), true);

		return $authorizeRequestResponseOptions;
	}
	
	public static function sanitizeValue($string){
		$string = htmlspecialchars_decode($string);
		$string = strip_tags($string);
		$re = "/\\[(.*?)\\]|<(.*?)\\>/i"; 
		$subst = "";
		$string = preg_replace($re, $subst, $string);
		$string = preg_replace('/[\x00-\x1f]/','',htmlspecialchars($string));
		$replace = array("\n","\r",'\n','\r','&nbsp;','&','<','>');
		$string = str_replace($replace, '', $string);
		$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
		$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
		$string = str_replace($no_permitidas, $permitidas, $string);		
		return $string;
	}
	
	private function getPayload($optionsAuthorize){
		$xmlPayload = "<Request>";
		foreach($optionsAuthorize as $key => $value){
	
			$xmlPayload .= "<" . $key . ">" . self::sanitizeValue($value) . "</" . $key . ">";
		}
		$xmlPayload .= "</Request>";
		return $xmlPayload;
	}

	/*
	* QUERY_PAYMENT
	*/

	public function getAuthorizeAnswer($optionsAnswer){
		$authorizeAnswer = $this->parseToAuthorizeAnswer($optionsAnswer);

		$authorizeAnswerResponse = $this->getAuthorizeAnswerResponse($authorizeAnswer);

		$authorizeAnswerResponseValues = $this->parseAuthorizeAnswerResponseToArray($authorizeAnswerResponse);

		return $authorizeAnswerResponseValues;
	}

	private function parseToAuthorizeAnswer($optionsAnswer){
		
		$obj_options_answer = (object) $optionsAnswer;
		
		return $obj_options_answer;
	}

	private function getAuthorizeAnswerResponse($authorizeAnswer){
		$client = $this->getClientSoap('Authorize');
		$authorizeAnswer = $client->GetAuthorizeAnswer($authorizeAnswer);
		return $authorizeAnswer;
	}

	private function parseAuthorizeAnswerResponseToArray($authorizeAnswerResponse){
		$authorizeAnswerResponseOptions = json_decode(json_encode($authorizeAnswerResponse), true);

		return $authorizeAnswerResponseOptions;
	}
	
	public function getAllPaymentMethods($merchant){
		$clientSoap = $this->getClientSoap('PaymentMethods');
		
		$get_all_data = (object) $merchant;
		
		$getAll = $clientSoap->Get($get_all_data);
		return json_decode(json_encode($getAll), TRUE);
	}

	public function getStatus($arr_datos_status){//TODO: crear una funcion en algun lado que la use.
		$clientSoap = $this->getClientSoap('Operations');
		
		$obj_datos_to_status = (object) $arr_datos_status;
		
		$get_status = $clientSoap->GetByOperationId($obj_datos_to_status);
		
		return json_decode(json_encode($get_status), TRUE);
	}
	
}

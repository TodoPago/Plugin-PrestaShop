<?php

Class todopagoTppaymentformModuleFrontController extends ModuleFrontController
{

	public function init()
	{
	    $this->page_name = 'Todo Pago Payment'; // page_name and body id
	    $this->display_column_left = false;
		$this->display_column_right = false;
		//error_log("hola\n",3,"/var/log/apache2/my_error_log.log.1");
	    parent::init();
	}

	public function initContent()
	{	
		global $smarty;

	    parent::initContent();
	    $this->setTemplate('tppaymentform.tpl');

		$smarty->assign(array(
			'jslinkForm' => "https://developers.todopago.com.ar/resources/TPHybridForm-v0.1.js",
			'publicKey' => $this->getPublicKey(),
			'email' => $this->getMail(),
			'nombre' => $this->getCompleteName()
		));
	}

	public function getPublicKey(){

		$id_orden = Tools::getValue('order');
		//$id_orden = 6;
		$requestPublicKey = ""; 

		$sql = 'SELECT public_request_key FROM '._DB_PREFIX_.'todopago_transaccion WHERE id_orden = '.$id_orden;

		$dataTransacciontions = Db::getInstance()->ExecuteS($sql);

		if (!$dataTransacciontions){
			return null;
		}else{
			foreach($dataTransacciontions as $publicKey){
				$requestPublicKey = $publicKey['public_request_key'];
			}
		}

		return $requestPublicKey;
	}

	public function getMail(){
		return $this->context->customer->email;
	}

	public function getCompleteName(){
		$completeName = $this->context->customer->firstname." ";
		$completeName .= $this->context->customer->lastname;

		//error_log(print_r($this->context->customer,TRUE),3,"/var/log/apache2/my_error_log.log.1");

		return $completeName;
	}
}

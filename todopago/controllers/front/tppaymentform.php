<?php

Class todopagoTppaymentformModuleFrontController extends ModuleFrontController
{

	public function init()
	{
	    $this->page_name = 'Todo Pago Payment'; // page_name and body id
	    $this->display_column_left = false;
		$this->display_column_right = false;
	    parent::init();
	}

	public function initContent()
	{	
		global $smarty;

	    parent::initContent();
	    $this->setTemplate('tppaymentform.tpl');

		$smarty->assign(array(
			'jslinkForm' => $this->getAmbientUrlForm(),
			'publicKey' => $this->getPublicKey(),
			'email' => $this->getMail(),
			'name' => $this->getCompleteName(),
			'orderId' => Tools::getValue('order')
		));
	}

	public function getPublicKey()
	{

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

	public function getMail()
	{
		return $this->context->customer->email;
	}

	public function getCompleteName()
	{
		$completeName = $this->context->customer->firstname." ";
		$completeName .= $this->context->customer->lastname;

		return $completeName;
	}

	public function getAmbientUrlForm()
	{	
		$fileName = "TPHybridForm-v0.1.js";
		$url = "https://forms.todopago.com.ar/resources/".$fileName;
		$mode = ($this->module->getModo())?"prod":"test";
		
		if($mode == "test"){
			$url = "https://developers.todopago.com.ar/resources/".$fileName;
		}

		return $url;
	}
}

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
	    parent::initContent();

		$this->context->smarty->assign(array(
			'jslinkForm' => $this->getAmbientUrlForm(),
			'publicKey' => $this->getPublicKey(),
			'email' => $this->getMail(),
			'name' => $this->getCompleteName(),
			'orderId' => Tools::getValue('order'),
			'urlBase' => $this->context->link->getModuleLink('todopago', 'payment', array('paso' => '2'), true)
		));

		if (version_compare(_PS_VERSION_, '1.7.0.0') >= 0 ) {
			$this->setTemplate('module:todopago/views/templates/front/tppayment.tpl');
		} else {
			$this->setTemplate('tppaymentform.tpl');
		}
	}

	public function setMedia()
	{
	    parent::setMedia();

	    $this->addCSS('modules/'.$this->module->name.'/css/form_todopago.css');
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

		$url = "https://forms.todopago.com.ar/resources/v2/TPBSAForm.min.js";
		$mode = ($this->module->getModo())?"prod":"test";

		if($mode == "test"){
			$url = "https://developers.todopago.com.ar/resources/v2/TPBSAForm.min.js";
		}

		return $url;
	}
}

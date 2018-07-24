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

	    if(version_compare(_PS_VERSION_, '1.7.0.0') >= 0 ){
	    	$prestaVersion = 17;
	    }else{
	    	$prestaVersion = 16;
	    }
            $order=Tools::getValue('order');

            $sql = 'SELECT params_SAR FROM '._DB_PREFIX_.'todopago_transaccion WHERE id_orden = '.$order;

            $dataTransacciontions = Db::getInstance()->ExecuteS($sql);

            $params_sar=json_decode($dataTransacciontions[0]["params_SAR"]);

            $total=$params_sar->operacion->AMOUNT;

            $total_amount=number_format($total, 2);

            $this->context->smarty->assign(array(
                    'jslinkForm' => $this->getAmbientUrlForm(),
                    'publicKey' => $this->getPublicKey(),
                    'email' => $this->getMail(),
                    'total'=>$total_amount,
                    'name' => $this->getCompleteName(),
                    'orderId' => Tools::getValue('order'),
                    'prestaVersion' => $prestaVersion,
                    'modulePath' => _PS_BASE_URL_.__PS_BASE_URI__, 
                    'logoForm' => $this->getLogoTP(),
                    'urlBase' => $this->context->link->getModuleLink('todopago', 'payment', array('paso' => '2'), true),
                    'payment_option'=>Tools::getValue('payment_option')
            ));

		if (version_compare(_PS_VERSION_, '1.7.0.0') >= 0 ) {
            $this->setTemplate('module:todopago/views/templates/front/formblock17.tpl');
        } else {
            $this->setTemplate('formblock16.tpl');
        }
	}

	public function setMedia()
	{
	    parent::setMedia();
	    //$this->addCSS('modules/'.$this->module->name.'/css/form_todopago.css');
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

	public function getLogoTP()
	{	
	    if(version_compare(_PS_VERSION_, '1.7.0.0') >= 0 ){
	    	$path = _PS_BASE_URL_.__PS_BASE_URI__.'/modules/todopago/imagenes';
	    }else{
	    	$path = "/modules/todopago/imagenes";
	    }

	    //$mode = ($this->module->getModo())?"prod":"test";
	   
            $logoForm = $path."/tp_logo_prod.png";

	    return $logoForm;
	}
}

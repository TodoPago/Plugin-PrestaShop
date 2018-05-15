<?php

Class todopagoPagemessagereturnModuleFrontController extends ModuleFrontController
{

	public function init()
	{
	    $this->page_name = 'Pagina Información'; // page_name and body id
	    $this->display_column_left = false;
		$this->display_column_right = false;
	    parent::init();
	}

	public function initContent()
	{	
	    parent::initContent();

	    $this->context->smarty->assign(array(
                    'step' => Tools::getValue('step'),
                    'status' => Tools::getValue('status'),
                    'total' => Tools::getValue('total_to_pay'),
                    'reference' => Tools::getValue('reference'),
                    'customer' => Tools::getValue('customer'),
                    'message' => Tools::getValue('message'),
            ));

	    if (version_compare(_PS_VERSION_, '1.7.0.0') < 0) {
	    	$this->setTemplate('returnerror.tpl');
	    }else{
			$this->setTemplate('module:todopago/views/templates/front/page_message_return.tpl');
		}
	}

	public function setMedia()
	{
	    parent::setMedia();

	    $this->addCSS('modules/'.$this->module->name.'/css/page-message.css');
	}
}

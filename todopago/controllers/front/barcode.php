<?php
/**
 * Codigo de barras usado para Pago Facil y Rapipago
 *
 */
class TodoPagoBarcodeModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		$this->display_column_left = false;//para que no se muestre la columna de la izquierda
		parent::initContent();
        $this->context->smarty->assign(array("clave"=>"valor"));
        $this->setTemplate('barcode.tpl'); //seteo el template
	}


}
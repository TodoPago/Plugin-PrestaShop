<?php

require_once(dirname(__FILE__)."/ControlFraude.php");
require_once(dirname(__FILE__)."/../../classes/Productos.php");

class ControlFraudeRetail extends ControlFraude {

	public function __construct($customer = array(), $cart = array(), $config = array()){
		parent::__construct($customer, $cart, $config);
		$this->datasources["carrier"] = new Carrier($this->datasources["cart"]->id_carrier);
	}

	protected function completeCSVertical() {
		$datosCS["CSSTCITY"] 			= substr($this->getField($this->datasources['address'],"city"),0,250);
		$datosCS["CSSTCOUNTRY"] 		= $this->getField($this->datasources['country'],"iso_code");
		$datosCS["CSSTEMAIL"] 			= $this->getField($this->datasources['customer'],"email");
		$datosCS["CSSTFIRSTNAME"] 		= $this->getField($this->datasources['customer'],"firstname");
		$datosCS["CSSTLASTNAME"] 		= $this->getField($this->datasources['customer'],"lastname");
		$datosCS["CSSTPHONENUMBER"] 	= $this->_getPhone($this->datasources,false);
		$datosCS["CSSTPOSTALCODE"] 		= $this->getField($this->datasources['address'],"postcode");
		$datosCS["CSSTSTATE"] 			= $this->_getStateIso($this->getField($this->datasources['address'],"id_state"));
		$datosCS["CSSTSTREET1"] 		= $this->getField($this->datasources['address'],"address1");
		$datosCS["CSMDD13"]				= $this->getField($this->datasources['carrier'],"name");
		$datosCS["CSMDD12"]				= $this->getField($this->datasources['config'],"deadline");
		$datosCS["CSMDD16"]				= "";
		
		return array_merge($this->getMultipleProductsInfo(), $datosCS);
	}

	protected function getCategoryArray($id_product){
		/*
		$controlFraude = new TPProductoControlFraude($id_product);
        return $controlFraude->codigo_producto;
		*/
		$controlFraude = new Product($id_product);
		$categories = $controlFraude->getDefaultCategory();
		
		$category_id = $categories[0];
		$category = new Category($category_id);
		
        $name = $category->getName();
		if(empty($name)) return "default";
		return $name;		
	}
}

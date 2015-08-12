<?php

require_once(dirname(__FILE__)."/ControlFraude.php");
require_once(dirname(__FILE__)."/../../classes/Productos.php");

class ControlFraudeService extends ControlFraude {

	protected function completeCSVertical(){
		$productos = $this->datasources["cart"]->getProducts();
		$controlFraude = new TPProductoControlFraude($productos[0]['id_product']);
		
		$datosCS["CSMDD28"] = $controlFraude->tipo_servicio;
		$datosCS["CSMDD29"] = $controlFraude->referencia_pago;
		return array_merge($this->getMultipleProductsInfo(), $datosCS);
	}

	protected function getCategoryArray($id_product){
		$controlFraude = new TPProductoControlFraude($id_product);
        return $controlFraude->codigo_producto;
	}
}

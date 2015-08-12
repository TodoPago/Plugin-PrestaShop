<?php

require_once(dirname(__FILE__)."/ControlFraude.php");
require_once(dirname(__FILE__)."/../../classes/Productos.php");

class ControlFraudeTicketing extends ControlFraude {

	protected function completeCSVertical(){
		$productos = $this->datasources["cart"]->getProducts();
		$controlFraude = new TPProductoControlFraude($productos[0]['id_product']);
		
		$datosCS["CSMDD33"] = $this->_getDateTimeDiff($controlFraude->fecha_evento);
		$datosCS["CSMDD34"] = $controlFraude->tipo_envio;		
		return array_merge($this->getMultipleProductsInfo(), $datosCS);
	}

	protected function getCategoryArray($id_product){
		$controlFraude = new TPProductoControlFraude($id_product);
        return $controlFraude->codigo_producto;
	}
}

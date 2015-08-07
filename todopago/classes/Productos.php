<?php

class TPProductoControlFraude extends ObjectModel{
	public $id_product;
	public $tipo_servicio = "";
	public $referencia_pago = "";
	public $tipo_delivery = "";
	public $fecha_evento = "";
	public $tipo_envio = "";
	public $codigo_producto = "default";
	
	public static $definition = array(
			'table' => 'todopago_productos',
			'primary' => 'id_product',
			'multilang' => false,
			'fields' => array(
					'id_product' => array('type' => self::TYPE_INT, 'required' => true),
					'tipo_servicio' => array('type' => self::TYPE_STRING, 'required' => false),
					'referencia_pago' => array('type' => self::TYPE_STRING, 'required' => false),
					'tipo_delivery' => array('type' => self::TYPE_STRING, 'required' => false),
					'fecha_evento' => array('type' => self::TYPE_DATE, 'required' => false),
					'tipo_envio' => array('type' => self::TYPE_STRING, 'required' => false),
					'codigo_producto' => array('type' => self::TYPE_STRING, 'required' => false)
			)
	);
	
	public static function existeRegistro($idProducto)
	{
		$sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.TPProductoControlFraude::$definition['table'].' WHERE '.TPProductoControlFraude::$definition['primary'].'='.$idProducto;
		
		if (Db::getInstance()->getValue($sql) > 0)
			return true;
		return false;
	}
	
	public static function getRegistroAsArray($idProducto)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.TPProductoControlFraude::$definition['table'].' WHERE '.TPProductoControlFraude::$definition['primary'].'='.$idProducto;
		return Db::getInstance()->executeS($sql);
	}
	
	public static function getRegistroAsArrayCampos($idProducto, $campos)
	{
		$sql = 'SELECT '.join(",",$campos).' FROM '._DB_PREFIX_.TPProductoControlFraude::$definition['table'].' WHERE '.TPProductoControlFraude::$definition['primary'].'='.$idProducto;
		return Db::getInstance()->executeS($sql);
	}
	
	public static function getValorRegistro($idProducto, $campo)
	{
		return Db::getInstance()->getValue('SELECT '.$campo.' FROM '._DB_PREFIX_.TPProductoControlFraude::$definition['table'].' WHERE '.TPProductoControlFraude::$definition['primary'].'='.$idProducto);
	}
}
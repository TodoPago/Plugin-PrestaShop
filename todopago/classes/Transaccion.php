<?php
/**
 * Clase en donde se guardan las transacciones
 */

class TPTransaccion extends ObjectModel{
	public $id_cart;
	public $detalle;
	
	public static $definition = array(
			'table' => 'todopago_transacciones',
			'primary' => 'id_cart',
			'multilang' => false,
			'fields' => array(
					'id_cart' => array('type' => self::TYPE_INT, 'required' => false),
					'detalle' => array('type' => self::TYPE_STRING, 'required' => false),
			)
		);
	
	/**
	 * Guarda los detalles de una transaccion
	 * @param int $idCart
	 * @param array $options
	 */
	public static function agregar($idCart, $detalle)
	{
		$registro = new TPTransaccion();
		$registro ->id_cart = $idCart;
		$registro ->detalle = json_encode($detalle);
		$registro->add(); 
	}
	
	public static function actualizar($idCart, $detalle)
	{ 
		$registro = new TPTransaccion($idCart);
		$registro ->detalle = json_encode($detalle);
		$registro->update();
	}
	
	public static function existe($idCart)
	{
		$sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.TPTransaccion::$definition['table'].' WHERE '.TPTransaccion::$definition['primary'].'='.$idCart;
		
		if (\Db::getInstance()->getValue($sql) > 0)
			return true;
		return false;
	}
	
	/**
	 * Recupera los detalles de una transaccion
	 * @param int $idCart
	 * @return array con los detalles
	 */
	public static function getOptions($idCart)
	{
		$registro = new TPTransaccion($idCart);
		return json_decode($registro->detalle, TRUE);
	}
	
	public static function getRequestKey($idCart)
	{
		$registro = new TPTransaccion($idCart);
		$options =  json_decode($registro->detalle, TRUE);
		return $options['RequestKey'];
	}
	
	/**
	 * Recupera la respuesta de una transaccion.
	 * @param int $idCart
	 * @return array con los detalles
	 */
	public static function getRespuesta($idCart)
	{
		$registro = new TPTransaccion($idCart);
		$options =  json_decode($registro->detalle, TRUE);
		return $options['respuesta'];
	}
	
	public static function getPago($idCart)
	{
		$registro = new TPTransaccion($idCart);
		$options =  json_decode($registro->detalle, TRUE);
		return $options['pago'];
	}
	
	public static function borrar($idCart)
	{
		$registro = new TPTransaccion($idCart);
		$registro->delete();
	}
}
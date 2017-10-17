<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array(
		//tabla para guardar informacion sobre las transacciones
		'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'todopago_transacciones'.'` (
				`id_cart` INT(11) NOT NULL,
				`detalle` VARCHAR(500) NOT NULL,
				PRIMARY KEY (`id_cart`)
			)',
		'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'todopago_productos'.'`(
			    `id_product` INT(11) NOT NULL,
			    `tipo_servicio` VARCHAR(500),
			    `tipo_delivery` VARCHAR(500),
				`referencia_pago` VARCHAR(500) ,
				`fecha_evento` DATETIME,
				`tipo_envio` VARCHAR(500),
				`codigo_producto` VARCHAR(50) NULL DEFAULT NULL,
			    PRIMARY KEY (`id_product`)
			)',
		'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'todopago_transaccion` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`id_orden` INT NULL,
				`first_step` TIMESTAMP NULL,
				`params_SAR` TEXT NULL,
				`response_SAR` TEXT NULL,
				`second_step` TIMESTAMP NULL,
				`params_GAA` TEXT NULL,
				`response_GAA` TEXT NULL,
				`request_key` TEXT NULL,
				`public_request_key` TEXT NULL,
				`answer_key` TEXT NULL,
				PRIMARY KEY (`id`)
			)',
		'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'todopago_gmaps` (
				`id` INT NOT NULL AUTO_INCREMENT,
				`billing_street` VARCHAR(60) NOT NULL,
				`billing_state` VARCHAR(60) NOT NULL,
				`billing_city` VARCHAR(64) NOT NULL,
				`billing_country` VARCHAR(100) NOT NULL,
				`billing_postalcode` VARCHAR(100) NOT NULL,
				`shipping_street` VARCHAR(60) NOT NULL,
				`shipping_state` VARCHAR(60) NOT NULL,
				`shipping_city` VARCHAR(64) NOT NULL,
				`shipping_country` VARCHAR(100) NOT NULL,
				`shipping_postalcode` VARCHAR(100) NOT NULL,
				`identify_key` VARCHAR(100) NOT NULL,
				PRIMARY KEY (`id`)
			)'
);

foreach ($sql as $query)
	if (Db::getInstance()->execute($query) == false)
		return false;

$query = 'SELECT codigo_producto FROM `'._DB_PREFIX_.'todopago_productos`';
if (Db::getInstance()->execute($query) == false)
{
	$sqlalter = 'ALTER TABLE `'._DB_PREFIX_.'todopago_productos` ADD `codigo_producto` VARCHAR(50) NULL DEFAULT NULL';
	Db::getInstance()->execute($sqlalter);
}

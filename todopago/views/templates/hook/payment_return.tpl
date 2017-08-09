{*
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
*}

{if $status == 'ok'}
	<h3>{l s='Gracias por su compra' mod='todopago'}</h3>
	<p>
		<br/> - {l s='Codigo de referencia: %s.' sprintf=$reference mod='todopago'}
		<br/> - {l s='Total:' mod='todopago'} <span id="amount" class="price">{displayPrice price=$total_to_pay}</span>
		<!-- <br/> - {l s='Mensaje: %s' sprintf=$mensaje mod='todopago'} -->
		<br/> - {l s='Mail del cliente: %s' sprintf=$customer mod='todopago'}
	</p>

{else}

	<h3>Ocurrio un error y no se pudo realizar el pago</h3>
	<p class="warning">
		{l s='Por favor intente nuevamente en unos minutos.' mod='todopago'}
		<br/>
		Detalles del error: {$StatusMessage}
		<br/>
		{l s='Status order: %s' sprintf=$status_desc mod='todopago'}
	</p>

{/if}

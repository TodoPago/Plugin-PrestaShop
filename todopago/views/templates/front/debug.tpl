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

{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='todopago'}">{l s='Checkout' mod='todopago'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s=$nombre mod='todopago'}
{/capture}


{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='todopago'}</p>
{else}
	<h2>Debug</h2>
	</br>
	
	<h4>Datos enviados</h4>
	<b>Modo: </b> {var_dump($variablesPaso['modo'])}
	</br>
	<b>Authorization: </b> {var_dump($variablesPaso['authorization'])}
	</br>
	<b>WSDLs </b> {var_dump($variablesPaso['wsdls'])}
	</br>
	<b>Endpoint</b> {var_dump($variablesPaso['endpoint'])}
	</br>
	<b>var_dump($opciones) </b>	{var_dump($variablesPaso['opciones'])}
	</br>
	</br>
	
	{if isset($variablesPaso['respuesta']) && isset($variablesPaso['mensaje'])}
	<h4>Respuesta</h4>
	<b>Mensaje: </b> {l s='%s' sprintf=$variablesPaso['mensaje'] mod='todopago'}
	</br>
	<b>var_dump($respuesta) </b> {var_dump($variablesPaso['respuesta'])}
	</br>
	{/if}
	</br>
	
	{if isset($variablesPaso['info'])
		<h4>Status</h4>
		<b>var_dump($connector->getStatus)</b> {var_dump($variablesPaso['info'])}
		</br>
		</br>
	{/if}
	
	<h4>{l s='Detalles de la compra:' mod='todopago'}</h4>
		<ul>
					<li>{l s='Cart id: %s' sprintf=$cart_id mod='todopago'}</li>
					<li>{l s='Total:' mod='todopago'} <span id="amount" class="price">{displayPrice price=$total}</span></li>
					<li>{l s='Mail del cliente: %s' sprintf=$cliente mod='todopago'}</li>
		</ul>
		
	</br>
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}">{l s='Otros metodos de pago' mod='todopago'}</a>
{/if}
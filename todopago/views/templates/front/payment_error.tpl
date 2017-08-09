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
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='todopago'}">{l s='Checkout' mod='todopago'}</a><span class="navigation-pipe">{$navigationPipe}</span>Todo Pago
{/capture}



{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='todopago'}</p>
{else}
	{if isset($save_cart) && $save_cart == 0 }
		<script language="javascript">
		{literal}
			$(document).ready(function() {
			    $(".cart_block").remove();
			    $(".ajax_cart_quantity").addClass("unvisible").text(0);
				$(".ajax_cart_product_txt").addClass("unvisible");
				$(".ajax_cart_product_txt_s").addClass("unvisible");
				$(".ajax_cart_total").text("");
				$("span.ajax_cart_no_product").removeClass("unvisible");
			});
		{/literal}
		</script>

	{/if}

	<h3>Ocurrio un error</h3>
	<b>Por favor intente nuevamente. </b>
	</br>
	</br>
	<div class="cart_navigation clearfix">
		{if isset($save_cart) && $save_cart == 1 }
			<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive btn btn-default">
				<i class="icon-chevron-left"></i>
				{l s='Otros metodos de pago' mod='todopago'}
			</a>
		{else}
			<a href="{$link->getPageLink('index', true, NULL, "step=1")|escape:'html'}" class="button-exclusive btn btn-default">
				<i class="icon-chevron-left"></i>
				{l s='Volver al inicio' mod='todopago'}
			</a>
		{/if}
	</div>	
{/if}
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
	<h4>{l s='Detalles de la compra:' mod='todopago'}</h4>
		<ul>
					<li>{l s='Cart id: %s' sprintf=$cart_id mod='todopago'}</li>
					<li>{l s='Total:' mod='todopago'} <span id="amount" class="price">{displayPrice price=$total}</span></li>
					<li>{l s='Mail del cliente: %s' sprintf=$cliente mod='todopago'}</li>
		</ul>
<!--	<form action="{$link->getModuleLink('todopago', 'validation', [], false)|escape:'html'}" method="post">
		<p class="cart_navigation" id="cart_navigation">
				<input type="submit" value="{l s='Descontar cantidad' mod='todopago'}" class="button btn btn-default button-medium"/>
			<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}">{l s='Otros metodos de pago' mod='todopago'}</a>
		</p>
	</form>
-->

	<div class="cart_navigation clearfix">
		{if isset ($payment) && $payment['status'] == 1} <!-- Si el pago fue autorizado -->
			<!-- <a href="{$payment['redir']}">		-->	
				<!--button id="callForm" type="submit" class="button btn btn-default button-medium">
					<span>{l s='Confirmar compra' mod='todopago'}<i class="icon-chevron-right right"></i></span>
				</button-->
			<!-- </a> -->
			<a href="index.php?fc=module&module=todopago&controller=tppaymentform&order={l s=$cart_id}" class="button btn btn-default button-medium">
				<span>{l s='Confirmar compra' mod='todopago'}<i class="icon-chevron-right right"></i></span>
			</a>
		{/if}
		
		<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button-exclusive btn btn-default">
			<i class="icon-chevron-left"></i>
			{l s='Otros metodos de pago' mod='todopago'}
		</a>
	</div>	

<!--script src="http://payment.com.ar/embeded/1.0/paymentnew.js"></script>
<script>        
  new Payment().openForm({
        publicKey: '{$payment['RequestKey']}',
        idRequest:'{$payment['RequestKey']}',
        idButton: 'callForm',
        preventSubmitSuccess: true,
        callback: function (data) {

          if(data.success == 'OK') {
              alert('Llego mensaje: '+data.success+ ' con codigo: '+ data.status )
          } else if(data.success == 'FAIL') {
              alert('Llego mensaje: '+data.success+ ' con codigo: '+ data.status )
          } 

        },
        style: {
            backgroundColor: '{$payment['embebed']['backgroundColor']}',
            border: '{$payment['embebed']['border']}',
            buttonBackgroundColor: '{$payment['embebed']['buttonBackgroundColor']}',
            buttonColor: '{$payment['embebed']['buttonColor']}',
            buttonBorder: '{$payment['embebed']['buttonBorder']}'
        }
    });                

</script-->
{/if}
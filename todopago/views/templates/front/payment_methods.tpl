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
	<h2> Metodos de pago </h2>
	</br>
	
	<!-- Lista de metodos de pago -->
	<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
		{foreach from=$variablesPaso['PaymentMethod'] item=method}
			<div class="panel panel-default">
			    <div class="panel-heading" role="tab" id="heading{$method['Id']}>
			      <h4 class="panel-title">
			        <a data-toggle="collapse" data-parent="#accordion" href="#collapse{$method['Id']}" aria-expanded="false" aria-controls="collapse{$method['Id']}">
			          <b> {$method['Name']} </b> <!-- Nombre -->
			        </a>
			      </h4>
			    </div>
			    <div id="collapse{$method['Id']}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{$method['Id']}">
			      <div class="panel-body">
			        <!-- Contenido -->
			        	<!-- poner un formulario aca -->
			        	<form action="{$link->getModuleLink('todopago', 'payment', ['paso' => '2'], true)|escape:'html'}" method="post">
			        		<input name="method" id="method" type="hidden" value="{$method['Id']}" > 
					        <ul>
					        	{if count($method['PromosCollection']) == 0}
					        		<!--No hay promociones disponibles -->
					        	{else}
						       		{foreach from=$method['PromosCollection'] item=promo}
						       				<li> {$promo['Name']} - {$promo['Description']}</li>
						       				</br>
						       				<!-- cantidad de cuotas -->
						       				<label for ="installment"> Cuotas </label>
						       				<select name="installment" id="installment">
						       					{for $x=$promo['InstallmentsMin']; $x<=$promo['InstallmentsMax']; $x++ }
						       						<option value="{$x}">{$x}</option>
						       					{/for}
						       				</select>
						       				
						       				<input name="promo" id="promo" type="hidden" value="{$promo['Id']}" > 
						       				</br>
						       				</br>
						       				<label> Bancos </label>
						       				{foreach from=$promo['BanksCollection'] item=banks}
						       					{if isset($banks['Id'])}  <!-- Un solo banco --> 
							       					<ul> 
							       						<li> <input name="bank" id="bank" type="radio" value="{$banks['Id']}" required> {$banks['Id']} - {$banks['Name']} </li>
							       					</ul>
						       					{else}<!-- Mas de un banco -->
						       						{foreach from=$banks item=bank}
						       						<ul> 
							       						<li> <input name="bank" id="bank" type="radio" value="{$bank['Id']}" required> {$bank['Id']} - {$bank['Name']} </li>
							       					</ul>
						       						{/foreach}
						       					{/if}
						       				{/foreach}
						       		{/foreach}
					       		{/if}
					        </ul>
					        <input type="submit" value="{l s='Continuar' mod='todopago'}" class="button btn btn-default button-medium"/>
			        	</form> <!-- que termine aca -->
			      </div>
			    </div>
			  </div>
		{/foreach}
	</div>
{/if}
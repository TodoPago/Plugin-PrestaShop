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
	  <div class="row">
	        <div class="col-md-5">
	          <form action="{$link->getModuleLink('todopago', 'payment', ['paso' => '3'], true)|escape:'html'}" method="POST" id="paymentForm">
	            <div class="form-group">
	              <label for="cardNumber">Numero de Tarjeta</label>
	              <div class="input-group">
	                <div class="input-group-addon"><i class="glyphicon glyphicon-credit-card"></i></div>
	                <input type="number" class="form-control" id="NumeroTarjeta" placeholder="Ej.: 4444111144441111" required="required">
	              </div>
	            </div>
	            <div class="form-group">
	              <label for="name">Nombre</label>
	              <div class="input-group">
	                <div class="input-group-addon"><i class="glyphicon glyphicon-user"></i></div>
	                <input type="text" class="form-control" id="Nombre" placeholder="Ingrese su nombre" required="required">
	              </div>
	            </div>
	            <div class="row">
	                  <div class="col-lg-6 col-sm-6 col-xs-12">
	                        <div class="form-group">
	                          <label for="expired">Vencimiento</label>
	                          <div class="input-group">
	                            <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>
	                            <input type="number" class="form-control" id="FechaExpiracion" placeholder="MMAA" required="required">
	                          </div>
	                        </div>
	                  </div>
	                  <div class="col-lg-6 col-sm-6 col-xs-12">
	                        <div class="form-group">
	                          <label for="cvc">CVC</label>
	                          <div class="input-group">
	                            <div class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></div>
	                            <input type="number" class="form-control" id="cvc" placeholder="Codigo de seguridad" required="required">
	                          </div>
	                        </div>
	                  </div>
	            </div> 
	            <div class="form-group">
	              <label for="email">Email</label>
	              <div class="input-group">
	                <div class="input-group-addon"><i class="glyphicon glyphicon-envelope"></i></div>
	                <input type="email" class="form-control" id="email" placeholder="Ingrese su email" required="required" value="{$cliente}">
	              </div>
	            </div>
	            <input type="hidden" name="ClavePago" id="ClavePago" value="123456-1234-1212-2121-4343-24234">
	            <button type="submit" class="btn btn-primary col-xs-12">Pagar</button>
	          </form>
	        </div>    
	  </div>
	  <script src="http://payment.com.ar/custom/1.0/payment.js"></script>
	  <script>
         new Payment().init({
             formId: 'paymentForm',
             fieldsId: {
                 cardHolderName: 'Nombre',
                 email:          'email',
                 cardNumber:     'NumeroTarjeta',
                 expirationDate: 'FechaExpiracion',
                 securityCode:   'cvc',
                 requestKey:     'ClavePago'
             }
         }); 
      </script>
{/if}
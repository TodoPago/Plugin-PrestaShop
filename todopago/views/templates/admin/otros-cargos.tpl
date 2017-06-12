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

<script>
	{literal}$(document).ready(function(){{/literal}
		if ( $( "#total_order" ).length !=0 ) {
			$('#total_order').hide();
			$('.panel-total .table-responsive .table tbody').append('<tr id="otro_cargos"><td class="text-right">{l s="Otros cargos"}</td><td class="amount text-right nowrap">{displayPrice price=$cf currency=$id_currency}</td></tr>');
			$('.panel-total .table-responsive .table tbody').append('<tr id="total2"><td class="text-right"><strong>{l s="Total"}</strong></td><td class="amount text-right nowrap"><strong>{displayPrice price=$total_paid_tax_incl currency=$id_currency}</strong></td></tr>');

		}else{
			setTimeout(function(){
				$('#total_order').hide();
				$('.panel-total .table-responsive .table tbody').append('<tr id="otro_cargos"><td class="text-right">{l s="Otros cargos"}</td><td class="amount text-right nowrap">{displayPrice price=$cf currency=$id_currency}</td></tr>');
				$('.panel-total .table-responsive .table tbody').append('<tr id="total2"><td class="text-right"><strong>{l s="Total"}</strong></td><td class="amount text-right nowrap"><strong>{displayPrice price=$total_paid_tax_incl currency=$id_currency}</strong></td></tr>');			
			}, 3000);
		}
		
	{literal}});{/literal}
</script>	

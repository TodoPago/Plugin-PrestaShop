{extends file='page.tpl'}
{block name="page_content"}
	
	{if $step == 'first'}
		<h3>Ocurrio un error</h3>
		<p>Por favor intente nuevamente. </p>
		</br>
		<div class="cart_navigation clearfix">
			<a href="{$link->getPageLink('order', true, NULL, 'step=3')|escape:'html'}" class="button-exclusive btn btn-default">
				{l s='Volver al Checkout' mod='todopago'}
			</a>
		</div>	

	{else}

		{if $status == 'ok'}
			<h3>{l s='Gracias por su compra' mod='todopago'}</h3>
			<p>	
				<div>- Codigo de referencia: {$reference}</div>
				<div>- Total: {$total}</div>
				<div>- Mail del cliente: {$customer}</div>
				<br>
				<a href="{$link->getPageLink('index', true, NULL, 'step=3')|escape:'html'}" class="button-exclusive btn btn-default">
					{l s='Continuar comprando' mod='todopago'}
				</a>
			</p>
			
		{elseif $status eq 'failed'}

			<h3>Ocurrio un error y no se pudo realizar el pago</h3>
			<p class="warning">
				{l s='Por favor intente nuevamente en unos minutos.' mod='todopago'}
				<br/>
				Detalles del error: {$message}
				<br/>
				<a href="{$link->getPageLink('index', true, NULL, 'step=3')|escape:'html'}" class="button-exclusive btn btn-default">
					{l s='Volver a la Homepage' mod='todopago'}
				</a>
			</p>
			
		{else}	
			
		{/if}

	{/if}
	
{/block}

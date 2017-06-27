<h3>Hubo un error al realizar el pago</h3>
<p class="warning">
	Descripción del error: {$message}
	<br/>
	<br/>
	<a href="{$link->getPageLink('index', true, NULL, 'step=3')|escape:'html'}" class="button-exclusive btn btn-default">
		{l s='Volver a la Homepage' mod='todopago'}
	</a>
</p>
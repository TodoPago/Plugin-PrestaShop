{if $activo}
<style type="text/css">
#todopago_big, #todopago_med, #todopago_tiny{
	display: none;
}

@media (max-width: 319px){
	#todopago_tiny{
		display: block;
	}
}

@media (min-width: 320px) and (max-width: 479px){
	#todopago_tiny{
		display: block;
	}
}

@media (min-width: 480px) and (max-width: 599px){
	#todopago_med{
		display: block;
	}
}

@media (min-width: 600px){
	#todopago_big{
		display: block;
	}
}
</style>


<p class="payment_module">
	<a href="{$link->getModuleLink('todopago', 'payment', ['paso' => '1'], true)|escape:'html'}" title="Todo Pago">
		<img src="http://www.todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg" alt="Todo Pago"/>
	</a>
</p> 
{/if}


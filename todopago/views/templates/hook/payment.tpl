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
	<a href="{$link->getModuleLink('todopago', 'payment', ['paso' => '1'], true)|escape:'html'}" title="{$nombre}">
		

		<img id="todopago_big" src="http://www.todopago.com.ar/sites/todopago.com.ar/files/pluginstarjeta.jpg" alt="{$nombre}"/>

		<img id="todopago_med" src="https://todopago.com.ar/sites/todopago.com.ar/files/kit_banner_promocional_296x60_1.jpg" alt="{$nombre}"/>

		<img id="todopago_tiny" src="https://todopago.com.ar/sites/todopago.com.ar/files/kit_boton_192x55_01.jpg" alt="{$nombre}"/>



	</a>
</p> 
{/if}


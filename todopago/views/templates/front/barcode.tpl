<script type="text/javascript">

	function imprSelec(muestra)
	{
		var ficha=document.getElementById(muestra);
		var ventimp=window.open(' ','popimpr');
		ventimp.document.write(ficha.innerHTML);
		ventimp.document.close();
		ventimp.print();
		ventimp.close();
	}
</script>

{capture name=path}Codigo de barra{/capture}
<div id="content" style="width: 75%;">
	<div class="titulos"><h2>Nombre de la tienda<h2><hr></div>
	<div><div class="titulos">Nro de Operaci&oacute;n</div>numero de la operacion<hr></div>
	<div><div class="titulos">Total a pagar</div> $ total<hr></div>
	<div><div class="titulos">Vencimiento</div> fecha de vencimiento<hr></div>
	<div class="titulos"><h3>DATOS PERSONALES<h3><hr></div>
	<div><div class="titulos">Nombre</div> Nombre y apellido <hr></div>
	<div><div class="titulos">Podr&aacute;s pagar este cup&oacute;n en los locales de:</div> Rapipago o pago facil<hr></div>
	
	<img style="width: 100%; height: 100px" src="<?php echo '/modules/todopago/lib/image.php?dpi=72&scale=5&rotation=0&text=1234567890" /> <br /> 
	<div class="right">
		<a href="javascript:imprSelec('content')">Imprimir Tabla</a>
		<a href="<?php echo $this->url->link('common/home')?>">Click aca para ir a la pagina principal.</a>
	</div>
	<br />
</div>



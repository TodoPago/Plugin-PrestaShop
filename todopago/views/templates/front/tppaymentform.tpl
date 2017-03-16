<link rel="stylesheet" type="text/css" href="{$content_dir}modules/todopago/css/form_todopago.css">
<script language="javascript" src="{$jslinkForm}"></script>

{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='formulario todopago'}">{l s='Checkout' mod='formulario todopago'}</a><span class="navigation-pipe">{$navigationPipe}</span>Formulario de Todopago
{/capture}

<div class="order_carrier_content box">
	
	<div class="alert alert-warning">
		<h4>Advertencia:</h4>
			<ol id="alert-form">
		</ol>
	</div>

	<div id="tp-form-tph">
		<div id="tp-logo"></div>
		<div id="tp-content-form">
			<h5>Elegí tu forma de pago </h5>
			
			<div class="">
				<select id="formaDePagoCbx" class="select-control"></select>
			</div>	
			
			<div class="">
				<select id="bancoCbx" class="select-control"></select>
			</div>	
			
			<div class="">
				<select id="promosCbx" class="select-control"></select>
				<label id="labelPromotionTextId"></label>
			</div>

			<div class="input-group input-group-sm">
				<input type="checkbox" id="peiCbx"/>
				<label id="labelPeiCheckboxId"></label>	
			</div>

			<div class="input-group input-group-sm">
				<input id="numeroTarjetaTxt" class="form-control fixed-width-xl">
			</div>
			
			<div class="row date-group">
			  <div class="col-sm-1">
				<div class="input-group-sm">
				  <input id="mesTxt" class="left form-control">
				</div><!-- /input-group -->
			  </div><!-- /.col-lg-6 -->
			  <div class="col-sm-1">
				<div class="input-group-sm">
				 <input id="anioTxt" class="left form-control">
				</div><!-- /input-group -->
			  </div><!-- /.col-lg-6 -->
			</div><!-- /.row -->
						
			<div class="input-group input-group-sm">
				<label id="labelCodSegTextId" class="tp-label"></label>
				<input id="codigoSeguridadTxt" class="left form-control"/>
			</div>
			
			<div class="input-group input-group-sm">
				<input id="apynTxt" class="form-control"/>
			</div>
			
			<div class="input-group input-group-sm">
				<select id="tipoDocCbx" class="select-control"></select>
			</div>
			
			<div class="input-group input-group-sm">
				<input id="nroDocTxt" class="form-control"/>
			</div>
			
			<div class="input-group input-group-sm">
				<input id="emailTxt" class="form-control"/><br/>
			</div>
			
			<!-- Para los casos en el que el comercio opera con PEI -->
			<div class="pei-box">
				<input id="peiTokenTxt"/>
				<label id="labelPeiTokenTextId"></label>
			</div>
		
			<div id="tp-bt-wrapper">
				<button id="btnConfirmarPagoValida" class="tp-button button btn-sm btn btn-success">Pagar</button>
				<button id="btn_Billetera" class="tp-button button btn-sm btn btn-success"/>Billetera</button>
			</div>
		</div>	
	</div>
</div>
<script language="javascript">
		var countLoad = 0;
		$(document).ready(function(){
			$(".alert").hide();
			$.uniform.restore();
		});

		//securityRequesKey, esta se obtiene de la respuesta del SAR
		var security = "{$publicKey}";
		var mail = "{$email}";
		var completeName = "{$name}";
		var dni = 'Numero de documento';
		var defDniType = 'DNI';

		/************* CONFIGURACION DEL API ************************/
		window.TPFORMAPI.hybridForm.initForm({
			callbackValidationErrorFunction: 'validationCollector',
            callbackBilleteraFunction: 'billeteraPaymentResponse',
            callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
            callbackCustomErrorFunction: 'customPaymentErrorResponse',
            botonPagarId: 'btnConfirmarPagoValida',
            botonPagarConBilleteraId: 'btn_Billetera',
            modalCssClass: 'modal-class',
            modalContentCssClass: 'modal-content',
            beforeRequest: 'initLoading',
            afterRequest: 'stopLoading'
		});

		/************* SETEO UN ITEM PARA COMPRAR ************************/
        window.TPFORMAPI.hybridForm.setItem({
            publicKey: security,
            defaultNombreApellido: completeName,
            defaultNumeroDoc: dni,
            defaultMail: mail,
            defaultTipoDoc: defDniType
        });
		
		urlBase = "{$content_dir}/index.php?";
		orderId = "{$orderId}";

		//callbacks de respuesta del pago
		function validationCollector(response) {
			$('#alert-form').empty();
			var errorMessage = "<li>"+response.error+"</li>";
			$(".alert").show();	
			$("#alert-form").append(errorMessage);	
		}

		function billeteraPaymentResponse(response){
			window.location.href = urlBase+"paso=2&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
		}

		function customPaymentSuccessResponse(response){
			window.location.href = urlBase+"paso=2&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
		}

		function customPaymentErrorResponse(response) {
			if(response.AuthorizationKey)
				window.location.href = urlBase+"paso=2&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
			else
				window.location.href = urlBase+"paso=2&estado=0&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer=error&Message="+response.ResultMessage+"&Code="+response.ResultCode;
		}

		function initLoading() {	
			$.uniform.restore();
		}

		function stopLoading() {
			if(document.getElementById("peiCbx").attributes.checked.value == 'checked') {
				document.getElementById("peiCbx").checked = true;
			}
		}
</script>

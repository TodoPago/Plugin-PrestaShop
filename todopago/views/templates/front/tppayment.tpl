{extends file='page.tpl'}
{block name="page_content"}
<script language="javascript" src="{$jslinkForm}" />
<script language="javascript">
</script>

{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='formulario todopago'}">{l s='Checkout' mod='formulario todopago'}</a> Formulario de Todopago
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
			
			<div id="tp-bt-wrapper">
				<button id="btnConfirmarPagoValida" class="tp-button button btn-sm btn btn-success">Pagar</button>
				<button id="btn_Billetera" class="tp-button button btn-sm btn btn-success"/>Billetera</button>
			</div>
		</div>	
	</div>
</div>
<script language="javascript">
		function ready(fn) {
		  if (document.readyState != 'loading'){
		    fn();
		  } else if (document.addEventListener) {
		    document.addEventListener('DOMContentLoaded', fn);
		  } else {
		    document.attachEvent('onreadystatechange', function() {
		      if (document.readyState != 'loading')
		        fn();
		    });
		  }
		}

		var countLoad = 0;
		ready(function() {
			jQuery(".alert").hide();

			//securityRequesKey, esta se obtiene de la respuesta del SAR
			var security = "{$publicKey}";
			var mail = "{$email}";
			var completeName = "{$name}";
			var defDniType = 'DNI';

			urlBase = "{$urlBase}?";
			orderId = "{$orderId}";

			//callbacks de respuesta del pago
			window.validationCollector = function (response) {
				jQuery('#alert-form').empty();
				var errorMessage = "<li>"+response.error+"</li>";
				jQuery(".alert").show();	
				jQuery("#alert-form").append(errorMessage);	
			}

			window.billeteraPaymentResponse = function (response){
				window.location.href = urlBase+"paso=2&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
			}

			window.customPaymentSuccessResponse = function (response){
				window.location.href = urlBase+"paso=2&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
			}

			window.customPaymentErrorResponse = function (response) {
				window.location.href = urlBase+"paso=2&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
			}

			window.initLoading = function () {	
			}

			window.stopLoading = function () {
			}		

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
	            defaultMail: mail,
	            defaultTipoDoc: defDniType
	        });
			
	
		});
</script>
{/block}

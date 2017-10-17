{extends file='page.tpl'}

{block name="page_content"}
<!-- presta 1.7 -->
<script language="javascript" src="{$jslinkForm}" />
<script language="javascript">
</script>


{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='formulario todopago'}">{l s='Checkout' mod='formulario todopago'}</a> Formulario de Todopago
{/capture}

<div class="order_carrier_content box">
   <div id="tp-form-tph">
      <div id="tp-logo"></div>
      <div id="tp-content-form">
         <h5>Elegí tu forma de pago </h5>
         <div class="row">
            <div class="col-md-8 col-sm-8 col-xs-12">
               <select id="formaPagoCbx" class="select-control"></select>
            </div>
         </div>
         <div class="row">
            <div class="col-md-8 col-sm-8 col-xs-12">
							 <input id="numeroTarjetaTxt" id="numeroTarjetaTxt" type="text" class="cleanChangeMP" maxlength="19" placeholder="Número de tarjeta" min-length="" autocomplete="off">
						</div>
         </div>
				 <div class="row">
					 <label id="numeroTarjetaLbl" style="color:red; font-size:1em; margin-top:0px; margin-left: 10px; font-weight: normal;" ></label>
				 </div>
				 <div class="row">
					 <div class="col-md-8 col-sm-8 col-xs-12">
					<select id="medioPagoCbx" class="select-control"></select><select id="bancoCbx" class="select-control"></select>
				 </div>
			 </div>
			 <div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12">
						 <select id="promosCbx" class="select-control"></select>
					</div>
			 </div>
			 <div class="row">
				<div class="col-md-12 col-sm-12 col-xs-12">
				 <label id="promosLbl" class="select-control"></label>
				 </div>
			 </div>
			 <div class="row">
					<span class="col-md-4 col-sm-4 col-xs-6 help-block uppercase" style="display:none">Fecha de vencimiento</span>
			 </div>
         <div class="row">
            <div class="col-md-1 col-sm-1 col-xs-1">
			          <select id="mesCbx"  maxlength="2" class="left" style="width: 58px;"></select>
							</div>
						<div class="col-md-1 col-sm-1 col-xs-1">
			          <select id="anioCbx"  maxlength="2"  style="width: 58px;"></select>
            </div>
						</div>
						<div class="row">
						<div class="col-md-12 col-sm-12 col-xs-12">
						<label id="fechaLbl" style="color:red; font-size:1em; margin-top:0px; margin-left: 10px; font-weight: normal;"></label>
						  </div>
							</div>
						<div class="row">
            <div class="col-md-3 col-sm-3 col-xs-4">
							<input  class="inputbox" id="codigoSeguridadTxt"/>
			        <label id="codigoSeguridadLbl" for="codigoSeguridadTxt" class="tp-label" style="font-size:0.8em; margin-top:0px;"></label>
			        <div class="error" style="color:red; font-size:1em; margin-top:0px; margin-left: 10px;" id="codigoSeguridadTxtError"></div>
						</div>
					 </div>

         <div class="row">
            <div class="col-md-8 col-sm-8 col-xs-12">
							<input id="nombreTxt" />
							<div class="error" style="color:red; font-size:1em; margin-top:0px; margin-left: 10px;" id="nombreTxtError"></div>
            </div>
         </div>
         <div class="row">
            <div class="col-md-1 col-sm-1 col-xs-2">
               <select  id="tipoDocCbx" class="select-control"> </select>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-8">
							<input class="inputbox" id="nroDocTxt"/>
							<div class="error"  style="color:red; font-size:1em; margin-top:0px; margin-left: 10px;" id="nroDocTxtError"></div>
						</div>
         </div>
         <div class="row">
            <div class="col-md-8 col-sm-8 col-xs-12">
               <input id="emailTxt" />
							 <div class="error" style="color:red; font-size:1em; margin-top:0px; margin-left: 10px;" id="emailTxtError"></div>
            </div>
         </div>

         <div class="input-group input-group-sm">
            <input type="checkbox" id="peiCbx"/>
            <label id="peiLbl"></label>
         </div>
         <!-- Para los casos en el que el comercio opera con PEI -->
				 <div class="pei-box">
					 <label id="tokenPeiLbl" for="tokenPeiTxt" class="tp-label">Token PEI</label>
					 <div class="input-box">
						 <input id="tokenPeiTxt"/>
						 <div class="error" style="color:red; font-size:1em; margin-top:0px; margin-left: 10px;" id="peiTokenTxtError"></div>
					 </div>
				 </div>
         <div id="tp-bt-wrapper">
            <button id="MY_btnConfirmarPago" class="tp-button button btn-sm btn btn-success">Pagar</button>
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
			$("#formaPagoCbx").change(function(){
			$("#codigoSeguridadLbl").html("");
		if($("#container").hasClass("contentContainer")){
			$(".error").html('');
			$("#container").removeClass('contentContainer');
			$(".form-hidden").show();
		}else{
			$(".error").html('');
			$(".form-hidden").hide();
			$("#container").addClass('contentContainer');
		}
		})

			//securityRequesKey, esta se obtiene de la respuesta del SAR
			var security = "{$publicKey}";
			var mail = "{$email}";
			var completeName = "{$name}";
			var defDniType = 'DNI';

			urlBase = "{$urlBase}?";
			orderId = "{$orderId}";

			//callbacks de respuesta del pago
			window.validationCollector = function (parametros) {
				console.log("My validator collector");
				console.log(parametros.field + " ==> " + parametros.error);
				$("#"+parametros.field).addClass("error");
				var field = parametros.field;
				field = field.replace(/ /g, "");
				console.log(field);
				$("#"+field+"Error").html(parametros.error);
				//alert(parametros.error);
				console.log(parametros);
			}

			window.billeteraPaymentResponse = function (response){
				window.location.href = urlBase+"&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
			}

			window.customPaymentSuccessResponse = function (response){
				window.location.href = urlBase+"&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
			}

			window.customPaymentErrorResponse = function (response) {
				window.location.href = urlBase+"&estado=1&cart="+orderId+"&fc=module&module=todopago&controller=payment&Answer="+response.AuthorizationKey;
			}

			window.initLoading = function () {
				console.log("init");
				$("#codigoSeguridadLbl").html("");
			}

			window.stopLoading = function () {
			}

			/************* CONFIGURACION DEL API ************************/
			window.TPFORMAPI.hybridForm.initForm({
				callbackValidationErrorFunction: 'validationCollector',
	            callbackBilleteraFunction: 'billeteraPaymentResponse',
	            callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
	            callbackCustomErrorFunction: 'customPaymentErrorResponse',
	            botonPagarId: 'MY_btnConfirmarPago',
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

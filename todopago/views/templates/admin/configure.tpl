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
<script src="{$url_base}modules/todopago/js/fancybox/source/jquery.fancybox.js"></script>
<script src="{$url_base}modules/todopago/js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>

<!-- Add jQuery library -->
<script type="text/javascript" src="{$url_base}modules/todopago/js/fancybox/lib/jquery-1.10.1.min.js"></script>

<!-- Add mousewheel plugin (this is optional) -->
<script type="text/javascript" src="{$url_base}modules/todopago/js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>

<!-- Add fancyBox main JS and CSS files -->
<script type="text/javascript" src="{$url_base}modules/todopago/js/fancybox/source/jquery.fancybox.js?v=2.1.5"></script>

<!-- Add Thumbnail helper (this is optional) -->
<link rel="stylesheet" type="text/css" href="{$url_base}modules/todopago/js/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=1.0.7" />
<script type="text/javascript" src="{$url_base}modules/todopago/js/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>

<!-- Add Media helper (this is optional) -->
<script type="text/javascript" src="{$url_base}modules/todopago/js/fancybox/source/helpers/jquery.fancybox-media.js?v=1.0.6"></script>

<!-- Add version compare js -->
<script type="text/javascript" src="{$url_base}modules/todopago/js/version_compare/version_compare.js"></script>

<!-- check_version js -->
<script type="text/javascript" src="{$url_base}modules/todopago/js/version_compare/check_version.js"></script>

<!-- Default Value TodoPago Billetera value -->
<script type="text/javascript" src="{$url_base}modules/todopago/js/billetera/billetera.js"></script>

<script type="text/javascript">
	$(document).ready(function() {
                checkBilleteraValue("#banner1");
            
                check_last_version("{$version}");
                
		$("#fieldset_0_1_1").css("display","none");

		$('.fancybox').fancybox({
			'titleShow': false
		});

		//desabilita el boton credencial de produccion o test segun el ambiente habilitado
		if(ambienttype() == "production"){
			$("#fieldset_0_2_2").find("#cred-button").attr("disabled", "disabled");
		}else{
			$("#fieldset_0_3_3").find("#cred-button").attr("disabled", "disabled");
		}

		//get credentials
		$(".login-credencial").click(function(){

			$("#error_message").html("").hide();

			$.ajax({
				type: "POST",
				accepts: "application/json",
				data: { 
			        'user': $("#id_user").val(), 
			        'pass': $("#id_pass").val(),
			        'mode': ambienttype()
			    },
			 	url: "{$url_base}modules/todopago/controllers/front/credenciales.php",
			 	beforeSend: function(){
			 		$(".loader").show();
			 	},
			 	success: function(data){
			       	$(".loader").hide();
			       	response = $.parseJSON(data);

			        if(response.codigoResultado === undefined){
			        	
			        		$("#error_message").html(response.mensajeResultado).show();

			        }else if(response.codigoResultado == 1){

				        if(ambienttype() == "production"){
				        	parentAmSection = "#fieldset_0_3_3";
				        }else{
				        	parentAmSection = "#fieldset_0_2_2";
				        }	

				   		$(parentAmSection + " .form-wrapper").children(".form-group:eq(1)").find("#id_site").val(response.merchandid);
				   		$(parentAmSection + " .form-wrapper").children(".form-group:eq(2)").find("#security").val(response.security);
				   		$(parentAmSection + " .form-wrapper").children(".form-group:eq(3)").find("#authorization").val(response.apikey);	

					    $(parentAmSection + " .form-wrapper").children(".form-group:eq(1)").find("#id_site").focus();
						
				    	parent.jQuery.fancybox.close();
			        }
			    },
			    error: function(data){
			    	error_response = $.parseJSON(data);
			       $(".loader").hide();
			       $("#error_message").html(error_response).show();
			    }
			});

		});

		function ambienttype() {
			section = $("#fieldset_0").find(".prestashop-switch:eq(1)"); 	
			return (section.find("#modo_on").attr('checked') == "checked")? "production": "developer";
		}
});

</script>
<div class="tab-content panel">	
	<!-- Tab Configuracion -->
	<div id="general">
                <div id="panel_actualizacion_disponible" class="alert alert-warning">
                    <span class="text-error">Se encuentra disponible una versi&oacute;n m&aacute;s reciente del plugin de Todo Pago, puede consultarla desde <a target="_blank" href="https://github.com/TodoPago/Plugin-PrestaShop">aqu&iacute;</a></span>
                </div>
            
		<div class="panel">
			<div class="panel-heading">
				<i class="icon-cogs"></i>Versi&oacute;n utilizada
			</div>
			Utilizando la versi&oacute;n: {$version}
		</div>	
		{$config_general}		
	</div>
</div>

<link rel="stylesheet" type="text/css" href="{$content_dir}modules/todopago/css/flexbox.css">
<link rel="stylesheet" type="text/css" href="{$content_dir}modules/todopago/css/form_todopago.css">
<link rel="stylesheet" type="text/css" href="{$content_dir}modules/todopago/css/queries.css">

<script language="javascript" src="{$content_dir}modules/todopago/js/jquery-3.3.1.min.js"></script>

<script language="javascript" src="{$jslinkForm}"></script>
    
{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='formulario todopago'}">{l s='Checkout' mod='formulario todopago'}</a><span class="navigation-pipe">{$navigationPipe}</span>Formulario de Todo pago
{/capture}
<div class="progress">
    <div class="progress-bar progress-bar-striped active" id="loading-hibrid">
    </div>
</div>

<div class="tp_wrapper" id="tpForm">
    
    <section class="tp-total tp-flex">
        <div>
            <strong>Total a pagar ${$total}</strong>
        </div>
        <div>
            Elegí tu forma de pago
        </div>
    </section>
    
    <section class="billetera_virtual_tp tp-flex tp-flex-responsible">
        <div class="tp-flex-grow-1 tp-bloque-span text_size_billetera">
            <p>Pagá con tu <strong>Billetera Virtual Todo Pago</strong></p>
            <p>y evitá cargar los datos de tu tarjeta</p>
        </div>
        <div class="tp-flex-grow-1 tp-bloque-span">
            <button id="btn_Billetera" title="Iniciar sesión" class="tp_btn tp_btn_sm text_size_billetera">
                Iniciar Sesión
            </button>
        </div>
    </section>

    <section class="billeterafm_tp">
        <div class="field field-payment-method">
            <label for="formaPagoCbx" class="text_small">Forma de Pago</label>
            <div class="input-box">
                <select id="formaPagoCbx" class="tp_form_control"></select>
                <span class="tp-error" id="formaPagoCbxError"></span>
            </div>
        </div>
    </section>

    <section class="billetera_tp">
        <div class="tp-row">
            <p>
                Con tu tarjeta de crédito o débito
            </p>
        </div>
        <!-- Número de tarjeta y banco -->
        <div class="tp-bloque-full tp-flex tp-flex-responsible tp-main-col">
            <!-- Tarjeta -->
            <div class="tp-flex-grow-1">
                <label for="numeroTarjetaTxt" class="text_small">Número de Tarjeta</label>
                <input id="numeroTarjetaTxt" class="tp_form_control" maxlength="19" title="Número de Tarjeta"
                       min-length="14" autocomplete="off">
                <img src="{$content_dir}images/empty.png" id="tp-tarjeta-logo"
                     alt=""/>
                <!-- <span class="error" id="numeroTarjetaTxtError"></span> -->
                <label id="numeroTarjetaLbl" class="tp-error"></label>
            </div>
            <!-- Banco -->
            <div class="tp-flex-grow-1">
                <label for="bancoCbx" class="text_small">Banco</label>
                <select id="bancoCbx" class="tp_form_control" placeholder="Selecciona banco"></select>
                <span class="tp-error" id="bancoCbxError">
            </div>
            <div class="tp_col tp-bloque-span payment-method">
                <label for="medioPagoCbx" class="text_small">Medio de Pago</label>
                <select id="medioPagoCbx" class="tp_form_control" placeholder="Mediopago"></select>
                <span class="tp-error" id="medioPagoCbxError"></span>
            </div>
        </div>
        <div class="tp-row tp-bloque-full tp-flex tp-flex-responsible tp-main-col" id="pei-block">
            <section class="tp-row" id="peibox">
                <label id="peiLbl" for="peiCbx" class="text_small right">Pago con PEI</label>
                <label class="switch" id="switch-pei">
                    <input type="checkbox" id="peiCbx">
                    <span class="slider round"></span>
                    <span id="slider-text"></span>
                </label>
            </section>
        </div>

        <!-- Vencimiento + DNI-->
        <div class="tp-bloque-full tp-flex tp-flex-row tp-flex-responsible tp-flex-space-between tp-main-col">
            <!-- vencimiento -->
            <div class="tp-flex-grow-1 tp-flex tp-flex-col">
                <!-- títulos -->
                <div class="tp-row tp-flex tp-flex-space-between tp-title">
                    <div class="tp-flex-grow-1">
                        <label for="mesCbx" class="text_small">Vencimiento</label>
                    </div>
                    <div class="tp-flex-grow-1 tp-title-right">
                        <label class="text_small"></label>
                    </div>
                    <div class="tp-flex-grow-1 tp-title-left">
                        <label class="text_small">Código de seguridad</label>
                    </div>
                </div>
                <!-- inputs -->
                <div class="tp-row tp-flex tp-flex-space-between tp-input-row">
                    <div class="tp-flex-grow-1">
                        <select id="mesCbx" maxlength="2" class="tp_form_control" placeholder="Mes"></select>
                    </div>
                    <div class="tp-flex-grow-1">
                        <select id="anioCbx" maxlength="2" class="tp_form_control"></select>
                    </div>
                    <div class="tp-flex-grow-1">
                        <input id="codigoSeguridadTxt" class="tp_form_control" maxlength="4"
                               autocomplete="off"/>
                    </div>
                </div>
                <!-- warnings -->
                <div class="tp-row tp-flex tp-error-title">
                    <label class="tp-error" id="labelMuerto"></label>
                    <label id="fechaLbl" class="left tp-error"></label>
                    <label id="codigoSeguridadLbl" class="left tp-label spacer tp-error"></label>
                </div>
            </div>
            <!-- DNI -->
            <div class="tp-flex-grow-1 tp-flex tp-flex-col">
                <!-- títulos -->
                <div class="tp-row tp-flex tp-flex-space-between tp-title">
                    <div id="tp-dni-label-tipo" class="tp-flex-grow-1">
                        <label for="tipoDocCbx" class="text_small">Tipo</label>
                    </div>
                     
                    <div class="tp-flex-grow-1 tp-title-right">
                        <label id="NumeroDocCbxLabel" for="NumeroDocCbx" class="text_small">Número</label>
                    </div>
                </div>
                <!-- inputs -->
                <div class="tp-row tp-flex tp-input-row">
                    <div class="tp-flex-grow-1">
                        <select id="tipoDocCbx" class="tp_form_control"></select>
                    </div>
                    <div class="tp-flex-grow-1" id="tp-dni-numero">
                        <input id="nroDocTxt" maxlength="10" type="text" class="tp_form_control"
                               autocomplete="off"/>
                    </div>
                </div>
                <div class="tp-row tp-flex tp-error-title">
                    <label class="tp-error" id="nroDocLbl"></label>
                </div>
            </div>
        </div>


        <!-- Nombre y Apellido, y Mail -->
        <div class="tp-bloque-full tp-flex tp-flex-responsible tp-main-col">
            <div class="tp-flex-grow-1">
                <label for="nombreTxt" class="text_small">Nombre y Apellido</label>
                <input id="nombreTxt" class="tp_form_control" autocomplete="off" placeholder="" maxlength="50">
                <label class="tp-error" id="nombreLbl"></label>
            </div>
            <div class="tp-flex-grow-1">
                <label for="emailTxt" class="text_small">Email</label>
                <input id="emailTxt" type="email" class="tp_form_control tp-input-row"
                       placeholder="nombre@mail.com"
                       data-mail=""
                       autocomplete="off"/>
                <label id="emailLbl" class="left tp-label spacer tp-error"></label>
            </div>
        </div>

        <!-- Cantidad de cuotas y CFT -->
        <div class="tp-bloque-full tp-flex tp-main-col tp-flex-responsible">
            <div class="tp-flex-grow-1">
                <label for="promosCbx" class="text_small">Cantidad de cuotas</label>
                <select id="promosCbx" class="tp_form_control"></select>
                <span class="tp-error" id="promosCbxError"></span>
            </div>
            <div class="tp-flex-grow-1">
                <label for="promosLbl" class="text_small"></label>
                <label id="promosLbl" class="left tp_form_control"></label>
            </div>
        </div>

        <!-- Token de PEI -->
        <div class="tp-bloque-full tp-flex tp-main-col tp-flex-responsible">
            <div class="tp-flex-grow-1">
                <label id="tokenPeiLbl" for="tokenPeiTxt" class="text_small"></label>
                <input id="tokenPeiTxt" class="tp_form_control tp-input-row" />
            </div>
            <div class="tp-flex-grow-1">
            </div>
        </div>

        <!-- Pagar -->
        <div class="tp_row">
            <div class="tp_col tp_span_2_of_2">
                <button id="btn_ConfirmarPago" class="tp_btn" title="Pagar" class="button"><span>Pagar</span>
                </button>
            </div>
            <div class="tp_col tp_span_2_of_2">
                <div class="confirmacion">
                    Al confirmar el pago acepto los <a href="https://www.todopago.com.ar/terminos-y-condiciones-comprador" target="_blank" title="Términos y Condiciones" id="tycId" class="tp_color_text">Términos
                        y Condiciones</a> de Todo Pago.
                </div>
            </div>
        </div>
    </section>
    <div class="tp_row">
        <div id="tp-powered">
            Powered by <img id="tp-powered-img" src="{$content_dir}{$logoForm}"/>
        </div>
    </div>
</div>

<script language="javascript">
    var tpformJquery = $.noConflict();
    var urlScript = "{$jslinkForm}";
    var urlBase = "{$urlBase}?";
    var orderId = "{$orderId}";
    var security = "{$publicKey}";
    var mail = "{$email}";
    var completeName = "{$name}";
    var defDniType = 'DNI';
    var medioDePago = document.getElementById('medioPagoCbx');
    var tarjetaLogo = document.getElementById('tp-tarjeta-logo');
    var poweredLogo = document.getElementById('tp-powered-img');
    var numeroTarjetaTxt = document.getElementById('numeroTarjetaTxt')
    var poweredLogoUrl = "{$content_dir}images/";
    var emptyImg = "{$content_dir}images/empty.png";
    var peiCbx = tpformJquery("#peiCbx");
    var switchPei = tpformJquery("#switch-pei");
    var sliderText = tpformJquery("#slider-text");
    var payment_option="{$payment_option}";
    var idTarjetas = {
        42: 'VISA',
        43: 'VISAD',
        1: 'AMEX',
        2: 'DINERS',
        6: 'CABAL',
        7: 'CABALD',
        14: 'MC',
        15: 'MCD'
    };
    var diccionarioTarjetas = {
        'VISA': 'VISA',
        'VISA DEBITO': 'VISAD',
        'AMEX': 'AMEX',
        'DINERS': 'DINERS',
        'CABAL': 'CABAL',
        'CABAL DEBITO': 'CABALD',
        'MASTER CARD': 'MC',
        'MASTER CARD DEBITO': 'MCD',
        'NARANJA': 'NARANJA'
    };
   
    if("{$prestaVersion}" <= "16"){   
        $(window).load(function() {
            $.uniform.restore("input");
        });
    }
    
    /************* HELPERS *************/
    numeroTarjetaTxt.onblur = clearImage;
    function clearImage() {
        tarjetaLogo.src = emptyImg;
    }
    function cardImage(select) {
        var tarjeta = idTarjetas[select.value];
        if (tarjeta === undefined) {
            tarjeta = diccionarioTarjetas[select.textContent];
        }
        if (tarjeta !== undefined) {
            tarjetaLogo.src = 'https://forms.todopago.com.ar/formulario/resources/images/' + tarjeta + '.png';
            tarjetaLogo.style.display = 'block';
        }
    }
    /************* SMALL SCREENS DETECTOR (?) *************/
    function detector() {
        console.log(tpformJquery("#tp-form").width());
        var tpFormWidth = tpformJquery("#tp-form").width();
        if (tpFormWidth < 950) {
            tpformJquery(".tp-col-right").css("flex-basis", "350px");
            tpformJquery(".tp-col-left").css("flex-basis", "350px");
        }
        if (tpFormWidth < 800) {
            tpformJquery(".tp-col-right").css("flex-basis", "300px");
            tpformJquery(".tp-col-left").css("flex-basis", "300px");
        }
        if (tpFormWidth < 720) {
            tpformJquery(".tp-container").css({
                "margin-left": "0%",
                "width": "100%",
                "padding": "5px"
            });
            tpformJquery(".left-col").width('100%');
            tpformJquery(".right-col").width('100%');
            tpformJquery(".advertencia").css("height", "50px");
            tpformJquery(".row").css({
                "height": "60px",
                "width": "95%",
                "margin-bottom": "30px"
            });
            tpformJquery("#codigo-col").css("margin-bottom", "10px");
            tpformJquery("#row-pei").css("height", "100px");
            tpformJquery(".tp-col-left").css("flex-basis", "320px");
            tpformJquery(".tp-col-right").css("flex-basis", "320px");
            tpformJquery(".tp-container-2-columns").css({
                "height": "400px"
            });
        }
        {literal}
        if (tpformJquery("#tp-form").width() < 600) {
            tpformJquery(".tp-container-2-columns").css({"margin-top": "200px"});
        }
        {/literal}
    }
    loadScript(urlScript, function () {
        loader();
    });
    function loadScript(url, callback) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        if (script.readyState) {  //IE
            script.onreadystatechange = function () {
                if (script.readyState === "loaded" || script.readyState === "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {  //et al.
            script.onload = function () {
                callback();
            };
        }
        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
    }
    function loader() {
        tpformJquery("#loading-hibrid").css("width", "50%");
        setTimeout(function () {
            ignite();
            tpformJquery(".payment-method").hide();
            tpformJquery(".billeterafm_tp").hide();
        }, 100);
        setTimeout(function () {
            tpformJquery("#loading-hibrid").css("width", "100%");
        }, 1000);
        setTimeout(function () {
            tpformJquery(".progress").hide('fast');
        }, 2000);
        setTimeout(function () {
            tpformJquery("#tpForm").fadeTo('fast', 1);
            click_billetera_btn();
        }, 2200);
    }
    
    function click_billetera_btn(){
        if("{$payment_option}"==="billetera"){
            $(".billetera_tp").css("display", "none");
            $("#btn_Billetera").trigger("click");
        }
    }
    
    //callbacks de respuesta del pago
    window.validationCollector = function (parametros) {
        
        console.log("My validator collector");
        console.log(parametros.field + " -> " + parametros.error);
        tpformJquery("#peibox").hide();
        var input = parametros.field;

        if (input.search("Txt") !== -1) {
            label = input.replace("Txt", "Lbl");
        } else {
            label = input.replace("Cbx", "Lbl");
        }

        if (document.getElementById(label) !== null) {
            document.getElementById(label).innerText = parametros.error;
        }
    };
    
    
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
        cardImage(medioDePago);
        //tpformJquery("#codigoSeguridadLbl").html("");
        tpformJquery("#peibox").hide();
    };
    window.stopLoading = function () {
        console.log('Stop loading...');
        tpformJquery("#peibox").hide();
        if (document.getElementById('peiLbl').style.display === "inline-block") {
            tpformJquery("#peibox").css('display', 'table-cell');
        } else {
            tpformJquery("#peibox").hide("fast");
        }
        var rowPei = tpformJquery("#row-pei");
        //tpformJquery.uniform.restore();
        if (peiCbx.css('display') !== 'none') {
            activateSwitch(getPEIState());
            //alert(getPEIState());
        } else {
            rowPei.css("display", "none");
        }
        
        $("#btn_Billetera").html("Iniciar Sesión");
    };
    // Verifica que el usuario no haya puesto para solo pagar con PEI y actúa en consecuencia
    function activateSwitch(soloPEI) {
        
        readPeiCbx();
        if (!soloPEI) {
            switchPei.click(function () {
                console.log("CHECKED", peiCbx.prop("checked"));
                if (peiCbx.prop("checked")) {
                    switchPei.prop("checked", true);
                    peiCbx.prop("checked", true);
                    sliderText.text("SÍ");
                    sliderText.css('transform', 'translateX(0)');
                } else {
                    switchPei.prop("checked", false);
                    peiCbx.prop("checked", false);
                    sliderText.text("NO");
                    sliderText.css('transform', 'translateX(26px)');
                }
            });
        }
    }
    function readPeiCbx() {
        if (peiCbx.prop("checked", true)) {
            switchPei.prop("checked", true);
            sliderText.text("SÍ");
            sliderText.css('transform', 'translateX(0)');
        } else {
            switchPei.prop("checked", true);
            sliderText.text("NO");
            sliderText.css('transform', 'translateX(26px)');
        }
    }
    function getPEIState() {
        return (peiCbx.prop("disabled"));
    }
    tpformJquery('#peiLbl').bind("DOMSubtreeModified", function () {
        tpformJquery("#peibox").hide();
    });
    function ignite() {
        /************* CONFIGURACION DEL API ************************/
        window.TPFORMAPI.hybridForm.initForm({
            callbackValidationErrorFunction: 'validationCollector',
            callbackBilleteraFunction: 'billeteraPaymentResponse',
            callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
            callbackCustomErrorFunction: 'customPaymentErrorResponse',
            botonPagarId: 'btn_ConfirmarPago',
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
    }
</script>

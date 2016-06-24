<a name="inicio"></a>		
Todo Pago - módulo SDK-PHP para conexión con gateway de pago
=======

 + [Instalación](#instalacion)
 	+ [Versiones de php soportadas](#Versionesdephpsoportadas)
 	+ [Generalidades](#general)
 + [Uso](#uso)		
    + [Inicializar la clase correspondiente al conector (TodoPago\Sdk)](#initconector)
    + [Solicitud de autorización](#solicitudautorizacion)
    + [Confirmación de transacción](#confirmatransaccion)
    + [Ejemplo](#ejemplo)
    + [Modo test](#test)
 + [Datos adicionales para prevención de fraude](#datosadicionales) 
 + [Características](#caracteristicas)
    + [Status de la operación](#status)
    + [Consulta de operaciones por rango de tiempo](#statusdate)
    + [Devolucion](#devolucion)
    + [Devolucion parcial](#devolucionparcial)
    + [Formulario hibrido](#formhidrido)
    + [Obtener Credenciales](#credenciales)
 + [Diagrama de secuencia](#secuencia)
 + [Tablas de referencia](#tablareferencia)		
 + [Tabla de errores](#codigoerrores)		 

<a name="instalacion"></a>		
## Instalación		
Se debe descargar la última versión del SDK desde el botón Download ZIP del branch master.		
Una vez descargado y descomprimido, debe incluirse el archivo Sdk.php que se encuentra en la carpeta /TodoPago/lib como librería dentro del proyecto.

También se puede realizar la instalación a través de Composer (opcion recomendada).

```php
composer require todopago/php-sdk
```	

Luego de la instalacion se debe incluir el archivo vendor/autoload.php en el proyecto.

**Observación**: Descomentar: extension=php_soap.dll, extension=php_openssl.dll y extension=php_curl.dll del php.ini, ya que para la conexión al gateway se utiliza la clase SoapClient del API de PHP.

<a name="Versionesdephpsoportadas"></a>   
####1. Versiones de php soportadas    
La versi&oacute;n implementada de la SDK, esta testeada para la version PHP 5.3 en adelante.

<a name="general"></a>
####2. Generalidades
Esta versión soporta únicamente pago en moneda nacional argentina (CURRENCYCODE = 32).

[<sub>Volver a inicio</sub>](#inicio)	
<br>
<a name="uso"></a>		
## Uso		

<a name="initconector"></a>
####Inicializar la clase correspondiente al conector (TodoPago\Sdk).

- Crear un array con los http header suministrados por Todo Pago
```php
$http_header = array('Authorization'=>'PRISMA 912EC803B2CE49E4A541068D495AB570');
```

- Crear una instancia de la clase TodoPago\Sdk
```php		
$connector = new TodoPago\Sdk($http_header, $mode); // $mode: "test" para testing, "prod" para producción
```		

<a name="solicitudautorizacion"></a>
####Solicitud de autorización		
En este caso hay que llamar a sendAuthorizeRequest(). 		
```php		
$values = $connector->sendAuthorizeRequest($optionsSAR_comercio, $optionsSAR_operacion);		
```		
<ins><strong>datos propios del comercio</strong></ins>		
$optionsSAR_comercio debe ser un array con la siguiente estructura:		
<a name="url_ok"></a>		
<a name="url_error"></a>	
```php
$optionsSAR_comercio = array (
	'Security'=> '1234567890ABCDEF1234567890ABCDEF',
	'EncodingMethod'=>'XML',
	'Merchant'=>305,
	'URL_OK'=>'localhost:8888/sdk-php/ejemplo/exito.php?Order=27398173292187',
	'URL_ERROR'=>'localhost:8888/sdk-php/ejemplo/error.php?Order=27398173292187'
);		
```		

*En el ejemplo se envían parámetros en la url (en nuestro ejemplo: ?Order=27398173292187), para ser recibidos por la tienda vía **get** y de este modo recuperar el valor en un próximo paso.

<ins><strong>datos propios de la operación</strong></ins>		
$optionsSAR_operacion debe ser un array con la siguiente estructura:		
		
```php
$optionsSAR_operacion = array (
	'MERCHANT'=> 305, //dato fijo (número identificador del comercio)
	'OPERATIONID'=>'27398173292187', //número único que identifica la operación, generado por el comercio.
	'CURRENCYCODE'=> 32, //por el momento es el único tipo de moneda aceptada
	'AMOUNT'=>54.00,
	'EMAILCLIENTE'=>'email_cliente@dominio.com',
	);		
```		

<p><strong>Códigos de rechazo</strong></p>
```php	
    array (size=5)
    'StatusCode' => int -1
    'StatusMessage' => string 'Solicitud de Autorizacion Registrada' (length=36)
    'URL_Request' => string 'https://developers.todopago.com.ar/formulario/commands?command=formulario&m=6d2589f2-37e6-1334-7565-3dc19404480c' (length=102)
    'RequestKey' => string '6d2589f2-37e6-1334-7565-3dc19404480c' (length=36)
    'PublicRequestKey' => string '6d2589f2-37e6-1334-7565-3dc19404480c' (length=36)
```
La **url_request** es donde está hosteado el formulario de pago y donde hay que redireccionar al usuario, una vez realizado el pago según el éxito o fracaso del mismo, el formulario redireccionará a una de las 2 URLs seteadas en **$optionsSAR_comercio** ([URL_OK](#url_ok), en caso de éxito o [URL_ERROR](#url_error), en caso de que por algún motivo el formulario rechace el pago)

Si, por ejemplo, se pasa mal el <strong>MerchantID</strong> se obtendrá la siguiente respuesta:
```php
array (size=2)
  'StatusCode' => int 702
  'StatusMessage' => string 'ERROR: Cuenta Inexistente' (length=25)
(length=30)
```
<a name="confirmatransaccion"></a>
####Confirmación de transacción.		
En este caso hay que llamar a **getAuthorizeAnswer()**, enviando como parámetro un array como se describe a continuación.		
```php		
$optionsQuery = array (		
		'Security'   => '1234567890ABCDEF1234567890ABCDEF', // Token de seguridad, provisto por TODO PAGO. MANDATORIO.		
		'Merchant'   => '12345678',		
		'RequestKey' => '0123-1234-2345-3456-4567-5678-6789',		
		'AnswerKey'  => '1111-2222-3333-4444-5555-6666-7777' // *Importante		
);		
```		

Se deben guardar y recuperar los valores de los campos <strong>RequestKey</strong> y <strong>AnswerKey</strong>.

El parámetro <strong>RequestKey</strong> es siempre distinto y debe ser persistido de alguna forma cuando el comprador es redirigido al formulario de pagos.

<ins><strong>Importante</strong></ins> El campo **AnswerKey** se adiciona  en la redirección que se realiza a alguna de las direcciones ( URL ) epecificadas en el  servicio **SendAurhorizationRequest**, esto sucede cuando la transacción ya fue resuelta y es necesario regresar al site para finalizar la transacción de pago, también se adiciona el campo Order, el cual tendrá el contenido enviado en el campo **OPERATIONID**. Para nuestro ejemplo: <strong>http://susitio.com/paydtodopago/ok?Order=27398173292187&Answer=1111-2222-3333-4444-5555-6666-7777</strong>		
		
```php		
array(		
  'StatusCode'       => -1, 		
  'StatusMessage'    => 'APROBADA',		
  'AuthorizationKey' => '1294-329E-F2FD-1AD8-3614-1218-2693-1378',		
  'EncodingMethod'   => 'XML',		
  'Payload'          => 		
    array (		
      'Answer' => 		
        array (		
          'DATETIME'               => '2014/08/11 15:24:38',		
          'RESULTCODE'             => '-1',		
          'RESULTMESSAGE'          => 'APROBADA',		
          'CURRENCYNAME'           => 'Pesos',		
          'PAYMENTMETHODNAME'      => 'VISA',		
          'TICKETNUMBER'           => '12',		
          'CARDNUMBERVISIBLE'      => '450799******4905',		
          'AUTHORIZATIONCODE'      => 'TEST38'), 		
      'Request' => 		
        array (		
          'MERCHANT'               => '12345678',		
          'OPERATIONID'            => 'ABCDEF-1234-12221-FDE1-00000012',		
          'AMOUNT'                 => '1.00',		
          'CURRENCYCODE'           => '032', 		
          );		
```		
Este método devuelve el resumen de los datos de la transacción.		

Si se pasa mal el <strong>AnswerKey</strong> o el <strong>RequestKey</strong> se verá el siguiente rechazo:

```php
array (size=2)
  'StatusCode' => int 404
  'StatusMessage' => string 'ERROR: Transaccion Inexistente' (length=30)
```

<a name="ejemplo"></a>      
####Ejemplo
Existe un ejemplo en https://github.com/TodoPago/sdk-php/tree/master/resources/ejemplo.php que muestra los resultados de los métodos principales  del SDK.<br>
	
<a name="test"></a>      
####Modo Test

El SDK-PHP permite trabajar con los ambiente de desarrollo y de produccion de Todo Pago.<br>
El ambiente se debe instanciar como se indica a continuacion.

```php
$mode = "test";//identificador de entorno obligatorio, la otra opcion es prod
$http_header = array('Authorization'=>'TODOPAGO 912EC803B2CE40E4A541068D495AB570');//authorization key del ambiente requerido

$connector = new TodoPago\Sdk($http_header, $mode);
```

[<sub>Volver a inicio</sub>](#inicio)
<br>

<a name="datosadicionales"></a>		
## Datos adicionales para control de fraude		
Los datos adicionales para control de fraude son **obligatorios**, de lo contrario baja el score de la transacción.

Los campos marcados como **condicionales** afectan al score negativamente si no son enviados, pero no son mandatorios o bloqueantes.

```php		
$optionsSAR_operacion = array(		
	...........................................................................		
	'CSBTCITY'=>'Villa General Belgrano', //Ciudad de facturación, MANDATORIO.		
	'CSBTCOUNTRY'=>'AR', //País de facturación. MANDATORIO. Código ISO. (http://apps.cybersource.com/library/documentation/sbc/quickref/countries_alpha_list.pdf)		
	'CSBTCUSTOMERID'=>'453458', //Identificador del usuario al que se le emite la factura. MANDATORIO. No puede contener un correo electrónico.		
	'CSBTIPADDRESS'=>'192.0.0.4', //IP de la PC del comprador. MANDATORIO.		
	'CSBTEMAIL'=>'decidir@hotmail.com', //Mail del usuario al que se le emite la factura. MANDATORIO.		
	'CSBTFIRSTNAME'=>'Juan' ,//Nombre del usuario al que se le emite la factura. MANDATORIO.		
	'CSBTLASTNAME'=>'Perez', //Apellido del usuario al que se le emite la factura. MANDATORIO.		
	'CSBTPHONENUMBER'=>'541160913988', //Teléfono del usuario al que se le emite la factura. No utilizar guiones, puntos o espacios. Incluir código de país. MANDATORIO.		
	'CSBTPOSTALCODE'=>' C1010AAP', //Código Postal de la dirección de facturación. MANDATORIO.		
	'CSBTSTATE'=>'B', //Provincia de la dirección de facturación. MANDATORIO. Ver tabla anexa de provincias.		
	'CSBTSTREET1'=>'Cerrito 740', //Domicilio de facturación (calle y nro). MANDATORIO.		
	'CSBTSTREET2'=>'Piso 8', //Complemento del domicilio. (piso, departamento). NO MANDATORIO.		
	'CSPTCURRENCY'=>'ARS', //Moneda. MANDATORIO.		
	'CSPTGRANDTOTALAMOUNT'=>'125.38', //Con decimales opcional usando el punto como separador de decimales. No se permiten comas, ni como separador de miles ni como separador de decimales. MANDATORIO. (Ejemplos:$125,38-> 125.38 $12-> 12 o 12.00)				
	'CSMDD7'=>'', // Fecha registro comprador(num Dias). NO MANDATORIO.		
	'CSMDD8'=>'Y', //Usuario Guest? (Y/N). En caso de ser Y, el campo CSMDD9 no deberá enviarse. NO MANDATORIO.		
	'CSMDD9'=>'', //Customer password Hash: criptograma asociado al password del comprador final. NO MANDATORIO.		
	'CSMDD10'=>'', //Histórica de compras del comprador (Num transacciones). NO MANDATORIO.		
	'CSMDD11'=>'', //Customer Cell Phone. NO MANDATORIO.		
	'CSSTCITY'=>'rosario', //Ciudad de enví­o de la orden. MANDATORIO.		
	'CSSTCOUNTRY'=>'', //País de envío de la orden. MANDATORIO.		
	'CSSTEMAIL'=>'jose@gmail.com', //Mail del destinatario, MANDATORIO.		
	'CSSTFIRSTNAME'=>'Jose', //Nombre del destinatario. MANDATORIO.		
	'CSSTLASTNAME'=>'Perez', //Apellido del destinatario. MANDATORIO.		
	'CSSTPHONENUMBER'=>'541155893737', //Número de teléfono del destinatario. MANDATORIO.		
	'CSSTPOSTALCODE'=>'1414', //Código postal del domicilio de envío. MANDATORIO.		
	'CSSTSTATE'=>'D', //Provincia de envío. MANDATORIO. Son de 1 caracter		
	'CSSTSTREET1'=>'San Martín 123', //Domicilio de envío. MANDATORIO.		
	'CSMDD12'=>'',//Shipping DeadLine (Num Dias). NO MADATORIO.		
	'CSMDD13'=>'',//Método de Despacho. NO MANDATORIO.		
	'CSMDD14'=>'',//Customer requires Tax Bill ? (Y/N). NO MANDATORIO.		
	'CSMDD15'=>'',//Customer Loyality Number. NO MANDATORIO. 		
	'CSMDD16'=>'',//Promotional / Coupon Code. NO MANDATORIO. 		
	//Retail: datos a enviar por cada producto, los valores deben estar separados con #:		
	'CSITPRODUCTCODE'=>'electronic_good', //Código de producto. MANDATORIO. Valores posibles(adult_content;coupon;default;electronic_good;electronic_software;gift_certificate;handling_only;service;shipping_and_handling;shipping_only;subscription)		
	'CSITPRODUCTDESCRIPTION'=>'NOTEBOOK L845 SP4304LA DF TOSHIBA', //Descripción del producto. MANDATORIO.		
	'CSITPRODUCTNAME'=>'NOTEBOOK L845 SP4304LA DF TOSHIBA', //Nombre del producto. MANDATORIO.		
	'CSITPRODUCTSKU'=>'LEVJNSL36GN', //Código identificador del producto. MANDATORIO.		
	'CSITTOTALAMOUNT'=>'1254.40', //CSITTOTALAMOUNT=CSITUNITPRICE*CSITQUANTITY "999999[.CC]" Con decimales opcional usando el punto como separador de decimales. No se permiten comas, ni como separador de miles ni como separador de decimales. MANDATORIO.		
	'CSITQUANTITY'=>'1', //Cantidad del producto. MANDATORIO.		
	'CSITUNITPRICE'=>'1254.40', //Formato Idem CSITTOTALAMOUNT. MANDATORIO.		
	...........................................................		
```		

[<sub>Volver a inicio</sub>](#inicio)
<br>

<a name="caracteristicas"></a>
## Características

<a name="status"></a>
####Status de la Operación
La SDK cuenta con un método para consultar el status de la transacción desde la misma SDK. El método se utiliza de la siguiente manera:
```php
$client = new TodoPago\Sdk($http_header, $mode);
$client->getStatus(array('MERCHANT'=>'305', 'OPERATIONID'=>'01'));// Merchant es el id site y $operation_id es el id operación que se envió en el array a través del método sendAuthorizeRequest() 
```
El siguiente método retornará el status actual de la transacción en Todopago.

<ins><strong>Ejemplo de Respuesta</strong></ins>
```php
array (size=1)
  'Operations' => 
    array (size=19)
      'RESULTCODE' => string '999' (length=3)
      'RESULTMESSAGE' => string 'RECHAZADA' (length=9)
      'DATETIME' => string '2015-05-13T14:11:38.287+00:00' (length=29)
      'OPERATIONID' => string '01' (length=2)
      'CURRENCYCODE' => string '32' (length=2)
      'AMOUNT' => int 54
      'TYPE' => string 'compra_online' (length=13)
      'INSTALLMENTPAYMENTS' => string '4' (length=1)
      'CUSTOMEREMAIL' => string 'cosme@fulanito.com' (length=18)
      'IDENTIFICATIONTYPE' => string 'DNI' (length=3)
      'IDENTIFICATION' => string '1212121212' (length=10)
      'CARDNUMBER' => string '12121212XXXXXX1212' (length=18)
      'CARDHOLDERNAME' => string 'Cosme Fulanito' (length=14)
      'TICKETNUMBER' => int 0
      'AUTHORIZATIONCODE' => null
      'BARCODE' => null
      'COUPONEXPDATE' => null
      'COUPONSECEXPDATE' => null
      'COUPONSUBSCRIBER' => null
```

Además, se puede conocer el estado de las transacciones a través del portal [www.todopago.com.ar](http://www.todopago.com.ar/). Desde el portal se verán los estados "Aprobada" y "Rechazada". Si el método de pago elegido por el comprador fue Pago Fácil o RapiPago, se podrán ver en estado "Pendiente" hasta que el mismo sea pagado.
	

<a name="statusdate"></a>
####Consulta de operaciones por rango de tiempo
En este caso hay que llamar a getByRangeDateTime() y devolvera todas las operaciones realizadas en el rango de fechas dado

```php
$client = new TodoPago\Sdk($http_header, $mode);

//Fecha en formato "Y-m-d"
$date1 = date("Y-m-d", time()-60*60*24*30);
$date2 = date("Y-m-d", time());

$client->getByRangeDateTime(array('MERCHANT'=>'305', "STARTDATE" => $date1, "ENDDATE" => $date2, "PAGENUMBER" => 1));
```	
<a name="devolucion"></a>
####Devolución

La SDK dispone de métodos para realizar la devolución, de una transacción realizada a traves de TodoPago.

Se debe llamar al método ```voidRequest``` de la siguiente manera:
```php

$options = array(
	"Security" => "837BE68A892F06C17B944F344AEE8F5F", // API Key del comercio asignada por TodoPago 
	"Merchant" => "35", // Merchant o Nro de comercio asignado por TodoPago
	"RequestKey" => "6d2589f2-37e6-1334-7565-3dc19404480c" // RequestKey devuelto como respuesta del servicio SendAutorizeRequest
);
$resp = $todopago->voidRequest($options);	
```

También se puede llamar al método ```voidRequest``` de la esta otra manera:
```php

$options = array(
	"Security" => "837BE68A892F06C17B944F344AEE8F5F", // API Key del comercio asignada por TodoPago 
	"Merchant" => "35", // Merchant o Nro de comercio asignado por TodoPago
	"AuthorizationKey" => "6d2589f2-37e6-1334-7565-3dc19404480c" // AuthorizationKey devuelto como respuesta del servicio GetAuthorizeAnswer
);
$resp = $todopago->voidRequest($options);	
```

**Respuesta del servicio:**
Si la operación fue realizada correctamente se informará con un código 2011 y un mensaje indicando el éxito de la operación.

```php
array(
	"StatusCode" => 2011,
	"StatusMessage" => "Operación realizada correctamente",
);
```
<br>

<a name="devolucionparcial"></a>
####Devolución parcial

La SDK dispone de métodos para realizar la devolución parcial, de una transacción realizada a traves de TodoPago.

Se debe llamar al método ```returnRequest``` de la siguiente manera:
```php

$options = array(
	"Security" => "837BE68A892F06C17B944F344AEE8F5F", // API Key del comercio asignada por TodoPago 
	"Merchant" => "35", // Merchant o Nro de comercio asignado por TodoPago
	"RequestKey" => "6d2589f2-37e6-1334-7565-3dc19404480c" // RequestKey devuelto como respuesta del servicio SendAutorizeRequest
	"AMOUNT" => "23.50" // Opcional. Monto a devolver, si no se envía, se trata de una devolución total
);
$resp = $todopago->returnRequest($options);
```

También se puede llamar al método ```returnRequest``` de la esta otra manera:
```php

$options = array(
	"Security" => "837BE68A892F06C17B944F344AEE8F5F", // API Key del comercio asignada por TodoPago 
	"Merchant" => "35", // Merchant o Nro de comercio asignado por TodoPago
	"AuthorizationKey" => "6d2589f2-37e6-1334-7565-3dc19404480c" // AuthorizationKey devuelto como respuesta del servicio GetAuthorizeAnswer
	"AMOUNT" => "23.50" // Opcional. Monto a devolver, si no se envía, se trata de una devolución total
);
$resp = $todopago->returnRequest($options);	
```

**Respuesta de servicio:**
Si la operación fue realizada correctamente se informará con un código 2011 y un mensaje indicando el éxito de la operación.

```php
array(
	"StatusCode" => 2011,
	"StatusMessage" => "Operación realizada correctamente",
);
```
<br>
<a name="formhidrido"></a>
####Formulario hibrido

**Conceptos basicos**<br>
El formulario hibrido, es una alternativa al medio de pago actual por redirección al formulario externo de TodoPago.<br> 
Con el mismo, se busca que el comercio pueda adecuar el look and feel del formulario a su propio diseño.

**Libreria**<br>
El formulario requiere incluir en la pagina una libreria Javascript de TodoPago.<br>
El endpoint depende del entorno:
+ Desarrollo: https://developers.todopago.com.ar/resources/TPHybridForm-v0.1.js
+ Produccion: https://forms.todopago.com.ar/resources/TPHybridForm-v0.1.js

**Restricciones y libertades en la implementación**

+ Ninguno de los campos del formulario podrá contar con el atributo name.
+ Se deberá proveer de manera obligatoria un botón para gestionar el pago con Billetera Todo Pago.
+ Todos los elementos de tipo <option> son completados por la API de Todo Pago.
+ Los campos tienen un id por defecto. Si se prefiere utilizar otros ids se deberán especificar los
mismos cuando se inicialice el script de Todo Pago.
+ Pueden aplicarse todos los detalles visuales que se crean necesarios, la API de Todo Pago no
altera los atributos class y style.
+ Puede utilizarse la API para setear los atributos placeholder del formulario, para ello deberá
especificar dichos placeholders en la inicialización del formulario "window.TPFORMAPI.hybridForm.setItem". En caso de que no se especifiquen los placeholders se usarán los valores por defecto de la API.

**HTML del formulario**

El formulario implementado debe contar al menos con los siguientes campos.

```html
<body>
	<select id="formaDePagoCbx"></select>
	<select id="bancoCbx"></select>
	<select id="promosCbx"></select>
	<label id="labelPromotionTextId"></label>
	<input id="numeroTarjetaTxt"/>
	<input id="mesTxt"/>
	<input id="anioTxt"/>
	<input id="codigoSeguridadTxt"/>
	<label id="labelCodSegTextId"></label>
	<input id="apynTxt"/>
	<select id="tipoDocCbx"></select>
	<input id="nroDocTxt"/>
	<input id="emailTxt"/><br/>
	<button id="MY_btnConfirmarPago"/>
</body>
```

**Inizialización y parametros requeridos**<br>
Para inicializar el formulario se usa window.TPFORMAPI.hybridForm.initForm. El cual permite setear los elementos y ids requeridos.

Para inicializar un ítem de pago, es necesario llamar a window.TPFORMAPI.hybridForm.setItem. Este requiere obligatoriamente el parametro publicKey que corresponde al PublicRequestKey (entregado por el SAR).
Se sugiere agregar los parametros usuario, e-mail, tipo de documento y numero.

**Javascript**
```js
window.TPFORMAPI.hybridForm.initForm({
    callbackValidationErrorFunction: 'validationCollector',
	callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
	callbackCustomErrorFunction: 'customPaymentErrorResponse',
	botonPagarId: 'MY_btnConfirmarPago',
	modalCssClass: 'modal-class',
	modalContentCssClass: 'modal-content',
	beforeRequest: 'initLoading',
	afterRequest: 'stopLoading'
});

window.TPFORMAPI.hybridForm.setItem({
    publicKey: 'taf08222e-7b32-63d4-d0a6-5cabedrb5782', //obligatorio
    defaultNombreApellido: 'Usuario',
    defaultNumeroDoc: 20234211,
    defaultMail: 'todopago@mail.com',
    defaultTipoDoc: 'DNI'
});

//callbacks de respuesta del pago
function validationCollector(parametros) {
}
function customPaymentSuccessResponse(response) {
}
function customPaymentErrorResponse(response) {
}
function initLoading() {
}
function stopLoading() {
}
```

**Callbacks**<br>
El formulario define callbacks javascript, que son llamados según el estado y la informacion del pago realizado:
+ customPaymentSuccessResponse: Devuelve response si el pago se realizo correctamente.
+ customPaymentErrorResponse: Si hubo algun error durante el proceso de pago, este devuelve el response con el codigo y mensaje correspondiente.

**Ejemplo de Implementación**:
<a href="/resources/form_hibrido-ejemplo/index.html" target="_blank">Formulario hibrido</a>
<br>

[<sub>Volver a inicio</sub>](#inicio)

<a name="credenciales"></a>
####Obtener credenciales
El SDK permite obtener las credenciales "Authentification", "MerchandId" y "Security" de la cuenta de Todo Pago, ingresando el usuario y contraseña.<br>
Esta funcionalidad es util para obtener los parametros de configuracion dentro de la implementacion.
	
- Crear una instancia de la clase User:
```php

$http_header = array();

$connector = new Sdk($http_header, "test");//instanciar SDK

$datosUsuario = array(
	"user" => "usuario@todopago.com.ar", 
	"password" => "contraseña"
);

$credenciales = new TodoPago\Data\User($datosUsuario);
```

Tambien se puede pasar los datos de usuario de la siguiente manera:  

```php
$credenciales = new TodoPago\Data\User("usuario@todopago.com.ar", "contraseña");
```

```php
$credenciales = new TodoPago\Data\User();
$credenciales->setUser("usuario@todopago.com.ar");
$credenciales->setPassword("contraseña");
```

- Obtener respuesta de servicio:
```php		
$rta = $connector->getCredentials($credenciales);
$rta->getMerchant();
$rta->getApikey();
```		
**Observación**: El Security se obtiene a partir de apiKey, eliminando TODOPAGO de este ultimo.

[<sub>Volver a inicio</sub>](#inicio)
<br>

<a name="secuencia"></a>
##Diagrama de secuencia
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/secuencia-page-001.jpg)

[<sub>Volver a inicio</sub>](#inicio)
<br>

<a name="tablareferencia"></a>    
## Tablas de Referencia   
######[Provincias](#p)    
				
<p>Solo utilizado para incluir los datos de control de fraude</p>
<table>		
<tr><th>Provincia</th><th>Código</th></tr>		
<tr><td>CABA</td><td>C</td></tr>		
<tr><td>Buenos Aires</td><td>B</td></tr>		
<tr><td>Catamarca</td><td>K</td></tr>		
<tr><td>Chaco</td><td>H</td></tr>		
<tr><td>Chubut</td><td>U</td></tr>		
<tr><td>Córdoba</td><td>X</td></tr>		
<tr><td>Corrientes</td><td>W</td></tr>		
<tr><td>Entre Ríos</td><td>E</td></tr>		
<tr><td>Formosa</td><td>P</td></tr>		
<tr><td>Jujuy</td><td>Y</td></tr>		
<tr><td>La Pampa</td><td>L</td></tr>		
<tr><td>La Rioja</td><td>F</td></tr>		
<tr><td>Mendoza</td><td>M</td></tr>		
<tr><td>Misiones</td><td>N</td></tr>		
<tr><td>Neuquén</td><td>Q</td></tr>		
<tr><td>Río Negro</td><td>R</td></tr>		
<tr><td>Salta</td><td>A</td></tr>		
<tr><td>San Juan</td><td>J</td></tr>		
<tr><td>San Luis</td><td>D</td></tr>		
<tr><td>Santa Cruz</td><td>Z</td></tr>		
<tr><td>Santa Fe</td><td>S</td></tr>		
<tr><td>Santiago del Estero</td><td>G</td></tr>		
<tr><td>Tierra del Fuego</td><td>V</td></tr>		
<tr><td>Tucumán</td><td>T</td></tr>		
</table>

[<sub>Volver a inicio</sub>](#inicio)

<a name="codigoerrores"></a>    
## Tabla de errores     

<table>		
<tr><th>Id mensaje</th><th>Mensaje</th></tr>				
<tr><td>-1</td><td>Aprobada.</td></tr>
<tr><td>1081</td><td>Tu saldo es insuficiente para realizar la transacción.</td></tr>
<tr><td>1100</td><td>El monto ingresado es menor al mínimo permitido</td></tr>
<tr><td>1101</td><td>El monto ingresado supera el máximo permitido.</td></tr>
<tr><td>1102</td><td>La tarjeta ingresada no corresponde al Banco indicado. Revisalo.</td></tr>
<tr><td>1104</td><td>El precio ingresado supera al máximo permitido.</td></tr>
<tr><td>1105</td><td>El precio ingresado es menor al mínimo permitido.</td></tr>
<tr><td>2010</td><td>En este momento la operación no pudo ser realizada. Por favor intentá más tarde. Volver a Resumen.</td></tr>
<tr><td>2031</td><td>En este momento la validación no pudo ser realizada, por favor intentá más tarde.</td></tr>
<tr><td>2050</td><td>Lo sentimos, el botón de pago ya no está disponible. Comunicate con tu vendedor.</td></tr>
<tr><td>2051</td><td>La operación no pudo ser procesada. Por favor, comunicate con tu vendedor.</td></tr>
<tr><td>2052</td><td>La operación no pudo ser procesada. Por favor, comunicate con tu vendedor.</td></tr>
<tr><td>2053</td><td>La operación no pudo ser procesada. Por favor, intentá más tarde. Si el problema persiste comunicate con tu vendedor</td></tr>
<tr><td>2054</td><td>Lo sentimos, el producto que querés comprar se encuentra agotado por el momento. Por favor contactate con tu vendedor.</td></tr>
<tr><td>2056</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2057</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>2059</td><td>La operación no pudo ser procesada. Por favor intentá más tarde.</td></tr>
<tr><td>90000</td><td>La cuenta destino de los fondos es inválida. Verificá la información ingresada en Mi Perfil.</td></tr>
<tr><td>90001</td><td>La cuenta ingresada no pertenece al CUIT/ CUIL registrado.</td></tr>
<tr><td>90002</td><td>No pudimos validar tu CUIT/CUIL.  Comunicate con nosotros <a href="#contacto" target="_blank">acá</a> para más información.</td></tr>
<tr><td>99900</td><td>El pago fue realizado exitosamente</td></tr>
<tr><td>99901</td><td>No hemos encontrado tarjetas vinculadas a tu Billetera. Podés  adherir medios de pago desde www.todopago.com.ar</td></tr>
<tr><td>99902</td><td>No se encontro el medio de pago seleccionado</td></tr>
<tr><td>99903</td><td>Lo sentimos, hubo un error al procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99970</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99971</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99977</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99978</td><td>Lo sentimos, no pudimos procesar la operación. Por favor reintentá más tarde.</td></tr>
<tr><td>99979</td><td>Lo sentimos, el pago no pudo ser procesado.</td></tr>
<tr><td>99980</td><td>Ya realizaste un pago en este sitio por el mismo importe. Si querés realizarlo nuevamente esperá 5 minutos.</td></tr>
<tr><td>99982</td><td>En este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99983</td><td>Lo sentimos, el medio de pago no permite la cantidad de cuotas ingresadas. Por favor intentá más tarde.</td></tr>
<tr><td>99984</td><td>Lo sentimos, el medio de pago seleccionado no opera en cuotas.</td></tr>
<tr><td>99985</td><td>Lo sentimos, el pago no pudo ser procesado.</td></tr>
<tr><td>99986</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99987</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99988</td><td>Lo sentimos, momentaneamente el medio de pago no se encuentra disponible. Por favor intentá más tarde.</td></tr>
<tr><td>99989</td><td>La tarjeta ingresada no está habilitada. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente.</td></tr>
<tr><td>99990</td><td>La tarjeta ingresada está vencida. Por favor seleccioná otra tarjeta o actualizá los datos.</td></tr>
<tr><td>99991</td><td>Los datos informados son incorrectos. Por favor ingresalos nuevamente.</td></tr>
<tr><td>99992</td><td>La fecha de vencimiento es incorrecta. Por favor seleccioná otro medio de pago o actualizá los datos.</td></tr>
<tr><td>99993</td><td>La tarjeta ingresada no está vigente. Por favor seleccioná otra tarjeta o actualizá los datos.</td></tr>
<tr><td>99994</td><td>El saldo de tu tarjeta no te permite realizar esta operacion.</td></tr>
<tr><td>99995</td><td>La tarjeta ingresada es invalida. Seleccioná otra tarjeta para realizar el pago.</td></tr>
<tr><td>99996</td><td>La operación fué rechazada por el medio de pago porque el monto ingresado es inválido.</td></tr>
<tr><td>99997</td><td>Lo sentimos, en este momento la operación no puede ser realizada. Por favor intentá más tarde.</td></tr>
<tr><td>99998</td><td>Lo sentimos, la operación fue rechazada. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente o seleccioná otro medio de pago.</td></tr>
<tr><td>99999</td><td>Lo sentimos, la operación no pudo completarse. Comunicate con la entidad emisora de la tarjeta para verificar el incoveniente o seleccioná otro medio de pago.</td></tr>
</table>

[<sub>Volver a inicio</sub>](#inicio)

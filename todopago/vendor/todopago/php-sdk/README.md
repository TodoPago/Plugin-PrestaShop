<a name="inicio"></a>		
todopago-sdk-php		
=======		
		
Modulo para conexión con gateway de pago Todo Pago		

######[Instalación](#instalacion)		
######[Versiones de php soportadas](#Versionesdephpsoportadas)
######[Generalidades](#general)	
######[Uso](#uso)		
######[Datos adicionales para prevención de fraude](#datosadicionales) 		
######[Ejemplo](#ejemplo)		
######[Modo test](#test)
######[Status de la operación](#status)
######[Diagrama de secuencia](#secuencia)
######[Tablas de referencia](#tablas)		

<a name="instalacion"></a>		
## Instalación		
Se debe descargar la última versión del SDK desde el botón Download ZIP, branch master.		
Una vez descargado y descomprimido, debe incluirse el archivo Sdk.php que se encuentra en la carpeta /TodoPago/lib como librería dentro del proyecto.		
<br />		

También se puede realizar la instalación a través de Composer.<br/>
```composer require todopago/php-sdk```<br/>
E incluir el archivo vendor/autoload.php en el proyecto.<br/>
<br/>
Observación: Descomentar: extension=php_soap.dll y extension=php_openssl.dll del php.ini, ya que para la conexión al gateway se utiliza la clase SoapClient del API de PHP. 

[<sub>Volver a inicio</sub>](#inicio)		

<a name="Versionesdephpsoportadas"></a>		
## Versiones de php soportadas		
La versi&oacute;n implementada de la SDK, esta testeada para versiones desde  PHP5.3 en adelante.
<br />		
[<sub>Volver a inicio</sub>](#inicio)		

<a name="general"></a>
## Generalidades
Esta versión soporta únicamente pago en moneda nacional argentina (CURRENCYCODE = 32).
[<sub>Volver a inicio</sub>](#inicio)		


<a name="uso"></a>		
## Uso		
####1.Inicializar la clase correspondiente al conector (TodoPago\Sdk).

- crear un array con los http header suministrados por Todo Pago
```php
$http_header = array('Authorization'=>'PRISMA 912EC803B2CE49E4A541068D495AB570');
```
- crear una instancia de la clase TodoPago\Sdk
```php		
$connector = new TodoPago\Sdk($http_header, $mode); // $mode: "test" para testing, "prod" para producción
```		
		
####2.Solicitud de autorización		
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

*en el ejemplo se envían parámetros en la url (en nuestro ejemplo: ?Order=27398173292187), para ser recibidos por la tienda vía ***get** y de este modo recuperar el valor en un próximo paso.

<ins><strong>datos propios de la operación</strong></ins>		
$optionsSAR_operacion debe ser un array con la siguiente estructura:		
		
```php
$optionsSAR_operacion = array (
	'MERCHANT'=> 305, //dato fijo (número identificador del comercio)
	'OPERATIONID'=>'27398173292187', //número único que identifica la operación
	'CURRENCYCODE'=> 32, //por el momento es el único tipo de moneda aceptada
	'AMOUNT'=>54.00,
	'EMAILCLIENTE'=>'email_cliente@dominio.com',
	);		
```		

<ins><strong>Ejemplo de Respuesta</strong></ins>
```php	
    array (size=5)
    'StatusCode' => int -1
    'StatusMessage' => string 'Solicitud de Autorizacion Registrada' (length=36)
    'URL_Request' => string 'https://developers.todopago.com.ar/formulario/commands?command=formulario&m=6d2589f2-37e6-1334-7565-3dc19404480c' (length=102)
    'RequestKey' => string '6d2589f2-37e6-1334-7565-3dc19404480c' (length=36)
    'PublicRequestKey' => string '6d2589f2-37e6-1334-7565-3dc19404480c' (length=36)
```
La **url_request** es donde está hosteado el formulario de pago y donde hay que redireccionar al usuario, una vez realizado el pago según el éxito o fracaso del mismo, el formulario redireccionará a una de las 2 URLs seteadas en **$optionsSAR_comercio** ([URL_OK](#url_ok), en caso de éxito o [URL_ERROR](#url_error), en caso de que por algún motivo el formulario rechace el pago)

<ins><strong>Códigos de rechazo</strong></ins>
<table>
<tr><th>Código</th><th>Descripción</th></tr>
<tr><td>-1</td><td>Aprobada</td></tr>
<tr><td>403</td><td>Error de Autenticación</td></tr>
<tr><td>404</td><td>Transacción Inexistente</td></tr>
<tr><td>702</td><td>Cuenta Inexistente</td></tr>
<tr><td>720</td><td>Error de Parametrización Interno</td></tr>
<tr><td>999</td><td>Error de Sistema</td></tr>
</table>

Si, por ejemplo, se pasa mal el <strong>MerchantID</strong> se obtendrá la siguiente respuesta:
```php
array (size=2)
  'StatusCode' => int 702
  'StatusMessage' => string 'ERROR: Cuenta Inexistente' (length=25)
(length=30)
```

####3.Confirmación de transacción.		
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
<br />		
		
[<sub>Volver a inicio</sub>](#inicio)		
		
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
	'CSITPRODUCTCODE'=>'electronic_good', //Código de producto. CONDICIONAL. Valores posibles(adult_content;coupon;default;electronic_good;electronic_software;gift_certificate;handling_only;service;shipping_and_handling;shipping_only;subscription)		
	'CSITPRODUCTDESCRIPTION'=>'NOTEBOOK L845 SP4304LA DF TOSHIBA', //Descripción del producto. CONDICIONAL.		
	'CSITPRODUCTNAME'=>'NOTEBOOK L845 SP4304LA DF TOSHIBA', //Nombre del producto. CONDICIONAL.		
	'CSITPRODUCTSKU'=>'LEVJNSL36GN', //Código identificador del producto. CONDICIONAL.		
	'CSITTOTALAMOUNT'=>'1254.40', //CSITTOTALAMOUNT=CSITUNITPRICE*CSITQUANTITY "999999[.CC]" Con decimales opcional usando el punto como separador de decimales. No se permiten comas, ni como separador de miles ni como separador de decimales. CONDICIONAL.		
	'CSITQUANTITY'=>'1', //Cantidad del producto. CONDICIONAL.		
	'CSITUNITPRICE'=>'1254.40', //Formato Idem CSITTOTALAMOUNT. CONDICIONAL.		
	...........................................................		
```		
		
<a name="ejemplo"></a>		
## Ejemplo		
Existe un ejemplo en https://github.com/TodoPago/sdk-php/tree/master/resources/ejemplo.php que muestra los resultados de los métodos principales  del SDK.		
		

<a name="status"></a>
## Status de la Operación
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

[<sub>Volver a inicio</sub>](#inicio)		

<a name="secuencia"></a>
##Diagrama de secuencia
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/secuencia-page-001.jpg)

<a name="tablas"></a>		
## Tablas de Referencia		
######[Códigos de Estado](#cde)		
######[Provincias](#p)		
<a name="cde"></a>		
<p>Codigos de Estado</p>		
<table>		
<tr><th>IdEstado</th><th>Descripción</th></tr>		
<tr><td>1</td><td>Ingresada</td></tr>		
<tr><td>2</td><td>A procesar</td></tr>		
<tr><td>3</td><td>Procesada</td></tr>		
<tr><td>4</td><td>Autorizada</td></tr>		
<tr><td>5</td><td>Rechazada</td></tr>		
<tr><td>6</td><td>Acreditada</td></tr>		
<tr><td>7</td><td>Anulada</td></tr>		
<tr><td>8</td><td>Anulación Confirmada</td></tr>		
<tr><td>9</td><td>Devuelta</td></tr>		
<tr><td>10</td><td>Devolución Confirmada</td></tr>		
<tr><td>11</td><td>Pre autorizada</td></tr>		
<tr><td>12</td><td>Vencida</td></tr>		
<tr><td>13</td><td>Acreditación no cerrada</td></tr>		
<tr><td>14</td><td>Autorizada *</td></tr>		
<tr><td>15</td><td>A reversar</td></tr>		
<tr><td>16</td><td>A registar en Visa</td></tr>		
<tr><td>17</td><td>Validación iniciada en Visa</td></tr>		
<tr><td>18</td><td>Enviada a validar en Visa</td></tr>		
<tr><td>19</td><td>Validada OK en Visa</td></tr>		
<tr><td>20</td><td>Recibido desde Visa</td></tr>		
<tr><td>21</td><td>Validada no OK en Visa</td></tr>		
<tr><td>22</td><td>Factura generada</td></tr>		
<tr><td>23</td><td>Factura no generada</td></tr>		
<tr><td>24</td><td>Rechazada no autenticada</td></tr>		
<tr><td>25</td><td>Rechazada datos inválidos</td></tr>		
<tr><td>28</td><td>A registrar en IdValidador</td></tr>		
<tr><td>29</td><td>Enviada a IdValidador</td></tr>		
<tr><td>32</td><td>Rechazada no validada</td></tr>		
<tr><td>38</td><td>Timeout de compra</td></tr>		
<tr><td>50</td><td>Ingresada Distribuida</td></tr>		
<tr><td>51</td><td>Rechazada por grupo</td></tr>		
<tr><td>52</td><td>Anulada por grupo</td></tr>		
</table>		
		
<a name="p"></a>		
<p>Provincias</p>
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
<tr><td>Entre Ríos</td><td>R</td></tr>		
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

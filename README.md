<a name="inicio"></a>
Prestashop - módulo Todo Pago (v1.4.x a 1.6.x)
==========

**Para reinstalar debe desinstalar cualquier versión anterior antes de copiar el plugin a la carpeta modules de Prestashop. De lo contrario fallará la instalacion de la nueva versión**

Plug in para la integración con gateway de pago <strong>Todo Pago</strong>
- [Consideraciones Generales](#consideracionesgenerales)
- [Instalación](#instalacion)
- [Configuración](#configuracion)
	- [Configuración plug in](#confplugin)
	- [Formulario de pago integrado](#formulario)
	- [Obtener datos de configuracion](#getcredentials)
	- [Nuevas columnas y atributos](#tca)
- [Prevencion de Fraude](#cybersource)
	- [Consideraciones generales](#cons_generales)
	- [Consideraciones para vertical retail](#cons_retail)
- [Características](#features)
	- [Consulta de transacciones](#constrans)
	- [Devoluciones](#devoluciones)
- [Tablas de referencia](#tablasProv)
- [Tabla de errores](#codigoerrores)
- [Versiones disponibles](#availableversions)

<a name="consideracionesgenerales"></a>
## Consideraciones Generales
El plug in de pagos de <strong>Todo Pago</strong>, provee a las tiendas Prestashop de un nuevo método de pago, integrando la tienda al gateway de pago.
La versión de este plug in esta testeada en PHP 5.3 en adelante y Prestashop 1.6.

<a name="instalacion"></a>
## Instalación
1. Descomprimir el archivo .zip.
2. Copiar carpeta la carpeta "todopago" en la carpeta prestashop/modules.
3.	Ir a  "Módulos" dentro del Área de Administración.
4. En la lista de módulos, ir a la fila llamada "Todo pago" y 	hacer click donde dice "Instalar". De aparecer un cartel de advertencia, elegir la opción "Seguir con la instalación". Una vez instalado, se redirige a la pagina de configuración, a la que se puede acceder desde la lista de módulos.

Observación:
Descomentar: <em>extension=php_soap.dll</em> y <em>extension=php_openssl.dll</em> del php.ini, ya que para la conexión al gateway se utiliza la clase <em>SoapClient</em> del API de PHP.
<br />
[<sub>Volver a inicio</sub>](#inicio)

<a name="configuracion"></a>
##Configuración

<a name="confplugin"></a>
####Configuración plug in
Para llegar al menu de configuración ir a <em>Módulos</em> y en la lista buscar el ítem llamado <strong>Todo Pago.
El Plug-in esta separado en configuarción general y 3 sub-menues.</strong>
<sub><em>Menú principal</em></sub>
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/general.jpg)
<a name="confplanes"></a>
<br />
[<sub>Volver a inicio</sub>](#inicio)

<a name="formulario"></a>
####Formulario de pago integrado
El plugin tiene dos opciones de formulario para emplear en el proceso de pago. 
El formulario externo, que redirecciona a un formulario externo en Todo Pago y el fomulario integrado que permite hacer el pago dentro del e-commerce.  

En la pagina de admin del plugin, en la seccion "CONFIGURACION - FORMULARIO HIBRIDO" se puede habilitar uno de los formulario.
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/prestashop/admin-formulario-hibrido.png)

El formulario seleccionado se mostrara en la etapa final del proceso de pago "Confirmar pago".
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/prestashop/formulario-hibrido.png)

[<sub>Volver a inicio</sub>](#inicio)

<a name="getcredentials"></a>
####Credenciales

En la secciones de ambientes de developers y produccion, se debe ingresar el MerchantID, Authorization y Security de Todo Pago.<br>
Estos se pueden obtener desde la pagina de Todo Pago o desde el boton "Obtener credenciales".<br> 
Al Ingresar el usuario de todo pago se completan los campos con los datos del ambiente seleccionado.

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/prestashop/login-credenciales.png)

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/prestashop/seccion-ambiente-credenciales.png)

Nota: El boton "Obtener credenciales" se habilita si el ambiente en cuestion se encuentra seleccionado en General->Ejecucion en produccion.

<a name="tca"></a>
#### Nuevas columnas y atributos
El plug in creará nuevas tablas y registros en tablas existentes para lograr las nuevas funcionalidades y su persistencia dentro del framework 

#####Tablas:
1. <i>todopago_transacciones</i>, para guardar un registro de las ordenes que utilizaron este método de pago.
2. <i>todopago_productos</i>, para guardar la información necesaria en la [Prevención del Fraude](#cybersource).

#####Registros:
Los valores de configuración se encuentran guardados en la tabla <i>configuration</i>
<br/>
[<sub>Volver a inicio</sub>](#inicio)

<a name="cybersource"></a>
## Prevención de Fraude
- [Consideraciones Generales](#cons_generales)
- [Consideraciones para vertical RETAIL](#cons_retail)

<a name="cons_generales"></a>
####Consideraciones Generales (para todas los verticales, por defecto RETAIL)
El plugin toma valores estándar del framework para validar los datos del comprador. Para ello se utilizan las clases Customer, Address y State para recuperar los registros almacenados en la base de datos que corresponden al cliente que efectúa la compra y Cart para recuperar el carrito en el que se almacena los datos relativos a la compra en sí.

```php
   $cart = $this->context->cart;
   $customer = new Customer($cart->id_customer);
   $address = new Address($cart->id_address_invoice);
   $state = new State($address->id_state);

-- Ciudad de Facturación: $address->city;
-- País de facturación:  $address->country;
-- Identificador de Usuario: $customer->id;
-- Email del usuario al que se le emite la factura: $customer->email;
-- Nombre de usuario el que se le emite la factura: $customer->firstname;
-- Apellido del usuario al que se le emite la factura: $customer->lastname;
-- Teléfono del usuario al que se le emite la factura: $address->phone;
-- Provincia de la dirección de facturación: $state->iso_code;
-- Domicilio de facturación: $address->address1;
-- Moneda: $cart->id_currency;
-- Total:  $cart->getOrderTotal(true, Cart::BOTH);
-- IP de la pc del comprador: Tools::getRemoteAddr();
```
También se utiliza la clase <em>Customer</em> para obtener el password del usuario (comprador) y la tabla <em>Orders</em>, donde se consultan las transacciones facturadas al comprador.
<a name="cons_retail"></a>
####Consideraciones para vertical RETAIL
Las consideración para el caso de empresas del rubro <strong>RETAIL</strong> son similares a las <em>consideraciones generales</em> con la diferencia de se utiliza el atributo id_address_delivery en lugar de id_address_invoice para recuperar el registro de la tabla address

```php
   $cart = $this->context->cart;
   $customer = new Customer($cart->id_customer);
   $address = new Address($cart->id_address_delivery);
   $state = new State($address->id_state);
   $carrier = new Carrier($cart->id_carrier);
   
-- Ciudad de envío de la orden: $address->city;
-- País de envío de la orden: $address->country;
-- Mail del destinatario: $customer->email;
-- Nombre del destinatario: $customer->firstname;
-- Apellido del destinatario: $customer->lastname;
-- Número de teléfono del destinatario: $address->phone;
-- Código postal del domicio de envío: $address->postcode;
-- Provincia de envío: $state->iso_code;
-- Domicilio de envío: $address->address1;
-- Método de despacho: $carrier->name;
-- Listado de los productos: $cart->getProducts();
```
nota: la funcion $cart->getProducts() devuelve un array con el listado de los productos, que se usan para conseguir la información que se debe enviar mediante la función <strong>_getProductsDetails()</strong>.

####Muy Importante
<strong>Provincias:</strong> uno de los datos requeridos para prevención común a todos los verticales  es el campo provinicia/state tanto del comprador como del lugar de envío, para tal fin el plug in utiliza el valor del campo id_state, que figura en el registro Address recuperado, para recuperar el objeto State correspondiente a ese id, y así obtener el iso_code. El formato de estos datos deben ser tal cual la tabla de referencia (tabla provincias). En Prestashop el listado se encuentra en Localización -> Provincias.
<br />
<strong>Celular:</strong> se utiliza el atributo phone_mobile del registro Address recuperado.
[<sub>Volver a inicio</sub>](#inicio)

<a name="features"></a>
## Características
 - [Consulta de transacciones](#constrans)
 - [Devoluciones](#devoluciones)
 
<br />
<a name="constrans"></a>
####Consulta de transacciones
El plugin genera un nuevo tab con el nombre "Todopago" en la pagina de detalle de pedido "Pedidos->Pedidos->Detalle". 
Esa permite consultar online las características de la transacción en el sistema de Todo Pago.

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/prestashop/estado-orden-tp.png)

[<sub>Volver a inicio</sub>](#inicio)

<a name="devoluciones"></a>
####Devoluciones
TodoPago permite realizar la devolucion total o parcial de dinero de una orden de compra realizada.

Para ello se debe habilitar desde Admin la opcion de devoluciones, ir a Pedido->Devoluciones de Mercancía.

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/prestashop/admin-enable-devoluciones.png)

Para realizar una devolucion parcial o total, Prestashop tiene en la seccion "Pedido->Pedidos->Ver Orden". Dos botones "Reembolso parcial" el cual permite devolver un monto especifico y "Devolver productos" que permite devolver el total del monto.

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/prestashop/funcionalidad-devolucion.png)

[<sub>Volver a inicio</sub>](#inicio)

<a name="tablasProv"></a>
## Tablas de Referencia
######[Provincias](#p)

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

<a name="availableversions"></a>
## Versiones Disponibles##
<table>
  <thead>
    <tr>
      <th>Version del Plugin</th>
      <th>Estado</th>
      <th>Versiones Compatibles</th>
    </tr>
  <thead>
  <tbody>
    <tr>
      <td><a href="https://github.com/TodoPago/Plugin-PrestaShop/archive/master.zip">v1.4.x - v1.6.x</a></td>
      <td>Stable (Current version)</td>
      <td>PrestaShop v1.6.x<br />
      </td>
    </tr>
  </tbody>
</table>

[<sub>Volver a inicio</sub>](#inicio)

<a name="inicio"></a>
Prestashop
==========

**PARA INSTALAR LA VERSIÓN v1.0.0 SE DEBE DESINSTALAR CUALQUIER VERSIÓN ANTERIOR ANTES DE COPIAR EL PLUGIN A LA CARPETA modules DE PRESTASHOP. DE LO CONTRARIO FALLARÁ LA INSTALACIÓN DE LA NUEVA VERSIÓN**

Plug in para la integración con gateway de pago <strong>Todo Pago</strong>
- [Consideraciones Generales](#consideracionesgenerales)
- [Instalación](#instalacion)
- [Configuración](#configuracion)
- [Datos adiccionales para prevención de fraude](#cybersource)
- [Consulta de transacciones](#constrans)
- [Tablas de referencia](#tablas)

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
Descomentar: <em>extension=php_soap.dll</em> del php.ini, ya que para la conexión al gateway se utiliza la clase <em>SoapClient</em> del API de PHP.
<br />
[<sub>Volver a inicio</sub>](#inicio)

<a name="configuracion"></a>
##Configuración
Para llegar al menu de configuración ir a <em>Módulos</em> y en la lista buscar el ítem llamado <strong>Todo Pago</strong>. El Plug-in esta separado en configuarción general y 3 sub-menues.<br />
<sub><em>Menú principal</em></sub>
![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/general.jpg)
<a name="confplanes"></a>
<br />

Nota: lo siguientes campos de la configuración deben ingresarse con formato JSON
*  Authorization. Ejemplo: {"Authorization": "PRISMA 345678RGAGHUAJRG6789GJDDSDHJK"}

[<sub>Volver a inicio</sub>](#inicio)
<a name="tca"></a>
## Nuevas columnas y atributos
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
El plug in toma valores estándar del framework para validar los datos del comprador. Para ello se utilizan las clases Customer, Address y State para recuperar los registros almacenados en la base de datos que corresponden al cliente que efectúa la compra y Cart para recuperar el carrito en el que se almacena los datos relativos a la compra en sí.

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

<a name="tablas"></a>
## Tablas de Referencia

####Provincias
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

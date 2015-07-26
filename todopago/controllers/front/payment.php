<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.6.0
 */
 //controlador

use TodoPago\Sdk;
use TPTransaccion as Transaccion;
use TPProductoCybersource as ProductoCybersource;

require_once (dirname(__FILE__) . '../../../lib/TodoPago/lib/Sdk.php');
require_once (dirname(__FILE__) . '../../../classes/Transaccion.php');
require_once (dirname(__FILE__) . '../../../classes/Productos.php');
class TodoPagoPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;
    private $codigoAprobacion = -1; //valor del campo SatusCode que indica que la transaccion fue aprobada (en este caso -1).
    
    public function initContent()
    {
        $this->display_column_left = false;//para que no se muestre la columna de la izquierda
        $this->db = Db::getInstance();
        parent::initContent();//llama al init() de FrontController, que es la clase padre

        //variables a usar
        $cart = $this->context->cart;
        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $cliente = new Customer($cart->id_customer);//recupera al objeto cliente
        $paso = (int) Tools::getValue('paso');
        $prefijo;//Modo en el que se ejecuta el modulo
        $options;//lo que se envia a los webservices
        $respuesta=array();//lo que devuelven los webservices
        $ClavePago;
        $urlRedir;
        $servicioConfig;
        $exception_message = '';
        $template;//template de Smarty que se usa. Depende del paso.
        $smarty;//variables que se usan en la template que corresponda al paso
        $this->tranEstado = $this->_tranEstado($cart->id);
        
        try 
        {
            if (!$this->module->checkCurrency($cart))
                Tools::redirect('index.php?controller=order');
            
            //si el carrito esta vacio
            if ($cart == NULL ||  $cart->getProducts() == NULL || $cart->getOrderTotal(true, Cart::BOTH) == 0)
            {
                throw new Exception('Carrito vacio');
            }
            
            //si ya existe una orden para este carrito
            if ($cart->OrderExists() == true)
            {
                throw new Exception('Ya existe una orden para el carro id '.$cart->id);
            }
            
            //Prefijo que se usa para la peticion al webservice, dependiendo del modo en el que este seteado el modulo
            $prefijo = $this->module->getPrefijoModo();
            
            //Traigo los settings del servicio (proxy, ubicacion del certificado y timeout
            $servicioConfig = $this->_getServiceSettings($prefijo);
            
            $mode = ($this->module->getModo())?"prod":"test";
            //creo el conector con el valor de Authorization, la direccion de WSDL y endpoint que corresponda
            $connector = new Sdk($this->_getAuthorization(), $mode);
                    
            if (isset($servicioConfig['proxy'])) // si hay un proxy
                $connector->setProxyParameters($proxy['host'], $proxy['port'], $proxy['user'], $proxy['pass']);
            
            if ($servicioConfig['certificado'] != '')//si hay una ubicación de certificado
                $connector->setLocalCert($servicioConfig['certificado']);
            
            if ($servicioConfig['timeout'] != '')//si hay un timeout
                $connector->setConnectionTimeout($servicioConfig['timeout']);
            
            if($this->tranEstado == 0) 
                $this->_tranCrear($cart->id, array());
            
            //comunicacion con el  webservice
            switch ($paso)
            {
                case 1: 
                    list($smarty, $template) = $this->first_step_todopago($cart, $prefijo, $cliente, $connector);
                break;
                case 2:
                    $this->second_step_todopago($prefijo, $cart, $connector);        
                break;
                default:
                    $this->module->log('Redireccionando al paso 1');
                    Tools::redirect($this->context->link->getModuleLink('todopago', 'payment', array ('paso' => '1'), true));
                break;
            }
        }
        catch (Exception $e)
        {
            //Guardo el mensaje
            $this->module->log('EXCEPCION: '.$e->getMessage());
            $template='payment_error';
        }
        
        //asigno las variables que se van a a ver en la template de payment (payment.tpl)
        $this->context->smarty->assign(array(
            'nombre' => Configuration::get($this->module->getPrefijo('PREFIJO_CONFIG').'_NOMBRE'),//nombre con el que aparece este modulo de pago en el frontend
            //variables de la compra
            'cart_id' => $cart->id,
            'nbProducts' => $cart->nbProducts(),//productos
            'cust_currency' => $cart->id_currency,//moneda en la que paga el cliente
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),//moneda
            'total' => $total,//total de la orden
            'cliente' =>$cliente->email,
            //otros
            'this_path' => $this->module->getPathUri(),
            'this_path_modulo' => strtolower('modules/'.$this->module->name.'/'),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
        ));

        //variables que dependen de cada paso
        if (isset($smarty))//hay casos en los que esta variable no esta seteada
        {
            $this->context->smarty->assign(array(
                    'payment' => $smarty
            ));
        }
        
        $this->setTemplate($template.'.tpl');//plantilla que se va a usar.
    }
    
    public function first_step_todopago($cart, $prefijo, $cliente, $connector)
    {
        /** PASO 1: sendAuthorizeRequest
         * La respuesta contiene los siguientes campos: 
         * StatusCode: codigo correspondiente al resultado de la autorizacion, 
         * StatusMessage: mensaje explicativo, 
         * URL_Request. url del formulario al que se ingresan los datos,
         * RequestKey: id necesario para el formulario,
         * PublicRequestKey: igual al RequestKey
         */
        $this->module->logInfo($cart->id,'first step');
        if($this->tranEstado != 1)
        {
            throw new Exception("first_step ya realizado");
            $smarty['status'] = 0;//indica que hubo un error en este paso
        }
        
        $options = $this->getOptionsSARComercio($prefijo, $cart->id);
        $options = array_merge($options, $this->getOptionsSAROperacion($prefijo, $cliente, $cart));
        
        $this->module->logInfo($cart->id,'params SAR',$options);
        
        $respuesta = $connector->sendAuthorizeRequest($options['comercio'], $options['operacion']);//me comunico con el webservice
        $this->module->logInfo($cart->id,'response SAR',$respuesta);
        if ($respuesta['StatusCode']  != $this->codigoAprobacion)//Si la transacción salió mal
        {
            $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], "");
            $this->_tranUpdate($cart->id, array("first_step" => null));
            $smarty['status'] = 0;//indica que hubo un error en este paso            
            throw new Exception($respuesta['StatusMessage']);
        }
        $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], $respuesta['RequestKey']);//guardo la request key y otros datos importantes
        
        $now = new DateTime();
        $this->_tranUpdate($cart->id, array("first_step" => $now->format('Y-m-d H:i:s'), "params_SAR" => json_encode($options), "response_SAR" => json_encode($respuesta), "request_key" => $respuesta['RequestKey'], "public_request_key" => $respuesta['PublicRequestKey']));
        
        //variables que se pasan al smarty
        $smarty['redir'] = $respuesta['URL_Request'];//direccion del formulario
        $smarty['StatusMessage'] = $respuesta['StatusMessage'];//mensaje que devuelve el primer webservice
        $smarty['status'] = 1;//indica que este paso se ejecuto correctamente
        $smarty['RequestKey'] = $respuesta['RequestKey'];
        $smarty['PublicRequestKey'] = $respuesta['PublicRequestKey'];
        
        // Chequeo si form embebed o redirect
        $embebed = $this->_getEmbebedSettings();
        if($embebed['enabled'])
        {
            $smarty['embebed'] = $embebed;
            $template = 'payment_embebed';
        } else {
            $template = 'payment_execution';//plantilla a utilizar
        }            

        return array($smarty,$template);
    }
    
    public function second_step_todopago($prefijo, $cart, $connector)
    {
        /** PASO 2: getAuthorizeAnswer
         * La respuesta contiene los siguientes campos: 
         * StatusCode (codigo correspondiente al resultado de la autorizacion), 
         * StatusMessage (mensaje explicativo)
         * AuthorizationKey
         * EncodingMethod
         * Payload: contiene los detalles del pago aceptado
         * Request: contiene los campos enviados
         * Del formulario viene
         * AnswerKey: necesario para el getAuthorizeAnswer
         */
        
        $answerKey = Tools::getValue('Answer');//answerKey
        $status =Tools::getValue('estado');
        $cartId =Tools::getValue('cart');
        
        $this->module->logInfo($cartId,'second step');
        if($this->_tranEstado($cartId) != 2)
        {
            throw new Exception("second_step ya realizado");
            $smarty['status'] = 0;//indica que hubo un error en este paso
        }
        
        $options = $this->_getRequestOptionsPasoDos($prefijo, $cartId, $answerKey);
        $this->module->logInfo($cart->id,'params GAA',$options);
        $respuesta = $connector->getAuthorizeAnswer($options);
        $this->module->logInfo($cartId,'response GAA',$respuesta);

        $now = new DateTime();
        $this->_tranUpdate($cartId, array("second_step" => $now->format('Y-m-d H:i:s'), "params_GAA" => json_encode($options), "response_GAA" => json_encode($respuesta), "answer_key" => $answerKey));
        
        if ($status == 0)//si se llego a este paso mediante URL_ERROR
        {
            $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], $respuesta['Payload']['Answer']);
            $respuesta = Transaccion::getOptions($cart->id);
            $this->_tranUpdate($cartId, array("first_step" => null, "second_step" => null));
            throw new Exception($respuesta['status']);
        }
        
        //en el caso de pagar con Rapipago o Pago Facil
        if( strlen($respuesta['Payload']['Answer']["BARCODE"]) > 0) //si existe un barcode
        {
            $datosBarcode= array(
                    'nroop' =>  $order_id,
                    'venc' => $respuesta['Payload']['Answer']["COUPONEXPDATE"],
                    'total' => $respuesta['Payload']['Request']['AMOUNT'],
                    'code' => $respuesta['Payload']['Answer']["BARCODE"],
                    'tipocode' => $respuesta['Payload']['Answer']["BARCODETYPE"],
                    'empresa' => $respuesta['Payload']['Answer']["PAYMENTMETHODNAME"]
            );
            $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], $respuesta['Payload']['Answer']);//guardo el StatusMessage y los detalles de la transaaccion
            Tools::redirect($this->context->link->getModuleLink(strtolower($this->module->name), 'barcode', $datosBarcode, true));//redrijo al controller de barcode
        }
                            
        if ($respuesta['StatusCode'] == $this->codigoAprobacion && $this->_isAmountIgual($cart, $respuesta['Payload']['Request']['AMOUNT']))//Si todo salio bien
        {
            $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], $respuesta['Payload']['Answer']);//guardo el StatusMessage y los detalles de la transaaccion
            $this->module->log('Redireccionando al controller de validacion');
            Tools::redirect($this->context->link->getModuleLink(strtolower($this->module->name), 'validation', array(), false));//redirijo al controller de validacion
        }
        else 
        {
            throw new Exception($respuesta['StatusMessage']);
        }        
    }
    
    private function _tranEstado($cartId)
    {
        $res = $this->db->executeS("SELECT * FROM "._DB_PREFIX_."todopago_transaccion WHERE id_orden=".$cartId);
        
        if(!$res) {
            return 0;
        } else {
            $res = $res[0];
            if($res['first_step'] == null) {
                return 1;
            } else if ($res['second_step'] == null) {
                return 2;
            } else {
                return 3;
            }
        }
    }
    
    private function _tranCrear($cartId)
    {
        $data = array("id_orden" => $cartId);
        $this->db->insert("todopago_transaccion", $data);
        $this->tranEstado = $this->_tranEstado($cartId);
    }

    private function _tranUpdate($cartId, $data)
    {
        $this->db->update("todopago_transaccion", $data, "id_orden = ".$cartId, 0, true);
        $this->tranEstado = $this->_tranEstado($cartId);
    }

    /**
     * Recupera los datos del proxy
     * @param string $prefijo Prefijo usado para los settings del proxy en la base de datos
     * @param boolean $modo Modo de ejecucion. True= produccion, False= test
     */
    private function _getProxySettings($prefijo, $modo)
    {
        $prefijo;
        $statusProxy = (boolean)Configuration::get($prefijo.'_STATUS');
        
        if ($statusProxy)
        {
            return array(
                    'host' => Configuration::get($prefijo.'_HOST'),
                    'port' => Configuration::get($prefijo.'_PORT'),
                    'user' => Configuration::get($prefijo.'_USER'),
                    'pass' => Configuration::get($prefijo.'_PASS')
            );
        }
    }
    
    private function _getServiceSettings($modo)
    {
        $prefijo = $this->module->getPrefijo('PREFIJO_CONFIG');
        return array(
            'proxy' => $this->_getProxySettings($this->module->getPrefijo('CONFIG_PROXY'), $modo),
            'certificado' => (string) Configuration::get($prefijo.'_certificado'),
            'timeout' => (string) Configuration::get($prefijo.'_timeout')
        );
    }
    
    private function _getEmbebedSettings()
    {
        $prefijo = $this->module->getPrefijo('CONFIG_EMBEBED');
        return array(
            'enabled' => (string) Configuration::get($prefijo.'_EMBEBED'),
            'backgroundColor' => (string) Configuration::get($prefijo.'_BACKGROUNDCOLOR'),
            'border' => (string) Configuration::get($prefijo.'_BORDER'),
            'buttonBackgroundColor' => (string) Configuration::get($prefijo.'_BUTTONBACKGROUNDCOLOR'),
            'buttonColor' => (string) Configuration::get($prefijo.'_BUTTONCOLOR'),
            'buttonBorder' => (string) Configuration::get($prefijo.'_BUTTONBORDER')
        );        
    }
    /**
     * Recupera los WSDL de Authorization, PaymentMethods y Operations guardados en la base de datos
     * @param String $prefijo indica el ambiente en uso
     * @return array resultado de decodear el los wsdls que están en formato json.
     */
    private function _getWSDLs($prefijo)
    {
        return json_decode(Configuration::get($prefijo.'_WSDL'), TRUE);
    }
    
    /**
     * Recupera el authorize.
     * @param String $prefijo indica el ambiente en uso
     * @return array resultado de decodear el authorization que está en formato json.
     */
    private function _getAuthorization()
    {
        $prefijo = $this->module->getPrefijo('PREFIJO_CONFIG');
        return json_decode(Configuration::get($prefijo.'_AUTHORIZATION'), TRUE);
    }
    
    /**
     * Recupera el endpoint
     * @param String $prefijo indica el ambiente en uso
     */
    private function _getEndpoint($prefijo)
    {
        return Configuration::get($prefijo.'_ENDPOINT');
    }
    
    public function getOptionsSARComercio($prefijo,$cartId)
    {
        $params = array (
                'comercio' => array(
                    'Security' => Configuration::get($prefijo.'_SECURITY'),
                    'EncodingMethod' => 'XML',
                    'Merchant' => Configuration::get($prefijo.'_ID_SITE'),
                    'URL_OK' => $this->context->link->getModuleLink(strtolower($this->module->name), 'payment', array('paso' => '2', 'estado' => '1', 'cart' => $cartId), true),
                    'URL_ERROR' => $this->context->link->getModuleLink(strtolower($this->module->name), 'payment', array('paso' => '2', 'estado' => '0', 'cart' => $cartId), true),
                )
        );
        
        return $params;        
    }
    
    public function getOptionsSAROperacion($prefijo, $cliente, $cart)
    {
        $params = array (
                'operacion' => array(
                    'MERCHANT' => Configuration::get($prefijo.'_ID_SITE'),
                    'OPERATIONID' => (string) $cart->id,
                    'CURRENCYCODE' => '032',
                    'AMOUNT' => $this->context->cart->getOrderTotal(true, Cart::BOTH)
                )
        );
        
        $params['operacion'] = array_merge_recursive($params['operacion'], $this->_getParamsCybersource($cliente, $cart));
        return $params;        
    }
    
    private function _getRequestOptionsPasoDos($prefijo, $cartId, $answerKey)
    {
        return array (
                'Merchant' => Configuration::get($prefijo.'_ID_SITE'),
                'MERCHANT' => Configuration::get($prefijo.'_ID_SITE'),
                'Security' => Configuration::get($prefijo.'_SECURITY'),
                'AnswerKey'=> $answerKey,
                'RequestKey' => Transaccion::getRespuesta($cartId)
        );
    }
    
    private function _guardarTransaccion($cart, $statusMessage, $respuesta)
    {        
        if (!Transaccion::existe($cart->id))
        {
            Transaccion::agregar(
                    $cart->id, 
                    array(
                        'customer' => $cart->id_customer,
                        'respuesta' => $respuesta,
                        'status' => $statusMessage,
                        'total' => $cart->getOrderTotal(true, Cart::BOTH)
                    )
            );
        }
        else
        {
            Transaccion::actualizar(
                    $cart->id,
                    array(
                        'customer' => $cart->id_customer,
                        'status' => $statusMessage,
                        'respuesta'=> $respuesta
                    )
            );
        }
    }
    
    /**
     * Devuelve los parametros necesarios para Cybersource
     * @param Customer $customer
     * @param Cart $cart
     * @return array con los parametros
     */
    private function _getParamsCybersource($customer, $cart)
    {
        $prefijo= $this->module->getPrefijo('PREFIJO_CONFIG');
        $segmento = $this->module->getSegmentoTienda();
        
        $general = $this->_getGeneralCybersourceParams($prefijo, $customer, $cart->id_address_delivery, $cart->getOrderTotal(true, Cart::BOTH));
        switch ($segmento)
        {
            case 'retail':
                return array_merge($general,$this->_getRetailCybersourceParams($prefijo, $customer, $cart));
            break;
            case 'services':
                return array_merge($general,$this->_getServicesCybersourceParams($prefijo, $customer, $cart));
            break;
            case 'digital goods':
                return array_merge($general,$this->_getDigitalGoodsCybersourceParams($prefijo, $customer, $cart));
            break;
            case 'ticketing':
                return array_merge($general,$this->_getTicketingCybersourceParams($prefijo, $customer, $cart));
            break;
            default:
                return $general;
            break;
        }
    }

    private function _getStateIso($id)
    {
        $state = new State($id);
        return $state->iso_code;
    }
    
    /**
     * Obtiene los datos a enviar por cada producto. De ser mas de uno, los valores deben estar separado con #
     * @param array $productos
     * @return array con los siguientes campos: CSITPRODUCTCODE, CSITPRODUCTDESCRIPTION, CSITPRODUCTNAME, 
     * CSITPRODUCTSKU, CSITTOTALAMOUNT, CSITQUANTITY, CSITUNITPRICE
     */
    private function _getProductsDetails($productos)
    {
        /**
         * Campos del array
         * CSITPRODUCTCODE: Código de producto. CONDICIONAL. Valores posibles(adult_content;coupon;default;electronic_good;electronic_software;gift_certificate;handling_only;service;shipping_and_handling;shipping_only;subscription)
         * CSITPRODUCTDESCRIPTION: Descripción del producto.
         * CSITPRODUCTNAME: Nombre del producto.
         * CSITPRODUCTSKU:  Código identificador del producto. CONDICIONAL.
         * CSITTOTALAMOUNT:  CSITTOTALAMOUNT=CSITUNITPRICE*CSITQUANTITY "999999[.CC]" Con decimales opcional usando el puntos como separador de decimales. No se permiten comas, ni como separador de miles ni como separador de decimales. CONDICIONAL.
         * CSITQUANTITY: Cantidad del producto. CONDICIONAL.
         * CSITUNITPRICE: Formato Idem CSITTOTALAMOUNT. CONDICIONAL.
         */

        $code =array();
        $description = array();
        $name = array();
        $sku = array();
        $total = array();
        $quantity = array();
        $unit = array();
        
        foreach ($productos as $item) {
            $configCybersource = new ProductoCybersource($item['id_product']);
            $code[]  = $configCybersource->codigo_producto;
            
            $desc = $item['description_short'];
            $desc = substr(Sdk::sanitizeValue($desc),0,50);
            $description[]   = $desc;
            
            $name[]  = substr($item['name'],0,250);
            $sku[]  = substr($item['reference'],0,250);
            $total[]  = number_format($item['total_wt'],2,".","");
            $quantity[]  = $item['cart_quantity'];
            $unit[]  = number_format($item['price_wt'],2,".","");
        }

        return array (
            'CSITPRODUCTCODE' => join("#", $code),
            'CSITPRODUCTDESCRIPTION' => join("#", $description),
            'CSITPRODUCTNAME' => join("#", $name),
            'CSITPRODUCTSKU' => join("#", $sku),
            'CSITTOTALAMOUNT' => join("#", $total),
            'CSITQUANTITY' => join("#", $quantity),
            'CSITUNITPRICE' => join("#", $unit),
        );
    }
    
    /**
     * Recupera las opciones de cybersource comunes a todos los casos
     * @param $customer Cliente
     * @param $total float
     */
    private function _getGeneralCybersourceParams($prefijo, $customer, $address, $total)
    {
        $address = new Address($address);
        $validOrders = Db::getInstance()->getValue('SELECT COUNT(`'.Order::$definition['primary'].'`) FROM '._DB_PREFIX_.Order::$definition['table'].' WHERE id_customer = '.$customer->id.' AND valid = 1');
        
        $country = new Country($address->id_country);
        $params = array(
                //Obligatorios
                'CSBTCITY' => substr($address->city,0,250), //Ciudad de facturacion
                'CSBTCOUNTRY' => $country->iso_code, //Pais de facturacion (codigo ISO)
                'CSBTCUSTOMERID' => $customer->id, //Identificador del usuario al que se le emite la factura
                'CSBTIPADDRESS' => Tools::getRemoteAddr(),//ver https://www.prestashop.com/forums/topic/154027-add-customer-ip-to-contact-email/
                'CSBTEMAIL' => $customer->email, //Mail del usuario al que se le emite la factura
                'CSBTFIRSTNAME' => $customer->firstname, //nombre
                'CSBTLASTNAME' => $customer->lastname, //apellido
                'CSBTPHONENUMBER' => $this->_phoneSanitize(empty($address->phone)?$address->phone_mobile:$address->phone), //telefono
                'CSBTPOSTALCODE' => $address->postcode, //codigo postal
                'CSBTSTATE' => $this->_getStateIso($address->id_state), //provincia
                'CSBTSTREET1' => $address->address1, //domicilio: calle y numero
                'CSPTCURRENCY' => 'ARS', //moneda
                'CSPTGRANDTOTALAMOUNT' => number_format($total,2,".",""), //total
                //Opcionales
                //'CSBTSTREET2' => $address->address2, //Complemento del domicilio. (piso, departamento).
                //'CSMDD6' => Configuration::get($prefijo.'_CANAL'), // Canal de venta (Valores posibles: Web, Mobile, Telefonica). Valor configurado en el backoffice
                'CSMDD7' => $this->_getDateTimeDiff($customer->date_add), //Fecha registro comprador(num Dias). Dias que pasaron desde la fecha de registro o la fecha en un formato especifico
                'CSMDD10' => $validOrders, //Histórica de compras del comprador (Num transacciones).
                'CSMDD11' => $this->_phoneSanitize(empty($address->phone_mobile)?$address->phone:$address->phone_mobile) //Customer Cell Phone.S
        );
                
        return array_merge_recursive($params, $this->_getCustomerDetails($customer));
    }
    
	private function _phoneSanitize($number){
		$number = str_replace(array(" ","(",")","-","+"),"",$number);
		
		if(substr($number,0,2)=="54") return $number;
		
		if(substr($number,0,2)=="15"){
			$number = substr($number,2,strlen($number));
		}
		if(strlen($number)==8) return "5411".$number;
		
		if(substr($number,0,1)=="0") return "54".substr($number,1,strlen($number));
		return "54".$number;
	}
    /**
     * Obtiene los parametros de Cybersource propios de las tiendas de tipo Retail
     * @param Customer $customer
     * @param Cart $cart
     * @return array
     */
    private function _getRetailCybersourceParams($prefijo, $customer, $cart)
    {
        $paramsGenerales = $this->_getGeneralCybersourceParams($prefijo, $customer, $cart->id_address_delivery, $cart->getOrderTotal(true, Cart::BOTH));
        $carrier = new Carrier($cart->id_carrier);
        
        $params = array(
                //Parametros obligatorios
                'CSSTCITY' => $paramsGenerales['CSBTCITY'], //Ciudad de envio de la orden.
                'CSSTCOUNTRY' => $paramsGenerales['CSBTCOUNTRY'], //País de envío de la orden.
                'CSSTEMAIL' => $paramsGenerales['CSBTEMAIL'], //Mail del destinatario.
                'CSSTFIRSTNAME' => $paramsGenerales['CSBTFIRSTNAME'], //Nombre del destinatario.
                'CSSTLASTNAME' => $paramsGenerales['CSBTLASTNAME'], //Apellido del destinatario.
                'CSSTPHONENUMBER' => $paramsGenerales['CSBTPHONENUMBER'], //Número de teléfono del destinatario.
                'CSSTPOSTALCODE' => $paramsGenerales['CSBTPOSTALCODE'], //Código postal del domicilio de envío.
                'CSSTSTATE' => $paramsGenerales['CSBTSTATE'], //Provincia de envío. Son de 1 caracter
                'CSSTSTREET1' => $paramsGenerales['CSBTSTREET1'], //Domicilio de envío.
                //Parametros opcionales
                //'CSSTSTREET2' => $paramsGenerales['CSBTSTREET2'], //Localidad de envío.
                'CSMDD12' => Configuration::get($prefijo.'_DEADLINE'),//Shipping DeadLine (Num Dias). Se configura en el backoffice
                'CSMDD13' => $carrier->name,//Método de Despacho.
                //'CSMDD14' => 'N',//Customer requires Tax Bill ? (Y/N).
                //'CSMDD15' => '',//Customer Loyality Number.
                'CSMDD16' => ''//Promotional / Coupon Code.
        );
        
        return array_merge_recursive($params, $this->_getProductsDetails($cart->getProducts()));
    }
        
    /**
     * Obtiene los parametros de Cybersource propios de las tiendas de tipo Ticketing
     * @param Customer $customer
     * @param Cart $cart
     * @return array
     */
    private function _getTicketingCybersourceParams($prefijo, $customer, $cart)
    {
        $dias = array();
        $envio =array();
        
        //si hay mas de una entrada se concatenan como en los detalles de producto
        foreach ($cart->getProducts() as $item)
        {
            $configCybersource = new ProductoCybersource($item['id_product']);
            $dias[] = $this->_getDateTimeDiff($configCybersource->fecha_evento);
            $envio[] = $configCybersource->tipo_envio;
        }
        
        return array_merge_recursive(
                array(
                        'CSMDD33' => join("#", $dias),
                        'CSMDD34' => join("#", $envio)
                ), 
                $this->_getProductsDetails($cart->getProducts())
        );
    }

    /**
     * Obtiene los parametros de Cybersource propios de las tiendas de tipo Services
     * @param Customer $customer
     * @param Cart $cart
     * @return array
     */
    private function _getServicesCybersourceParams($prefijo, $customer, $cart)
    {
        /**
         * CSMDD28: Tipo de Servicio. MANDATORIO. Valores posibles: Luz, Gas, Telefono, Agua, TV, Cable, Internet, Impuestos.
         * CSMDD29, CSMDD30, CSMDD31: Referencias de pago de los servicios. MANDATORIO.
         * Detalles de los servicios. Se llama a la funcion _getProductsDetails
         */
        $detallesServiciosProductos = $this->_getDetallesCybersourceProductos($cart->getProducts());
        
        //no debe haber más de tres servicios
        if (count($cart->getProducts()) > 3)
        {
            throw new Exception('No se pueden elegir mas de tres servicios');
        }
        
        //los servicios deben ser del mismo tipo
        if ($this->_isServiciosIgualTipo($detallesServiciosProductos))
        {
            throw new Exception('Los servicios deben ser del mismo tipo');
        }
        
        return array_merge_recursive(
                array('CSMDD28'=>$detallesServiciosProductos[0]['tipo_servicio'] ),
                $this->_getReferenciasPago($detallesProductos),
                $this->_getProductsDetails($cart->getProducts())
        );
    }
    
    /**
     * Obtiene los parametros de Cybersource propios de las tiendas de tipo DigitalGoods
     * @param Customer $customer
     * @param Cart $cart
     * @return array
     */
    private function _getDigitalGoodsCybersourceParams($prefijo, $customer, $cart)
    {
        $params = array(
                'CSMDD31'=>'', //Tipo de delivery. MANDATORIO. Valores posibles: WEB Session, Email, SmartPhone
        );
        
        return array_merge_recursive($params, $this->_getProductsDetails($cart->getProducts()));
    }
    
    private function _getDateTimeDiff($fecha)
    {
        return date_diff(new DateTime($fecha), new DateTime())->format('%a');
    }
    
    private function _isAmountIgual($cart, $amount)
    {
        if ($cart->getOrderTotal(true, Cart::BOTH) == $amount)
            return true;
        else
            return false;
    }
    
    private function _getCustomerDetails($customer)
    {
        /*
         * CSMDD8:  Es un usuario invitado? (Y/N) En caso de ser Y, el campo CSMDD9 no deberá enviarse.
         * CSMDD9: Customer password Hash: criptograma asociado al password del comprador final.
        */
        
        if ($customer->isGuest())
        {
            return array(
                    'CSMDD8' => 'S',
            );
        }
        else
        {
            return array(
                    'CSMDD8' => 'N',
                    'CSMDD9' => $customer->passwd
            );
        }
    }
    
    private function _getDetallesCybersourceProductos($productos)
    {
        $detalles = array();
        
        foreach ($productos as $item)
        {
            $detalles[] = ProductoCybersource::getRegistroAsArray($item['id_product']);
        }
        
        return $detalles;
    }
    
    /**
     * Verifica que todos los servicios sean del mismo tipo
     * @param array $detallesProductos
     * @return boolean
     */
    private function _isServiciosIgualTipo($detallesProductos)
    {
        $cantidad = count($detallesProductos);
        
        for ($i=0; $i < $cantidad; $i++)
        {
            //si el tipo de servicio del producto actual es distinto al del producto anterior, y no estamos en el comienzo del array
            if ( ($i > 0) && ($detallesProductos[$i]['tipo_servicio'] != $detallesProductos[$i-1]['tipo_servicio']))
            {
                return false;
            }
        }
        
        return true;
    }
    
    private function _getReferenciasPago($detallesProductos)
    {
        $referencias = array(
                'CSMDD29'=>'', //Referencia de pago del servicio 1. MANDATORIO.
                'CSMDD30'=>'', //Referencia de pago del servicio 2. MANDATORIO.
                'CSMDD31'=>'', //Referencia de pago del servicio 3. MANDATORIO.
        );
        
        for ($i=0; $i < count($detallesProductos); $i++)
        {
            $referencias['CSMDD'.(29+$i)] = $detallesProductos[$i]['referencia_pago'];
        }
    }
}

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
use TPProductoControlFraude as ProductoControlFraude;

require_once (dirname(__FILE__) . '../../../lib/TodoPago/lib/Sdk.php');
require_once (dirname(__FILE__) . '../../../classes/Transaccion.php');
require_once (dirname(__FILE__) . '../../../classes/Productos.php');
require_once (dirname(__FILE__) . '../../../lib/ControlFraude/ControlFraudeFactory.php');

class TodoPagoPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;
    private $codigoAprobacion = -1; //valor del campo SatusCode que indica que la transaccion fue aprobada (en este caso -1).
    private $first_step = false;
	
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
        
        $options = $this->getOptionsSARComercio($prefijo, $cart->id);
        $options = array_merge($options, $this->getOptionsSAROperacion($prefijo, $cliente, $cart));
        
        $this->module->logInfo($cart->id,'params SAR',$options);
        
        $respuesta = $connector->sendAuthorizeRequest($options['comercio'], $options['operacion']);//me comunico con el webservice
        $this->module->logInfo($cart->id,'response SAR',$respuesta);
        if ($respuesta['StatusCode']  != $this->codigoAprobacion)//Si la transacción salió mal
        {
			if(($respuesta['StatusCode']  == 702)&&(!$this->first_step)) {
				$http_header = $this->_getAuthorization();
				$merchant = Configuration::get($prefijo.'_ID_SITE');
				$security = Configuration::get($prefijo.'_SECURITY');
				if((isset($http_header["Authorization"]))&&(!empty($merchant))&&(!empty($security))){
					$this->first_step = true;
					$this->first_step_todopago($cart, $prefijo, $cliente, $connector);
				}
			}
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
			if(isset($respuesta['Payload']['Answer'])) {
				$this->_tranUpdate($cartId, array("first_step" => null, "second_step" => null));				
				throw new Exception($respuesta['status']);
			}
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
     * Recupera el authorize.
     * @param String $prefijo indica el ambiente en uso
     * @return array resultado de decodear el authorization que está en formato json.
     */
    private function _getAuthorization()
    {
        $prefijo = $this->module->getPrefijo('PREFIJO_CONFIG');
        return json_decode(Configuration::get($prefijo.'_AUTHORIZATION'), TRUE);
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
        
        $params['operacion'] = array_merge_recursive($params['operacion'], $this->_getParamsControlFraude($cliente, $cart));
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
    
    private function _guardarTransaccion($cart, $statusMessage, $respuesta) {        
        if (!Transaccion::existe($cart->id)){
            Transaccion::agregar(
                    $cart->id, 
                    array(
                        'customer' => $cart->id_customer,
                        'respuesta' => $respuesta,
                        'status' => $statusMessage,
                        'total' => $cart->getOrderTotal(true, Cart::BOTH)
                    )
            );
        }else{
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
     * Devuelve los parametros necesarios para ControlFraude
     * @param Customer $customer
     * @param Cart $cart
     * @return array con los parametros
     */
    private function _getParamsControlFraude($customer, $cart) {
        $prefijo = $this->module->getPrefijo('PREFIJO_CONFIG');
        $segmento = $this->module->getSegmentoTienda(true);
        $config = array("deadline" => Configuration::get($prefijo.'_DEADLINE'));
		
		$dataCS = ControlFraudeFactory::get_controlfraude_extractor($segmento, $customer, $cart, $config)->getDataCS();
		return $dataCS;
    }
    
    private function _isAmountIgual($cart, $amount)
    {
        if ($cart->getOrderTotal(true, Cart::BOTH) == $amount)
            return true;
        else
            return false;
    }
}

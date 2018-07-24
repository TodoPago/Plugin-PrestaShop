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

require_once (dirname(__FILE__) . '../../../classes/Transaccion.php');
require_once (dirname(__FILE__) . '../../../classes/Productos.php');
require_once (dirname(__FILE__) . '../../../lib/ControlFraude/ControlFraudeFactory.php');
require_once dirname(__FILE__) . '/../../../../config/config.inc.php';
require_once (dirname(__FILE__) . '/../../vendor/autoload.php');

class TodoPagoPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;
    private $codigoAprobacion = -1; //valor del campo SatusCode que indica que la transaccion fue aprobada (en este caso -1).
    private $first_step = false;
    
    public function __construct(){
        parent::__construct();
    }
    
    public function initContent()
    {
        $this->display_column_left = false;//para que no se muestre la columna de la izquierda
        $this->db = Db::getInstance();
        parent::initContent();//llama al init() de FrontController, que es la clase padre
        
        $payment_option=Tools::getValue('payment_option');
        
        $this->module->log->info("InitContent de Payment");
        
        $this->module->log->info("Valor de payment_option = {$payment_option}");
        
        if($this->compare_presta() >= 0){
            if($payment_option == "billetera"){
                //Actualizo el valor de la base para que sea billetera
                $this->module->log->info("Actualizo el valor de la tabla a: {$payment_option}");
                $this->update_todopago_banner_table("billetera");
            }
        }
        
        //variables a usar
        $cart = $this->context->cart;

        if($cart->id == null && Tools::getValue('order') != null) {
            $order = new Order((int)Tools::getValue('order'));
            $cart = new Cart((int)$order->id_cart);
        }

        if($cart->id == null && Tools::getValue('cart') != null) {
            $cart = new Cart((int)Tools::getValue('cart'));
        }

        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $cliente = new Customer($cart->id_customer);//recupera al objeto cliente
        $paso = (int) Tools::getValue('paso');
        
        $this->module->log->info("La variable paso vale: ".$paso);
        
        try
        {
            if (!$this->module->checkCurrency($cart))
                Tools::redirect('index.php?controller=order');

            //si el carrito esta vacio
            if ($cart == NULL ||  $cart->getProducts() == NULL || $cart->getOrderTotal(true, Cart::BOTH) == 0)
                throw new Exception('Carrito vacio');

            //si ya existe una orden para este carrito
            if ($cart->OrderExists() == true && $paso != 3)
                throw new Exception('Ya existe una orden para el carro id '.$cart->id);

			//Prefijo que se usa para la peticion al webservice, dependiendo del modo en el que este seteado el modulo
			$prefijo = $this->module->getPrefijoModo();
			$connector = $this->prepare_connector($prefijo);
			$this->tranEstado = $this->_tranEstado($cart->id);  
            
            switch ($paso)
            {
                case 1:
                    $this->module->log->info('Estás en el paso 1');
                    list($smarty, $template) = $this->first_step_todopago($cart, $prefijo, $cliente, $connector);
                break;
                case 2:
                    $this->module->log->info('Estás en el paso 2');
                    $this->second_step_todopago($prefijo, $cart, $connector);
                break;
                case 3:
                    $this->module->log->info('Estás en el paso 3');
                    $order=Tools::getValue('order');
                    $orderIdTPOperation = Tools::getValue('orderOperation');
                    $amount = Tools::getValue('amount');
                    $this->doRefund($order, $orderIdTPOperation, $amount);
                    die;
                default:
                    $this->module->log->info('Redireccionando al paso 1');
                    $route_redirect=$this->context->link->getModuleLink('todopago', 'payment', array ('paso' => '1','payment_option'=>$payment_option), true);
                    $this->module->log->info('Redireccionando al paso 1 , ruta que le paso: '.$route_redirect);
                    Tools::redirect($route_redirect);
                break;
            }
        }
        catch (Exception $e)
        {
            $this->module->log->error('EXCEPCION',$e);

            if(!Configuration::get('TODOPAGO_CARRITO_COMPRAS')){
                $this->context->cart->delete();
                Db::getInstance()->delete(_DB_PREFIX_.'todopago_transaccion','id_orden = '.$cart->id);
            }

            if ($this->compare_presta() >= 0){
                Tools::redirect($this->context->link->getModuleLink('todopago', 'pagemessagereturn', array('step' => 'first')));
            }else{
                $template='payment_error';
            }

        }

        //asigno las variables que se van a a ver en la template de payment (payment.tpl)
        $this->context->smarty->assign(array(
            'nombre' => "Todo Pago",//nombre con el que aparece este modulo de pago en el frontend
            'cart_id' => $cart->id,
            'nbProducts' => $cart->nbProducts(),//productos
            'save_cart' => ((Configuration::get('TODOPAGO_CARRITO_COMPRAS'))? "1": "0"),
            'cust_currency' => $cart->id_currency,//moneda en la que paga el cliente
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),//moneda
            'total' => $total,//total de la orden
            'cliente' =>$cliente->email,
            'this_path' => $this->module->getPathUri(),
            'this_path_modulo' => strtolower('modules/'.$this->module->name.'/'),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
            'payment_option'=>Tools::getValue('payment_option')
        ));
        
        if (isset($smarty))//hay casos en los que esta variable no esta seteada
        {
            $this->context->smarty->assign(array(
                    'payment' => $smarty
            ));
        }

        $this->module->log->info('Version de Presta mayor a 1.7.0: '.$this->compare_presta());
        
        $this->module->log->info('Antes de llamar al formulario hibrido o externo.Valor de payment_option= '.$payment_option);
        
        if ($this->compare_presta() >= 0){
            $embebed = $this->_getEmbebedSettings();

            if($embebed['enabled'] == 1){
                //pruebo redirect a form hibrido
                
                $module_name="todopago";
                
                $controller_name="tppaymentform";
                
                $fc="module";
                               
                $payment_option=$this->db->executeS("SELECT payment_option FROM " . _DB_PREFIX_ . "todopago_banner_billetera");
                
                $this->update_todopago_banner_table("form");
       
                //$route=$this->context->link->getModuleLink('todopago', 'tppaymentform', array('order' => $cart->id,'payment_option'=>$payment_option[0]["payment_option"]),true);
                
                //$alt_route=_PS_BASE_URL_.__PS_BASE_URI__."index.php?order={$cart->id}&payment_option={$payment_option[0]["payment_option"]}&fc={$fc}&module={$module_name}&controller={$controller_name}";
                
                $valor_billetera=$payment_option[0]["payment_option"];
                
                $valores_concatenados="Valores concatenados ->Nombre del modulo: {$module_name} , nombre del controlador: {$controller_name} , fc: {$fc} , valor de payment option: {$valor_billetera}";
                
                $this->module->log->info($valores_concatenados);
                
                $alt_route="index.php?order={$cart->id}&payment_option={$valor_billetera}&fc={$fc}&module={$module_name}&controller={$controller_name}";
                
                $this->module->log->info("Ruta para abrir el form y procesar el pago: {$alt_route}");
                
                Tools::redirect($alt_route);
            }
            else{
                //form externo
                $this->module->log->info("Redir a formulario ext");
                Tools::redirect($smarty['redir']);
            }
            
        }else{
            $this->module->log->info('Justo antes de setear el template: '.$template);
            $this->setTemplate($template.'.tpl');
        }
    }

    public function update_todopago_banner_table($value){
        $this->db->executeS("UPDATE " . _DB_PREFIX_ . "todopago_banner_billetera set payment_option='{$value}'");
        
        //$this->db->update(_DB_PREFIX_.'todopago_banner_billetera',array('id'=>1,'payment_option'=>$value));
    }
    
    protected function prepare_connector($prefijo)
    {
            //Traigo los settings del servicio (proxy, ubicacion del certificado y timeout
            $servicioConfig = $this->_getServiceSettings($prefijo);

            $mode = ($this->module->getModo())?"prod":"test";
            //creo el conector con el valor de Authorization
            $connector = new Sdk($this->_getAuthorization(), $mode);

            if (isset($servicioConfig['proxy'])) // si hay un proxy
                    $connector->setProxyParameters($proxy['host'], $proxy['port'], $proxy['user'], $proxy['pass']);

            if ($servicioConfig['certificado'] != '')//si hay una ubicación de certificado
                    $connector->setLocalCert($servicioConfig['certificado']);

            if ($servicioConfig['timeout'] != '')//si hay un timeout
                    $connector->setConnectionTimeout($servicioConfig['timeout']);

            return $connector;
    }

    protected function prepare_order($cart)
    {
            if($this->tranEstado == 0)
                    $this->_tranCrear($cart->id, array());
    }

    protected function get_paydata($prefijo, $cart, $cliente)
    {
    $options = $this->getOptionsSARComercio($prefijo, $cart->id);
    $options = array_merge($options, $this->getOptionsSAROperacion($prefijo, $cliente, $cart));

    $this->module->log->info('params SAR - '.json_encode($options));
            return $options;
    }

    protected function call_SAR($options, $cart, $prefijo, $cliente, $connector)
    {
        $user_location = str_replace(' ', '', $options['operacion']['CSBTSTREET1']) . $options['operacion']['CSBTPOSTALCODE'];
        $base_location = $this->get_base_gmaps($user_location);

        if (Configuration::get($this->module->getPrefijo('PREFIJO_CONFIG') . '_GMAPS') == 1 && $base_location == null) {
            $g = new \TodoPago\Client\Google();
            $connector->setGoogleClient($g);
            $respuesta            = $connector->sendAuthorizeRequest($options['comercio'], $options['operacion']); //me comunico con el webservice
            $responseGoogleStatus = $connector->getGoogleClient()->getGoogleResponse()['billing']['status'];
            $responseGoogle       = $connector->getGoogleClient()->getFinalAddress();

            if (!$responseGoogle['billing']['CSBTPOSTALCODE']) {
                $responseGoogle['billing']['CSBTPOSTALCODE'] = $options['operacion']['CSBTPOSTALCODE'];
            }
            if (!$responseGoogle['shipping']['CSSTPOSTALCODE']) {
                $responseGoogle['shipping']['CSSTPOSTALCODE'] = $options['operacion']['CSSTPOSTALCODE'];
            }
            if ($responseGoogleStatus == 'OK') {
                $this->set_base_gmaps($user_location, $responseGoogle);
            } else {
                $respuesta = $connector->sendAuthorizeRequest($options['comercio'], $options['operacion']);
            }
        } else if (Configuration::get($this->module->getPrefijo('PREFIJO_CONFIG') . '_GMAPS') == 1 && $base_location != null) {
            $options   = $this->merge_sar_with_base($options, $base_location);
            $respuesta = $connector->sendAuthorizeRequest($options['comercio'], $options['operacion']); //me comunico con el webservice

        } else {

            $respuesta = $connector->sendAuthorizeRequest($options['comercio'], $options['operacion']); //me comunico con el webservice

        }

        $this->module->log->info('response SAR - ' . json_encode($respuesta));

        //validate states set
        if (Configuration::get($prefijo . '_APROBADA') == " " || Configuration::get($prefijo . '_DENEGADA') == " " || Configuration::get($prefijo . '_PROCESO') == " " || Configuration::get($prefijo . '_PENDIENTE') == " ") {

            $this->module->log->info('Los estados del proceso de pago de Todopago no estan definidos');
            throw new Exception("Los estados de la compra de Todopago no estan definidos");
        }

        if ($respuesta['StatusCode'] != $this->codigoAprobacion) //Si la transacción salió mal
            {
            if (($respuesta['StatusCode'] == 702) && (!$this->first_step)) {
                $http_header = $this->_getAuthorization();
                $merchant    = Configuration::get($prefijo . '_ID_SITE');
                $security    = Configuration::get($prefijo . '_SECURITY');
                if ((isset($http_header["Authorization"])) && (!empty($merchant)) && (!empty($security))) {
                    $this->first_step = true;
                    $this->module->log->info('Reintento');
                    $this->first_step_todopago($cart, $prefijo, $cliente, $connector);
                }
            }
            $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], "");
            $this->_tranUpdate($cart->id, array(
                "first_step" => null
            ));
            $smarty['status'] = 0; //indica que hubo un error en este paso
            throw new Exception($respuesta['StatusMessage']);
        }
        $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], $respuesta['RequestKey']); //guardo la request key y otros datos importantes

        $now = new DateTime();
        $this->_tranUpdate($cart->id, array(
            "first_step" => $now->format('Y-m-d H:i:s'),
            "params_SAR" => pSql(json_encode($options)),
            "response_SAR" => json_encode($respuesta),
            "request_key" => $respuesta['RequestKey'],
            "public_request_key" => $respuesta['PublicRequestKey']
        ));

        return $respuesta;
  }

  protected function get_base_gmaps($user_location)
  {
      $base_location = $this->db->executeS("SELECT * FROM " . _DB_PREFIX_ . "todopago_gmaps WHERE identify_key ='" . $user_location . "'");
      return $base_location;
  }

  protected function set_base_gmaps($user_location, $responseGoogle)
  {
      $res = $this->db->executeS("INSERT INTO " . _DB_PREFIX_ . "todopago_gmaps (identify_key, billing_street, billing_state, billing_city, billing_country, billing_postalcode, shipping_street, shipping_state,shipping_city, shipping_country, shipping_postalcode) values ('" . $user_location . "','" . $responseGoogle['billing']['CSBTSTREET1'] . "','" . $responseGoogle['billing']['CSBTSTATE'] . "','" . $responseGoogle['billing']['CSBTCITY'] . "', '" . $responseGoogle['billing']['CSBTCOUNTRY'] . "','" . $responseGoogle['billing']['CSBTPOSTALCODE'] . "', '" . $responseGoogle['shipping']['CSSTSTREET1'] . "','" . $responseGoogle['shipping']['CSSTSTATE'] . "','" . $responseGoogle['shipping']['CSSTCITY'] . "','" . $responseGoogle['shipping']['CSSTCOUNTRY'] . "','" . $responseGoogle['shipping']['CSSTPOSTALCODE'] . "')");
  }

  protected function merge_sar_with_base($options, $base_location)
  {
      $options[1]['CSBTSTREET1']    = $base_location[0]['billing_street'];
      $options[1]['CSBTSTATE']      = $base_location[0]['billing_state'];
      $options[1]['CSBTCITY']       = $base_location[0]['billing_city'];
      $options[1]['CSBTCOUNTRY']    = $base_location[0]['billing_country'];
      $options[1]['CSBTPOSTALCODE'] = $base_location[0]['billing_postalcode'];
      $options[1]['CSSTSTREET1']    = $base_location[0]['shipping_street'];
      $options[1]['CSSTSTATE']      = $base_location[0]['shipping_state'];
      $options[1]['CSSTCITY']       = $base_location[0]['shipping_city'];
      $options[1]['CSSTCOUNTRY']    = $base_location[0]['shipping_country'];
      $options[1]['CSSTPOSTALCODE'] = $base_location[0]['shipping_postalcode'];
      return $options;
  }

    protected function custom_commerce($respuesta)
    {
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
        $template = 'payment_execution';
    }
    return array($smarty,$template);
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
        $this->module->log->info('first step');

		$this->prepare_order($cart);

		$options = $this->get_paydata($prefijo, $cart, $cliente);

        $respuesta = $this->call_SAR($options, $cart, $prefijo, $cliente, $connector);

		return $this->custom_commerce($respuesta);
    }

	protected function call_GAA($prefijo, $connector)
	{
        $answerKey = Tools::getValue('Answer');
        $cartId =Tools::getValue('cart');

        if($answerKey == "error") {
            $options = $this->_getRequestOptionsPasoDos($prefijo, $cartId, $answerKey);
            $this->module->log->info('params GAA - '.json_encode($options));
            $this->module->log->info("GAA - NO SE HACE PORQUE NO HAY ANSWERKEY - FORMULARIO NO PAGADO O ERROR");
            $respuesta = array(
                "StatusCode" => Tools::getValue("Code"),
                "StatusMessage" => Tools::getValue("Message")
            );
        } else {
            $options = $this->_getRequestOptionsPasoDos($prefijo, $cartId, $answerKey);
            $this->module->log->info('params GAA - '.json_encode($options));
            $respuesta = $connector->getAuthorizeAnswer($options);
            $this->module->log->info('response GAA - '.json_encode($respuesta));
        }

            $now = new DateTime();
            $this->_tranUpdate($cartId, array("second_step" => $now->format('Y-m-d H:i:s'), "params_GAA" => pSql(json_encode($options)), "response_GAA" => json_encode($respuesta), "answer_key" => $answerKey));

		return $respuesta;
	}

	protected function take_action($respuesta)
	{
		$cartId =Tools::getValue('cart');
		$status =Tools::getValue('estado');
		$cart = new Cart($cartId);

        if ($status == "0")//si se llego a este paso mediante URL_ERROR
        {
            $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], "");
            $this->module->log->info('Redireccionando al controller de validacion');
            Tools::redirect($this->context->link->getModuleLink(strtolower($this->module->name), 'validation', array("error" =>"true", "message"=>$respuesta['StatusMessage']), false));//redirijo al controller de validacion
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
            $this->module->log->info('Redireccionando al controller de validacion ok');

            Tools::redirect($this->context->link->getModuleLink(strtolower($this->module->name), 'validation', array(), false));//redirijo al controller de validacion
        } else {
            $this->_guardarTransaccion($cart, $respuesta['StatusMessage'], "");
            $this->module->log->info('Redireccionando al controller de validacion error');
            Tools::redirect($this->context->link->getModuleLink(strtolower($this->module->name), 'validation', array("error" =>"true","message"=>$respuesta['StatusMessage']), false));//redirijo al controller de validacion
        }

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

        $this->module->log->info('second step');

		$respuesta = $this->call_GAA($prefijo, $connector);

		$this->take_action($respuesta);
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
            'enabled' => (Configuration::get($prefijo.'_EMBEBED') == 1 ? 1: 0),
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
        return $this->module->getAuthorization();
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
                    'AMOUNT' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
                    'ECOMMERCENAME' => 'PRESTASHOP',
                    'ECOMMERCEVERSION' => _PS_VERSION_,
                    'PLUGINVERSION' => $this->module->version,
                )
        );
	$embebed = $this->_getEmbebedSettings();
        if($embebed['enabled']) {
		$params["operacion"]["PLUGINVERSION"] =  $params["operacion"]["PLUGINVERSION"] . "-H";
	} else {
		$params["operacion"]["PLUGINVERSION"] =  $params["operacion"]["PLUGINVERSION"] . "-E";
	}


        if(Configuration::get($this->module->getPrefijo('PREFIJO_CONFIG').'_CUOTASENABLE') == 1) {
            $params['operacion']['MAXINSTALLMENTS'] = Configuration::get($this->module->getPrefijo('PREFIJO_CONFIG').'_CUOTASCANT');
        }

        $timeout=Configuration::get($this->module->getPrefijo('PREFIJO_CONFIG').'_TIMEOUT');
        if(!empty($timeout)){
            $params['operacion']['TIMEOUT']=Configuration::get($this->module->getPrefijo('PREFIJO_CONFIG').'_TIMEOUT_MS');
        }

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

        if ($cart->getOrderTotal(true, Cart::BOTH) == $amount){

            return true;

        }elseif($cart->getOrderTotal(true, Cart::BOTH) < $amount){

            $realAmount = $amount - ($amount - $cart->getOrderTotal(true, Cart::BOTH));

            if($cart->getOrderTotal(true, Cart::BOTH) == $realAmount){
                return true;
            }

            return false;
        }else{
	    return false;
	}
    }

    //obtengo RequestKey de la orden
    private function getRequestKeyTransaccion($IdOrder){

        $sql = 'SELECT request_key FROM '._DB_PREFIX_.'todopago_transaccion WHERE id_orden = '.$IdOrder;
        $dataTransacciontions = Db::getInstance()->ExecuteS($sql);

        if (!$dataTransacciontions){
            return null;
        }else{
            foreach($dataTransacciontions as $dataRequest){
                return $dataRequest['request_key'];
            }
        }
    }

    public function voidPaymentTP($orderIdTransaccion){

        if(Configuration::get('TODOPAGO_MODO') == ""){
            $merchant = Configuration::get('TODOPAGO_TEST_ID_SITE');
            $security = Configuration::get('TODOPAGO_TEST_SECURITY');
        }else{
            $merchant = Configuration::get('TODOPAGO_PRODUCCION_ID_SITE');
            $security = Configuration::get('TODOPAGO_PRODUCCION_SECURITY');
        }

        $requestKey = $this->getRequestKeyTransaccion($orderIdTransaccion);

        $options = array(
            "Security" => $security,
            "Merchant" => $merchant,
            "RequestKey" => $requestKey
        );

        $prefijo = $this->module->getPrefijoModo();
        $connector = $this->prepare_connector($prefijo);

        //log devolucion request
        $this->module->log->info('Devolución total datos:' . json_encode($options));

        $refResponse = $connector->voidRequest($options);

        $this->module->log->info('Respuesta Devolución total:' . json_encode($refResponse));

        return $refResponse;
    }

    public function partialRefundTP($orderIdTransaccion, $amount){

        if(Configuration::get('TODOPAGO_MODO') == ""){
            $merchant = Configuration::get('TODOPAGO_TEST_ID_SITE');
            $security = Configuration::get('TODOPAGO_TEST_SECURITY');
        }else{
            $merchant = Configuration::get('TODOPAGO_PRODUCCION_ID_SITE');
            $security = Configuration::get('TODOPAGO_PRODUCCION_SECURITY');
        }

        //getRequestKey
        $requestKey = $this->getRequestKeyTransaccion($orderIdTransaccion);

        $options = array(
            "Security" => $security,
            "Merchant" => $merchant,
            "RequestKey" => $requestKey,
            "AMOUNT" => $amount
        );

        $prefijo = $this->module->getPrefijoModo();
        $connector = $this->prepare_connector($prefijo);

        $this->module->log->info('Devolución parcial datos:' . json_encode($options));

        $refResponse = $connector->returnRequest($options);

        $this->module->log->info('Respuesta Devolución parcial:' . json_encode($refResponse));

        return $refResponse;
    }

    public function doRefund($orderId, $orderIdTPOperation, $amount){
        if(isset($orderId) && is_numeric($amount) && $amount != 0){
            //valida formato moneda
            if(preg_match("/^-?[0-9]+(?:\.[0-9]{1,2})?$/", $amount)){
                //verifica si es toal o parcial
                $res = Db::getInstance()->executeS("SELECT total_paid FROM "._DB_PREFIX_."orders WHERE id_order=".$orderId);
                //get credenciales

                if(number_format($res[0]['total_paid'], 2) == $amount){
                    //es devolucion total
                    $response = $this->voidPaymentTP($orderIdTPOperation);
                }elseif($amount < number_format($res[0]['total_paid'], 2)){
                    //devolucion parcial
                    $response = $this->partialRefundTP($orderIdTPOperation, $amount);
                }else{
                   $response = array(
                        "StatusCode" => '',
                        "StatusMessage" => "Debe Ingresar un monto menor o igual al total de la compra sin interes"
                    );
                }

            }else{
                $response = array(
                    "StatusCode" => '',
                    "StatusMessage" => "Formato de moneda invalido"
                );
            }

            echo json_encode($response);
        }else{
            $response = array(
                "StatusCode" => '',
                "StatusMessage" => "Ingrese el monto a devolver"
            );

            echo json_encode($response);
        }

    }
    
     private function compare_presta(){
        return version_compare(_PS_VERSION_, '1.7.0.0');
    }
   
}

<?php
/**
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
*/


if (!defined('_PS_VERSION_'))
	exit;

require_once (dirname(__FILE__) . '/vendor/autoload.php');
require_once (dirname(__FILE__) . '/classes/Transaccion.php');
require_once (dirname(__FILE__) . '/classes/Productos.php');
require_once (dirname(__FILE__) . '/classes/Formulario.php');
require_once (dirname(__FILE__) . '/lib/ControlFraude/ControlFraudeFactory.php');
require_once (dirname(__FILE__) . '/lib/Logger/logger.php');

class TodoPago extends PaymentModule
{
	protected $config_form = false;

	/** segmento de la tienda */
	private $segmento = array('Retail', 'Services', 'Digital Goods', 'Ticketing');

	/** canal de ingreso del pedido */
	private $canal = array ('Web', 'Mobile', 'Telefono');

	/** tipo de envio. Usado en el sistema de prevencion del fraude (ticketing) */
	protected $envio = array ('Pickup', 'Email', 'Smartphone', 'Other');

	/** tipo de servicio. Usado en el sistema de prevencion del fraude (servicios) */
	protected  $servicio = array( 'Luz', 'Gas', 'Telefono', 'Agua', 'TV', 'Cable', 'Internet', 'Impuestos');

	/** tipo de delivery. Usado en el sistema de prevencion del fraude (digital goods) */
	protected  $delivery = array('WEB Session', 'Email', 'SmartPhone');

	protected $product_code = array('default', 'adult_content', 'coupon', 'electronic_good', 'electronic_software', 'gift_certificate', 'handling_only', 'service', 'shipping_and_handling', 'shipping_only', 'subscription');

	public $log;

	public function __construct()//constructor
	{
		//acerca del modulo en si
		$this->name = 'todopago';
		$this->tab = 'payments_gateways';
		$this->version = '1.15.0';
		$this->author = 'Todo Pago';
		$this->bootstrap = true;
		$this->is_eu_compatible = 0;
		$this->need_instance = 1;
		$this->controllers = array('payment', 'validation');
		$this->currencies = true;
        	$this->currencies_mode = 'checkbox';

		parent::__construct();

		//lo que se muestra en el listado de modulos en el backoffice
		$this->displayName = $this->l('Todo Pago');//nombre
		$this->description = $this->l('Pagos con tarjeta');//descripcion
		$this->confirmUninstall = $this->l('Realmente quiere desinstalar este modulo?');//mensaje que aparece al momento de desinstalar el modulo

		$this->log = $this->configureLog();
	}

	/**
	 * Don't forget to create update methods if needed:
	 * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
	 */
	public function install()
	{//instalacion del modulo
            if (Module::isInstalled('botondepago'))
            {
              Module::disableByName($this->name);   //note during testing if this is not done, your module will show as installed in modules
              die(Tools::displayError('Primero debe desinstalar la version anterior del modulo.'));
            }

            $this->createConfigVariables();

            include(dirname(__FILE__).'/sql/install.php');//script sql con la creacion de la tabla transaction

            //Sí es presta 1.7 > /////////////////////////////////////////////////////
            //Crear tabla todopago_banner_billetera

            if ($this->compare_presta() >= 0) {
                
                $this->createTableTodoPagoBilletera();
                $this->insertTableTodoPagoBilletera();
            }
            //////////////////////////////////////////////////////////////////////////

            return parent::install() &&
                                    $this->registerHook('displayBackOfficeHeader') && //para insertar /js/back.js
                                    //$this->registerHook('displayHeader') && //para insertar /js/front.js
                                    $this->registerHook('displayAdminProductsExtra') && //para crear la tab y mostrar contenido en ella
                    $this->registerHook('actionProductUpdate') && //se llama cuando se actualiza un producto, asi podemos recuperar lo que se ingreso en la tab
                    $this->registerHook('displayAdminOrderContentOrder') && //muestra contenido en el detalle de la orden
                                    $this->registerHook('displayAdminOrderTabOrder') &&
                                    $this->unregisterHook('displayAdminProductsExtra') && //muestra una tab en el detalle de la orden
                                    $this->registerHook('displayPayment') &&
                                    $this->registerHook('displayPaymentReturn') &&
                                    $this->registerHook('paymentOptions');
                                    $this->registerHook('displayPDFInvoice') && //en la factura muestra costo financiero
                                    $this->registerHook('displayAdminOrder');  //en el detalle del pedido muestra costo financiero

            return true;
	}

	public function uninstall()
	{//desinstalacion
		$this->deleteConfigVariables();
		//include(dirname(__FILE__).'/sql/uninstall.php');//no se borran para no perder los datos
		
                //Si es presta 1.7 >= /////////////////////////////////////////////
                if ($this->compare_presta() >= 0) {
                    $tabla_billetera='DROP TABLE IF EXISTS `'._DB_PREFIX_.'todopago_banner_billetera'.'`';
                    Db::getInstance()->executeS($tabla_billetera);
                }
                ///////////////////////////////////////////////////////////////////
                
                return parent::uninstall();
	}

	public function configureLog() {
		require_once (dirname(__FILE__) . '/lib/Logger/logger.php');

		$cart = $this->context->cart;
		$endpoint = ($this->getModo())?"TODOPAGO_ENDPOINT_PROD":"TODOPAGO_ENDPOINT_TEST";
		$logger = new \TodoPago\Logger\TodoPagoLogger();
		$logger->setPhpVersion(phpversion());
		$logger->setCommerceVersion(_PS_VERSION_);
		$logger->setPluginVersion($this->version);
		$payment = false;
		if($cart != null)
			if($cart->id != null)
				$payment = true;
		if($payment) {
			$logger->setEndPoint($endpoint);
			$logger->setCustomer($cart->id_customer);
			$logger->setOrder($cart->id);
		}
		$logger->setLevels("debug","fatal");
		$logger->setFile(dirname(__FILE__)."/todopago.log");
		return $logger->getLogger($payment);
	}

	public function getPrefijo($nombre)
	{
		$prefijo = 'TODOPAGO';
		$variables = parse_ini_file('config.ini');

		if ( strcasecmp($nombre, 'PREFIJO_CONFIG') == 0)
			return $prefijo;

		foreach($variables as $key => $value){
			if ( strcasecmp($key, $nombre) == 0 )
				return $prefijo.'_'.$value;
		}
		return '';
	}

	/**
	 * Crea las variables de configuracion, asi se encuentran todas juntas en la base de datos
	 */
	public function createConfigVariables()
	{
		$prefijo = 'TODOPAGO';
		$variables = parse_ini_file('config.ini');

		foreach ( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getConfigFormInputs(null, null) ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.strtoupper( $nombre ),'');
		}
		foreach ( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getAmbienteFormInputs('test') ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.$variables['CONFIG_TEST'].'_'.strtoupper( $nombre ),'');
		}
		foreach ( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getAmbienteFormInputs('produccion') ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.$variables['CONFIG_PRODUCCION'].'_'.strtoupper( $nombre ),'');
		}
		foreach ( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getEstadosFormInputs(NULL) ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.$variables['CONFIG_ESTADOS'].'_'.strtoupper( $nombre ),'');
		}
		foreach( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getServicioConfFormInputs()) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.strtoupper( $nombre ),'');
		}
		foreach( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getEmbebedFormInputs()) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.strtoupper( $nombre ),'');
		}
	}

	/**
	 * Borra las variables de configuracion de la base de datos
	 */
	public function deleteConfigVariables()
	{
		Db::getInstance()->delete(Configuration::$definition['table'],'name LIKE \'%'.$this->getPrefijo('PREFIJO_CONFIG').'%\'');
	}

	/**
	 * Carga el formulario de configuration del modulo.
	 */
	public function getContent()
	{
		$this->_postProcess();

		$this->context->smarty->assign(array(
			'module_dir' 	 	  => $this->_path,
			'version'    	 	  => $this->version,
			'url_base'			  => "//".Tools::getHttpHost(false).__PS_BASE_URI__,
			'config_general' 	  => $this->renderConfigForms(),
			//'config_mediosdepago' => $this->renderMediosdePagoForm(),
		));
		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');//recupero el template de configuracion

		return $output;
	}

	private function _getBancosFromService($data)
	{
		$bank_collection = $data['BanksCollection']['Bank'];

		$bancos = array();
		foreach($bank_collection as $bank)
		{
			$bancos[$bank['Id']] = $bank;
		}

		return $bancos;
	}

	private function _getMediosFromService($data)
	{
		$method_collection = $data['PaymentMethodsCollection']['PaymentMethod'];

		$medios = array();
		foreach($method_collection as $medio)
		{
			$medios[$medio['Id']] = $medio;
		}

		return $medios;
	}

	private function _getRelacionesFromService($data)
	{
		$relations_collection = $data['PaymentMethodBanksCollection']['PaymentMethodBank'];

		$relaciones = array();
		foreach($relations_collection as $rel)
		{
			$relaciones[$rel['PaymentMethodId']][] = $rel['BankId'];
		}
		return $relaciones;
	}

	public function renderMediosdePagoForm()
	{
		$medios_pago = $this->getMediosdePago();
		$bancos = $this->_getBancosFromService($medios_pago);
		$medios = $this->_getMediosFromService($medios_pago);
		$relaciones = $this->_getRelacionesFromService($medios_pago);

		$this->context->smarty->assign(array(
			'medios' => $this->_renderMedios($medios),
			'bancos' => $this->_renderBancos($bancos),
			'relaciones' => $this->_renderRelaciones($relaciones, $medios, $bancos),
		));

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure_medios.tpl');
		return $output;
	}

	private function _renderRelaciones($relaciones, $medios, $bancos)
	{
		$this->context->smarty->assign(array(
			'medios' => $medios,
			'bancos' => $bancos,
			'relaciones' => $relaciones,
		));

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/relaciones.tpl');
		return $output;
	}

	private function _renderBancos($bancos)
	{
		$this->context->smarty->assign(array(
			'bancos' => $bancos,
		));

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/bancos.tpl');
		return $output;
	}

	private function _renderMedios($medios)
	{
		$this->context->smarty->assign(array(
			'medios' => $medios,
		));

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/mediosdepago.tpl');
		return $output;
	}

	/**
	 * @return el html de todos los formularios
	 */
	public function renderConfigForms()
	{
		return $this->renderForm('config')
					.$this->renderForm('login')
					.$this->renderForm('test')
					.$this->renderForm('produccion')
					.$this->renderForm('estado')
					.$this->renderForm('proxy')
					.$this->renderForm('servicio')
					.$this->renderForm('embebed');
	}

	/**
	 * Crea las opciones para un select
	 * @param array $opciones
	 */
	public function getOptions($opciones)
	{
		$rta = array();

		foreach ($opciones as $item)
		{
				$rta[] = array(
					'id_option' => strtolower($item),
					'name' => $item
				);
		}

		return $rta;
	}

	/**
	 * 	Genera el  formulario que corresponda segun la tabla ingresada
	 * @param string $tabla nombre de la tabla
	 * @param array $fields_value
	 */
	public function renderForm($tabla)
	{
		$form_fields;

		switch ($tabla)
		{
			case 'config':
				$prefijo = $this->getPrefijo('PREFIJO_CONFIG');
				$form_fields = TodoPago\Formulario::getFormFields('general ', TodoPago\Formulario::getConfigFormInputs($this->getOptions($this->segmento), $this->getOptions($this->canal), Configuration::get($prefijo.'_TIMEOUT_MS')));
				break;

			case 'login':
				$form_fields = TodoPago\Formulario::getFormFields('Obtener credenciales', TodoPago\Formulario::getLoginCredenciales($tabla));
				$prefijo = $this->getPrefijo('CONFIG_LOGIN_CREDENCIAL');
				break;

			case 'test':
				$form_fields = TodoPago\Formulario::getFormFields('ambiente developers', TodoPago\Formulario::getAmbienteFormInputs($tabla));
				$prefijo = $this->getPrefijo('CONFIG_TEST');
				break;

			case 'produccion':
				$form_fields = TodoPago\Formulario::getFormFields('ambiente '.$tabla, TodoPago\Formulario::getAmbienteFormInputs($tabla));
				$prefijo = $this->getPrefijo('CONFIG_PRODUCCION');
				break;

			case 'proxy':
				$form_fields = TodoPago\Formulario::getFormFields('configuracion - proxy', TodoPago\Formulario::getProxyFormInputs());
				$prefijo = $this->getPrefijo('CONFIG_PROXY');
				break;

			case 'estado':
				$form_fields = TodoPago\Formulario::getFormFields('estados del pedido', TodoPago\Formulario::getEstadosFormInputs($this->getOrderStateOptions()));
				$prefijo = $this->getPrefijo('CONFIG_ESTADOS');
				break;

			case 'servicio':
				$form_fields = TodoPago\Formulario::getFormFields('configuracion - servicio', TodoPago\Formulario::getServicioConfFormInputs());
				$prefijo = $this->getPrefijo('PREFIJO_CONFIG');
				break;

			case 'embebed':
				$form_fields = TodoPago\Formulario::getFormFields('configuracion - formulario hibrido', TodoPago\Formulario::getEmbebedFormInputs());
				$prefijo = $this->getPrefijo('CONFIG_EMBEBED');
				break;
		}

		if (isset($prefijo))
			$fields_value= TodoPago\Formulario::getConfigs($prefijo, TodoPago\Formulario::getFormInputsNames($form_fields['form']['input']));

		//obtiene el authorization code desde el json guardado
		$fields_value=$this->getAuthorizationKeyFromJSON($fields_value, $tabla);

		return $this->getHelperForm($tabla,$fields_value)->generateForm(array($form_fields));
	}

	/**
	 * Genera un formulario
	 * @param String $tabla nombre de la tabla que se usa para generar el formulario
	 */
	public function getHelperForm($tabla, $fields_value=NULL)
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;//no mostrar el toolbar
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;//el idioma por defecto es el que esta configurado en prestashop
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit'.ucfirst($tabla);//nombre del boton de submit. Util al momento de procesar el formulario

		//mejorar este codigo, solo para el form de login de credenciales remueve la url y token de action
		if($tabla != "login"){
			$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
			.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
			$helper->token = Tools::getAdminTokenLite('AdminModules');
		}else{
			$helper->currentIndex = "#";
			$helper->token = "";
		}

		if($tabla == "login")
			$fields_value['id_user'] = " ";


		$helper->tpl_vars = array(
				'fields_value' => $fields_value,
				'languages' => $this->context->controller->getLanguages(),
				'id_language' => $this->context->language->id
		);

		return $helper;
	}

	/**
	 * recupero y guardo los valores ingresados en el formulario
	 */
	protected function _postProcess()
	{

		if (Tools::isSubmit('btnSubmitConfig'))
		{
			TodoPago\Formulario::postProcessFormularioConfigs($this->getPrefijo('PREFIJO_CONFIG'), TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getConfigFormInputs(null, null) ) );
		}
		elseif (Tools::isSubmit('btnSubmitTest'))
		{
			TodoPago\Formulario::postProcessFormularioConfigs($this->getPrefijo('CONFIG_TEST'), TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getAmbienteFormInputs('test') ) );
		}
		elseif (Tools::isSubmit('btnSubmitProduccion'))
		{
			TodoPago\Formulario::postProcessFormularioConfigs($this->getPrefijo('CONFIG_PRODUCCION'), TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getAmbienteFormInputs('produccion') ) );
		}
		elseif (Tools::isSubmit('btnSubmitProxy'))
		{
			TodoPago\Formulario::postProcessFormularioConfigs($this->getPrefijo('CONFIG_PROXY'), TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getProxyFormInputs() ) );
		}
		elseif (Tools::isSubmit('btnSubmitEstado'))
		{
			TodoPago\Formulario::postProcessFormularioConfigs($this->getPrefijo('CONFIG_ESTADOS'), TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getEstadosFormInputs(NULL) ) );
		}
		elseif (Tools::isSubmit('btnSubmitServicio'))
		{
			TodoPago\Formulario::postProcessFormularioConfigs($this->getPrefijo('PREFIJO_CONFIG'), TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getServicioConfFormInputs() ) );
		}
		elseif (Tools::isSubmit('btnSubmitEmbebed'))
		{
			TodoPago\Formulario::postProcessFormularioConfigs($this->getPrefijo('CONFIG_EMBEBED'), TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getEmbebedFormInputs() ) );
		}
		elseif (Tools::isSubmit('btnSubmitControlfraude'))
		{

			$registro = array();
			//recupero los nombres de los campos
			$campos = TodoPago\Formulario::getFormInputsNames(TodoPago\Formulario::getProductoFormInputs($this->getSegmentoTienda(), NULL, NULL, NULL));

			//recupero lo ingresado en el formulario
			if (isset ($campos)  && count($campos) > 0)
			{
				foreach($campos as $item)
				{
					$registro[$item] = Tools::getValue($item);
				}
			}

			Hook::exec('actionProductUpdate', array('id_product' => Tools::getValue('id_product'), 'form' => $registro));//llamo al hook desde aca porque no funciona de otra forma
		}
	}

	/**
	 * Usada en payment.php
	 */
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

	/**
	 * Verifica si el modulo esta activo para el usuario final
	 */
	public function isActivo()
	{
		return (boolean)Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_STATUS');
	}

	/**
	 * Verifica si el modulo esta en produccion o en test
	 */
	public function getModo()
	{
		return  (bool) Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_MODO');
	}

	/**
	 * Verifica si ControlFraude está hablitado
	 */
	public function isControlFraudeActivo()
	{
		return (boolean)Configuration::get($this->getPrefijo('CONFIG_CONTROLFRAUDE').'_STATUS');
	}

	/**
	 * Devuelve el prefijo correspondiente al modo en el que se ejecuta el modulo
	 */
	public function getPrefijoModo()
	{
		if ($this->getModo())//true = produccion
		{
			return $this->getPrefijo('CONFIG_PRODUCCION');
		}
		else //false = test
		{
			return $this->getPrefijo('CONFIG_TEST');
		}
	}

	/**
	 * Obtiene el segmento de la tienda
	 */
	public function getSegmentoTienda($cs = false)
	{
		$prefijo= $this->getPrefijo('PREFIJO_CONFIG');
		$segmento = Configuration::get($prefijo.'_SEGMENTO');
		if($cs) {
			switch ($segmento)
			{
				case 'retail':
					return ControlFraudeFactory::RETAIL;
				break;
				case 'services':
					return ControlFraudeFactory::SERVICE;
				break;
				case 'digital goods':
					return ControlFraudeFactory::DIGITAL_GOODS;
				break;
				case 'ticketing':
					return ControlFraudeFactory::TICKETING;
				break;
				default:
					return ControlFraudeFactory::RETAIL;
				break;
			}
		}
		return 	$segmento;
	}

	public function getOrderStatesModulo($nombre=NULL)
	{
		$prefijo = $this->getPrefijo('CONFIG_ESTADOS');
		$sql = 'SELECT name, value FROM '._DB_PREFIX_.Configuration::$definition['table'];

		if ($nombre!=NULL && isset($nombre))//si se busca un valor especifico
		{
			//$resultOrderState = Db::getInstance()->getValue($sql.' WHERE name="'.$prefijo.'_'.$nombre.'"');
			$resultOrderState = Db::getInstance()->executeS($sql.' WHERE name="'.$prefijo.'_'.$nombre.'"');
		}

		return $resultOrderState;
	}

	public function getOrderStateOptions()
	{
		$list =  Db::getInstance()->executeS('SELECT os.id_order_state as id, name, logable as valid_order
			FROM `'._DB_PREFIX_.OrderState::$definition['table'].'` os
			LEFT JOIN `'._DB_PREFIX_.OrderState::$definition['table'].'_lang` osl
				ON (os.'.OrderState::$definition['primary'].' = osl.'.OrderState::$definition['primary'].' AND osl.`id_lang` = '.(int)$this->context->language->id.')
			WHERE deleted = 0');
		$options = array();

		//ingreso la opcion por defecto
		$options[] = array(
					'id_option' => NULL,
					'name' => 'Ninguno',
					'valid_order' => '1'
			);

		//si la query devuelve un resultado
		if (count($list) !=0)
		{
			foreach ($list as $item)
			{
					$options[] = array(
							'id_option' => $item['id'],
							'name' => $item['name'],
						    'valid_order' => $item['valid_order']
					);
			}
		}

		return $options;
	}

	public function getAuthorizationKeyFromJSON($fields_val, $tabla)
	{
		if($tabla == 'test' || $tabla == 'produccion')
		{
			foreach($fields_val as $index=>$value)
			{
				if($index == "authorization" && $value != null)
				{
					$authKey = json_decode($value);
					$fields_val[$index] = $authKey->Authorization;
				}
			}
		}

		return $fields_val;
	}

	/**
	 * Recupera el authorize.
	 * @param String $prefijo indica el ambiente en uso
	 * @return array resultado de decodear el authorization que está en formato json.
	 */
	public function getAuthorization()
	{
		$prefijo = $this->getPrefijoModo();
		$auth = json_decode(Configuration::get($prefijo.'_AUTHORIZATION'), TRUE);
		if(!empty($auth)) return $auth;

		$prefijo = $this->getPrefijo('PREFIJO_CONFIG');
		return json_decode(Configuration::get($prefijo.'_AUTHORIZATION'), TRUE);
	}

	public function getMediosdePago()
	{
		$prefijo = $this->getPrefijoModo();
		$mode = ($this->getModo())?"prod":"test";
		$connector = new TodoPago\Sdk($this->getAuthorization(), $mode);

		$opciones = array('MERCHANT'=>Configuration::get($prefijo.'_ID_SITE'));
		return $connector->getAllPaymentMethods($opciones);
	}

	public function hookdisplayAdminOrder($params)
	{

		$order=new Order($params['id_order']); //Busca orden

		//Busca costo financiero
		$dbquery = new DbQuery();
		$dbquery->select('response_GAA')
		->from('todopago_transaccion')
		->where('id_orden='.(int)$order->id_cart);
		$transaccion = Db::getInstance()->getValue($dbquery);
		$gaaResponse=json_decode($transaccion, true);

		if($gaaResponse['Payload']['Request']['AMOUNTBUYER']>$gaaResponse['Payload']['Request']['AMOUNT']){ //Si la transacción tiene costo financiero
			$cf=$gaaResponse['Payload']['Request']['AMOUNTBUYER']-$gaaResponse['Payload']['Request']['AMOUNT'];
		}else{
			$cf=0;
		}
		$this->smarty->assign(array(
			'total_paid_tax_incl' => $order->total_paid_tax_incl,
			'id_currency' => $order->id_currency,
			'cf' => $cf,

		));
		return $this->display(__FILE__, 'views/templates/admin/otros-cargos.tpl');

	}

	public function hookdisplayPDFInvoice($params)
	{
		$order=new Order($params['object']->id_order); //Busca orden

		//Busca costo financiero
		$dbquery = new DbQuery();
		$dbquery->select('response_GAA')
		->from('todopago_transaccion')
		->where('id_orden='.(int)$order->id_cart);
		$transaccion = Db::getInstance()->getValue($dbquery);
		$gaaResponse=json_decode($transaccion, true);

		if($gaaResponse['Payload']['Request']['AMOUNTBUYER']>$gaaResponse['Payload']['Request']['AMOUNT']){ //Si la transacción tiene costo financiero
			$cf=$gaaResponse['Payload']['Request']['AMOUNTBUYER'] - $gaaResponse['Payload']['Request']['AMOUNT'];
		}else{
			$cf=0;
		}

		$this->smarty->assign(array(
			'costo_financiero' => $cf
			)
		);
		return $this->display(__FILE__, 'views/templates/admin/pdf_invoice_otros_cargos.tpl');

	}

	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->local_path.'css/back.css', 'all');
		$this->context->controller->addJS($this->local_path.'js/back.js', 'all');
	}

	public function hookPaymentOptions($params)
	{
            if (!$this->active || !$this->isActivo()) {
                return;
            }

            if (!$this->checkCurrency($params['cart'])) {
                return;
            }

            $cart = $this->context->cart;
                   
            $payment_options = [
                    $this->todopago_payment_option(),
                    $this->todopago_billetera_payment_option()
            ];

            return $payment_options;
	}

	public function hookDisplayPayment()
	{
		//si el modulo no esta activo
		if (!$this->active ||  !$this->isActivo())
			return;

		$this->smarty->assign(array(
			'nombre' => "Todo Pago",//nombre que se muestra al momento de elegir los metodos de pago 
			'activo' => $this->isActivo(),
			'this_path' => $this->_path,
			'this_path_ejemplo' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
                        'banner_billetera'=>Configuration::get('TODOPAGO_BANNER_BILLETERA')
		));
		return $this->display(__FILE__, 'payment.tpl');//asigno el template que quiero usar
	}

	public function hookDisplayPaymentReturn($params)
	{
		if (!$this->active) {
            		return;
        	}

		//si el modulo no esta activo
		if (!$this->active || !$this->isActivo())
			return;

		if ($this->compare_presta() < 0) {
			$order = $params['objOrder'];
		}else{
			$order= $params['order'];	
		}	

		$detallesOrden = TPTransaccion::getOptions($order->id_cart);
		$state = $order->getCurrentState();//recupero el estado de la orden
		$estadoDenegada = $this->getOrderStatesModulo('DENEGADA');
		$customer = new Customer($order->id_customer);//recupera al objeto cliente


		if ($state != $estadoDenegada[0]['value'])//si el estado de la orden no es denegada
		{	
			if ($this->compare_presta() >= 0) {
				$total = "ARS ".number_format((float)$params['order']->total_paid, 2, '.', '');
			}else{
				$total = Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false);
			}
			
			$this->smarty->assign(array(//muestro: total a pagar, status y numero de referencia de orden
				'total_to_pay' => $total,
				'status' => 'ok',
				'reference' => $order->reference,
				'mensaje' => $detallesOrden['status'],
				'customer' => $customer->email
			));

			$status = "ok";

		}else{
			$this->smarty->assign(array(
				'status' => 'failed',
				'status_desc' => $state
				)
			);

			$status = "failed";
		}

		if ($this->compare_presta() < 0) {

			return $this->display(__FILE__, 'payment_return.tpl');//asigno el template que quiero usar
		}else{
			
			if ($this->compare_presta() >= 0) {
				$total_to_pay = "ARS ".number_format((float)$params['order']->total_paid, 2, '.', '');
			}else{
				$total_to_pay = Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false);
			}
			
			$reference = $order->reference;
			$customer_mail = $customer->email;
			
			return Tools::redirect($this->context->link->getModuleLink('todopago', 'pagemessagereturn', array('step' => 'second', 'status' => $status, 'total_to_pay' => $total_to_pay, 'reference' => $reference, 'customer' => $customer_mail)));
		}	
	}

	/**
	 * Para crear una tab en la vista de cada producto  y mostrar contenido en ella
	 * @param array $params al parecer es null
	 */
	public function hookDisplayAdminProductsExtra($params) {
		$idProducto = Tools::getValue('id_product');//recupero el id del producto desde el backoffice

		$this->displayName = $this->l('Prevencion del Fraude');//cambio el nombre que aparece en la tab

		//obtengo los campos de los select del formulario
		$servicioOption = $this->getOptions($this->servicio);
		$deliveryOption = $this->getOptions($this->delivery);
		$envioOption = $this->getOptions($this->envio);
		$productOption = $this->getOptions($this->product_code);

		//recupero los campos del formulario
		$form_fields = TodoPago\Formulario::getFormFields('Prevencion del fraude', TodoPago\Formulario::getProductoFormInputs($this->getSegmentoTienda(),$servicioOption, $deliveryOption, $envioOption, $productOption));

		//si no hay ningun input porque no hay datos para agregar para el segmento de la tienda
		if (count($form_fields['form']['input']) == 0)
		{
			$form_fields['form']['input'] = array(
				array(
						'label' => 'No se necesita agregar informaciÃ³n para este segmento.'
				)
			);
		}
		//recupero el contenido del formulario, si existiera
		elseif (TPProductoControlFraude::existeRegistro($idProducto))
		{
			$campos = TodoPago\Formulario::getFormInputsNames($form_fields['form']['input']);

			$fields_value = array();

			foreach ($campos as $nombre)
			{
				$fields_value[$nombre] = TPProductoControlFraude::getValorRegistro($idProducto, $nombre);
			}
		}

		//creo el helperForm y seteo el controlador, id de de producto y token necesarios para que el form apunte donde corresponde
		$helperForm = $this->getHelperForm('Controlfraude',$fields_value);
		$helperForm->currentIndex .= '&id_product='.$idProducto;

		//obtengo el html del formulario y lo agrego al smarty
		$this->smarty->assign(array(
				'segmento' => $this->getSegmentoTienda(),//para filtrar campos del formulario segun el segmento
				'tab' => $this->displayName,
				'nombreDiv' => strtolower($this->name).'-controlfraude',
				'form' => $helperForm->generateForm(array($form_fields)),
				'campos' => $campos
			)
		);

		return ;
	}

	/**
	 * Para recuperar lo que se ingreso en la tab
	 * @param array $params contiene el id del producto actualizado
	 */
	public function hookActionProductUpdate($params)
	{
		/**
		 * Params:
		 * id_product: id del producto. Viene tanto desde AdminProducts como desde el postProcess del modulo
		 * form: contiene lo escrito en los campos del formulario. No existe si el hook no se ejecuta desde el postProcess del modulo
		 */
		try
		{
			if (isset($params['form']) && count($params['form'])>0) //si el hook se ejecuto desde el _postProcess
			{
				$idProducto = $params['id_product'];//recupero el id del producto desde el backoffice
				$segmento = $this->getSegmentoTienda();//recupero el segmento de la tienda
				$this->displayName = $this->l('Prevencion del fraude');//nombre que se muestra en la tab

				$this->log->info('ActionProductUpdate - Segmento '.$segmento.' - params: '.json_encode($params));

				$registro = $params['form'];//recupero desde los params

				if (isset($registro) && count($registro)>0)
				{
					//creo un nuevo registro o actualizo el existente
					if (!TPProductoControlFraude::existeRegistro($idProducto))
					{
						$registro['id_product'] = $idProducto;
						Db::getInstance()->insert(TPProductoControlFraude::$definition['table'],  $registro);
						$this->log->info('ActionProductUpdate - Segmento '.$segmento.' - insertado registro para producto id='.$idProducto.' : '.json_encode($registro));
					}
					else
					{
						Db::getInstance()->update(TPProductoControlFraude::$definition['table'],  $registro, TPProductoControlFraude::$definition['primary'].'='.$idProducto);
						$this->log->info('ActionProductUpdate - Segmento '.$segmento.' - actualizado registro para producto id='.$idProducto.' : '.json_encode($registro));
					}
				}
				Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts').'&id_product='.$idProducto.'&updateproduct&token='.Tools::getAdminTokenLite('AdminProducts'));
			}
		}
		catch (Exception $e)
		{
			$this->log->error('EXCEPCION',$e);
		}
	}

	/**
	 * Se ejecuta cuando se quiere acceder a la orden desde el backoffice
	 * @param $params un array con los siguientes objetos: order, products y customer
	 */
	public function hookDisplayAdminOrderContentOrder($params)
	{
		$order_id = $params['order']->id_cart;
		if(!TPTransaccion::existe($order_id)) {
			return ;
		}
		$prefijo = $this->getPrefijoModo();
		$mode = ($this->getModo())?"prod":"test";
		$connector = new TodoPago\Sdk($this->getAuthorization(), $mode);

		$opciones = array('MERCHANT'=>Configuration::get($prefijo.'_ID_SITE'), 'OPERATIONID'=>$order_id);

		$this->log->info('GetStatus - Params:'.json_encode($opciones));

		$status = $connector->getStatus($opciones);

		$this->log->info('GetStatus - Response:'.json_encode($status));

		$rta = '';

		//si hay un status para esta orden
		if(isset($status['Operations']) && is_array($status['Operations'])){
			/*foreach($status['Operations'] as $key => $value){
				if($key == "REFUNDS"){
					$rta .=$key.": <br>";
					if(is_array($value)){
						$rta .="&ensp;&ensp;Order Id   -   Amount   -  Date.<br>";
						foreach ($value as $key => $refundsList){
							if(isset($refundsList["ID"])) {
								$rta .="&ensp;&ensp;".$refundsList['ID']." - ".$refundsList['AMOUNT']." - ".$refundsList['DATETIME']."<br>";
							} else {
								foreach($refundsList as $key => $value){
									$rta .="&ensp;&ensp;".$value['ID']." - ".$value['AMOUNT']." - ".$value['DATETIME']."<br>";
								}
							}
					    }
					}else{
						$rta .="No tiene devoluciones<br>";
					}

				}else{
					if(is_array($value)) $value = implode("-",$value);
					$rta .= $key .": ". $value."<br>";
				}
			}*/
                        $rta = $this->printGetStatus($status['Operations'], 0);
		}else{
			$rta = "No hay datos para esta orden";
		}

		//aca hago el codigo de la devolucion
		$id_order_cart = Tools::getValue('id_order');
		$res = Db::getInstance()->executeS("SELECT total_products_wt, total_shipping, total_paid, id_cart FROM "._DB_PREFIX_."orders WHERE id_order=".$id_order_cart);

		//Busca costo financiero
		$dbquery = new DbQuery();
		$dbquery->select('response_GAA')
		->from('todopago_transaccion')
		->where('id_orden='.$res[0]['id_cart']);
		$transaccion = Db::getInstance()->getValue($dbquery);

		$gaaResponse=json_decode($transaccion, true);

		$cf=$gaaResponse['Payload']['Request']['AMOUNTBUYER'] - $gaaResponse['Payload']['Request']['AMOUNT'];


		$this->smarty->assign(array(
				'status' => $rta,
				'precio' => number_format($res[0]['total_products_wt'],2),
				'envio' => number_format($res[0]['total_shipping'],2),
				'total' => number_format($res[0]['total_paid'],2),
				'other' => $cf,
				'url_base_ajax' => "//".Tools::getHttpHost(false).__PS_BASE_URI__,
				'url_refund' => $this->context->link->getModuleLink('todopago', 'payment', array ('paso' => '3'), true),
				'order_id' => $id_order_cart,
				'orderIdTPOperation' => $order_id
			)
		);

		return $this->display(__FILE__, 'views/templates/admin/order-content.tpl');//indico la template a utilizar
	}
        
        private function printGetStatus($array, $indent) {
            $rta = '';
            foreach ($array as $key => $value) {

                if ($key !== 'nil' && $key !== "@attributes") {
                    if (is_array($value) ){
                        $rta .= str_repeat("-", $indent) . "$key: <br/>";
                        $rta .= $this->printGetStatus($value, $indent + 2);
                    } else {
                        $rta .= str_repeat("-", $indent) . "$key: $value <br/>";
                    }
                }
            }
            return $rta;
        }

	/**
	 * Se ejecuta cuando se quiere acceder a la orden desde el backoffice
	 * @param $params un array con los siguientes objetos: order, products y customer
	 */
	public function hookDisplayAdminOrderTabOrder($params)
	{
		$order_id = $params['order']->id_cart;
		if(TPTransaccion::existe($order_id)) {
			return $this->display(__FILE__, 'views/templates/admin/order-tab.tpl');//indico la template a utilizar
		}
		return ;
	}
        
        //******** PRIVATE METHODS ********//
        private function createTableTodoPagoBilletera(){
            $tabla_billetera='CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'todopago_banner_billetera'.'` (
                            `id` INT(1) NOT NULL,
                            `payment_option` VARCHAR(50) NOT NULL,
                            PRIMARY KEY (`id`)
            )';

            Db::getInstance()->executeS($tabla_billetera);
        }
        
        private function insertTableTodoPagoBilletera(){            
            $insert="insert into "._DB_PREFIX_."todopago_banner_billetera values(1,'form')";
            
            Db::getInstance()->executeS($insert);
        }
        
        private function todopago_payment_option(){
            $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

            $newOption->setCallToActionText('Todo Pago')
                ->setAction($this->context->link->getModuleLink($this->name, 'payment', array("payment_option"=>"form")))
                ->setInputs([])
                ->setAdditionalInformation($this->context->smarty->fetch('module:todopago/views/templates/hook/todopago_payment_intro.tpl'));
            
            return $newOption;
        }
        
        private function todopago_billetera_payment_option(){
            $newOption2 = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

            $newOption2->setCallToActionText('Billetera Virtual Todo Pago')
                ->setAction($this->context->link->getModuleLink($this->name, 'payment', array("payment_option"=>"billetera")))
                ->setInputs([])
                ->setAdditionalInformation($this->context->smarty->fetch('module:todopago/views/templates/hook/billetera_todopago_payment_intro.tpl'));
            
            return $newOption2;
        }
        
        private function compare_presta(){
            return version_compare(_PS_VERSION_, '1.7.0.0');
        }
}

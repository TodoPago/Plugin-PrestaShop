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

require_once (dirname(__FILE__) . '/classes/Transaccion.php');
require_once (dirname(__FILE__) . '/classes/Productos.php');
require_once (dirname(__FILE__) . '/classes/Formulario.php');
require_once (dirname(__FILE__) . '/lib/TodoPago/lib/Sdk.php');

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

	public function __construct()//constructor
	{
		//acerca del modulo en si
		$this->name = 'todopago';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'Todo Pago';
		$this->need_instance = 0;

		$this->bootstrap = true;//para que use bootstrap
		
		parent::__construct();
		
		//lo que se muestra en el listado de modulos en el backoffice
		$this->displayName = $this->l('Todo Pago');//nombre
		$this->description = $this->l('Pagos con tarjeta');//descripcion

		$this->confirmUninstall = $this->l('Realmente quiere desinstalar este modulo?');//mensaje que aparece al momento de desinstalar el modulo
		
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
		
		return parent::install() &&
		//registro los hooks que voy a utilizar
					$this->registerHook('displayPayment') && //muestra el payment en la lista de pagos disponibles
					$this->registerHook('displayPaymentReturn') && //muestra un mensaje una vez que termina todo el proceso de pago y verificacion
					$this->registerHook('displayBackOfficeHeader') && //para insertar /js/back.js
					$this->registerHook('displayHeader') && //para insertar /js/front.js
					$this->registerHook('displayAdminProductsExtra') && //para crear la tab y mostrar contenido en ella
            		$this->registerHook('actionProductUpdate') && //se llama cuando se actualiza un producto, asi podemos recuperar lo que se ingreso en la tab
            		$this->registerHook('displayAdminOrderContentOrder') && //muestra contenido en el detalle de la orden
					$this->registerHook('displayAdminOrderTabOrder'); //muestra una tab en el detalle de la orden
	}

	public function uninstall()
	{//desinstalacion
		$this->deleteConfigVariables();
		//include(dirname(__FILE__).'/sql/uninstall.php');//no se borran para no perder los datos
		return parent::uninstall();
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
		
		foreach ( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getConfigFormInputs() ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.strtoupper( $nombre ));
		}
		foreach ( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getAmbienteFormInputs('test') ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.$variables['CONFIG_TEST'].'_'.strtoupper( $nombre ));
		}
		foreach ( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getAmbienteFormInputs('produccion') ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.$variables['CONFIG_PRODUCCION'].'_'.strtoupper( $nombre ));
		}
		foreach ( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getEstadosFormInputs(NULL) ) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.$variables['CONFIG_ESTADOS'].'_'.strtoupper( $nombre ));
		}		
		foreach( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getServicioConfFormInputs()) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.strtoupper( $nombre ));
		}
		foreach( TodoPago\Formulario::getFormInputsNames( TodoPago\Formulario::getEmbebedFormInputs()) as $nombre)
		{
			Configuration::updateValue($prefijo.'_'.strtoupper( $nombre ));
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

		$this->context->smarty->assign('module_dir', $this->_path);

		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');//recupero el template de configuracion
		
		return $output.
					$this->renderConfigForms();
	}
	
	/**
	 * @return el html de todos los formularios
	 */
	public function renderConfigForms()
	{
		return $this->renderForm('config')
					.$this->renderForm('test')
					.$this->renderForm('produccion')
					.$this->renderForm('estado')
					.$this->renderForm('proxy')
					.$this->renderForm('servicio');
					//.$this->renderForm('embebed');
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
				$form_fields = TodoPago\Formulario::getFormFields('general ', TodoPago\Formulario::getConfigFormInputs($this->getOptions($this->segmento), $this->getOptions($this->canal)));
				$prefijo = $this->getPrefijo('PREFIJO_CONFIG');
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
				$form_fields = TodoPago\Formulario::getFormFields('configuracion - embebed', TodoPago\Formulario::getEmbebedFormInputs());
				$prefijo = $this->getPrefijo('CONFIG_EMBEBED');
				break;				
		}
		
		if (isset($prefijo))
			$fields_value= TodoPago\Formulario::getConfigs($prefijo, TodoPago\Formulario::getFormInputsNames($form_fields['form']['input']));
		
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
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
		.'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		
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
		elseif (Tools::isSubmit('btnSubmitCybersource'))
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
	* Opcional: para archivos CSS y JavaScript que se usaran en el BackOffice de este modulo.
	*/
	public function hookBackOfficeHeader()
	{
/*		$this->context->controller->addJS(strtolower($this->_path).'js/back.js');
		$this->context->controller->addCSS($this->_path.'css/back.css');*/
	}

	/**
	 * Opcional: para archivos CSS y JavaScript que se usaran en el FrontOffice de este modulo.
	 */
	public function hookHeader()
	{
/*		$this->context->controller->addJS(strtolower($this->_path).'/js/front.js');
		$this->context->controller->addCSS($this->_path.'/css/front.css');*/
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
	 * Verifica si Cybersource está hablitado
	 */
	public function isCybersourceActivo()
	{
		return (boolean)Configuration::get($this->getPrefijo('CONFIG_CYBERSOURCE').'_STATUS');
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
	public function getSegmentoTienda()
	{
		$prefijo= $this->getPrefijo('PREFIJO_CONFIG');
		return Configuration::get($prefijo.'_SEGMENTO');	
	}
	
	public function getOrderStatesModulo($nombre=NULL)
	{
		$prefijo = $this->getPrefijo('CONFIG_ESTADOS');
		$sql = 'SELECT name, value FROM '._DB_PREFIX_.Configuration::$definition['table'];
		
		if ($nombre!=NULL && isset($nombre))//si se busca un valor especifico
		{
			return Db::getInstance()->getValue($sql.' WHERE name="'.$prefijo.'_'.$nombre.'"');
		}
		else
		{
			return Db::getInstance()->executeS($sql.' WHERE name LIKE \''.$prefijo.'%\'');
		}
	}
	
	public function getOrderStateOptions()
	{
		$list =  Db::getInstance()->executeS('SELECT os.id_order_state as id, name
			FROM `'._DB_PREFIX_.OrderState::$definition['table'].'` os
			LEFT JOIN `'._DB_PREFIX_.OrderState::$definition['table'].'_lang` osl 
				ON (os.'.OrderState::$definition['primary'].' = osl.'.OrderState::$definition['primary'].' AND osl.`id_lang` = '.(int)$this->context->language->id.')
			WHERE deleted = 0');
		$options = array();
	
		//ingreso la opcion por defecto
		$options[] = array(
					'id_option' => NULL,
					'name' => 'Ninguno'
			);
		
		//si la query devuelve un resultado
		if (count($list) !=0)
		{
			foreach ($list as $item)
			{
					$options[] = array(
							'id_option' => $item['id'],
							'name' => $item['name']
					);
			}
		}
	
		return $options;
	}
	
	/**
	 * Recupera los WSDL de Authorization, PaymentMethods y Operations guardados en la base de datos
	 * @param String $prefijo indica el ambiente en uso
	 * @return array resultado de decodear el los wsdls que están en formato json.
	 */
	protected function getWSDLs($prefijo)
	{
		return json_decode(Configuration::get($prefijo.'_WSDL'), TRUE);
	}
	
	/**
	 * Recupera el authorize.
	 * @param String $prefijo indica el ambiente en uso
	 * @return array resultado de decodear el authorization que está en formato json.
	 */
	protected function getAuthorization()
	{
		$prefijo = $this->getPrefijo('PREFIJO_CONFIG');
		return json_decode(Configuration::get($prefijo.'_AUTHORIZATION'), TRUE);
	}
	
	/**
	 * Recupera el endpoint
	 * @param String $prefijo indica el ambiente en uso
	 */
	protected function getEndpoint($prefijo)
	{
		return Configuration::get($prefijo.'_ENDPOINT');
	}

	/**
	 * Log
	 */
	public function logInfo($order, $action, $params = false)
	{
		$log = 'TodoPago - Orden: ' . $order . ' - ' . $action;
		if($params) $log = $log . ' - Parametros: ' .json_encode($params);
		$this->log($log);
	}
	
	/**
	 * Muestra el medio de pago en la lista
	 */
	public function hookDisplayPayment()
	{
		//si el modulo no esta activo
		if (!$this->active ||  !$this->isActivo())
			return;
			
		$this->smarty->assign(array(
			'nombre' => Configuration::get($this->getPrefijo('PREFIJO_CONFIG').'_NOMBRE'),//nombre que se muestra al momento de elegir los metodos de pago 
			'this_path' => $this->_path,
			'this_path_ejemplo' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
		));
		return $this->display(__FILE__, 'payment.tpl');//asigno el template que quiero usar
	}
	
	/**
	 * Muestra datos relativos a la compra, una vez realizada
	 * @param unknown $params viene desde OrderConfirmationController
	 */
	public function hookDisplayPaymentReturn($params)
	{
		/* $params  (desde OrderConfirmationController):
		 * 		getOrdersTotalPaid()
		 * 		currency -> sign
		 * 		order
		 * 		currency
		 */
		//si el modulo no esta activo
		if (!$this->active || !$this->isActivo())
			return;
		
		$cart_id = $this->context->cart->id;
		$order= $params['objOrder'];
		$detallesOrden=TPTransaccion::getOptions($order->id_cart);
		
		$state = $order->getCurrentState();//recupero el estado de la orden
		$estadoDenegada = $this->getOrderStatesModulo('DENEGADA');
		$customer = new Customer($order->id_customer);//recupera al objeto cliente

		if ($state != $estadoDenegada)//si el estado de la orden no es denegada
		{
			$this->smarty->assign(array(//muestro: total a pagar, status y numero de referencia de orden
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
				'status' => 'ok',
				'reference' => $order->reference,
				'mensaje' => $detallesOrden['status'],
				'customer' => $customer->email
			));
		}
		else
		{
			$this->smarty->assign(array(
				'status' => 'failed',
				'status_desc' => $state
				)
			);
		}
		return $this->display(__FILE__, 'payment_return.tpl');//asigno el template que quiero usar
	}
	
	public function log($mensaje)
	{
		$nombre = 'log';
		
		$archivo = fopen(dirname(__FILE__).'/'.$nombre.'.txt', 'a+');
		fwrite($archivo, date('Y/m/d - H:i:s').' - '.$mensaje . PHP_EOL);
		fclose($archivo);
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
		elseif (TPProductoCybersource::existeRegistro($idProducto))
		{
			$campos = TodoPago\Formulario::getFormInputsNames($form_fields['form']['input']);
			
			$fields_value = array();
		
			foreach ($campos as $nombre)
			{
				$fields_value[$nombre] = TPProductoCybersource::getValorRegistro($idProducto, $nombre);
			}
		}
		
		//creo el helperForm y seteo el controlador, id de de producto y token necesarios para que el form apunte donde corresponde
		$helperForm = $this->getHelperForm('Cybersource',$fields_value);
		$helperForm->currentIndex .= '&id_product='.$idProducto;
		
		//obtengo el html del formulario y lo agrego al smarty	
		$this->smarty->assign(array(
				'segmento' => $this->getSegmentoTienda(),//para filtrar campos del formulario segun el segmento
				'tab' => $this->displayName,
				'nombreDiv' => strtolower($this->name).'-cybersource',
				'form' => $helperForm->generateForm(array($form_fields)),
				'campos' => $campos
			)
		);
		
		return $this->display(__FILE__, 'views/templates/admin/cybersource.tpl');//indico la template a utilizar
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
		 
		$idProducto = $params['id_product'];//recupero el id del producto desde el backoffice
		$segmento = $this->getSegmentoTienda();//recupero el segmento de la tienda
		$this->displayName = $this->l('Prevencion del fraude');//nombre que se muestra en la tab
			
		$this->log('ActionProductUpdate - Segmento '.$segmento.' - params: '.json_encode($params));
		
		try
		{
			if (isset($params['form']) && count($params['form'])>0) //si el hook se ejecuto desde el _postProcess
			{
				$registro = $params['form'];//recupero desde los params
			}

			//si se recuperaron valores
			if (isset($registro) && count($registro)>0)
			{
				//creo un nuevo registro o actualizo el existente
				if (!TPProductoCybersource::existeRegistro($idProducto))
				{
					$registro['id_product'] = $idProducto;
					Db::getInstance()->insert(TPProductoCybersource::$definition['table'],  $registro);
					$this->log('ActionProductUpdate - Segmento '.$segmento.' - insertado registro para producto id='.$idProducto.' : '.json_encode($registro));
				}
				else
				{
					Db::getInstance()->update(TPProductoCybersource::$definition['table'],  $registro, TPProductoCybersource::$definition['primary'].'='.$idProducto);
					$this->log('ActionProductUpdate - Segmento '.$segmento.' - actualizado registro para producto id='.$idProducto.' : '.json_encode($registro));
				}
			}
		}
		catch (Exception $e)
		{
			$this->log('EXCEPCION: '.$e->getMessage());
		}
		//redirijo a la pagina de edicion del producto
		Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts').'&id_product='.$idProducto.'&updateproduct&token='.Tools::getAdminTokenLite('AdminProducts'));
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
		$status = $connector->getStatus($opciones);
		
		$this->log('DisplayAdminOrderContentOrder - $status:'.json_encode($status));
		
		$rta = '';
		if (count($status) > 0)//si hay un status para esta orden
		{
			foreach ($status['Operations'] as $key => $value)
			{
				$rta .= "$key: $value <br>";
			}
		}
		else
		{
			$rta = "No hay datos para esta orden";
		}
		
		$this->smarty->assign(array(
				'status' => $rta
			)
		);
	  
		return $this->display(__FILE__, 'views/templates/admin/order-content.tpl');//indico la template a utilizar
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
}
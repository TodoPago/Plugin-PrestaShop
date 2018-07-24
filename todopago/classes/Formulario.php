<?php

namespace TodoPago;

require_once(dirname(__FILE__).'/../../../config/config.inc.php');

class Formulario {
	/**
	 * Genera los form fields necesarios para crear un formulario
	 */
	public static function getFormFields($titulo, $inputs)
	{

		//solo para las credenciales
		//mejorar este codigo
		if($titulo == "Obtener credenciales"){

			$elements = array(
						'form' => array(
								'legend' => array(
										'title' => $titulo,//titulo del form
										'icon' => 'icon-cogs',//icono
								),
								'input' =>$inputs,
								'buttons' => array(
							        array(
							            'href' => '#',
							            'title' => 'Credenciales',
							            'class' => 'button login-credencial'
							        )
							    )
						)

			);

		}else{

			$elements = array(
						'form' => array(
								'legend' => array(
										'title' => $titulo,//titulo del form
										'icon' => 'icon-cogs',//icono
								),
								'input' =>$inputs,
								'submit' => array(
										'title' => 'Guardar',
										'class' => 'button'
								)
						)

			);

		}

		return $elements;
	}

	/**
	 * @return un array con los campos del formulario
	 */
	public static function getConfigFormInputs($segmentoOptions, $canalOptions, $timeoutValue=0)
	{
                $img_1="https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta1.jpg";
                $img_2="https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta2.jpg";
                $img_3="https://todopago.com.ar/sites/todopago.com.ar/files/billetera/pluginstarjeta3.jpg";  
            
		return array(
				array(
						'type' => 'switch',
						'label' =>'Enabled',
						'name' =>  'status',
						'desc' => 'Activa y desactiva el metodo de pago',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'active_on',
										'value' => true,
										'label' =>'SI'
								),
								array(
										'id' => 'active_off',
										'value' => false,
										'label' =>'NO'
								)
						),
						'required' => false
				),
				array(
						'type' => 'select',
						'label' =>'Segmento del comercio',
						'name' =>  'segmento',
						'desc' => 'La eleccion del segmento determina los datos a enviar',
						'required' => false,
						'options' => array(
								'query' => $segmentoOptions,
								'id' => 'id_option',
								'name' => 'name'
						)
				),
/*				array(
						'type' => 'select',
						'label' =>'Canal de ingreso del pedido',
						'name' =>  'canal',
						'required' => false,
						'options' => array(
								'query' => $canalOptions,
								'id' => 'id_option',
								'name' => 'name'
						)
				),*/
				array(
						'type' => 'text',
						'label' =>'Dead line',
						'name' =>  'deadline',
						'desc' => 'Dias maximos para la entrega.',
						'required' => false
				),
				array(
						'type' => 'switch',
						'label' =>'Ejecucion en produccion',
						'name' => 'modo',
						'desc' => 'Si no esta activada esta opcion, se ejecuta en ambiente Developers',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'active_on',
										'value' => true,
										'label' =>'Produccion'
								),
								array(
										'id' => 'active_off',
										'value' => false,
										'label' =>'Developers'
								)
						)
				),
				array(
						'type' => 'switch',
						'label' =>'Limitar cuotas',
						'name' =>  'cuotasenable',
						'desc' => 'Activa o desactiva el límite máximo de cuotas a mostrar en el formulario de pago',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'active_on',
										'value' => true,
										'label' =>'SI'
								),
								array(
										'id' => 'active_off',
										'value' => false,
										'label' =>'NO'
								)
						),
						'required' => false
				),
				array(
						'type' => 'select',
						'label' =>'Cuotas máximas',
						'name' =>  'cuotascant',
						'desc' => 'Cantidad máxima de cuotas a desplegar en el formulario de pago',
						'required' => false,
						'options' => array(
								'query' => array(
									array("id_option" => 1,  "name" => "01"),
									array("id_option" => 2,  "name" => "02"),
									array("id_option" => 3,  "name" => "03"),
									array("id_option" => 4,  "name" => "04"),
									array("id_option" => 5,  "name" => "05"),
									array("id_option" => 6,  "name" => "06"),
									array("id_option" => 7,  "name" => "07"),
									array("id_option" => 8,  "name" => "08"),
									array("id_option" => 9,  "name" => "09"),
									array("id_option" => 10, "name" => "10"),
									array("id_option" => 11, "name" => "11"),
									array("id_option" => 12, "name" => "12"),
								),
								'id' => 'id_option',
								'name' => 'name'
						)
				),
				array(
						'type' => 'switch',
						'label' =>'Configurar tiempo de expiración del formulario de pago personalizado',
						'name' => 'timeout',
						'desc' => '',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'active_on',
										'value' => true,
										'label' =>'Si'
								),
								array(
										'id' => 'active_off',
										'value' => false,
										'label' =>'No'
								)
						)
				),
				array(
					'type' => 'switch',
					'label' => 'Geolocalizacíón utilizando el servicio de Google maps',
					'name' => 'gmaps',
					'is_bool' => true,
					'values' => array(
							array(
									'id' => 'active_on',
									'value' => true,
									'label' =>'Si'
							),
							array(
									'id' => 'active_off',
									'value' => false,
									'label' =>'No'
							)
					)
				),
				array(
					'type' => 'html',
					'label' => 'Tiempo de expiración del formulario de pago',
					'name' => 'timeout_ms',
					'desc' => 'Tiempo maximo en el que se puede realizar el pago en el formulario en milisegundos. Por defecto si no se envia el valor es de 1800000 (30 minutos). Valor mínimo: 300000 (5 minutos). Máximo: 21600000 (6hs)',
					'required' => false,
					'html_content' => '<input type="number" name="timeout_ms" min=300000 max="21600000" value="'. $timeoutValue .'" style="display: block; height: 31px; padding: 6px 8px; font-size: 12px; line-height: 1.42857; color: #555; background-color: #F5F8F9; background-image: none; border: 1px solid #C7D6DB; border-radius: 3px;" >'
				),
				array(
						'type' => 'switch',
						'label' =>'Mantener carrito',
						'name' =>  'carrito_compras',
						'desc' => 'Mantener o vaciar el carro de compras al momento de producirse un error en el proceso de pago',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'on',
										'value' => true,
										'label' =>'SI'
								),
								array(
										'id' => 'off',
										'value' => false,
										'label' =>'NO'
								)
						),
						'required' => false
				),
                    
                                array(
                                    'type'      => 'radio',
                                    'label'     => 'Billetera en checkout',
                                    'desc'      => 'Seleccione el banner que desea mostrar para billetera',
                                    'name'      => 'banner_billetera',
                                    'required'  => true,
                                    'class'     => 'bannerBilletera',
                                    //'is_bool'   => true,
                                                                                          
                                    'values'    => array(                                 
                                      array(
                                        'id'    => 'banner1',
                                        'value' => $img_1,
                                        'label' => '<img src="'.$img_1.'" style="margin:-20px 0 15px 0;">'
                                      ),
                                      array(
                                        'id'    => 'banner2',
                                        'value' => $img_2,
                                        'label' => '<img src="'.$img_2.'" style="margin:-20px 0 15px 0;">'
                                      ),
                                      array(
                                        'id'    => 'banner3',
                                        'value' => $img_3,
                                        'label' => '<img src="'.$img_3.'" style="margin:-20px 0 15px 0;">'
                                      )
                                    ),
                                  ),

		);
	}

	/**
	 * @return un array con los campos del formulario
	 */
	public static function getLoginCredenciales($tabla)
	{

		return  array(
					array(
							'type' => 'text',
							'label' =>'Usuario de Todo Pago',
							'name' =>  'id_user',
							'required' => true
					),
					array(
							'type' => 'password',
							'label' =>'Contraseña de Todo Pago',
							'name' =>  'id_pass',
							'required' => true
					),
					array(
		                    'type' => 'html',
		                    'name' => 'html_data',
		                    'html_content' => '<div class="loader"><img class="loader-image" src="'._PS_BASE_URL_.__PS_BASE_URI__.'/modules/todopago/imagenes/loader.gif" alt="loading.."></div>
		                    <div id="error_message"></div>'
				    )
		);
	}

	/**
	 * @return un array con los campos del formulario
	 */
	public static function getAmbienteFormInputs($tabla)
	{

		return  array(
					array(
			                'type' => 'html',
			                'label' =>'Credenciales Todo Pago',
			                'name' => 'credenciales_button',
			                'html_content' => '<a href="#fieldset_0_1_1" id="cred-button" class="fancybox btn btn-default btn-primary modalbox">Obtener credenciales</a>',
			                'desc' => ' '
		            ),
					array(
							'type' => 'text',
							'label' =>'Id del sitio (Merchant ID)',
							'name' =>  'id_site',
							'desc' => 'Numero de comercio provisto por Todo Pago',
							'required' => false
					),
					array(
							'type' => 'text',
							'label' =>'Codigo de seguridad (Key sin PRISMA/TODOPAGO y sin espacios)',
							'name' =>  'security',
							'desc' => 'Codigo provisto por Todo Pago',
							'required' => false
					),
					array(
							'type' => 'text',
							'label' =>'Authorization HTTP (código de autorizacion)',
							'name' =>  'authorization',
							'desc' => 'Codigo provisto por Todo Pago',
							'required' => false
					)
		);
	}

	/**
	 * @return un array con los campos del formulario
	 */
	public static function getProxyFormInputs()
	{
		return array(
				array(
						'type' => 'switch',
						'label' =>'Activado',
						'name' =>  'status',
						'desc' => 'Activa y desactiva el proxy',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'active_on',
										'value' => true,
										'label' =>'SI'
								),
								array(
										'id' => 'active_off',
										'value' => false,
										'label' =>'NO'
								)
						),
						'required' => false
				),
/*				array(
						'type' => 'switch',
						'label' =>'Modo',
						'name' =>  'modo',
						'desc' => 'Si no esta activada esta opcion, se ejecuta en modo test',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'active_on',
										'value' => true,
										'label' =>'SI'
								),
								array(
										'id' => 'active_off',
										'value' => false,
										'label' =>'NO'
								)
						),
						'required' => false
				),*/
				array(
						'type' => 'text',
						'label' =>'Host',
						'name' =>  'host',
						'desc' => 'Ejemplo: localhost',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'Port',
						'name' =>  'port',
						'desc' => 'Ej: 8080',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'Usuario',
						'name' =>  'user',
						'desc' => 'Ej: user',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'Contrase&ntildea',
						'name' =>  'pass',
						'desc' => 'Ej: pass',
						'required' => false
				)
		);
	}

	/**
	 * @return un array con los campos del formulario
	 */
	public static function getEstadosFormInputs($estadosOption)
	{
		if(is_array($estadosOption)){
			$approvalsStatus = array_filter($estadosOption, function ($item) {
				return $item['valid_order'] == 1;
			});
		}

		if(!isset($approvalsStatus)){
			$approvalsStatus = array();
		}
		return array(
					array(
							'type' => 'select',
							'label' =>'En proceso',
							'name' =>  'proceso',
							'desc' => 'Para pagos con tarjeta de credito mientras se espera la respuesta del gateway.',
							'required' => false,
							'options' => array(
									'query' => $estadosOption,
									'id' => 'id_option',
									'name' => 'name'
							)
					),
					array(
							'type' => 'select',
							'label' =>'Aprobada',
							'name' =>  'aprobada',
							'desc' => 'Estado final de lo aprobado por el medio de pago',
							'required' => false,
							'options' => array(
									'query' => $approvalsStatus,
									'id' => 'id_option',
									'name' => 'name'
							)
					),
					array(
							'type' => 'select',
							'label' =>'Cupon pendiente de pago',
							'name' =>  'pendiente',
							'required' => false,
							'options' => array(
									'query' => $estadosOption,
									'id' => 'id_option',
									'name' => 'name'
							)
					),
					array(
							'type' => 'select',
							'label' =>'Denegada',
							'name' =>  'denegada',
							'desc' => 'Cuando por cualquier motivo la transcaccion fue denegada.',
							'required' => false,
							'options' => array(
									'query' => $estadosOption,
									'id' => 'id_option',
									'name' => 'name'
							)
					)
		);
	}

	public static function getServicioConfFormInputs()
	{
		return array(
				array(
						'type' => 'text',
						'label' =>'Ruta donde se encuentra el certificado',
						'name' =>  'certificado',
						//'desc' => '',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'Time out del servicio de pago',
						'name' =>  'timeout',
						//'desc' => '',
						'required' => false
				)
		);
	}

	public static function getProductoFormInputs($segmento, $servicioOption, $deliveryOption, $envioOption, $productOption)
	{
/*
		return array(
				array(
						'type' => 'select',
						'label' =>'Tipo de servicio',
						'name' =>  'tipo_servicio',
						//'desc' => 'Utilizar esta opcion en el caso que el producto sea un servicio',
						'required' => false,
						'options' => array(
								'query' => $servicioOption,
								'id' => 'id_option',
								'name' => 'name'
						)
				),
				array(
						'type' => 'text',
						'label' =>'Referencia de pago',
						'name' =>  'referencia_pago',
						//'desc' => 'Utilizar esta opcion en el caso que el producto sea un servicio',
						'required' => false
				),
				array(
						'type' => 'select',
						'label' =>'Tipo de delivery',
						'name' =>  'tipo_delivery',
						//'desc' => 'Utilizar esta opcion en el caso que el producto sea un bien digital',
						'required' => false,
						'options' => array(
								'query' => $deliveryOption,
								'id' => 'id_option',
								'name' => 'name'
						)
				),
				array(
						'type' => 'select',
						'label' =>'Tipo de envio',
						'name' =>  'tipo_envio',
						//'desc' => 'Utilizar esta opcion en el caso que el producto sea una entrada',
						'required' => false,
						'options' => array(
								'query' => $envioOption,
								'id' => 'id_option',
								'name' => 'name'
						)
				),
				array(
						'type' => 'date',
						'label' =>'Fecha del evento',
						'name' =>  'fecha_evento',
						//'desc' => 'Utilizar esta opcion en el caso que el producto sea una entrada',
						'required' => false
				)
		);

*/
		if($segmento == 'retail')
		{
			return array(
				 array(
							'type' => 'select',
							'label' =>'Código de producto',
							'name' =>  'codigo_producto',
							//'desc' => 'Utilizar esta opcion en el caso que el producto sea un servicio',
							'required' => false,
							'options' => array(
									'query' => $productOption,
									'id' => 'id_option',
									'name' => 'name'
							)
					)
			);
		}
		elseif ($segmento == 'services')
		{
			return array(
				 array(
							'type' => 'select',
							'label' =>'Código de producto',
							'name' =>  'codigo_producto',
							//'desc' => 'Utilizar esta opcion en el caso que el producto sea un servicio',
							'required' => false,
							'options' => array(
									'query' => $productOption,
									'id' => 'id_option',
									'name' => 'name'
							)
					),
				 array(
							'type' => 'select',
							'label' =>'Tipo de servicio',
							'name' =>  'tipo_servicio',
							//'desc' => 'Utilizar esta opcion en el caso que el producto sea un servicio',
							'required' => false,
							'options' => array(
									'query' => $servicioOption,
									'id' => 'id_option',
									'name' => 'name'
							)
					),
					array(
							'type' => 'text',
							'label' =>'Referencia de pago',
							'name' =>  'referencia_pago',
							//'desc' => 'Utilizar esta opcion en el caso que el producto sea un servicio',
							'required' => false
					)
			);
		}

		elseif ($segmento == 'digital goods')
		{
			return array(
				 array(
							'type' => 'select',
							'label' =>'Código de producto',
							'name' =>  'codigo_producto',
							//'desc' => 'Utilizar esta opcion en el caso que el producto sea un servicio',
							'required' => false,
							'options' => array(
									'query' => $productOption,
									'id' => 'id_option',
									'name' => 'name'
							)
					),
				array(
						'type' => 'select',
						'label' =>'Tipo de delivery',
						'name' =>  'tipo_delivery',
						//'desc' => 'Utilizar esta opcion en el caso que el producto sea un bien digital',
						'required' => false,
						'options' => array(
								'query' => $deliveryOption,
								'id' => 'id_option',
								'name' => 'name'
						)
				)
			);
		}

		elseif ($segmento == 'ticketing')
		{
			return array(
				 array(
							'type' => 'select',
							'label' =>'Código de producto',
							'name' =>  'codigo_producto',
							//'desc' => 'Utilizar esta opcion en el caso que el producto sea un servicio',
							'required' => false,
							'options' => array(
									'query' => $productOption,
									'id' => 'id_option',
									'name' => 'name'
							)
					),
				array(
						'type' => 'select',
						'label' =>'Tipo de envio',
						'name' =>  'tipo_envio',
						//'desc' => 'Utilizar esta opcion en el caso que el producto sea una entrada',
						'required' => false,
						'options' => array(
								'query' => $envioOption,
								'id' => 'id_option',
								'name' => 'name'
						)
				),
				array(
						'type' => 'date',
						'label' =>'Fecha del evento',
						'name' =>  'fecha_evento',
						//'desc' => 'Utilizar esta opcion en el caso que el producto sea una entrada',
						'required' => false
				)
			);
		}
	}

	/**
	 * Devuelve los nombres de los inputs que existen en el form
	 * @param array $inputs campos de un formulario
	 * @return un array con los nombres
	 */
	public static function getFormInputsNames($inputs)
	{
		$nombres=array();

		foreach ($inputs as $campo)
		{
			if (array_key_exists('name', $campo))
			{
				$nombres[] = $campo['name'];
			}
		}

		return $nombres;
	}

	/**
	 * Escribe en la base de datos los valores de tablas de configuraciones
	 * @param string $prefijo prefijo con el que se identifica al formulario en la tabla de configuraciones. Ejemplo: DECIDIR_TEST
	 * @param array $inputsName resultado de la funcion getFormInputsNames
	 */
	public static function postProcessFormularioConfigs($prefijo, $inputsName)
	{
		foreach ($inputsName as $nombre)
		{
			//mejorarlo este codigo
			if($nombre == "authorization"){

				$auth = \Tools::getValue($nombre);
				if(json_decode($auth) == NULL) {
					//armo json de autorization
					$autorizationId = new \stdClass();
					$autorizationId->Authorization = $auth;
					$auth = json_encode($autorizationId);
				}

				$valueField = $auth;

			}else{
				$valueField = \Tools::getValue($nombre);
			}

			if($nombre=='timeout_ms' AND ($valueField<300000 OR $valueField>21600000)
			 ){
				continue;
			}



			\Configuration::updateValue( $prefijo.'_'.strtoupper( $nombre ), $valueField);

		}
	}

	/**
	 * Trae de los valores de configuracion del modulo, listos para ser usados como fields_value en un form
	 * @param string $prefijo prefijo con el que se identifica al formulario en la tabla de configuraciones. Ejemplo: DECIDIR_TEST
	 * @param array $inputsName resultado de la funcion getFormInputsNames
	 */
	public static function getConfigs($prefijo, $inputsName)
	{
		$configs = array();

		foreach ($inputsName as $nombre)
		{
			$configs[$nombre] = \Configuration::get( $prefijo.'_'.strtoupper( $nombre ));
		}

		return $configs;
	}

	public static function getEmbebedFormInputs()
	{
		/* Configuracion para el form embebed
		    backgroundColor: '#CDF788',
            border: '10px solid #8DC92C',
            buttonBackgroundColor: '#F1F734',
            buttonColor: '#727356',
            buttonBorder: '10px solid #8DC92C'
		*/
		return array(
				array(
						'type' => 'switch',
						'label' =>'Activado',
						'name' =>  'embebed',
						'desc' => 'Si esta desactivado redireccionara a un formulario externo',
						'is_bool' => true,
						'values' => array(
								array(
										'id' => 'active_on',
										'value' => true,
										'label' =>'SI'
								),
								array(
										'id' => 'active_off',
										'value' => false,
										'label' =>'NO'
								)
						),
						'required' => false
				)
		);
	}
}

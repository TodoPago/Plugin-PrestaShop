<?php

namespace TodoPago;

require_once(dirname(__FILE__).'../../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../../init.php');

class Formulario {
	/**
	 * Genera los form fields necesarios para crear un formulario
	 */
	public static function getFormFields($titulo, $inputs)
	{
		return array(
				'form' => array(
						'legend' => array(
								'title' => $titulo,//titulo del form
								'icon' => 'icon-cogs',//icono
						),
						'input' =>$inputs,
						'submit' => array(
								'title' => 'Guardar',
								'class' => 'button'
						),
				),
		);
	}
	
	/**
	 * @return un array con los campos del formulario
	 */
	public static function getConfigFormInputs($segmentoOptions, $canalOptions)
	{
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
						'type' => 'text',
						'label' =>'Authorization HTTP',
						'name' =>  'authorization',
						'desc' => 'Codigo provisto por Todo Pago',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'Nombre a mostrar en el front end',
						'name' =>  'nombre',
						'desc' => 'Nombre con el que aparecera el metodo de pago',
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
				)
		);
	}
	
	/**
	 * @return un array con los campos del formulario
	 */
	public static function getAmbienteFormInputs($tabla)
	{
		return array(
				array(
						'type' => 'text',
						'label' =>'Id del sitio',
						'name' =>  'id_site',
						'desc' => 'Numero de comercio provisto por Todo Pago',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'Codigo de seguridad ("Security")',
						'name' =>  'security',
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
								'query' => $estadosOption,
								'id' => 'id_option',
								'name' => 'name'
						)
				),
/*				array(
						'type' => 'switch',
						'label' =>'Generar factura automaticamente',
						'name' => 'factura',
						'desc' => 'Cuando llega al estado de aprobacion emite la factura',
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
*/				array(
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
			\Configuration::updateValue( $prefijo.'_'.strtoupper( $nombre ), \Tools::getValue($nombre));
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
						'desc' => 'Activa y desactiva el formulario embebed',
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
						'type' => 'text',
						'label' =>'backgroundColor',
						'name' =>  'backgroundColor',
						//'desc' => '',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'border',
						'name' =>  'border',
						//'desc' => '',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'buttonBackgroundColor',
						'name' =>  'buttonBackgroundColor',
						//'desc' => '',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'buttonColor',
						'name' =>  'buttonColor',
						//'desc' => '',
						'required' => false
				),
				array(
						'type' => 'text',
						'label' =>'buttonBorder',
						'name' =>  'buttonBorder',
						//'desc' => '',
						'required' => false
				)
		);
	}	
}
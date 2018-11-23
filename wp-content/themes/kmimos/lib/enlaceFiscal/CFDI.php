<?php
date_default_timezone_set('America/Mexico_City');

$CFDI = new CFDI();

class CFDI {

	public $db;

	protected $raiz='';

	// EndPoint Enlace Fiscal
	protected $url = 'https://api.enlacefiscal.com/v6/';

	// Modo:  [ produccion , debug ]
	protected $modo = 'debug'; 

	// Credenciales de acceso kmimos
	protected $kmimos = [
		'RFC' => 'KMI160615640',
		'produccion' => [
			'token' => '',
			'x-api-key' => ''
		],
		'debug' => [
			'token' => 'c83e1f14de69b963add399109a97a392',
			'x-api-key' => 'e9aT1ajrRh1NyRkzOtDoN1ZEGmIsEKuJ6f3FYyLh'
		]
	];

	// Credenciales de acceso al API
	protected $RFC = ''; // Usuario
	protected $token = ''; // Clave
	protected $xapikey = ''; // Llave de acceso

	// Saldo en enlaceFiscal
	protected $saldo = 0;

	// Init
	public function CFDI(){
		$this->raiz = dirname(dirname(dirname(dirname(dirname(__DIR__)))));

		if( !isset($db) || is_string( $db ) ){
			include($this->raiz.'/vlz_config.php');
			if( !class_exists('db') ){
				include($this->raiz.'/wp-content/themes/kmimos/procesos/funciones/db.php');
			}
		    $db = new db( new mysqli($host, $user, $pass, $db) );
		}

		$this->db = $db;

		$this->load_config_kmimos();

		if( $this->modo == 'produccion' ){		
			$_saldos = $this->obtenerSaldo();
			if( !empty($_saldos) ){
				$r = json_decode($_saldos);
				$this->saldo = $r['AckEnlaceFiscal']['saldo'];
				if( $this->saldo < 0.89 ){
					wp_mail( 
						'italococchini@gmail.com', 
						'Notificacion enlaceFiscal', 
						'ERROR AL EMITIR LOS CFDI. <BR> DESCRIPCION: SALDO INSUFICIENTE <br> Monto: '.$this->saldo 
					);
				}
			}
		}
	}

	public function load_config_kmimos(){
		$this->RFC = $this->kmimos['RFC'];
		$this->token = $this->kmimos[ $this->modo ]['token']; 
		$this->xapikey = $this->kmimos[ $this->modo ]['x-api-key']; 
	}

	// Probar conexion con enlaceFiscal
	public function probarConexion(){ 
		$aData = array(
			'Solicitud' => array(
				'rfc'  => $this->RFC,
                'accion' => 'probarConexion'
            )
		);

		return $this->request( $aData, 'probarConexion' );
	}

	// Obtener Saldo para Generar CFDI
	public function obtenerSaldo(){
		$aData = array(
			'Solicitud' => array(
				'rfc'  => $this->RFC,
                'accion' => 'obtenerSaldo'
            )
		);

		return $this->request( $aData, 'obtenerSaldo' );
	}

	// Configuracion general CFDI
	protected function get_configuracion( $param=[] ){
		$data = [];

		// Configuracion general
			$_options = $this->db->get_var(
				"SELECT value FROM facturas_configuracion WHERE codigo = 'cfdi_parametros' ",
				'value' 
			);
			$conf = unserialize($_options);

		// comision cuidadores
		$data['comision'] = (isset($conf['comision']))? $conf['comision'] : '' ;

		// Serie
		$data['serie'] = (isset($conf['serie']))? $conf['serie'] : '' ;

		// Porcentaje de IVA
		$data['tasaCuota'] = ( isset($conf['iva']) )? $conf['iva'] : 0 ; // IVA
		$data['base_iva'] = $data['tasaCuota'] + 1; 

		// Forma de Pago - Catalago del SAT
			$t_pago = (isset($param['servicio_tipo_pago']))? $param['servicio_tipo_pago'] : '' ;
			if( !empty($t_pago) ){			
				$formaDePago = $this->db->get_var( 
					"SELECT sat_clave FROM facturas_forma_pago WHERE kmimos_clave = '".strtoupper($t_pago)."'",
					'sat_clave' 
				);
			}
			if( !$formaDePago ){
				$formaDePago = $this->db->get_var( 
					"SELECT sat_clave FROM facturas_forma_pago WHERE `default` = 1 limit 1" ,
					'sat_clave'
				);
			}
		$data['formaDePago'] = $formaDePago;

		return $data;
	}

	public function generar_Cfdi_NotasCreditos( $data=[] ){

		$razon_social = $this->db->get_var("SELECT meta_value from wp_usermeta where meta_key = 'billing_razon_social' AND user_id = ".$data['cliente']['id']);

		$data['receptor']['razon_social'] = ( !empty($razon_social) )? utf8_encode($razon_social) : 'Publico en General';
		$data['tipo_pago'] = 'PAGO'; 

		if( !array_key_exists( 'consecutivo', $data ) ){
			$data['consecutivo'] = '';
		}

		// Configuracion general
			$conf = $this->get_configuracion( [ 
				'servicio_tipo_pago' => $data['tipo_pago'] 
			]);
			extract($conf);

		// Varibles de Estructura
			$data['fechaEmision'] = date('Y-m-d H:i:s', strtotime("now -1 minute"));
 			$partidas = [];
			$_subtotal = 0;
			$_impuesto = 0;
			$_total = 0;

			$descuento = 0;
			if( isset($data['descuento']) && $data['descuento'] > 0 ){
				$descuento = $data['descuento'];
			}

		// Parametros obligatorios segun EnlaceFiscal 
			$serie = 'NC';
			$uso = 'G02';
			$metodo_pago = 'PUE';
			$clave_sat = '84111506';
			$unidad = 'ACT';

		// Agregar Partida: Penalizacion
			if( isset($data['detalle']) && !empty($data['detalle']) ){
				foreach ($data['detalle'] as $item) {

						$cantidad = 1;

					// Calcular precio base de la partida
						$base = $item['costo'] / $base_iva; // Costo del servicio sin Impuesto

						$subtotal = $base * $cantidad;
					// Cobro de comision Kmimos
						if( $data['comision'] > 0 ){
							$subtotal -= $subtotal * $data['comision'];
						}

					// Calcular impuestos
						$impuesto = $subtotal * $tasaCuota;
						$impuesto = number_format($impuesto, 2, ".", "" );

					// Desglose general de la factura
					// *************************************
						$_impuesto += number_format($impuesto, 2, ".", "" );
						$_subtotal += number_format($subtotal, 2, ".", "" );
						$_total += $subtotal + $impuesto; 

					// Agregar la partida a la factura
					// *************************************
						$partidas[] = [
						    "cantidad" => $cantidad,
						    "claveUnidad" => $unidad,
						    "claveProdServ" => $clave_sat, //  9011150.0 por definir
						    "descripcion" => $item['titulo'],
						    "valorUnitario" =>(float) number_format($base, 2, '.', ''),
						    "importe" => (float) number_format( $subtotal, 2, '.', ''),
						    "Impuestos" => [
						    	0 => [
									"tipo" => "traslado",
									"claveImpuesto" => "IVA",
									"tipoFactor" => "tasa",
									"tasaOCuota" => (float) $tasaCuota,
									"baseImpuesto" => (float) number_format( $subtotal, 2, '.', ''),
									"importe" => (float) number_format( $impuesto, 2, '.', '')
							    ]
						    ]
						];
				}
			}

		// Estructura de datos CFDI
			$CFDi = [
				"CFDi" => [
					"modo" => $this->modo,
					"versionEF" => "6.0",
					"serie" => $serie, //"FAA",
					"folioInterno" => $data['reserva_id'].$data['consecutivo'],
					"tipoMoneda" => "MXN",
					"fechaEmision" => $data['fechaEmision'], //"2017-02-22 11:03:43",
					"subTotal" => (float) number_format($_subtotal,2, ".", "" ), //"20.00", ( Sin IVA )
					"total" => (float) number_format( $_total, 2, '.', ''), // "23.20" ( Con IVA )
					"rfc" => $this->RFC,
					"DatosDePago" => [
						"metodoDePago" => "PUE",
						"formaDePago" => $formaDePago, 
					],
					"Receptor" => [
						"rfc" => $this->RFC, //$data['receptor']['rfc'],
						"nombre" => $data['receptor']['razon_social'],
						"usoCfdi" => $uso, //"gastos"
						/*"DomicilioFiscal" => [
			                "calle" => $data['receptor']['calle'],
			                "colonia" => $data['receptor']['colonia'],
			                "noExterior" => $data['receptor']['noExterior'],
			                "noInterior" => $data['receptor']['noInterior'],
			                "localidad" => $data['receptor']['localidad'],
			                "municipio" => $data['receptor']['city'],
			                "estado" => $data['receptor']['estado'],
			                "pais" => "Mexico",
			                "cp" => $data['receptor']['postcode']
			            ]*/
					],
					"Partidas" => $partidas,
					"Impuestos" => [
						"Totales" => [
							"traslados" =>  (float) number_format( $_impuesto, 2, '.', '')
						],
						"Impuestos" => [
							0 => [
								"tipo" => "traslado",
								"claveImpuesto" => "IVA",
								"tipoFactor" => "tasa",
								"tasaOCuota" => (float)$tasaCuota,
								"importe" =>(float) number_format( $_impuesto, 2, '.', '')
							]
						]
					]
				]
			];

			// configuracion para publico en general
			if( $data['receptor']['razon_social'] == 'Publico en General' ){
				unset($CFDi['CFDi']["Receptor"]['DomicilioFiscal']);
			}

	 	// return $CFDi;
		$cfdi_respuesta = $this->request( $CFDi, 'generarCfdi' );

		$estatus = '';
		$referencia = '';
		if( !empty($cfdi_respuesta) ){
			$ack = json_decode($cfdi_respuesta);

		    // Datos complementarios
		    $data_reserva['comentario'] = '';
		    $data_reserva['subtotal'] = $CFDi['CFDi']['subTotal'];
		    $data_reserva['impuesto'] = $CFDi['CFDi']['Impuestos']['Totales']['traslados'];
		    $data_reserva['total'] = $CFDi['CFDi']['total'];

			$this->guardarCfdi( $data['tipo'], $data, $ack );

			if( $ack->AckEnlaceFiscal->estatusDocumento == 'aceptado' ){
				$estatus = $ack->AckEnlaceFiscal->estatusDocumento;
				$referencia = $ack->AckEnlaceFiscal->numeroReferencia;
			}
		}

		return [ 
			'ack' => json_decode($cfdi_respuesta), 
			'estatus' => $estatus,
			'referencia' => $referencia,
			'cfdi' => $CFDi
		];
	

	}

	// Generar CFDI para el Cliente ( Monto: 100% )
	public function generar_Cfdi_Cliente( $data=[] ){

		if( !array_key_exists( 'consecutivo', $data ) ){
			$data['consecutivo'] = '';
		}
		
		// Configuracion general
			$conf = $this->get_configuracion( [ 
				'servicio_tipo_pago' => $data['servicio']['tipo_pago'] 
			] );
			extract($conf);

		// Datos de configuracion del cuidador
			if( $this->modo != 'debug' ){
				$info_cfdi = $this->db->get_row( "SELECT * FROM FACTURAS_ALIADOS WHERE estatus='Activo' AND user_id =".$data['cuidador']['id'] );
				$serie = $info_cfdi->serie;
				// Credenciales 
				$this->RFC = $info_cfdi->rfc; // Usuario
				$this->token = $info_cfdi->tokenAPI; // Clave
				$this->xapikey = $info_cfdi->xApiKey; // llave de acceso
			}

		// Variables de Estructura
			$data['fechaEmision'] = date('Y-m-d H:i:s', strtotime("now -1 minute"));
			$personalizados = [];
 			$partidas = [];
			$_subtotal = 0;
			$_impuesto = 0;
			$_total = 0;

		// Restar monto si el Descuento es por Saldo a Favor
			$sql = "SELECT *  FROM `wp_woocommerce_order_items` as items 
					INNER JOIN wp_woocommerce_order_itemmeta as meta ON meta.order_item_id = items.order_item_id
				WHERE 
				    meta.meta_key = 'discount_amount'
					and items.`order_id` = ".$data['servicio']['id_orden']."
				    and items.order_item_name = 'saldo-".$data['cliente']['id']."'
			";
			$saldo_favor = $this->db->get_var( $sql, 'meta_value' );
			if( $saldo_favor > 0 ){
				//$data['servicio']['desglose']['descuento'] -= $saldo_favor;
			}
			$descuento = $data['servicio']['desglose']['descuento'];		

		// Cargar desglose de Partidas
			$desglose_partidas = $this->db->get_var( "SELECT * FROM wp_postmeta WHERE meta_key = '_booking_desglose' and post_id = ".$data['servicio']['id_reserva'], 'meta_value' );

			if( !empty($desglose_partidas) && !isset($data['servicio']['penalizado']) ){
				$_desglose_partidas = unserialize( utf8_encode($desglose_partidas) );
		
				$data['servicio']['variaciones'] = $_desglose_partidas['variaciones'];
				$data['servicio']['transporte'] = $_desglose_partidas['transporte'];
				$data['servicio']['adicionales'] = $_desglose_partidas['adicionales'];
			}

		// Agregar Partida: Penalizacion
			if( isset($data['servicio']['penalizado']) && !empty($data['servicio']['penalizado']) ){
				foreach ($data['servicio']['penalizado'] as $item) {

						$cantidad = 1;

						// Calcular precio base de la partida
						$base = $item['total'] / $base_iva; // Costo del servicio sin Impuesto

						$subtotal = $base * $cantidad;

						// Calcular impuestos
						$impuesto = $subtotal * $tasaCuota;
						$impuesto = number_format($impuesto, 2, ".", "" );

					// Desglose general de la factura
					// *************************************
						$_impuesto += number_format($impuesto, 2, ".", "" );
						$_subtotal += number_format($subtotal, 2, ".", "" );
						$_total += $subtotal + $impuesto; 

					// Agregar la partida a la factura
					// *************************************
						$partidas[] = [
						    "cantidad" => $cantidad,
						    "claveUnidad" => "DAY",
						    "claveProdServ" => '84111506', //  90111500 por definir
						    "descripcion" => 'Pago de comision por penalizacion',
						    "valorUnitario" =>(float) number_format($base, 2, '.', ''),
						    "importe" => (float) number_format( $subtotal, 2, '.', ''),
							"descuento" => (float) number_format( $descuento, 2, '.', ''),
						    "Impuestos" => [
						    	0 => [
									"tipo" => "traslado",
									"claveImpuesto" => "IVA",
									"tipoFactor" => "tasa",
									"tasaOCuota" => (float) $tasaCuota,
									"baseImpuesto" => (float) number_format( $subtotal, 2, '.', ''),
									"importe" => (float) number_format( $impuesto, 2, '.', '')
							    ]
						    ]
						];
				}
			}

		// Agregar Partida: Variaciones
			if( isset($data['servicio']['variaciones']) && !empty($data['servicio']['variaciones']) ){	
				foreach ($data['servicio']['variaciones'] as $item) {

					$codigo_sat = $this->db->get_var("SELECT value FROM facturas_configuracion WHERE clave ='".$data['servicio']['tipo']."'" );

					// Desglose del detalle de la factura 
					// *************************************
						$item[3] = str_replace(".", "", $item[3]);
						$item[3] = str_replace(",", ".", $item[3]);

						// Buscar Numeros de Noches
						$num_noches = explode(" ", $item[2]);
						if( !isset($num_noches[0]) || $num_noches[0] <= 0 ){
							$num_noches[0] = 1;
						}

						// Cantidad ( cantidad de mascotas x numero de noches )
						$cantidad = $item[0] * $num_noches[0];

						// Calcular precio base de la partida
						$base = $item[3] / $base_iva; // Costo del servicio sin Impuesto

						// Valor del servicio por la cantidad ( subtotal )
						$subtotal = $cantidad * $base;

						// Calcular impuestos
						$impuesto = $subtotal * $tasaCuota;
						$impuesto = number_format($impuesto, 2, ".", "" );

					// Desglose general de la factura
					// *************************************
						$_impuesto += number_format($impuesto, 2, ".", "" );
						$_subtotal += number_format($subtotal, 2, ".", "" );
						$_total += $subtotal + $impuesto; 
						$_total -= $descuento; 

					// Agregar la partida a la factura
					// *************************************
						$partidas[] = [
						    "cantidad" => $cantidad,
						    "claveUnidad" => "DAY",
						    "claveProdServ" => $codigo_sat, //  9011150.0 por definir
						    "descripcion" => $item[0]." ". $item[1] ." x ".$item[2] ." x $".$item[3],
						    "valorUnitario" =>(float) number_format($base, 2, '.', ''),
						    "importe" => (float) number_format( $subtotal, 2, '.', ''),
							"descuento" => (float) number_format( $descuento, 2, '.', ''),
						    "Impuestos" => [
						    	0 => [
									"tipo" => "traslado",
									"claveImpuesto" => "IVA",
									"tipoFactor" => "tasa",
									"tasaOCuota" => (float) $tasaCuota,
									"baseImpuesto" => (float) number_format( $subtotal, 2, '.', ''),
									"importe" => (float) number_format( $impuesto, 2, '.', '')
							    ]
						    ]
						];

					// Reset descuento
					$descuento = 0;
				}
			}

		// Agregar Partida: Transporte
			if( isset($data['servicio']['transporte']) && !empty($data['servicio']['transporte']) ){			
				foreach ($data['servicio']['transporte'] as $item) {

					$codigo_sat = $this->db->get_var("SELECT value FROM facturas_configuracion WHERE clave ='".$item[0]."'" );

					// Desglose del detalle de la factura 
					// *************************************
						$item[2] = str_replace(".", "", $item[2]);
						$item[2] = str_replace(",", ".", $item[2]);

						// Buscar Numeros de Noches
						$num_noches = explode(" ", $item[1]);
						if( !isset($num_noches[0]) || $num_noches[0] <= 0 ){
							$num_noches[0] = 1;
						}

						// Cantidad
						$cantidad = 1 * $num_noches[0];

						// Desglose Impuesto: Calcular precio base
						$base = $item[2] / $base_iva;

						// Valor del servicio por la cantidad
						$subtotal = $cantidad * $base;

						// Calcular impuestos
						$impuesto = $subtotal * $tasaCuota;

					// Desglose general de la factura
					// *************************************
						$_subtotal += number_format($subtotal, 2);
						$_impuesto += number_format($impuesto, 2);
						$_total += $subtotal + $impuesto;

					// Agregar la partida a la factura
					// *************************************
						$partidas[] = [
						    "cantidad" => $cantidad,
						    "claveUnidad" => "DAY",
						    "claveProdServ" => $codigo_sat, // por definir
						    "descripcion" =>  $item[0] ." x ".$item[1] ." x $".$item[2],
						    "valorUnitario" =>(float)  number_format($base, 2, '.', ''),
						    "importe" => (float) number_format($subtotal, 2, '.', ''),
						    "Impuestos" => [
						    	0 => [
									"tipo" => "traslado",
									"claveImpuesto" => "IVA",
									"tipoFactor" => "tasa",
									"tasaOCuota" => (float) $tasaCuota,
									"baseImpuesto" => (float) number_format($subtotal, 2, '.', ''),
									"importe" => (float) number_format($impuesto, 2, '.', '')
							    ]
						    ]				
						];
				}
			}

		// Agregar Partida: Adicionales
			if( isset($data['servicio']['adicionales']) && !empty($data['servicio']['adicionales']) ){	
				foreach ($data['servicio']['adicionales'] as $item) {

					$codigo_sat = $this->db->get_var("SELECT value FROM facturas_configuracion WHERE clave ='".utf8_decode($item[0])."'" );

					// Desglose del detalle de la factura 
					// *************************************
						$item[2] = str_replace(".", "", $item[2]);
						$item[2] = str_replace(",", ".", $item[2]);

						// Buscar Numeros de Noches
						$num_noches = explode(" ", $item[1]);
						if( !isset($num_noches[0]) || $num_noches[0] <= 0 ){
							$num_noches[0] = 1;
						}

						// Cantidad
						$cantidad = 1 * $num_noches[0];

						// Desglose Impuesto: Calcular precio base
						$base = $item[2] / $base_iva;

						// Valor del servicio por la cantidad
						$subtotal = $cantidad * $base;

						// Calcular impuestos
						$impuesto = $subtotal * $tasaCuota;

					// Desglose general de la factura
					// *************************************
						$_subtotal += number_format($subtotal, 2);
						$_impuesto += number_format($impuesto, 2);
						$_total += $subtotal + $impuesto;

					// Agregar la partida a la factura
					// *************************************
						$partidas[] = [
						    "cantidad" => $cantidad,
						    "claveUnidad" => "DAY",
						    "claveProdServ" => $codigo_sat, // por definir
						    "descripcion" =>  $item[0] ." x ".$item[1] ." x $".$item[2],
						    "valorUnitario" =>(float)  number_format($base, 2, '.', ''),
						    "importe" => (float) number_format($subtotal, 2, '.', ''),
						    "Impuestos" => [
						    	0 => [
									"tipo" => "traslado",
									"claveImpuesto" => "IVA",
									"tipoFactor" => "tasa",
									"tasaOCuota" => (float) $tasaCuota,
									"baseImpuesto" => (float) number_format($subtotal, 2, '.', ''),
									"importe" => (float) number_format($impuesto, 2, '.', '')
							    ]
						    ]				
						];
					 
				}
			}

		// Agregar Campos Personalizados
			$personalizados[] = [
                "nombreCampo" => "Número de Reserva",
                "valor" => $data['servicio']['id_reserva']
	        ];

		// Estructura de datos CFDI
			$CFDi = [
				"CFDi" => [
					"modo" => $this->modo,
					"versionEF" => "6.0",
					"serie" => $serie, //"FAA",
					"folioInterno" => $data['servicio']['id_reserva'].$data['consecutivo'],
					"tipoMoneda" => "MXN",
					"fechaEmision" => $data['fechaEmision'], //"2017-02-22 11:03:43",
					"subTotal" => (float) number_format($_subtotal,2, ".", "" ), //"20.00", ( Sin IVA )
					"total" => (float) number_format( $_total, 2, '.', ''), // "23.20" ( Con IVA )
					"rfc" => $this->RFC,
					"descuentos" => (float) number_format( $data['servicio']['desglose']['descuento'], 2, '.', ''),
					"DatosDePago" => [
						"metodoDePago" => "PUE",
						"formaDePago" => $formaDePago, 
					],
					"Receptor" => [
						"rfc" => $data['receptor']['rfc'],
						"nombre" => $data['receptor']['razon_social'],
						"usoCfdi" => $data['receptor']['uso_cfdi'], //"gastos"
						"DomicilioFiscal" => [
			                "calle" => $data['receptor']['calle'],
			                "noExterior" => $data['receptor']['noExterior'],
			                "noInterior" => $data['receptor']['noInterior'],
			                "colonia" => $data['receptor']['colonia'],
			                "localidad" => $data['receptor']['localidad'],
			                "municipio" => $data['receptor']['city'],
			                "estado" => $data['receptor']['estado'],
			                "pais" => "Mexico",
			                "cp" => $data['receptor']['postcode']
			            ]
					],
					"Partidas" => $partidas,
					"Impuestos" => [
						"Totales" => [
							"traslados" =>  (float) number_format( $_impuesto, 2, '.', '')
						],
						"Impuestos" => [
							0 => [
								"tipo" => "traslado",
								"claveImpuesto" => "IVA",
								"tipoFactor" => "tasa",
								"tasaOCuota" => (float)$tasaCuota,
								"importe" =>(float) number_format( $_impuesto, 2, '.', '')
							]
						]
					],
					"Personalizados" => $personalizados
				]
			];

			// configuracion para publico en general
			if( $data['receptor']['razon_social'] == 'Publico en General' ){
				$CFDi['CFDi']["Receptor"]["usoCfdi"] = ( empty($data['receptor']['uso_cfdi']) )? 'gastos' : $data['receptor']['uso_cfdi'];
				unset($CFDi['CFDi']["Receptor"]['DomicilioFiscal']);
			}

						 

	 	// return $CFDi;
		$cfdi_respuesta = $this->request( $CFDi, 'generarCfdi' );
		return [ 
			'ack' => $cfdi_respuesta, 
			'cfdi' => $CFDi,
			'datos' => $data,
		];
	}

	// Generar CFDI para los cuidadores ( Monto: 20% )
	public function generar_Cfdi_Cuidador( $data=[] ){
		
		// Cargar configuracion de kmimos
			$this->load_config_kmimos();
		
		// Variables de Estructura
			$conf = $this->get_configuracion( [ 
				'servicio_tipo_pago' => $data['servicio']['tipo_pago'] 
			] );
			extract($conf);


			$data['rfc'] = $this->RFC; // RFC Kmimos
			$data['fechaEmision'] = date('Y-m-d H:i:s');
			$data['recibo'] = $data['periodo']['fecha'] . $data['cuidador']['id'];

		// Desglose de la factura
			$partidas = [];
			$montos_generales = [
				'total' => 0,
				'subtotal' => 0,
				'impuesto' => 0,
			];

			$notas_creditos_list = []; 
			foreach ($data['servicio']['desglose'] as $reserva => $desglose) {

				$codigo_sat = $this->db->get_var("SELECT value FROM facturas_configuracion WHERE clave ='".$desglose['tipo']."'" );

				// Calculo comision ( 20% )
					$servicio_total = $desglose['total']; // 100% reserva ( 261.25 )
					$servicio_total = $servicio_total - ( $servicio_total / $comision ); // 20% de kmimos (52.25)

				// Calculo de desglose
					$_subtotal = $servicio_total / $base_iva;// Costo base 	( 45.05 ) 
					$_impuesto = $_subtotal * $tasaCuota ;   // IVA 16%		(  7.20 )
					$_total = $_subtotal + $_impuesto;		 // Total 		( 52.25 )

				// Montos Totales
					$montos_generales['subtotal'] += (float) number_format( $_subtotal, 2, '.', '');
					$montos_generales['impuesto'] += (float) number_format( $_impuesto, 2, '.', '');
					$montos_generales['total'] += (float) number_format( $_total, 2, '.', '');

				// Notas de Credito
					$descuento_partida = 0; 
					$descuento = 0; 
					$notas_credito = $this->db->get_var( "SELECT id, factura, monto FROM notas_creditos WHERE reserva_id = {$reserva} and tipo = 'cuidador' and estatus = 'pendiente'" );
					if( empty($notas_creditos->factura) && $notas_creditos->monto > 0 ){
						$descuento += $notas_creditos->monto;
						$descuento_partida = $notas_creditos->monto;						
						$notas_creditos_list[] = $notas_credito->id;
						$personalizados[] = [
			                "nombreCampo" => "Nota de Credito No. ".$notas_creditos->id,
			                "valor" => $notas_creditos->monto
				        ];	
					}

				// Partida de Factura
					$partidas[] = [
					    "cantidad" => 1,
					    "claveUnidad" => "A9", // A9 - Tarífa  
					    "claveProdServ" => $codigo_sat, /// Agregar datos a una tabla
					    "descripcion" => "Cargo por concepto de gastos administrativos - Reserva No. {$reserva}",
					    "valorUnitario" =>(float) number_format($_subtotal, 2, '.', ''),
					    "descuento" => (float) number_format($descuento_partida, 2, '.', ''),
					    "importe" => (float) number_format( $_subtotal, 2, '.', ''),
					    "Impuestos" => [
					    	0 => [
								"tipo" => "traslado",
								"claveImpuesto" => "IVA",
								"tipoFactor" => "tasa",
								"tasaOCuota" => (float) $tasaCuota,
								"baseImpuesto" => (float) number_format( $_subtotal, 2, '.', ''),
								"importe" => (float) number_format( $_impuesto, 2, '.', '')
						    ]
					    ]				
					];
			}


		// Agregar Campos Personalizados
			$personalizados[] = [
                "nombreCampo" => "Codigo de Cuidador",
                "valor" => $data['cuidador']['id']
	        ];		
			$personalizados[] = [
                "nombreCampo" => "Periodo",
                "valor" => $this->mes( (int)$data['periodo']['mes'] ) . " " . $data['periodo']['anio']
	        ];		

		// Estructura de datos CFDI
			$CFDi = [
				"CFDi" => [
					"modo" => $this->modo,
					"versionEF" => "6.0",
					"serie" => 'PC', //$serie,
					"folioInterno" => $data['recibo'],
					"tipoMoneda" => "MXN",
					"fechaEmision" => $data['fechaEmision'],
					"subTotal" => (float) number_format( $montos_generales['subtotal'], 2, '.', ''), 
					"total" => (float) number_format( $montos_generales['total'], 2, '.', ''),
					"descuentos" => (float) number_format( $descuento, 2, '.', ''),
					"rfc" => $data['rfc'],
					"DatosDePago" => [
						"metodoDePago" => "PUE",
						"formaDePago" => $formaDePago, 
					],
					"Receptor" => [
						"rfc" => $data['cuidador']['rfc'],
						"nombre" => $data['cuidador']['nombre'],
						"usoCfdi" => "gastos"
					],
					"Partidas" => $partidas,
					"Impuestos" => [
						"Totales" => [
							"traslados" =>  (float) number_format( $montos_generales['impuesto'], 2, '.', '')
						],
						"Impuestos" => [
							0 => [
								"tipo" => "traslado",
								"claveImpuesto" => "IVA",
								"tipoFactor" => "tasa",
								"tasaOCuota" => (float)$tasaCuota,
								"importe" =>(float) number_format( $montos_generales['impuesto'], 2, '.', '')
							]
						]
					],
					"Personalizados" => $personalizados
				]
			];

	 	// return $CFDi;
		$cfdi_respuesta = $this->request( $CFDi, 'generarCfdi' );

		$id_notas_creditos = implode(",", $notas_creditos_list);  

		// Actualizar Notas de Creditos
		$ef = json_decode($cfdi_respuesta);
		if( !empty($notas_creditos_list) ){
			if( isset($ef->estatusDocumento) && $ef->estatusDocumento == 'aceptado' ){
				$sql_nc = "UPDATE notas_creditos SET factura = '".$ef->folioInterno."' WHERE id IN ( {$id_notas_creditos} ) ";
				$this->db->query( $sql_nc );
			}
		}

		return [
			'ack' => $cfdi_respuesta,
			'data' => $CFDi
		];
	}

	// Registra el CFDI en la data de Kmimos
	public function guardarCfdi( $CFDi_receptor, $data, $ack ){

		

		if( empty($data) || empty($ack) ){ return false; }		

		$ef = $ack->AckEnlaceFiscal;
		if( isset($ef->estatusDocumento) && $ef->estatusDocumento == 'aceptado' ){
			
			$reserva_id = $this->db->get_var("select reserva_id from facturas where numeroReferencia = '".$ef->numeroReferencia."'", "reserva_id");
			if( $reserva_id <= 0 ){

				$data['comentario'] = ( isset($data['comentario']) )? $data['comentario'] : '';
				$data['total'] = ( isset($data['total']) )? $data['total'] : 0;
				$data['impuesto'] = ( isset($data['impuesto']) )? $data['impuesto'] : 0;
				$data['subtotal'] = ( isset($data['subtotal']) )? $data['subtotal'] : 0;
				$rfc = ( isset($data['receptor']['rfc']) )? $data['receptor']['rfc'] : 0;

				// guardar datos en DB
				$sql = "INSERT INTO facturas ( 
					rfc,
					receptor,
					cuidador_id,
					cliente_id,
					pedido_id,
					reserva_id,
					serie,
					numeroReferencia,
					serieCertificadoSAT,
					serieCertificado,
					folioFiscalUUID,
					fechaTFD,
					fechaGeneracion,
					estado,
					xml,
					urlXml,
					urlPdf,
					urlQR,
					comentario,
					total,
					impuesto,
					subtotal,
					servicio
				 )values(
				 	'".$rfc."',
				 	'".$CFDi_receptor."',
					'".$data['cuidador']['id']."',
					'".$data['cliente']['id']."',
					'".$data['servicio']['id_orden']."',
					'".$ef->folioInterno."',
					'".strtoupper($ef->serie)."',
					'".$ef->numeroReferencia."',
					'".$ef->noSerieCertificadoSAT."',
					'".$ef->noSerieCertificado."',
					'".$ef->folioFiscalUUID."',
					'".$ef->fechaTFD."',
					'".$ef->fechaGeneracionCFDi."',
					'".strtoupper($ef->estadoCFDi)."',
					'".$ef->xmlCFDi."',
					'".$ef->descargaXmlCFDi."',
					'".$ef->descargaArchivoPDF."',
					'".$ef->descargaArchivoQR."',
					'".$data['comentario']."',
					'".$data['total']."',
					'".$data['impuesto']."',
					'".$data['subtotal']."',
					'".$data['servicio']['tipo']."'
				 );
				";
				$this->db->query( $sql );

				// descargar archivo PDF
				$path = $this->raiz.'/wp-content/uploads/facturas/';
				$name_archivo = $ef->folioInterno.'_'.$ef->numeroReferencia;
				$filename = $path . $name_archivo; // [ folioInterno = Reserva_id ]

				sleep(20); // Tiempo para esperar que genere el archivo
				
				$file_pdf_sts = file_put_contents( 
					$filename. '.pdf', 
					$this->descargar_cfdi($ef->descargaArchivoPDF) 
				);
				
				$file_xml_sts = file_put_contents( 
					$filename. '.xml', 
					$this->descargar_cfdi($ef->descargaXmlCFDi) 
				);

				if( $file_pdf_sts ){
					$respuesta = $name_archivo;
				}
			}
		}

		return $respuesta;
	}

	// Descargar - Retorna bufer con la data del archivo
	public function descargar_cfdi($url){
	    $bufer = '';

	    if (ini_get ('allow_url_fopen')) {

	        // La forma facil...

	        $da = fopen ($url, 'r');

	        if (! $da) {
	            //echo "No ha podido leerse el contenido de $url\n";
	            return FALSE;
	        }

	        while (! feof ($da))
	            $bufer .= fread ($da, 4096);

	        fclose ($da);
	    } else {

	        preg_match ('/^\\s*(?:\\w+:\\/{2})?(.*?)(:\\d+)?(\\/.*)$/',
	                    $url, $coincidencias);

	        $dominio = $coincidencias[1];
	        $puerto  = $coincidencias[2];
	        $ruta    = $coincidencias[3];

	        if (! $puerto)
	            $puerto = '80';

	        if (! $ruta)
	            $ruta = '/';

	        $socket = fsockopen ($dominio, $puerto);

	        if (! $socket) {
	            //echo "No pudo establecerse una conexion con $dominio\n";
	            return FALSE;
	        }

	        fwrite ($socket, "GET $ruta HTTP/1.0\n\n");

	        while (! feof ($socket))
	            $bufer .= fread ($socket, 4096);

	        fclose ($socket);

	        $bufer = preg_replace ('/^.*?(\\r?\\n){2}/s', '', $bufer);
	    }

	    return $bufer;
	}

	// Meses en letras
	protected function mes( $id ){
		$mes = [ '', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre' ];
		return $mes[ $id ];
	}

	// Enviar solicitud a enlaceFiscal
	public function request( $aData = [], $accion = '' ){
		if( empty($aData) || empty($accion) ){ return false; }

		// Autenticacion
		$aAuth = [
			'User' => $this->RFC,
			'Pass' => $this->token
		];

		// Endpoint
		$sUrl = $this->url . $accion;

		// Datos
		$sDataJson =  json_encode($aData, JSON_UNESCAPED_UNICODE);
		$nContentLenght = strlen($sDataJson);

		// Configuracion cURL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $sUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $sDataJson);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type: application/json',
		    'x-api-key: ' . $this->xapikey,
		    'Content-Length: ' . $nContentLenght
		));
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, "{$aAuth['User']}:{$aAuth['Pass']}");

		// Ejecutar Solicitud
		$Output = curl_exec($ch);
		curl_close($ch);

		return $Output;
	}
	
}
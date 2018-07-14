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

	// RFC Cuenta principal
	protected $RFC = 'KMI160615640';

	// Credenciales de acceso 
	protected $auth = [
		'produccion' => [
			'token' => '',
			'x-api-key' => ''
		],
		'debug' => [
			'token' => 'c83e1f14de69b963add399109a97a392',
			'x-api-key' => 'e9aT1ajrRh1NyRkzOtDoN1ZEGmIsEKuJ6f3FYyLh'
		]
	];

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

		/*
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
		*/
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
	protected function get_configuracion($param=[]){
		$data = [];

		// Configuracion general
			$_options = $this->db->get_var(
				"SELECT valor FROM kmimos_opciones WHERE clave = 'cfdi_parametros' ",
				'valor' 
			);
			$conf = unserialize($_options);

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

	// Generar CFDI para el Cliente ( Monto: 100% )
	public function generar_Cfdi_Cliente( $data=[] ){

		$data['rfc'] = $this->RFC; // Dato de prueba hasta que se registre los datos del cuidador
		
		// Variables de Estructura
			$conf = $this->get_configuracion( [ 
				'servicio_tipo_pago' => $data['servicio']['tipo_pago'] 
			] );
			extract($conf);

			$data['fechaEmision'] = date('Y-m-d H:i:s');
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
				$data['servicio']['desglose']['descuento'] -= $saldo_favor;
			}

		// Agregar Partida: Variaciones
			if( isset($data['servicio']['variaciones']) && !empty($data['servicio']['variaciones']) ){			
				foreach ($data['servicio']['variaciones'] as $item) {


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

					// Desglose general de la factura
					// *************************************
						$_impuesto += number_format( $impuesto, 2 );
						$_subtotal += number_format($subtotal, 2);
						$_total += $subtotal + $impuesto; 

					// Agregar la partida a la factura
					// *************************************
						$partidas[] = [
						    "cantidad" => $cantidad,
						    "claveUnidad" => "DAY",
						    "claveProdServ" => "90111500", //  9011150.0 por definir
						    "descripcion" => $item[0]." ". $item[1] ." x ".$item[2] ." x $".$item[3],
						    "valorUnitario" =>(float) number_format($base, 2, '.', ''),
						    "importe" => (float) number_format( $subtotal, 2, '.', ''),
							"descuento" => (float) number_format( $data['servicio']['desglose']['descuento'], 2, '.', ''),
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
		
		// Agregar Partida: Transporte
			if( isset($data['servicio']['transporte']) && !empty($data['servicio']['transporte']) ){			
				foreach ($data['servicio']['transporte'] as $item) {

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
						    "claveProdServ" => "90111500", // por definir
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
						    "claveProdServ" => "90111500", // por definir
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
					"folioInterno" => $data['servicio']['id_reserva'],
					"tipoMoneda" => "MXN",
					"fechaEmision" => $data['fechaEmision'], //"2017-02-22 11:03:43",
					"subTotal" => (float) number_format( $_subtotal, 2, '.', ''), //"20.00", ( Sin IVA )
					"total" => (float) number_format( $_total, 2, '.', ''), // "23.20" ( Con IVA )
					"rfc" => $data['rfc'],
					"descuentos" => (float) number_format( $data['servicio']['desglose']['descuento'], 2, '.', ''),
					"DatosDePago" => [
						"metodoDePago" => "PUE",
						"formaDePago" => $formaDePago, 
					],
					"Receptor" => [
						"rfc" => $data['receptor']['rfc'],
						"nombre" => $data['receptor']['nombre'],
						"usoCfdi" => "gastos"
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

	 	// return $CFDi;
		$cfdi_respuesta = $this->request( $CFDi, 'generarCfdi' );
		return [ 
			'ack' => $cfdi_respuesta, 
			'param' => $CFDi,  
		];
	}

	// Generar CFDI para los cuidadores ( Monto: 20% )
	public function generar_Cfdi_Cuidador( $data=[] ){

		// Variables de Estructura
			$conf = $this->get_configuracion( [ 
				'servicio_tipo_pago' => $data['servicio']['tipo_pago'] 
			] );
			extract($conf);

			$data['rfc'] = $this->RFC; // RFC Kmimos
			$data['fechaEmision'] = date('Y-m-d H:i:s');

		// Desglose de la factura
			$servicio_total = $data['servicio']['desglose']['total']; // 100% reserva ( 261.25 )
			$servicio_total = $servicio_total - ( $servicio_total / 1.25 ); // 20% de kmimos (52.25)

			$_subtotal = $servicio_total / $base_iva;// Costo base 	( 45.05 ) 
			$_impuesto = $_subtotal * $tasaCuota ;   // IVA 16%		(  7.20 )
			$_total = $_subtotal + $_impuesto;		 // Total 		( 52.25 )

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
					"serie" => $serie,
					"folioInterno" => $data['servicio']['id_reserva']."021",
					"tipoMoneda" => "MXN",
					"fechaEmision" => $data['fechaEmision'],
					"subTotal" => (float) number_format( $_subtotal, 2, '.', ''), 
					"total" => (float) number_format( $_total, 2, '.', ''),
					"rfc" => $data['rfc'],
					"DatosDePago" => [
						"metodoDePago" => "PUE",
						"formaDePago" => $formaDePago, 
					],
					"Receptor" => [
						"rfc" => $data['receptor']['rfc'],
						"nombre" => $data['cuidador']['nombre'],
						"usoCfdi" => "gastos"
					],
					"Partidas" => [
						0 => [
							    "cantidad" => 1,
							    "claveUnidad" => "A9", // A9 - Tarífa  
							    "claveProdServ" => "90111500", 
							    "descripcion" => "Cargo por concepto de gastos administrativos",
							    "valorUnitario" =>(float) number_format($_subtotal, 2, '.', ''),
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
							]
					],
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

	 	// return $CFDi;
		$cfdi_respuesta = $this->request( $CFDi, 'generarCfdi' );
		return [ 
			'ack' => $cfdi_respuesta, 
			'param' => $CFDi,  
		];
	}

	// Registra el CFDI en la data de Kmimos
	public function guardarCfdi( $CFDi_receptor, $data, $ack ){
		if( empty($data) || empty($ack) ){ return false; }		

		$ef = $ack->AckEnlaceFiscal;
		if( isset($ef->estatusDocumento) && $ef->estatusDocumento == 'aceptado' ){
			
			$reserva_id = $this->db->get_var("select reserva_id from facturas where numeroReferencia = '".$ef->numeroReferencia."'", "reserva_id");
			if( $reserva_id <= 0 ){
	
				// guardar datos en DB
				$sql = "INSERT INTO facturas ( 
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
					urlQR
				 )values(
				 	'".$CFDi_receptor."',
					".$data['cuidador']['id'].",
					".$data['cliente']['id'].",
					".$data['servicio']['id_orden'].",
					".$ef->folioInterno.",
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
					'".$ef->descargaArchivoQR."'
				 );
				";
				$this->db->query( $sql );

				// descargar archivo PDF
				$path = $this->raiz.'/wp-content/uploads/facturas/';
				$filename = $path . $ef->folioInterno.'_'.$ef->numeroReferencia; // [ folioInterno = Reserva_id ]

				$file_pdf_sts = file_put_contents( 
					$filename. '.pdf', 
					$this->descargar_cfdi($ef->descargaArchivoPDF) 
				);
				
				$file_xml_sts = file_put_contents( 
					$filename. '.xml', 
					$this->descargar_cfdi($ef->descargaXmlCFDi) 
				);

				if( $file_pdf_sts ){
					$respuesta = $filename. '.pdf';
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

	// Enviar solicitud a enlaceFiscal
	public function request( $aData = [], $accion = '' ){
		if( empty($aData) || empty($accion) ){ return false; }

		// Autenticacion
		$param = $this->auth[ $this->modo ];
		$aAuth = [
			'User' => $this->RFC,
			'Pass' => $param['token']
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
		    'x-api-key: ' . $param['x-api-key'],
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
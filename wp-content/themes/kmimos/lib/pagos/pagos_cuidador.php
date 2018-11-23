<?php
	error_reporting(E_ERROR);
//date_default_timezone_set('America/Mexico_City');
//date_default_timezone_set('America/Caracas');

$pagos = new PagoCuidador();

class PagoCuidador {
	
	public $db;

	protected $comision_retiro = 10;

	public function PagoCuidador(){
		$this->raiz = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
		if( !isset($db) || is_string( $db ) ){
			include($this->raiz.'/vlz_config.php');
			if( !class_exists('db') ){
				include($this->raiz.'/wp-content/themes/kmimos/procesos/funciones/db.php');
			}
		    $db = new db( new mysqli($host, $user, $pass, $db) );
		}

		$this->db = $db;
	}

	/// *************************
	/// Pago al cuidador
	/// *************************

	public function get_pago_by_user( $user_id ){
		$result=[];

		// Buscar reservas en progreso
		$reservas = $this->get_reserva_activas_by_user( $user_id );

		$hoy = date("Y-m-d H:i:s");

		foreach ($reservas as $row) {
			$porcentaje = 0.35; // porcentaje retencion ( sin datos de bancos )
			$retencion_por_impuestos = 0;
			$total_a_pagar = 0;

			$_total_noches = $this->diferenciaDias( $row->booking_start, $row->booking_end );
			$total_noches = $_total_noches['dia'];
			
			$_dias = $this->diferenciaDias( $row->booking_start, $hoy );
			$dias = $_dias['dia'];

			// Calculo de pago cuidador
				$saldo_cuidador = $this->calculo_pago_cuidador( 
					$row->reserva_id,
					$row->total
				);
				$monto = $saldo_cuidador;

			// Debitar Notas de credito 
				$notas_creditos = $this->get_NC( $user_id );
				if( $notas_creditos > 0 ){
					$monto -= $notas_creditos;
				}

			// Calcular 35% de reserva por impuestos
				$retencion_por_impuestos = $monto * $porcentaje;

			// Validar Rango de fechas
				$fin_reserva = date("Y-m-d H:i:s", $row->booking_end );
				if( $fin_reserva <= $hoy ){
					$total_a_pagar = $monto;
				}else{

					// Desglose servicios 
						$des = $this->db->get_var(
							"SELECT meta_value FROM wp_postmeta WHERE 
								meta_key = '_booking_desglose'
								AND post_id = ".$row->reserva_id
						);

						$desglose = unserialize( utf8_encode($des) );
						$total_servicio = 0;
						$total_adicional = 0;	

						if( !empty($desglose) ){				
							# monto servicio principal
							if( count($desglose['variaciones']) >0 ){
								foreach ($desglose['variaciones'] as $item) {
									$item[4] = str_replace(".", "", $item[4]);
									$item[4] = str_replace(",", ".", $item[4]);
									$total_servicio += $item[4];
								}
							}

							# monto servicio adicional y transporte
							if( count($desglose['adicionales']) >0 ){
								foreach ($desglose['adicionales'] as $item) {
									$item[3] = str_replace(".", "", $item[3]);
									$item[3] = str_replace(",", ".", $item[3]);
									$total_adicional += $item[3];							
								}
							}
							if( count($desglose['transporte']) >0 ){
								foreach ($desglose['transporte'] as $item) {
									$item[3] = str_replace(".", "", $item[3]);
									$item[3] = str_replace(",", ".", $item[3]);
									$total_adicional += $item[3];							
								}
							}
						}

					// Restar servicios adicionales al pago
						$monto -= $total_adicional;

					// Calculo No. Dias transcurridos
						if( $dias > $total_noches ){
							$dias = $total_noches;
						}

					// Calcular pago por dia
						$pago_diario = 0;
						if( $monto > 0 ){
							$pago_diario = $monto / $total_noches;
						}

					// Calcular el total a pagar hasta hoy

						$total_a_pagar = $pago_diario * $dias;
				}


			// Retener el 35% si no posee los datos de facturacion
				$datos_bancarios = $this->db->get_var( "SELECT * FROM cuidadores WHERE user_id = {$user_id}" );
				if( empty($datos_bancarios) ){
					// Retener 35%
					$tiene_retencion = $this->db->get_var("SELECT id FROM cuidadores_transacciones WHERE user_id = {$user_id} AND reserva_id=".$row->reserva_id." AND tipo = 'RETENCION'
					");
					if( $tiene_retencion > 0 ){
						// liberar retencion 
					}else{
						$this->db->query( "INSERT INTO `cuidadores_transacciones` (
								`tipo`,
								`user_id`,
								`reserva_id`,
								`referencia`,
								`descripcion`,
								`monto`
							) VALUES (
								'RETENCION', 
								{$user_id}, 
								".$row->reserva_id.", 
								'R".$row->reserva_id."', 
								'Retenci&oacute;n por datos de facturaci&oacute;n Reserva #".$row->reserva_id."', 
								{$retencion_por_impuestos}
						);");
					}
				}

			// Debitar pagos realizados
				$total_transacciones = $this->total_transacciones_by_reserva( $user_id, $row->reserva_id );
				$total_a_pagar -= $total_transacciones;

			// Actualizar Saldo disponible en cuidadores_reservas
				$existe = $this->db->get_var("SELECT id FROM cuidadores_reservas WHERE user_id = {$user_id} AND reserva_id=".$row->reserva_id 
				);

				$estatus = 'pendiente';
				if( $monto == $total_transacciones ){
					$estatus = 'pagado';
				}

				$sql = '';
				if( $total_a_pagar > 0 ){				
					if( $existe > 0 ){
						// actualizar montos
						$sql = "UPDATE `cuidadores_reservas` SET 
							disponible = {$total_a_pagar},
							estatus = '{$estatus}'
						WHERE user_id = {$user_id} AND reserva_id=".$row->reserva_id;
					}else{
						// insertar reserva
						$sql = "INSERT INTO `cuidadores_reservas`( 
							`user_id`,
							`reserva_id`,
							`checkin`,
							`checkout`,
							`total_dias`,
							`disponible`,
							`total_reserva`,
							`estatus`
						) VALUES (
							{$user_id},
							".$row->reserva_id.",
							'".$row->booking_start."',
							'".$row->booking_end."',
							{$total_noches},
							{$total_a_pagar},
							'{$total_a_pagar}',
							'{$estatus}'
						);";
					}
					$this->db->query( $sql );
				}
		}

		return $total_a_pagar;
	}

	public function cargar_retiros( $user_id, $monto, $descripcion, $id_admin = 0 ){

		$disponible = $this->detalle_disponible( $user_id );
		$reservas=[];
		$reservas_pagos=[];
		if( $disponible >= $monto ){
			asort($disponible['detalle']);
			$resto = $monto;
			foreach ( $disponible['detalle'] as $reserva => $value ) {
				if( $value >= $resto ){
					$reservas[ $reserva ] = $resto;
					$reservas_pagos[] = ['reserva'=>$reserva, "monto"=>$resto];
					$resto = 0;
					break;
				}
				if( $resto > $value ){
					$resto -= $value;
					if( $resto < 0 ){
						$value += $resto;
					}
					$reservas[ $reserva ] = $value;
					$reservas_pagos[] = ['reserva'=>$reserva, "monto"=>$value];
				}
			}
		}

		if( !empty($reservas) ){
			$monto_pago = $monto;
			$tipo = 'pago_k';
			if( $id_admin == 0){
				$monto_pago = $monto - $this->comision_retiro;
				$tipo = 'pago_c';
			}

			// Cargar Transacciones
			$sql = "INSERT INTO `cuidadores_transacciones` ( 
				`tipo`,
				`user_id`,
				`referencia`,
				`descripcion`,
				`monto`,
				`reservas`,
				comision
			) VALUES (
				'{$tipo}', 
				{$user_id},
				'',
				'Retiro de saldo', 
				{$monto}, 
				'".serialize($reservas)."',
				".$this->comision_retiro."
			);";
			$this->db->query($sql);
			$tra_id = $this->db->insert_id();

			// Cargar pagos
			$cuidador = $this->db->get_var("SELECT banco FROM cuidadores WHERE user_id = {$user_id}");
			$banco = unserialize($cuidador);
			$pago_id =0;
			$existe_retiro = $this->db->get_row(
				"SELECT * from cuidadores_pagos where user_id = {$user_id} and estatus = 'pendiente'");
			if( isset($existe_retiro->id) && $existe_retiro->id > 0 ){

				// italo Ajustar detalle de reserva (merge)

				$sql_pago = "UPDATE cuidadores_pagos SET 
						total = total + {$monto_pago}
					WHERE id = ".$existe_retiro->id
				;
				$this->db->query($sql_pago);
				
				$pago_id = $existe_retiro->id;
			}else{
				$sql_pago = "
					INSERT INTO `cuidadores_pagos`( 
						`admin_id`,
						`user_id`,
						`total`,
						`cantidad`,
						`estatus`,
						`detalle`,
						`observaciones`,
						`cuenta`,
						`titular`,
						`banco`
					) VALUES (
						{$id_admin},
						{$user_id},
						{$monto_pago},
						".count($reservas_pagos).",
						'pendiente',
						'".serialize($reservas_pagos)."',
						'Solicitud de retiro por el cuidador ( Comision: $10 )',
						'".$banco['cuenta']."',
						'".$banco['titular']."',
						'".$banco['banco']."'
					);
				";
				$this->db->query($sql_pago);
				$pago_id = $this->db->insert_id();
			}

			return ['transaccion_id'=>$tra_id, 'pago_id'=>$pago_id, 'sql'=>$sql_pago];
		}

		return 0;
	}

	public function registrar_pago( $user_id, $total, $openpay_id, $comentario='' ){
		// buscar solicitudes de pago del cuidador
		$solicitudes = $this->db->get_results( "
			SELECT * 
			FROM cuidadores_pagos 
			WHERE user_id = {$user_id} and estatus = 'pendiente'
		");

		// debitar y asignar pago a las solicitudes
		if( !empty($solicitudes) ){
			$resto = $total;
			foreach ($solicitudes as $solicitud) {
				if( $resto > 0 ){
					// Si el monto es mayor o igual
					if( $resto >= $solicitud->total ){
						$resto -= $solicitud->total;
						$sql = "UPDATE cuidadores_pagos SET 
								estatus = 'in_progress', 
								openpay_id='$openpay_id' 
								observaciones=concat(observaciones,'<br>', {$comentario})
							WHERE id = ".$solicitud->id;
						$this->db->query($sql);
					}else{
					// Si el monto es menor 
						# diferencia
						$solicitud->total -= $resto;

						# cambiar referencia y estatus: modificacion
						$sql_update = "UPDATE cuidadores_pagos SET 
								estatus = 'in_progress', 
								total = {$resto},
								openpay_id='$openpay_id',
								observaciones=concat(observaciones,'<br>', '{$comentario}')
							WHERE id = ".$solicitud->id;
						$this->db->query($sql_update);
						$sql_insert = "INSERT INTO `cuidadores_pagos`(
							`admin_id`, 
							`user_id`, 
							`total`, 
							`cantidad`, 
							`estatus`, 
							`detalle`, 
							`autorizado`, 
							`openpay_id`, 
							`observaciones`, 
							`cuenta`, 
							`titular`, 
							`banco`
						) VALUES (
							".$solicitud->admin_id.", 
							".$solicitud->user_id.", 
							".$solicitud->total.", 
							".$solicitud->cantidad.", 
							'pendiente',
							'".$solicitud->detalle."', 
							'".$solicitud->autorizado."', 
							'".$openpay_id."', 
							'".$solicitud->observaciones."', 
							'".$solicitud->cuenta."', 
							'".$solicitud->titular."', 
							'".$solicitud->banco."'
						);";
						$this->db->query($sql_insert);
						$resto = 0;
 
						#generar una solicitud por el resto
							# dividir reservas

						#generar una solicitud por la diferencia de la solicitud
							# dividir reservas diferencia					
					}
				}
			}


		}
	}

	/// *************************
	/// Balance
	/// *************************

	public function balance( $user_id ){
		$hoy = date( "Y/m/d H:i:s" );
		$habilitado = true;
		$dia_habil = $hoy;

		$total_NC = $this->get_NC( $user_id );
		$total_en_progreso = $this->pagos_en_progreso( $user_id );
		$total_disponible = $this->detalle_disponible( $user_id );
		$proximo_pago = $this->proximo_pago( $user_id );
		$total_retenido = $this->total_retenido( $user_id );
		$no_disponible = $this->pagos_no_disponible();

		$ultimo_retiro = $this->ultimo_retiro( $user_id );
		if( !empty($ultimo_retiro) ){
			$dia_habil = date( "Y/m/d H:i:s", strtotime($ultimo_retiro." +24 hours" ) );
			if( $hoy < $dia_habil ){
				$habilitado = false;
			}
		}

		$pay = (object)[
			"disponible" 		=> $total_disponible['total'],
			"no_disponible" 	=> $no_disponible,
			"proximo_pago"		=> $proximo_pago,
			"en_progreso"		=> $total_en_progreso,
			"retenido"			=> $total_retenido,
			"retiro"=>(object)[
				"habilitado"		=> $habilitado,
				"ultimo_retiro" 	=> $ultimo_retiro,
				"tiempo_restante"	=> $dia_habil,
			],
			"detalle" => $total_disponible['detalle'],
		];
		return $pay;
	}

	public function proximo_pago( $user_id ){
		$total_disponible = $this->detalle_disponible( $user_id );
		$monto = $total_disponible['total'];
		if( $monto >= 1500 && $monto < 3000 ){
			$monto = $monto/2;
		}else if( $monto > 3000 ){
			$monto = $monto/3;
		}
		return $monto;
	}

	public function detalle_disponible( $user_id ){
		$reservas = $this->db->get_results( "SELECT * FROM cuidadores_reservas WHERE user_id = {$user_id} and estatus='pendiente'" );
		$total = 0;
		$hoy = date('Y-m-d');

		$list = ['total'=>0, 'detalle'=>[]];

		foreach ($reservas as $reserva) {
			$tr = $this->total_transacciones_by_reserva( $user_id, $reserva->reserva_id );
			$nc = $this->get_NC( $user_id, $reserva->reserva_id );
			$total = $reserva->total - ( $tr + $nc );

			if( $total > 0 ){
				$list['detalle'][$reserva->reserva_id] = $total;
				$list['total'] += $total;
			}
		}

		return ($list['total']==0)? []: $list;
	}

	protected function total_disponible( $user_id ){
		$reservas = $this->db->get_results( "SELECT * FROM cuidadores_reservas WHERE user_id = {$user_id} and estatus='pendiente'" );
		$total = 0;
		$nc = $this->get_NC( $user_id );
		$hoy = date('Y-m-d');

		foreach ($reservas as $reserva) {

			$dias = $this->diferenciaDias( $reserva->checkin, $hoy );			
			if( $dias > $reserva->total_dias ){
				$dias = $reserva->total_dias;
			}

			$pago_por_noches = $reserva->total_reserva / $reserva->total_dias;

			$monto = $pago_por_noches * $dias;

			$tr = $this->total_transacciones_by_reserva( $user_id, $reserva->reserva_id );

			$total += $monto - $tr;

		}

		$total -= $nc;

		return ( is_numeric($total) )? $total : 0;
	}

	protected function pagos_en_progreso( $user_id ){
		$total = $this->db->get_var( "SELECT SUM(total) as total FROM cuidadores_pagos WHERE user_id = {$user_id} and estatus = 'in_progress'" );
		return ( $total > 0 )? $total : 0;
	}

	protected function ultimo_retiro( $user_id ){
		$sql = "SELECT fecha FROM cuidadores_transacciones WHERE user_id = {$user_id} AND tipo='pago_c' ORDER BY id desc limit 1";
		$fecha = $this->db->get_var( $sql );
		return ( !empty($fecha) )? date("Y/m/d H:i:s", strtotime($fecha)) : '' ;
	}

	protected function total_retenido( $user_id ){
		$total = $this->db->get_var("SELECT SUM(total_reserva) as total 
				FROM cuidadores_reservas 
				WHERE user_id = {$user_id} AND estatus = 'pendiente' ");
		$pagado = $this->db->get_var("SELECT SUM(disponible) as total 
				FROM cuidadores_reservas 
				WHERE user_id = {$user_id} AND estatus = 'pendiente' ");
		$cuidador  = $total / 1.25; 
		$pendiente = $cuidador - $pagado;
		return ( $pendiente > 0 )? $pendiente : 0 ;
	}

	protected function get_total_generado( $user_id ){
		$total = $this->db->get_var( "SELECT SUM(total_reserva) as total FROM cuidadores_reservas WHERE user_id = {$user_id} " );
		return ( $total > 0 )? $total : 0 ;
	}

	protected function total_transacciones_by_reserva( $user_id, $reserva_id ){
		$list = $this->db->get_results( "SELECT * FROM cuidadores_transacciones WHERE user_id = {$user_id} AND reservas like '%i:{$reserva_id}%' " );
		$total = 0;
		foreach ( $list as $row ) {
			if( !empty($row->reservas) ){
				$data = unserialize($row->reservas);
				if( array_key_exists($reserva_id, $data) ){
					$total += $data[$reserva_id];
				}
			}
		}
		return ( $total > 0 )? $total : 0 ;
	}

	protected function get_NC( $user_id, $reserva=0 ){
		$where_reserva = ( $reserva > 0 )? ' AND reserva_id = '.$reserva : '' ;
		$total = $this->db->get_var( "SELECT SUM(monto) as total FROM notas_creditos WHERE user_id = {$user_id} and tipo='cuidador' and estatus='pendiente' {$where_reserva}");
		return ( $total > 0 )? $total : 0;
	}

	protected function pagos_no_disponible(){
		$hoy = date('Y-m-d');
		$desde = date('Y-m-d', strtotime( $hoy." +1 day" ));
		$hasta = date('Y-m-d', strtotime( $hoy." +1 year" ));

		$reservas = $this->getReservas( $desde, $hasta );
		$total = 0;
		foreach ($reservas as $reserva) {
			$total += $reserva->total;
		}
		return ( $total > 0 )? $total : 0;
	}

	/// *************************
	/// Reporte Backpanel
	/// *************************

	public function getPagosPorAprobar( $desde, $hasta ){
		if( empty($desde) || empty($hasta) ){
			return [];
		}

		$reservas = $this->getReservas($desde, $hasta);

		$obj_pagos = [];
		$pagos = [];
		$detalle = [];
		$count = 1;

		$dev = [];
		if( !empty($reservas) ){
			foreach ($reservas as $row) {

				$total = 0;
                $existe = $this->db->get_row( 'SELECT * FROM cuidadores_reservas WHERE reserva_id = '.$row->reserva_id );

                if( !isset( $existe->id ) ){

					// Datos del cuidador
						$cuidador = $this->db->get_row('SELECT * FROM cuidadores WHERE user_id = '.$row->cuidador_id);

						$pagos[ $row->cuidador_id ]['fecha_creacion'] = date('Y-m-d', strtotime("now"));
						$pagos[ $row->cuidador_id ]['user_id'] = $row->cuidador_id; 
						$pagos[ $row->cuidador_id ]['nombre'] = $cuidador->nombre ; 
						$pagos[ $row->cuidador_id ]['apellido'] = $cuidador->apellido ; 
						$pagos[ $row->cuidador_id ]['estatus'] = '';

					// Meta de padido
						$meta_pedido = $this->getMetaPedido( $row->pedido_id );

					// Metodos de pago
						$method_payment = '';
						if( !empty($meta_pedido['_payment_method_title']) ){
							$method_payment = $meta_pedido['_payment_method_title']; 
						}else{
							if( !empty($meta_reserva['modificacion_de']) ){
								$method_payment = 'Saldo a favor' ; 
							}else{
								$method_payment = 'Manual'; 
							}
						}

					// Calculo por reserva
						$monto = $this->calculo_pago_cuidador( 
							$row->reserva_id,
							$row->total
						);

					// Transacciones y Notas de Credito
						$nc = $this->get_NC( $row->cuidador_id, $row->reserva_id);
						$tr = $this->total_transacciones_by_reserva( $row->cuidador_id, $row->reserva_id);

					// Cualcular saldo
						$monto -= ( $nc + $tr );

					// Separadores
						if( $count == 4 ){
							$separador = '<br><br>';
							$count=1;
						}else{
							$separador = '';
							$count++;
						}

					// Detalle de Reservas	
						if( !isset($pagos[ $row->cuidador_id ]['detalle']) ){
							$pagos[ $row->cuidador_id ]['detalle']=[]; 
						}
						if( $monto > 0 ){
							$pagos[ $row->cuidador_id ]['detalle'][$row->reserva_id] = [
								'reserva'=>$row->reserva_id,
								'monto'=>$monto,
								'booking_start' => date('Y-m-d', strtotime($row->booking_start) ),
								'booking_end' => date('Y-m-d', strtotime($row->booking_end) ),
							];
					    }

						if( array_key_exists('total', $pagos[ $row->cuidador_id ]) ){
							$monto = $pagos[ $row->cuidador_id ]['total'] + $monto;
						}

						if( array_key_exists('total_row', $pagos[ $row->cuidador_id ]) ){
							$total = $pagos[ $row->cuidador_id ]['total_row'] + 1;
						}

					// Total a pagar
						$pagos[ $row->cuidador_id ]['total'] = $monto;
						$pagos[ $row->cuidador_id ]['cantidad'] = count($pagos[ $row->cuidador_id ]['detalle']);

					// Object
						if( $monto > 0 ){
							$obj_pagos[$row->cuidador_id ] = (object) $pagos[$row->cuidador_id ];
						}
				}
			}
		}
		
		return $obj_pagos;
	}

	public function getPagosPendientes(){

    	$sql = "SELECT * FROM cuidadores_pagos WHERE estatus = 'pendiente'";
    	$reservas = $this->db->get_results($sql);

    	$disponible = 0;

    	foreach ($reservas as $row) {
    		$disponible += $row->total;
    		$items = unserialize($row->detalle);
    		foreach ($items as $item) {
				$_pagos[ $row->user_id ]['detalle'][$item['reserva']]['reserva'] = $item['reserva'];
    			if( array_key_exists($item['reserva'], $detalle) ){
    				$_pagos[ $row->user_id ]['detalle'][$item['reserva']]['monto'] += $item['monto'];
    			}else{
    				$_pagos[ $row->user_id ]['detalle'][$item['reserva']]['monto'] = $item['monto'];
    			}
    		}

	        $cuidador = $this->db->get_row( "SELECT nombre, apellido 
	        	FROM cuidadores WHERE user_id = ".$row->user_id);

	        $_pagos[ $row->user_id ]['user_id'] = $row->user_id;
	        $_pagos[ $row->user_id ]['nombre'] = $cuidador->nombre;
	        $_pagos[ $row->user_id ]['apellido'] = $cuidador->apellido;
	        $_pagos[ $row->user_id ]['total'] = $disponible;
	        $_pagos[ $row->user_id ]['cantidad'] = count($_pagos[ $row->user_id ]['detalle']);
	        $_pagos[ $row->user_id ]['estatus'] = '';
	        $_pagos[ $row->user_id ]['fecha_creacion'] = date('Y-m-d', strtotime("now"));
    	}

        $_pagos[ $row->user_id ] = (object) $_pagos[ $row->user_id ];

        if( !empty($_pagos) ){
        	$pagos = (object) $_pagos;
        }
        return (object) $pagos;
	}

	public function getPagoCompletados( $desde, $hasta ){
		$where = " WHERE estatus = 'completed' ";
		if( !empty($desde) && !empty($hasta) ){
			$where = " and fecha_creacion >= '{$desde} 00:00:00' and fecha_creacion <= '{$hasta} 23:59:59' ";
		}
		$sql = "SELECT * FROM cuidadores_pagos {$where} order by fecha_creacion asc";
		return $this->db->get_results($sql);
	}	

	public function getPagoGenerados( $desde, $hasta ){
		$where = " WHERE estatus <> 'completed' and estatus <> 'pendiente' ";
		if( !empty($desde) && !empty($hasta) ){
			$where = " and fecha_creacion >= '{$desde} 00:00:00' and fecha_creacion <= '{$hasta} 23:59:59' ";
		}
		$sql = "SELECT * FROM cuidadores_pagos {$where} order by fecha_creacion asc";
 	
		return $this->db->get_results($sql);
	}

	public function getPagoGeneradosTotal( $desde, $hasta ){
		$where = " WHERE estatus = 'in_progress'";
		/*
		if( !empty($desde) && !empty($hasta) ){
			$where .= " and fecha_creacion >= '{$desde} 00:00:00' and fecha_creacion <= '{$hasta} 23:59:59' ";
		}
		*/
		$sql = "SELECT sum(total) as total 
			FROM cuidadores_pagos {$where} 
			ORDER BY fecha_creacion ASC
		";
		return $this->db->get_results($sql);
	}

	/// *************************
	/// Funciones
	/// *************************

	public function updatePagoCuidador($desde, $hasta, $user_id=0){
		if( empty($desde) || empty($hasta) ){
			return [];
		}
		$reservas = $this->getReservas($desde, $hasta, $user_id);

		$obj_pagos = [];
		$detalle = [];
		$pagos = [];
		$count = 1;

		$dev = [];
		if( !empty($reservas) ){
			foreach ($reservas as $row) {

				$total = 0;
				$condicion = 's:7:"reserva";s:'.strlen($row->reserva_id).':"'.$row->reserva_id.'";';
				$reserva_procesada = $this->db->get_row("SELECT * FROM cuidadores_pagos WHERE detalle like '%{$condicion}%' limit 1" );
 
				if( !isset($reserva_procesada->id) ){

					$cuidador = $this->db->get_row('SELECT * FROM cuidadores WHERE user_id = '.$row->cuidador_id);
					if( isset($cuidador->nombre) || isset($cuidador->apellido) ){
							
					// Datos del cuidador
						/*
						$pagos[ $row->cuidador_id ]['booking_start'] = date('Y-m-d', strtotime($row->booking_start));
						$pagos[ $row->cuidador_id ]['booking_end'] = date('Y-m-d', strtotime($row->booking_start));
						*/
						$pagos[ $row->cuidador_id ]['fecha_creacion'] = date('Y-m-d', strtotime("now"));
						$pagos[ $row->cuidador_id ]['user_id'] = $row->cuidador_id; 
						$pagos[ $row->cuidador_id ]['nombre'] = $cuidador->nombre ; 
						$pagos[ $row->cuidador_id ]['apellido'] = $cuidador->apellido ; 
						$pagos[ $row->cuidador_id ]['estatus'] = '';

					// Meta de padido
						$meta_pedido = $this->getMetaPedido( $row->pedido_id );

					// Metodos de pago
						$method_payment = '';
						if( !empty($meta_pedido['_payment_method_title']) ){
							$method_payment = $meta_pedido['_payment_method_title']; 
						}else{
							if( !empty($meta_reserva['modificacion_de']) ){
								$method_payment = 'Saldo a favor' ; 
							}else{
								$method_payment = 'Manual'; 
							}
						}

					// Calculo por reserva
						$monto = $this->calculo_pago_cuidador( 
							$row->reserva_id,
							$row->total
						);
 
						if( $count == 4 ){
							$separador = '<br><br>';
							$count=1;
						}else{
							$separador = '';
							$count++;
						}
  
						if( !isset($pagos[ $row->cuidador_id ]['detalle']) ){
							$pagos[ $row->cuidador_id ]['detalle']=[]; 
						}
						if( $monto > 0 ){
							$pagos[ $row->cuidador_id ]['detalle'][$row->reserva_id] = [
								'reserva'=>$row->reserva_id,
								'pedido'=>$row->pedido_id,
								'monto'=>$monto,
							];

							// 
					    }

						if( array_key_exists('total', $pagos[ $row->cuidador_id ]) ){
							$monto = $pagos[ $row->cuidador_id ]['total'] + $monto;
						}

						if( array_key_exists('total_row', $pagos[ $row->cuidador_id ]) ){
							$total = $pagos[ $row->cuidador_id ]['total_row'] + 1;
						}

					// Total a pagar
						$pagos[ $row->cuidador_id ]['total'] = $monto;
						$pagos[ $row->cuidador_id ]['cantidad'] = count($pagos[ $row->cuidador_id ]['detalle']);

					// Object
						if( $monto > 0 ){
							$obj_pagos[ $row->cuidador_id ] = (object) $pagos[ $row->cuidador_id ];
						}
					}
				}
			}
		}
		
		return $obj_pagos;
	}

	public function getRangoFechas(){
    	$d = getdate();
    	$strFecha = strtotime( date("Y-m-d", $d[0]) );
		$fecha = $this->inicio_fin_semana( $strFecha, 'tue' );
		return $fecha;
	}

	public function dia_semana( $dia, $periodo ){
    	$d = getdate();
		$hoy = $d[0];

	    $_periodo = [
	    	'semanal' => 7,
	    	'quincenal' => 15,
	    	'mensual' => 30,
	    ];

	    if( $periodo == 'semanal' ){	    	
			$dias = [
				'lunes' => 'Monday',
				'martes' => 'Tuesday',
				'miercoles' => 'Wednesday',
				'jueves' => 'Thursday',
				'viernes' => 'Friday',
			];
			$lunes = strtotime('next mon', $hoy);
			if( strtolower($dia) == 'lunes' ){
				$fecha = date('Y-m-d',$lunes);
			}else{
				$fecha = date('Y-m-d',strtotime('next '.$dias[ strtolower($dia) ], $lunes));
			}
	    }else{	
	    	$hoy = date('Y-m-d H:i:s');
			$fecha = date('Y-m-d',strtotime($hoy.' +'.$_periodo[ strtolower($periodo) ].' day') );
	    }
		return $fecha;
	}

	protected function inicio_fin_semana( $date, $str_to_date  ){

	    $diaInicio=$str_to_date;

	    $fecha['ini'] = date('Y-m-d',strtotime('last '.$diaInicio, $date));
	    $fecha['fin'] = date('Y-m-d',$date);
	    
	    $fecha['min'] = $fecha['ini'];
	    $fecha['max'] = date('Y-m-d',strtotime($diaInicio." +30"));

	    if( date("l",$date) == 'Tuesday' ){
	        $fecha['fin'] = date('Y-m-d',strtotime('last mon', $date));
	    }

	    return $fecha;
	}

	public function calculo_pago_cuidador( $reserva_id, $total ){
 
		$pago_cuidador = $total / 1.25;
		$pago_kmimos = $total - $pago_cuidador;

		// Cupones de la reserva
			$cupones = $this->db->get_results("SELECT items.order_item_name as name, meta.meta_value as monto  
            FROM `wp_woocommerce_order_items` as items 
                INNER JOIN wp_woocommerce_order_itemmeta as meta ON meta.order_item_id = items.order_item_id
                INNER JOIN wp_posts as p ON p.ID = ".$reserva_id." and p.post_type = 'wc_booking' 
                WHERE meta.meta_key = 'discount_amount'
                    and items.`order_id` = p.post_parent
                    and not items.order_item_name like ('saldo-%')
            ;");

		// Datos de los cupones
			$meta_cupon = [];
			if( !empty($cupones) ){
				foreach ($cupones as $cupon) {


					$cupon_id = $this->db->get_var("SELECT ID FROM wp_posts WHERE post_title = '".$cupon->name."' ");
					$metas =  $this->db->get_results("SELECT meta_key, meta_value FROM wp_postmeta WHERE meta_key like 'descuento%' and post_id = ".$cupon_id );

					$meta_cupon[ $cupon->name ][ 'total' ] = $cupon->monto; 
					if( $cupon->monto > 0 ){
						if( !empty($metas) ){
							foreach ($metas as $meta) {
								$meta_cupon[ $cupon->name ][ $meta->meta_key ] = $meta->meta_value;
							}
						}
 
						// tipo de descuento
						$_cupon = $meta_cupon[ $cupon->name ];
 						
						$_cupon['descuento_tipo'] = ( isset($_cupon['descuento_tipo']) )? $_cupon['descuento_tipo'] : ''; 
						switch ( strtolower($_cupon['descuento_tipo']) ) {
							case 'kmimos':
								if( $pago_kmimos < $_cupon['total'] ){
									$diferencia = $_cupon['total'] - $pago_kmimos;
									$pago_cuidador -= $diferencia;
								}else{
									$pago_kmimos -= $_cupon['total'];
								}
								break;
							case 'cuidador':
								if( $pago_cuidador < $_cupon['total'] ){
									$pago_cuidador = 0;
								}else{
									$pago_cuidador -= $_cupon['total'];
								}
								break;						
							case 'compartido':
								// Calculo de descuentos
								$descuento_kmimos = ( $_cupon['descuento_kmimos'] * $_cupon['total'] ) / 100;
								$descuento_cuidador = ( $_cupon['descuento_cuidador'] * $_cupon['total'] ) / 100;
								if( $pago_cuidador <= $descuento_cuidador ){
									$pago_cuidador = 0;
								}else{
									// validar si el monto de kmimos es superior a la comision
									$diferencia = 0;
									if( $pago_kmimos < $descuento_kmimos ){
										$diferencia = $descuento_kmimos - $pago_kmimos;
										$descuento_cuidador += $diferencia;
										$pago_kmimos = 0;
									}

									if( $descuento_cuidador >= $pago_cuidador ){
										$pago_cuidador = 0;
									}else{
										$pago_cuidador -= $descuento_cuidador;
									}
								}
								break;
							default:
								$pago_cuidador -= $_cupon['total'];
								break;
						}
					}
				}
			}

		return $pago_cuidador ; 
	}

	public function get_cuidadores_reservas_activas(){
		$hoy = date( 'Ymdhis' );
		$sql = "SELECT 
				DISTINCT(pr.post_author) 
			FROM wp_posts as r
				LEFT JOIN wp_postmeta as rm ON rm.post_id = r.ID and rm.meta_key = '_booking_order_item_id' 
				LEFT JOIN wp_postmeta as rm_cost ON rm_cost.post_id = r.ID and rm_cost.meta_key = '_booking_cost'
				LEFT JOIN wp_postmeta as rm_start ON rm_start.post_id = r.ID and rm_start.meta_key = '_booking_start'
				LEFT JOIN wp_postmeta as rm_end ON rm_end.post_id = r.ID and rm_end.meta_key = '_booking_end'
				LEFT JOIN wp_woocommerce_order_itemmeta as pri ON (pri.order_item_id = rm.meta_value and pri.meta_key = '_product_id')
				LEFT JOIN wp_posts as pr ON pr.ID = pri.meta_value
			WHERE r.post_type = 'wc_booking' 
				and not r.post_status like '%cart%' 
				and r.post_status = 'confirmed'
				AND ( '{$hoy}' >= rm_start.meta_value and '{$hoy}' <= rm_end.meta_value )
			";
		return $this->db->get_results( $sql );
	}

	protected function get_reserva_activas_by_user( $user_id, $reserva_id=0 ){
		$filtro_adicional = "";

		if( $reserva_id > 0 ){
			$filtro_adicional .= " AND r.ID = {$reserva_id}";
		}

		if( $user_id > 0 ){
			$filtro_adicional .= " AND pr.post_author = {$user_id}";
		}

		$hoy = date( 'Ymdhis' );
		$filtro_adicional .= " 
			AND ( '{$hoy}' >= rm_start.meta_value and '{$hoy}' <= rm_end.meta_value )
		";

		// SQL Nuevo
		$sql = "
			SELECT 
				pr.post_author as cuidador_id,
				r.ID as reserva_id,
				r.post_parent as pedido_id,
				( IFNULL(rm_cost.meta_value,0) ) as total,
				rm_start.meta_value as booking_start,
				rm_end.meta_value as booking_end
			FROM wp_posts as r
				LEFT JOIN wp_postmeta as rm ON rm.post_id = r.ID and rm.meta_key = '_booking_order_item_id' 
				LEFT JOIN wp_postmeta as rm_cost ON rm_cost.post_id = r.ID and rm_cost.meta_key = '_booking_cost'
				LEFT JOIN wp_postmeta as rm_start ON rm_start.post_id = r.ID and rm_start.meta_key = '_booking_start'
				LEFT JOIN wp_postmeta as rm_end ON rm_end.post_id = r.ID and rm_end.meta_key = '_booking_end'
				LEFT JOIN wp_woocommerce_order_itemmeta as pri ON (pri.order_item_id = rm.meta_value and pri.meta_key = '_product_id')
				LEFT JOIN wp_posts as pr ON pr.ID = pri.meta_value
			WHERE r.post_type = 'wc_booking' 
				and not r.post_status like '%cart%' 
				and r.post_status = 'confirmed'
				{$filtro_adicional}
		;";

		$reservas = $this->db->get_results($sql);

		return $reservas;
	}

	protected function getReservas( $desde="", $hasta="", $user_id=0 ){

		$filtro_adicional = "";

		if( $user_id > 0 ){
			$filtro_adicional = " AND pr.post_author = {$user_id}";
		}

		if( !empty($desde) && !empty($hasta) ){
			$desde = str_replace('-', '', $desde);
			$hasta = str_replace('-', '', $hasta);
			$filtro_adicional .= " 
				AND ( rm_start.meta_value >= '{$desde}000000' and  rm_start.meta_value <= '{$hasta}235959' )
			";
		}else{
			$hoy = date( 'Ymdhis' );
			$filtro_adicional .= " 
				AND ( '{$hoy}' >= rm_start.meta_value and '{$hoy}' <= rm_start.meta_value )
			";
		}

		// SQL Nuevo
		$sql = "
			SELECT 
				pr.post_author as cuidador_id,
				r.ID as reserva_id,
				r.post_parent as pedido_id,
				( IFNULL(rm_cost.meta_value,0) ) as total,
				rm_start.meta_value as booking_start,
				rm_end.meta_value as booking_end
			FROM wp_posts as r
				LEFT JOIN wp_postmeta as rm ON rm.post_id = r.ID and rm.meta_key = '_booking_order_item_id' 
				LEFT JOIN wp_postmeta as rm_cost ON rm_cost.post_id = r.ID and rm_cost.meta_key = '_booking_cost'
				LEFT JOIN wp_postmeta as rm_start ON rm_start.post_id = r.ID and rm_start.meta_key = '_booking_start'
				LEFT JOIN wp_postmeta as rm_end ON rm_end.post_id = r.ID and rm_end.meta_key = '_booking_end'
				LEFT JOIN wp_woocommerce_order_itemmeta as pri ON (pri.order_item_id = rm.meta_value and pri.meta_key = '_product_id')
				LEFT JOIN wp_posts as pr ON pr.ID = pri.meta_value
			WHERE r.post_type = 'wc_booking' 
				and not r.post_status like '%cart%' 
				and r.post_status = 'confirmed'
				{$filtro_adicional}
		;";

		$reservas = $this->db->get_results($sql);

		return $reservas;
	}

	protected function getMetaPedido( $post_id ){
		$metas = [
			'_payment_method' => '',
			'_payment_method_title' => '',
			'_order_total' => '',
			'_wc_deposits_remaining' => '',
			'_cart_discount' => '',
		];
		$sql = "SELECT u.meta_key, u.meta_value, u.post_id FROM wp_postmeta as u WHERE u.post_id = {$post_id} AND meta_key IN ( '".implode("','", array_keys($metas))."' )";
		$result = $this->db->get_results( $sql );
		
		if( !empty($result) ){
			foreach ($result as $row) { 
				$metas[ $row->meta_key ] = utf8_encode( $row->meta_value );
			}
		}
		return $metas;	
	}

	protected function diferenciaDias( $inicio, $fin ){
		$fecha1 = new DateTime($inicio);
		$fecha2 = new DateTime($fin);
		$intervalo = $fecha1->diff($fecha2);
		return [
			'obj' => $intervalo,
			'anio' => $intervalo->format('%Y'),
			'mes' => $intervalo->format('%m'),
			'dia' => $intervalo->format('%d'),
			'hora' => $intervalo->format('%H'),
			'minuto' => $intervalo->format('%i'),
			'segundo' => $intervalo->format('%s'),
		];
	    // $inicio = strtotime($inicio);
	    // $fin = strtotime($fin);
	    // $dif = $fin - $inicio;
	    // $diasFalt = (( ( $dif / 60 ) / 60 ) / 24);
	    // return ceil($diasFalt);
	}

}

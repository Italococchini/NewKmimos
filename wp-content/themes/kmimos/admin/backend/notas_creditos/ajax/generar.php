<?php
	session_start();

    date_default_timezone_set('America/Mexico_City');

    $raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
    include_once($raiz."/wp-load.php");

    $tema = (dirname(dirname(dirname(dirname(__DIR__)))));
    include_once($tema."/admin/backend/notas_creditos/lib/notas_creditos.php");
    include_once($tema."/lib/enlaceFiscal/CFDI.php");
    global $wpdb;

	$total = 0;
	$detalle = [];
    extract($_POST);


	$tiene_nota_credito = $wpdb->get_var("
		SELECT id 
		FROM notas_creditos 
		WHERE reserva_id = ".$reserva_id 
	); 

	if( $tiene_nota_credito > 0 ){
		echo json_encode("['error'=>'SI', 'mensaje'=>'Posee una nota de credito']");
		exit();
	}

	$reserva = kmimos_desglose_reserva_data( $pedido_id, true );
	$inicio = date('Y-m-d',$reserva['servicio']['inicio']) ;


	// *************************************
	// Detalle de la Nota de Credito
	// *************************************

		// Servicio principal
		if( !empty($reserva['servicio']['variaciones']) && !empty($s_principal) ){	
			foreach( $reserva['servicio']['variaciones'] as $item ){ 
				$code = md5($item[1]);

				if( in_array($code, $s_principal) ){
					$noches = $_POST[ 'noches_'.$code ];	
					$prorrateo = $item[3] * $noches * $item[0];
					if( $prorrateo > 0 ){				
						$detalle[] = [  
							'fecha' => $_POST[ 'hasta_'.$code ],
							'titulo'=> "{$item[0]} {$item[1]} x {$item[2]} x {$item[3]}",
							'cantidad'=> $item[0],
							'tamano'=> $item[1],
							'noches'=> $item[2],
							'costo' => $prorrateo,
							'precio_base' => $item[3],							
						];
						$total += $prorrateo;
					}
				}
			}
		}

		//$servicios
		if( !empty($reserva['servicio']['adicionales']) && !empty($servicios) ){
			foreach( $reserva['servicio']['adicionales'] as $key => $item ){ 
				$code = md5($item[0]);

				if( in_array($code, $servicios) ){
					$monto = str_replace(',','.', str_replace('.', '', $item[2]));
					$monto *= $item[1];
					$detalle[] = [
						'titulo'=> "{$item[0]} - {$item[1]} x {$item[2]}",
						'costo' => $monto,
					];
					$total += $monto;
				}

			}
		}

		//transporte
		if( !empty($reserva['servicio']['transporte']) && !empty($transporte) ){
			foreach( $reserva['servicio']['transporte'] as $key => $item ){ 
				$code = md5($item[0]);

				if( in_array($code, $transporte) ){
					$monto = str_replace(',','.', str_replace('.', '', $item[3]));
					$detalle[] = [
						'titulo'=> "{$item[0]}",
						'costo' => $monto,
					];
					$total += $monto;
				}
			}
		}

		$_detalle = serialize($detalle);

	// Validar tipo de nota de credito
		if( strtolower($tipo_usuario) == 'cliente' ){
			$comision = $total * 0.20;
			$total -= $comision;
			$r = factura_penalizacion( $reserva['cliente']['id'], $pedido_id, $reserva_id, $comision );
			$observaciones_cliente = 'Comision por penalizacion $ '.$comision ;
print_r($r);
		}
	// generar Notas de Credito EnlaceFiscal
		$NC_data = [
			'user_id' => $reserva['cuidador']['id'],
			'detalle' => $detalle,
			'total'   => $total,
			'reserva_id' => $reserva_id,
			'consecutivo' => date('m'),
			'cuidador' => ['id'=>$reserva['cuidador']['id']],
			'cliente' => ['id'=>$reserva['cliente']['id']],
			'tipo'=>'cuidador',
		];
		$cfdi_cuidador = $CFDI->generar_Cfdi_NotasCreditos( $NC_data );
		$factura_id_cuidador = $reserva_id . $NC_data['consecutivo'];

		$NC_data['user_id'] = $reserva['cliente']['id'];
		$NC_data['consecutivo'] += 1;
		$NC_data['tipo'] = 'cliente';
		$cfdi_cliente = $CFDI->generar_Cfdi_NotasCreditos( $NC_data );
		$factura_id_cliente = $reserva_id . $NC_data['consecutivo'];

	// Nota de Credito - Cuidador
		$sql_cuidador = "INSERT INTO notas_creditos ( 
				`tipo`,
				`user_id`,
				`reserva_id`,
				`monto`,
				`detalle`,
				`observaciones`,
				`estatus`,
				factura
			) VALUES (
				'cuidador', 
				".$reserva['cuidador']['id'].", 
				$reserva_id, 
				$total, 
				'{$_detalle}',
				'{$observaciones}',
				'pendiente',
				'{$factura_id_cuidador}'
			);";

	// Nota de Credito - Cliente
		$sql_cliente = "INSERT INTO notas_creditos ( 
				`tipo`,
				`user_id`,
				`reserva_id`,
				`monto`,
				`detalle`,
				`observaciones`,
				`estatus`,
				factura
			) VALUES (
				'cliente', 
				".$reserva['cliente']['id'].", 
				$reserva_id, 
				$total,
				'{$_detalle}',
				'{$observaciones} - {$observaciones_cliente}',
				'pendiente',
				'{$factura_id_cliente}'
			);";

		if( isset($cfdi_cuidador['estatus']) && $cfdi_cuidador['estatus']=='aceptado'){
			$wpdb->query( $sql_cuidador );
		}
		if( isset($cfdi_cliente['estatus']) && $cfdi_cliente['estatus']=='aceptado'){
			$wpdb->query( $sql_cliente );
			// Saldo a favor
				$sql_saldo = "
					UPDATE wp_usermeta SET 
						meta_value = meta_value + {$total} 
					WHERE meta_key='kmisaldo' and user_id = {$reserva['cliente']['id']}
				";

				$wpdb->query( $sql_saldo );
		}

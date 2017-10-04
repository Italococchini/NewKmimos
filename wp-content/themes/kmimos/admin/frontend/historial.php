<?php

	global $wpdb;
	$sql = "SELECT * FROM $wpdb->posts WHERE post_type = 'wc_booking' AND post_author = {$user_id} AND post_status NOT LIKE '%cart%' ORDER BY id DESC";
	$reservas = $wpdb->get_results($sql);

	if( count($reservas) > 0 ){

		$reservas_array = array(
			"pendientes_tienda" => array(
				"titulo" => 'Reservas pendientes por pagar en tienda por conveniencia',
				"reservas" => array()
			),
			"confirmadas" => array(
				"titulo" => 'Reservas Confirmadas',
				"reservas" => array()
			),
			"completadas" => array(
				"titulo" => 'Reservas Completadas',
				"reservas" => array()
			),
			"canceladas" => array(
				"titulo" => 'Reservas Canceladas',
				"reservas" => array()
			),
			"modificadas" => array(
				"titulo" => 'Reservas Modificadas',
				"reservas" => array()
			),
			"pendientes_confirmar" => array(
				"titulo" => 'Reservas Pendientes por Confirmar',
				"reservas" => array()
			),
			"error" => array(
				"titulo" => 'Reservas en error en tarjetas de credito',
				"reservas" => array()
			),
			"otros" => array(
				"titulo" => 'Otras Reservas',
				"reservas" => array()
			)
		);

		//PENDIENTE POR PAGO EN TIENDA DE CONVENINCIA
		foreach($reservas as $key => $reserva){

			$_metas_reserva = get_post_meta($reserva->ID);
			$_metas_orden = get_post_meta($reserva->post_parent);

			$servicio = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = ".$_metas_reserva['_booking_product_id'][0]);

			$reserva_status = $reserva->post_status;
			$orden_status = $wpdb->get_var("SELECT post_status FROM $wpdb->posts WHERE ID = ".$reserva->post_parent);

			$creada = strtotime( $reserva->post_date );
			$inicio = strtotime( $_metas_reserva['_booking_start'][0] );
			$fin    = strtotime( $_metas_reserva['_booking_start'][0] );

			$pdf = $_metas['_openpay_pdf'][0];
			$ver = $reserva->post_parent;
			$cancelar = $reserva->post_parent;
			$modificar = md5($reserva->ID)."_".md5($user_id)."_".md5($servicio->ID);
			$valorar = $reserva->ID;

			//RESERVAS PENDIENTES POR ERROR DE PAGOS DE TARJETAS
			if($reserva_status == 'pending') {

			}else if($orden_status == 'wc-on-hold' && ( $_metas['_payment_method'][0] == 'openpay_stores' || $_metas['_payment_method'][0] == 'tienda' ) ){

				$reservas_array["pendientes_tienda"]["reservas"][] = array(
					'id' => $reserva->ID, 
					'servicio' => $servicio->post_title, 
					'inicio' => date('d/m/Y', $inicio), 
					'fin' => date('d/m/Y', $fin), 
					'acciones' => array(
						"ver" => $ver,
						"modificar" => $modificar,
						"cancelar" => $cancelar,
						"pdf" => $pdf
					)
				);

				//RESERVAS CONFIRMADAS
			}else if($reserva->post_status=='confirmed' && strtotime($_metas_reserva['_booking_end'][0])>time()){
				
				$reservas_array["confirmadas"]["reservas"][] = array(
					'id' => $reserva->ID, 
					'servicio' => $servicio->post_title, 
					'inicio' => date('d/m/Y', $inicio), 
					'fin' => date('d/m/Y', $fin), 
					'acciones' => array(
						"ver" => $ver,
						"modificar" => $modificar
					)
				);

				//RESERVAS COMPLETADAS
			}else if($reserva->post_status=='complete' || ($reserva->post_status=='confirmed' && strtotime($_metas_reserva['_booking_end'][0])<time())){

				$reservas_array["completadas"]["reservas"][] = array(
					'id' => $reserva->ID, 
					'servicio' => $servicio->post_title, 
					'inicio' => date('d/m/Y', $inicio), 
					'fin' => date('d/m/Y', $fin), 
					'acciones' => array(
						"ver" => $ver,
						"valorar" => $valorar
					)
				);

				//RESERVAS CANCELADAS
			}else if($reserva->post_status=='cancelled' || $reserva->post_status=='wc_cancelled'){

				$reservas_array["canceladas"]["reservas"][] = array(
					'id' => $reserva->ID, 
					'servicio' => $servicio->post_title, 
					'inicio' => date('d/m/Y', $inicio), 
					'fin' => date('d/m/Y', $fin), 
					'acciones' => array(
						"ver" => $ver
					)
				);

			//RESERVAS MODIFICADAS
			}else if($reserva->post_status=='modified'){

				$reservas_array["modificadas"]["reservas"][] = array(
					'id' => $reserva->ID, 
					'servicio' => $servicio->post_title, 
					'inicio' => date('d/m/Y', $inicio), 
					'fin' => date('d/m/Y', $fin), 
					'acciones' => array(
						"ver" => $ver
					)
				);

			//RESERVAS PNDIENTES POR CONFIRMAR
			}else if($reserva->post_status!='confirmed'){

				$reservas_array["pendientes_confirmar"]["reservas"][] = array(
					'id' => $reserva->ID, 
					'servicio' => $servicio->post_title, 
					'inicio' => date('d/m/Y', $inicio), 
					'fin' => date('d/m/Y', $fin), 
					'acciones' => array(
						"ver" => $ver,
						"modificar" => $modificar,
						"cancelar" => $cancelar,
					)
				);

			}else{

				$reservas_array["otros"]["reservas"][] = array(
					'id' => $reserva->ID, 
					'servicio' => $servicio->post_title, 
					'inicio' => date('d/m/Y', $inicio), 
					'fin' => date('d/m/Y', $fin), 
					'acciones' => array(
						"ver" => $ver
					)
				);

			}
		}

		//BUILD TABLE
		$CONTENIDO .= '
			<h1 style="margin: 0px; padding: 0px;">Mi Historial de Reservas</h1><hr style="margin: 5px 0px 10px;">
			<div class="kmisaldo">
			<strong>'.kmimos_saldo_titulo().':</strong> MXN $'.kmimos_get_kmisaldo().'
		</div>'.
		build_table($reservas_array);
	}else{
		$CONTENIDO .= "<h1 style='line-height: normal;'>Usted aún no tiene reservas.</h1><hr>";
	}

?>
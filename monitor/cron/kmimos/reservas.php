<?php
 
/* ************************************* */
// Estructurar datos
/* ************************************* */
	require_once('funciones.php');

	$hoy = date('Y-m-d');
	if( isset($_GET['d']) && !empty($_GET['d']) ){
		$hoy = $_GET['d'];
	}

	// total de clientes
	$total_clientes = getTotalClientes( '2000-01-01', $hoy );

	$reservas = getReservas( $hoy, $hoy );
 

	// Resultado final
	$data =[
		"mascotas_total" => 0,
		"clientes" => [
			'total' => $total_clientes,
		],
		"noches" => [
			"total" => 0,	// multiplicar por numero de mascotas
			"numero" => 0,  // sin incluir numero de mascotas
		],
		"ventas" => [
			"cant" => 0,
			"tipo" => [
				"flash" => 0,
				"normal" => 0,
			],
			"estatus" => [],
			"tipo_pago" => [],
			"forma_pago" => [],
			"costo" => [
				'total' => 0
			],
		]
	];

	if( !empty($reservas['rows']) ){

		foreach ($reservas['rows'] as $key => $reserva) {

	  		/* ******************************************* */
	  		// Buscar datos 
	  		/* ******************************************* */

				# Metadatos de la Reservas
					$meta_reserva = getMetaReserva($reserva['nro_reserva']);

				# Metadatos del Pedido
					$meta_pedido = getMetaPedido($reserva['nro_pedido']);

				# Numero de Noches
					$nro_noches = dias_transcurridos(
							date_convert($meta_reserva['_booking_end'], 'd-m-Y'), 
							date_convert($meta_reserva['_booking_start'], 'd-m-Y') 
						);					
					if(!in_array('hospedaje', explode("-", $reserva['post_name']))){
						$nro_noches += 1;
					}

				# Estatus de la reserva
					$estatus = get_status(
			  			$reserva['estatus_reserva'], 
			  			$reserva['estatus_pago'], 
			  			$meta_pedido['_payment_method'],
			  			$meta_reserva
			  		);
			  		$estatus = strtolower( str_replace('', "_", $estatus['sts_corto']) );

			  	# forma de pago
			  		$f_pago = '';
			  		if( !empty($meta_pedido['_payment_method_title']) ){
						$f_pago = $meta_pedido['_payment_method_title'];
					}else{
						if( !empty($meta_reserva['modificacion_de']) ){
							$f_pago = 'Saldo a favor' ; 
						}else{
							$f_pago = 'Saldo a favor y/o cupones'; 
						}
					}

				# tipo de pago
			  		$t_pago = getTipoPagoReserva( $meta_reserva );
					if( $t_pago["enable"] == "yes" ){
						$t_pago = "Pago 20%";
					}else{
						$t_pago = "Pago Total";
					}
						
	  		/* ******************************************* */
	  		// Agregar datos 
	  		/* ******************************************* */

		  		// agregar total mascotas
		  			$data['mascotas_total'] += $reserva['nro_mascotas'];

		  		// agregar noches de la reservas
		  			$data['noches']['numero'] += $nro_noches; // sin incluir numero de mascotas
		  			$data['noches']['total'] += ( $nro_noches * $reserva['nro_mascotas'] ); // incluir numero de mascotas

		  		// agregar contador de reservas
		  			$data['ventas']['cant'] += 1;

				// agregar tipo de reserva
					$data["ventas"]['tipo']['flash'] += ( $meta_reserva['_booking_flash'] == "SI" )? 1 : 0 ;
					$data["ventas"]['tipo']['normal'] += ( $meta_reserva['_booking_flash'] != "SI" )? 1 : 0 ;

				// agregar estatus de la reserva
					if( isset($data["ventas"]['estatus'][$estatus]) ){
						$data["ventas"]['estatus'][$estatus] += 1;
					}else{
						$data["ventas"]['estatus'][$estatus] = 1;
					}

				// agregar forma de pago
					$f_pago = str_replace('y/o', '', $f_pago);
					$f_pago = str_replace('/', '_', $f_pago);
					$f_pago = str_replace(' ', '_', $f_pago);
					$f_pago = str_replace('__', '_', $f_pago);


					if( isset($data["ventas"]['forma_pago'][$f_pago]) ){
						$data["ventas"]['forma_pago'][$f_pago] += 1;
					}else{
						$data["ventas"]['forma_pago'][$f_pago] = 1;
					}

				// agregar forma de pago
					$t_pago = str_replace(' ', '_', $t_pago);

					if( isset($data["ventas"]['tipo_pago'][$t_pago]) ){
						$data["ventas"]['tipo_pago'][$t_pago] += 1;
					}else{
						$data["ventas"]['tipo_pago'][$t_pago] = 1;
					}

				// agregar costos de reserva por estatus
					if( isset($data["ventas"]['costo'][$estatus]) ){
						$data["ventas"]['costo'][$estatus] += $meta_reserva['_booking_cost'];
					}else{
						$data["ventas"]['costo'][$estatus] = $meta_reserva['_booking_cost'];
					}

					// sumar total de ingresos
					if( $estatus == 'confirmado' ){
						$data["ventas"]['costo']['total'] += $meta_reserva['_booking_cost'];
					}

		}
	}

/* ******************************************* */
// Guardar Datos Base
/* ******************************************* */
	
	//print_r(['reserva', $hoy, $data]);
	if( $data['ventas']['cant'] > 0 ){
		$d = save( 'ventas', $hoy, $data );
	}
	 
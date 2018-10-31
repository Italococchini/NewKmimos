<pre>
<?php

	date_default_timezone_set('America/Mexico_City');

	$raiz = dirname(__DIR__);
	require_once( $raiz . '/wp-load.php');

	$tema = $raiz . '/wp-content/themes/kmimos';
	include_once($tema.'/lib/pagos/pagos_cuidador.php');

	global $wpdb;

	print_r( $pagos->get_pago_by_user( 8631 ) );


/*
	$hoy = date("Y-m-d H:i:s");
//	$desde = date("Y-m-01", strtotime($hoy));
	$desde = date("2018-01-01", strtotime($hoy));
	$hasta = date("Y-m-d");

	$pagos_list = $pagos->updatePagoCuidador($desde, $hasta);

	if( !empty($pagos_list) ){
		foreach ($pagos_list as $ID => $pago) {
			// datos de la reserva

			// Detalle de las reservas
			foreach ($pago->detalle as $reserva_id => $reserva) {

				
				$desglose = kmimos_desglose_reserva_data( $reserva['pedido'], true );
print_r($desglose);

				$existe = $pagos->db->get_var('SELECT id FROM cuidadores_reservas WHERE reserva_id='.$reserva_id);
				if( $existe > 0 ){
					// $SQL = "UPDATE";
				}else{
					$_temp_duracion = explode(' ', $desglose['servicio']['duracion']);
					$SQL = "
						INSERT INTO `cuidadores_reservas` (
							`reserva_id`,
							user_id,
							`servicio`,
							`checkin`,
							`checkout`,
							`total_dias`,
							`total_reserva`,
							`monto_servicio_pri`,
							`monto_servicio_adi`,
							`estatus`,
							`desglose`
						) VALUES (
							".$reserva['reserva'].",
							".$ID.",
							'".$desglose['servicio']['tipo']."',
							'".date( 'Y-m-d', $desglose['servicio']['inicio'] )."',
							'".date( 'Y-m-d', $desglose['servicio']['fin'] )."',
							".$_temp_duracion[0].",
							".$desglose['servicio']['desglose']['total'].",
							0,
							0,
							'pendiente',
							'".serialize($desglose)."'
						);";
					$pagos->db->query( $SQL );

				}
			}
		}
	}
*/
?>	
</pre>
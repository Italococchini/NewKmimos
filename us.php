<?php
	include 'wp-load.php';

	global $wpdb;

	$hoy = time();

	$fecha = date('Y-m-d H:i:s');
	$nuevafecha = strtotime ( '-1 year' , strtotime ( $fecha ) ) ;
	$date = date ( 'Y-m-d H:i:s' , $nuevafecha );

	$mes_1 = strtotime ( '-1 month' , $hoy );
	$mes_3 = strtotime ( '-3 month' , $hoy );
	$mes_6 = strtotime ( '-6 month' , $hoy );
	$mes_12 = strtotime ( '-12 month' , $hoy );

	$SQL = "
		SELECT 
			r.ID AS reserva,
			r.post_author AS user,
			u.user_email AS email,
			r.post_date AS fecha,
			t.slug AS tipo
		FROM 
			wp_posts AS r
		INNER JOIN wp_users AS u ON ( r.post_author = u.ID )
		INNER JOIN wp_postmeta AS m ON ( r.ID = m.post_id AND m.meta_key = '_booking_product_id' )
		INNER JOIN wp_term_relationships AS re ON ( m.meta_value = re.object_id )
		INNER JOIN wp_terms AS t ON ( t.term_id = re.term_taxonomy_id AND re.term_taxonomy_id != 28 )
		WHERE
			post_type = 'wc_booking' AND 
			post_date >= '{$date}' AND 
			post_status = 'confirmed'
	";

	$reservas = $wpdb->get_results($SQL);

	$grupos = [];

	foreach ($reservas as $reserva) {
		if( $mes_1 <= strtotime($reserva->fecha) ){
			$grupos[ $reserva->tipo ][ $reserva->user ][ "mes_1" ][] = $reserva;
		}
		if( $mes_3 <= strtotime($reserva->fecha) ){
			$grupos[ $reserva->tipo ][ $reserva->user ][ "mes_3" ][] = $reserva;
		}
		if( $mes_6 <= strtotime($reserva->fecha) ){
			$grupos[ $reserva->tipo ][ $reserva->user ][ "mes_6" ][] = $reserva;
		}
		if( $mes_12 <= strtotime($reserva->fecha) ){
			$grupos[ $reserva->tipo ][ $reserva->user ][ "mes_12" ][] = $reserva;
		}
	}

	$recompras = [];
	foreach ($grupos as $key => $servicio) {
		foreach ($servicio as $key => $value) {
			# code...
		}
	}

	echo "<pre>";
		print_r($grupos);
	echo "</pre>";

?>
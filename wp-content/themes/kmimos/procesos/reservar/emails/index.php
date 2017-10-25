<?php
	
	extract($_GET);
	if( isset($_GET["id_orden"]) ){
		include((dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))))."/wp-load.php");
	}

	$PATH_TEMPLATE = (dirname(dirname(dirname(__DIR__))));

	$info = kmimos_get_info_syte();
	add_filter( 'wp_mail_from_name', function( $name ) { global $info; return $info["titulo"]; });
    add_filter( 'wp_mail_from', function( $email ) { global $info; return $info["email"]; });

    global $wpdb;
	$id = $id_orden;
	$data = kmimos_desglose_reserva_data($id, true);

	extract($data);

	/*	
	echo "<pre>";
		print_r($data);
	echo "</pre>";
	*/
	
 	$modificacion_de = get_post_meta($reserva_id, "modificacion_de", true);
    if( $modificacion_de != "" ){ $modificacion = 'Esta es una modificación de la reserva #: '.$modificacion_de;
 	}else{ $modificacion = ""; }

	$email_admin = $info["email"];

	$mascotas_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/mascotas.php';
    $mascotas_plantilla = file_get_contents($mascotas_plantilla);
    $mascotas = "";
	foreach ($cliente["mascotas"] as $mascota) {
		$temp = str_replace('[NOMBRE]', $mascota["nombre"], $mascotas_plantilla);
		$temp = str_replace('[RAZA]', $mascota["raza"], $temp);
		$temp = str_replace('[EDAD]', $mascota["edad"], $temp);
		$temp = str_replace('[TAMANO]', $mascota["tamano"], $temp);
		$temp = str_replace('[CONDUCTA]', $mascota["conducta"], $temp);
		$mascotas .= $temp;
	}
	
	$desglose_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/desglose.php';
    $desglose_plantilla = file_get_contents($desglose_plantilla);

    $desglose = "";
	foreach ($servicio["variaciones"] as $variacion) {
		$plural = ""; if($variacion[0]>1){$plural="s";}
		$temp = str_replace('[TAMANO]', strtoupper($variacion[1]), $desglose_plantilla);
		$temp = str_replace('[CANTIDAD]', $variacion[0]." mascota".$plural, $temp);
		$temp = str_replace('[TIEMPO]', $variacion[2], $temp);
		$temp = str_replace('[PRECIO_C_U]', "$ ".$variacion[3], $temp);
		$temp = str_replace('[SUBTOTAL]', "$ ".$variacion[4], $temp);
		$desglose .= $temp;
	}

	$adicionales_desglose_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/adicionales_desglose.php';
    $adicionales_desglose_plantilla = file_get_contents($adicionales_desglose_plantilla);

    $adicionales = "";
    foreach ($servicio["adicionales"] as $adicional) {
		$temp = str_replace('[SERVICIO]', $adicional[0], $adicionales_desglose_plantilla);
		$temp = str_replace('[CANTIDAD]', $adicional[1], $temp);
		$temp = str_replace('[PRECIO_C_U]', "$ ".$adicional[2], $temp);
		$temp = str_replace('[SUBTOTAL]', "$ ".$adicional[3], $temp);
		$adicionales .= $temp;
	}

	if( $adicionales != "" ){
		$adicionales_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/adicionales.php';
    	$adicionales_plantilla = file_get_contents($adicionales_plantilla);

    	$adicionales = $adicionales_plantilla.$adicionales;
	}
	
	$transporte_desglose_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/transporte_desglose.php';
    $transporte_desglose_plantilla = file_get_contents($transporte_desglose_plantilla);

    $transporte = "";
    foreach ($servicio["transporte"] as $valor) {
		$temp = str_replace('[SERVICIO]', $valor[0], $transporte_desglose_plantilla);
		$temp = str_replace('[SUBTOTAL]', $valor[2], $temp);
		$transporte .= $temp;
	}

	if( $transporte != "" ){
		$transporte_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/transporte.php';
    	$transporte_plantilla = file_get_contents($transporte_plantilla);

    	$transporte = $transporte_plantilla.$transporte;
	}

	$totales_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/totales.php';
    $totales_plantilla = file_get_contents($totales_plantilla);
    $totales_plantilla = str_replace('[TIPO_PAGO]', $servicio["tipo_pago"], $totales_plantilla);

    if( $servicio["desglose"]["enable"] == "yes" ){
    	$deposito_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/deposito.php';
    	$deposito_plantilla = file_get_contents($deposito_plantilla);
    	$deposito_plantilla = str_replace('[REMANENTE]', number_format( $servicio["desglose"]["remaining"], 2, ',', '.'), $deposito_plantilla);
        $totales_plantilla = str_replace('[TOTAL]', number_format( $servicio["desglose"]["total"], 2, ',', '.'), $totales_plantilla);
    	$totales_plantilla = str_replace('[PAGO]', number_format( $servicio["desglose"]["deposit"], 2, ',', '.'), $totales_plantilla);
    	$totales_plantilla = str_replace('[DETALLES]', $deposito_plantilla, $totales_plantilla);

    }else{
        $totales_plantilla = str_replace('[TOTAL]', number_format( $servicio["desglose"]["deposit"], 2, ',', '.'), $totales_plantilla);
    	$totales_plantilla = str_replace('[PAGO]', number_format( $servicio["desglose"]["deposit"]-$servicio["desglose"]["descuento"], 2, ',', '.'), $totales_plantilla);
    	$totales_plantilla = str_replace('[DETALLES]', "", $totales_plantilla);
    }
	
	if( $servicio["desglose"]["descuento"]+0 > 0 ){
		$descuento_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/descuento.php';
	    $descuento_plantilla = file_get_contents($descuento_plantilla);
	    $descuento_plantilla = str_replace('[DESCUENTO]', number_format( $servicio["desglose"]["descuento"], 2, ',', '.'), $descuento_plantilla);
	    $totales_plantilla = str_replace('[DESCUENTO]', $descuento_plantilla, $totales_plantilla);
	}else{
		$totales_plantilla = str_replace('[DESCUENTO]', "", $totales_plantilla);
	}

	if( !isset($_GET["acc"]) ){

		if( strtolower($servicio["metodo_pago"]) == "tienda" ){
			include("tienda.php");
		}else{
			include("otro.php");
		}

	}else{

		$booking = new WC_Booking( $servicio["id_reserva"] );
		$order = new WC_Order( $servicio["id_orden"] );

		$status = $booking->get_status();
/*
		if(  $_SESSION['admin_sub_login'] != 'YES' ){

			if( $status == "confirmed" || $status == "cancelled" || $status == "modified" ){
				$estado = array(
					"confirmed" => "Confirmada",
					"modified"  => "Modificada",
					"cancelled" => "Cancelada"
				);
				$msg = "
				<div style='text-align:center; margin-bottom: 25px;'>
					<img src='".get_home_url()."/wp-content/themes/kmimos/images/emails/header_solicitud_reserva.png' style='width: 100%;' >
				</div>

				<div style='padding: 0px; margin-bottom: 25px;'>
					<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
				    	Hola <strong>".$cuidador["nombre"]."</strong>
				    </div>
					<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
				    	Te notificamos que la reserva N° <strong>".$servicio["id_reserva"]."</strong> ya ha sido ".$estado[$status]." anteriormente.
				    </div>
					<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
				    	Por tal motivo ya no es posible realizar cambios en el estatus de la misma.
				    </div>
				</div>";
		   		
		   		echo get_email_html($msg);

		   		exit();
			}

		}*/

		if( $_GET["acc"] == "CFM" ){

    		$order->update_status('wc-on-hold');
			$booking->update_status('confirmed');

			include("confirmacion.php");

			if(  $_SESSION['admin_sub_login'] != 'YES' ){

				// ********************************************************************
		   		// BEGIN Notificacion para usuario referidos - Landing WOM /Referidos
		   		// ********************************************************************
			   		if(isset($cliente["id"])){	
				   		$user_referido = get_user_meta($cliente["id"], 'landing-referencia', true);
				   		if(!empty($user_referido)){
							$username = $cliente["nombre"];
							$http = (isset($_SERVER['HTTPS']))? 'https://' : 'http://' ;
							require_once( $PATH_TEMPLATE.'/template/mail/reservar/club-referido-primera-reserva.php');
							$user_participante = $wpdb->get_results( "
								select ID, user_email 
								from wp_users 
								where md5(user_email) = '{$user_referido}'" 
							);
							$user_participante = (count($user_participante)>0)? $user_participante[0] : [];
							if(isset($user_participante->user_email)){
								wp_mail( $user_participante->user_email, "¡Felicidades, otro perrhijo moverá su colita de felicidad!", $html );
							}
						} 
					}
		   		// ********************************************************************
		   		// END Notificacion para usuario referidos - Landing WOM /Referidos
		   		// ********************************************************************

			}
		}

		if( $_GET["acc"] == "CCL" ){
			include("cancelacion.php");
		}


	}


?>
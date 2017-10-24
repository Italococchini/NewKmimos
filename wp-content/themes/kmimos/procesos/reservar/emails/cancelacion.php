<?php
	
	$cuidador_info = $wpdb->get_row("SELECT * FROM cuidadores WHERE user_id = ".$cuidador["id"]);

	$sql = "
        SELECT 
            DISTINCT id,
            ROUND ( ( 6371 * 
                acos(
                    cos(
                        radians({$cuidador_info->latitud})
                    ) * 
                    cos(
                        radians(latitud)
                    ) * 
                    cos(
                        radians(longitud) - 
                        radians({$cuidador_info->longitud})
                    ) + 
                    sin(
                        radians({$cuidador_info->latitud})
                    ) * 
                    sin(
                        radians(latitud)
                    )
                )
            ), 2 ) as DISTANCIA,
            id_post,
            user_id,
            hospedaje_desde,
            adicionales
        FROM 
            cuidadores
        WHERE
            id_post != {$cuidador_info->id_post} AND 
            activo = 1
        ORDER BY DISTANCIA ASC
        LIMIT 0, 4
    ";

    $sugeridos = $wpdb->get_results($sql);

    $str_sugeridos = "";

    $file_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/cancelacion/partes/cuidadores.php';
    $plantilla_cuidador = file_get_contents($file_plantilla);

    foreach ($sugeridos as $valor) {
    	$nombre = $wpdb->get_row("SELECT post_title, post_name FROM wp_posts WHERE ID = ".$valor->id_post);

    	$rating = kmimos_petsitter_rating($valor->id_post, true);
    	$rating_txt = "";
    	foreach ($rating as $key => $value) {
    		if( $value == 1 ){
    			$rating_txt .= "<img style='width: 15px; padding: 0px 1px;' src='[URL_IMGS]/huesito.png' >";
    		}else{
    			$rating_txt .= "<img style='width: 15px; padding: 0px 1px;' src='[URL_IMGS]/huesito_vacio.png' >";
    		}
    	}

    	$servicios = vlz_servicios($valor->adicionales, true);
    	$servicios_txt = "";
    	foreach ($servicios as $key => $value) {
    		$servicios_txt .= "<img style='' src='[URL_IMGS]/servicios/".$value["img"]."' height='100%' >";
    	}

    	$temp = str_replace("[MONTO]", number_format( ($valor->hospedaje_desde*1.2), 2, ',', '.'), $plantilla_cuidador);
    	$temp = str_replace("[AVATAR]", kmimos_get_foto($valor->user_id), $temp);
    	$temp = str_replace("[NAME_CUIDADOR]", $nombre->post_title, $temp);

    	$temp = str_replace("[HUESOS]", $rating_txt, $temp);
    	$temp = str_replace("[SERVICIOS]", $servicios_txt, $temp);

    	$temp = str_replace('[LIKS]', get_home_url()."/petsitters/".$nombre->post_name."/", $temp);

    	$str_sugeridos .= $temp;
    }


    $file = $PATH_TEMPLATE.'/template/mail/reservar/cancelacion/cancelar_cliente.php';
    $mensaje_cliente = file_get_contents($file);


    $mensaje_cliente = str_replace('[name_cliente]', $cliente["nombre"], $mensaje_cliente);
    $mensaje_cliente = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_cliente);
    $mensaje_cliente = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_cliente);

    $mensaje_cliente = str_replace('[CUIDADORES]', $str_sugeridos, $mensaje_cliente);

    $mensaje_cliente = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cliente);

	$mensaje_cliente = get_email_html($mensaje_cliente);	

	//wp_mail( $cliente["email"], "Cancelación de Reserva", $mensaje_cliente);

    $file = $PATH_TEMPLATE.'/template/mail/reservar/cancelacion/cancelar_cuidador.php';
    $mensaje_cuidador = file_get_contents($file);

    $mensaje_cuidador = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_cuidador);
    $mensaje_cuidador = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_cuidador);

    $mensaje_cuidador = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cuidador);

	$mensaje_cuidador = get_email_html($mensaje_cuidador, false);	

	//wp_mail( $cuidador["email"], "Cancelación de Reserva", $mensaje_cuidador);

	if( $_GET["user"] == "CUI" ){
		echo $mensaje_cuidador;
	}else{
		echo $mensaje_cliente;
	}
?>

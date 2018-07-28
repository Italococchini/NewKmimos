<?php

    if( $superAdmin == "" && $status == "modified" ){
        
    }else{
        kmimos_set_kmisaldo($cliente["id"], $id, $servicio["id_reserva"], $usu);
    }
    update_cupos( $id, "-");
    
    $wpdb->query("UPDATE wp_posts SET post_status = 'wc-cancelled' WHERE ID = $id;");
    $wpdb->query("UPDATE wp_posts SET post_status = 'cancelled' WHERE ID = '{$servicio["id_reserva"]}';");

	$cuidador_info = $wpdb->get_row("SELECT * FROM cuidadores WHERE user_id = ".$cuidador["id"]);

	$sql = "
        SELECT 
            DISTINCT id,
            ROUND ( ( 6371 * acos( cos( radians({$cuidador_info->latitud}) ) * cos( radians(latitud) ) * cos( radians(longitud) - radians({$cuidador_info->longitud}) ) + sin( radians({$cuidador_info->latitud}) ) * sin( radians(latitud) ) ) ), 2 ) as DISTANCIA,
            id_post,
            user_id,
            hospedaje_desde,
            adicionales,
            experiencia
        FROM 
            cuidadores
        WHERE
            id_post != {$cuidador_info->id_post} AND 
            activo = 1 AND
            user_id != 8631
        ORDER BY DISTANCIA ASC
        LIMIT 0, 4
    ";

    $sugeridos = $wpdb->get_results($sql);

    $str_sugeridos = "";

    $file_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/cuidadores.php';
    $plantilla_cuidador = file_get_contents($file_plantilla);

    foreach ($sugeridos as $valor) {
    	$nombre = $wpdb->get_row("SELECT post_title, post_name FROM wp_posts WHERE ID = ".$valor->id_post);
    	$rating = kmimos_petsitter_rating($valor->id_post, true); $rating_txt = "";
    	foreach ($rating as $key => $value) {
    		if( $value == 1 ){ $rating_txt .= "<img style='width: 15px; padding: 0px 1px;' src='[URL_IMGS]/new/huesito.png' >";
    		}else{ $rating_txt .= "<img style='width: 15px; padding: 0px 1px;' src='[URL_IMGS]/huesito_vacio.png' >"; }
    	}
    	$servicios = vlz_servicios($valor->adicionales, true);
    	$servicios_txt = "";
        if( count($servicios)+0 > 0 && $servicios != "" ){
            foreach ($servicios as $key => $value) {
                //$servicios_txt .= "<img style='margin: 0px 3px 0px 0px;' src='[URL_IMGS]/servicios/".str_replace('.svg', '.png', $value["img"])."' height='100%' align='middle' >";
                $servicios_txt .= "<img style='margin: 0px 3px 0px 0px;' src='[URL_IMGS]/servicios/".str_replace('.svg', '_.png', $value["img"])."' height='100%' align='middle' >";
            }
        }

        if( $valor->experiencia > 1900 ){
            $valor->experiencia = date("Y")-$valor->experiencia;
        }

        $monto = explode(",", number_format( ($valor->hospedaje_desde*getComision()), 2, ',', '.') );
        $temp = str_replace("[EXPERIENCIA]", $valor->experiencia, $plantilla_cuidador);

        $temp = str_replace("[MONTO]", $monto[0], $temp);
        $temp = str_replace("[MONTO_DECIMALES]", ",".$monto[1], $temp);
    	$temp = str_replace("[AVATAR]", kmimos_get_foto($valor->user_id), $temp);
    	$temp = str_replace("[NAME_CUIDADOR]", $nombre->post_title, $temp);
    	$temp = str_replace("[HUESOS]", $rating_txt, $temp);
    	$temp = str_replace("[SERVICIOS]", $servicios_txt, $temp);
    	$temp = str_replace('[LIKS]', get_home_url()."/petsitters/".$nombre->post_name."/", $temp);
    	$str_sugeridos .= $temp;
    }

    $file_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/sugeridos.php';
    $plantilla_sugeridos = file_get_contents($file_plantilla);
    $plantilla_sugeridos = str_replace("[CUIDADORES]", $str_sugeridos, $plantilla_sugeridos);

    $msg_cliente = "";
    $msg_cuidador = "";

    if( $usu == "STM" ){
        $msg_cliente = "Te notificamos que el sistema ha cancelado la reserva con el cuidador <strong>[name_cuidador]</strong> debido a que se venció el plazo de confirmación.";
        $msg_cuidador = "Te notificamos que el sistema ha cancelado la reserva realizada por <strong>[name_cliente]</strong> debido a que se venció el plazo de confirmación.";
        $msg_administrador = "Te notificamos que el sistema ha cancelado la reserva realizada por <strong>[name_cliente]</strong> al cuidador <strong>[name_cuidador]</strong> debido a que se venció el plazo de confirmación.";
    }else{
        if( $usu == "CLI" ){
            $msg_cliente = "Te notificamos que la reserva ha sido cancelada exitosamente.";
            $msg_cuidador = "Te notificamos que el cliente <strong>[name_cliente]</strong> ha cancelado la reserva.";
            $msg_administrador = "Te notificamos que el cliente <strong>[name_cliente]</strong> ha cancelado la reserva.";
        }else{
            $msg_cliente = "Te notificamos que el cuidador <strong>[name_cuidador]</strong> ha cancelado la reserva.";
            $msg_cuidador = "Te notificamos que la reserva ha sido cancelada exitosamente.";
            $msg_administrador = "Te notificamos que el cuidador <strong>[name_cuidador]</strong> ha cancelado la reserva.";
        }
    }

    switch ( $usu ) {
        case 'STM':
            $titulo_cancelacion = "Solicitud Cancelada por el Sistema";
        break;
        case 'CUI':
            $titulo_cancelacion = "Solicitud Cancelada por el Cuidador";
        break;
        case 'CLI':
            $titulo_cancelacion = "Solicitud Cancelada por el Cliente";
        break;
        
        default:
            $titulo_cancelacion = "Solicitud Cancelada por el Sistema";
        break;
    }

    if( $usu == "CLI" ){
        $str_sugeridos = "";
        $plantilla_sugeridos = "";
    }

    /* CORREO CLIENTE */
        $file = $PATH_TEMPLATE.'/template/mail/reservar/cliente/cancelar.php';
        $mensaje_cliente = file_get_contents($file);

        $mensaje_cliente = str_replace('[MODIFICACION]', $modificacion, $mensaje_cliente);
        $mensaje_cliente = str_replace("[TITULO_CANCELACION]", $titulo_cancelacion, $mensaje_cliente);
        $mensaje_cliente = str_replace('[mensaje]', $msg_cliente, $mensaje_cliente);
        $mensaje_cliente = str_replace('[name_cliente]', "<strong style='text-transform: uppercase;'>".$cliente["nombre"]."</strong>", $mensaje_cliente);
        $mensaje_cliente = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[SUGERIDOS]', $plantilla_sugeridos, $mensaje_cliente);
        $mensaje_cliente = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cliente);
    	
        $mensaje_cliente = get_email_html($mensaje_cliente, true, true, $cliente["id"], false);	

        if( $NO_ENVIAR != "" ){
            echo $mensaje_cliente;
        }else{
           wp_mail( $cliente["email"], "Cancelación de Reserva", $mensaje_cliente);
        }

    /* CORREO CUIDADOR */
        $file = $PATH_TEMPLATE.'/template/mail/reservar/cuidador/cancelar.php';
        $mensaje_cuidador = file_get_contents($file);

        $mensaje_cuidador = str_replace('[MODIFICACION]', $modificacion, $mensaje_cuidador);
        $mensaje_cuidador = str_replace("[TITULO_CANCELACION]", $titulo_cancelacion, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[mensaje]', $msg_cuidador, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[name_cliente]', $cliente["nombre"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cuidador);

        $mensaje_cuidador = get_email_html($mensaje_cuidador, true, true, $cliente["id"]);

        if( $NO_ENVIAR != "" ){
            echo $mensaje_cuidador;
        }else{
           wp_mail( $cuidador["email"], "Cancelación de Reserva", $mensaje_cuidador);
        }

        $file = $PATH_TEMPLATE.'/template/mail/reservar/admin/cancelar.php';
        $mensaje_admin = file_get_contents($file);

        $mensaje_admin = str_replace('[MODIFICACION]', $modificacion, $mensaje_admin);
        $mensaje_admin = str_replace("[TITULO_CANCELACION]", $titulo_cancelacion, $mensaje_admin);
        $mensaje_admin = str_replace('[mensaje]', $msg_administrador, $mensaje_admin);
        $mensaje_admin = str_replace('[name_cliente]', $cliente["nombre"], $mensaje_admin);
        $mensaje_admin = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_admin);
        $mensaje_admin = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_admin);
        $mensaje_admin = str_replace('[CUIDADORES]', $str_sugeridos, $mensaje_admin);
        $mensaje_admin = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_admin);

        $mensaje_admin = get_email_html($mensaje_admin, true, true, $cliente["id"]);

        if( $NO_ENVIAR != "" ){
            echo $mensaje_admin;
        }else{
           kmimos_mails_administradores_new("Cancelación de Reserva", $mensaje_admin);
        }

        $CONTENIDO .= "<div class='msg_acciones'>Te notificamos que la reserva <strong>#".$servicio["id_reserva"]."</strong>, ha sido cancelada exitosamente.</div>";

?>

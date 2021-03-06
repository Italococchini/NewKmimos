<?php

    $datos_cliente = $PATH_TEMPLATE.'/template/mail/reservar/partes/datos_cliente.php';
    $datos_cliente = file_get_contents($datos_cliente);
    
    $datos_cuidador = $PATH_TEMPLATE.'/template/mail/reservar/partes/datos_cuidador.php';
    $datos_cuidador = file_get_contents($datos_cuidador);
    
    $inmediata = "";

    if( $confirmacion_titulo == "Confirmación de Reserva" ){

	   /* Correo Cliente */

		$cuidador_file = $PATH_TEMPLATE.'/template/mail/reservar/cliente/nueva.php';
        $mensaje_cliente = file_get_contents($cuidador_file);

        /*$mensaje_cliente = str_replace('[DATOS_CUIDADOR]', $datos_cuidador, $mensaje_cliente);

        $mensaje_cliente = str_replace('[SERVICIOS]', $servicios_plantilla, $mensaje_cliente);

        $mensaje_cliente = str_replace('[HEADER]', "reserva", $mensaje_cliente);

        $mensaje_cliente = str_replace('[mascotas]', $mascotas, $mensaje_cliente);
        $mensaje_cliente = str_replace('[desglose]', $desglose, $mensaje_cliente);
        $mensaje_cliente = str_replace('[ADICIONALES]', $adicionales, $mensaje_cliente);
        $mensaje_cliente = str_replace('[TRANSPORTE]', $transporte, $mensaje_cliente);
       	$mensaje_cliente = str_replace('[MODIFICACION]', $modificacion, $mensaje_cliente);
        $mensaje_cliente = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cliente);
        $mensaje_cliente = str_replace('[tipo_servicio]', trim($servicio["tipo"]), $mensaje_cliente);
        $mensaje_cliente = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[DETALLES_SERVICIO]', $detalles_plantilla, $mensaje_cliente);
        $mensaje_cliente = str_replace('[name_cliente]', $cliente["nombre"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[avatar_cuidador]', kmimos_get_foto($cuidador["id"]), $mensaje_cliente);
        $mensaje_cliente = str_replace('[telefonos_cuidador]', $cuidador["telefono"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[correo_cuidador]', $cuidador["email"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[direccion_cuidador]', $cuidador["direccion"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[TOTALES]', str_replace('[REEMBOLSAR]', "", $totales_plantilla), $mensaje_cliente);*/
        
        $mensaje = buildEmailTemplate(
            'reservar/cliente/nueva', 
            [
                'HEADER'                => "reserva",
                'id_reserva'            => $servicio["id_reserva"],
                'DATOS_CUIDADOR'        => $datos_cuidador,
                'SERVICIOS'             => $servicios_plantilla,
                'mascotas'              => $mascotas,
                'desglose'              => $desglose,
                'ADICIONALES'           => $adicionales,
                'TRANSPORTE'            => $transporte,
                'MODIFICACION'          => $modificacion,
                'tipo_servicio'         => trim($servicio["tipo"]),
                'DETALLES_SERVICIO'     => $detalles_plantilla,
                'name_cliente'          => $cliente["nombre"],
                'name_cuidador'         => $cuidador["nombre"],
                'avatar_cuidador'       => kmimos_get_foto($cuidador["id"]),
                'telefonos_cuidador'    => $cuidador["telefono"],
                'correo_cuidador'       => $cuidador["email"],
                'direccion_cuidador'    => $cuidador["direccion"],
                'TOTALES'               => str_replace('[REEMBOLSAR]', "", $totales_plantilla),
                'URL_IMGS'              => get_home_url()."/wp-content/themes/kmimos/images/emails",
            ]
        );

		$mensaje = get_email_html($mensaje, true, true, $cliente["id"], false, true);

        if( isset($NO_ENVIAR) ){
            echo $mensaje;
        }else{
            wp_mail( $cliente["email"], "Solicitud de reserva", $mensaje);
        }


    	/*
    		Correo Cuidador
    	*/

		$cuidador_file = $PATH_TEMPLATE.'/template/mail/reservar/cuidador/nueva.php';
        $mensaje_cuidador = file_get_contents($cuidador_file);
        $mensaje_cuidador = str_replace('[mascotas]', $mascotas, $mensaje_cuidador);

        $mensaje_cuidador = str_replace('[DATOS_CLIENTE]', $datos_cliente, $mensaje_cuidador);

        $totales_plantilla = str_replace('[REEMBOLSAR]', $reembolsar_plantilla, $totales_plantilla);

        $mensaje_cuidador = str_replace('[SERVICIOS]', $servicios_plantilla, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[HEADER]', "reserva", $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[mascotas]', $mascotas, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[desglose]', $desglose, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[ADICIONALES]', $adicionales, $mensaje_cuidador);
       	$mensaje_cuidador = str_replace('[TRANSPORTE]', $transporte, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[MODIFICACION]', $modificacion, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[tipo_servicio]', $servicio["tipo"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[DETALLES_SERVICIO]', $detalles_plantilla, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[ACEPTAR]', $servicio["aceptar_rechazar"]["aceptar"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[RECHAZAR]', $servicio["aceptar_rechazar"]["cancelar"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[name_cliente]', $cliente["nombre"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[avatar_cliente]', kmimos_get_foto($cliente["id"]), $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[telefonos_cliente]', $cliente["telefono"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[correo_cliente]', $cliente["email"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[TOTALES]', $totales_plantilla, $mensaje_cuidador);
	    $mensaje_cuidador = get_email_html($mensaje_cuidador, false, true, $cliente["id"], false);

        if( isset($NO_ENVIAR) ){
            echo $mensaje_cuidador;
        }else{
            wp_mail( $cuidador["email"], 'Nueva Reserva - '.$servicio["tipo"].' por: '.$cliente["nombre"], $mensaje_cuidador);
        }

    }else{
        $totales_plantilla = str_replace('[REEMBOLSAR]', $reembolsar_plantilla, $totales_plantilla);
        $inmediata = "Inmediata";
    }

        $admin_file = $PATH_TEMPLATE.'/template/mail/reservar/admin/nueva.php';
        $mensaje_admin = file_get_contents($admin_file);

        /* Generales */

            $mensaje_admin = str_replace('[SERVICIOS]', $servicios_plantilla, $mensaje_admin);

            if( $inmediata == "Inmediata" ){
                $mensaje_admin = str_replace('[HEADER]', "reservaInmediata", $mensaje_admin);
                $mensaje_admin = str_replace('[id_reserva]', "Código de reserva #".$servicio["id_reserva"], $mensaje_admin);
            }else{
                $mensaje_admin = str_replace('[HEADER]', "reserva", $mensaje_admin);
                $mensaje_admin = str_replace('[id_reserva]', "Reserva #: ".$servicio["id_reserva"], $mensaje_admin);
            }

            $mensaje_admin = str_replace('[DATOS_CLIENTE]', $datos_cliente, $mensaje_admin);
            $mensaje_admin = str_replace('[DATOS_CUIDADOR]', $datos_cuidador, $mensaje_admin);

            $mensaje_admin = str_replace('[MASCOTAS]', $mascotas, $mensaje_admin);
            $mensaje_admin = str_replace('[DESGLOSE]', $desglose, $mensaje_admin);
            $mensaje_admin = str_replace('[ADICIONALES]', $adicionales, $mensaje_admin);
            $mensaje_admin = str_replace('[TRANSPORTE]', $transporte, $mensaje_admin);
            $mensaje_admin = str_replace('[MODIFICACION]', $modificacion, $mensaje_admin);
            $mensaje_admin = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_admin);
            $mensaje_admin = str_replace('[TIPO_SERVICIO]', $servicio["tipo"], $mensaje_admin);
            $mensaje_admin = str_replace('[DETALLES_SERVICIO]', $detalles_plantilla, $mensaje_admin);
            $mensaje_admin = str_replace('[ACEPTAR]', $servicio["aceptar_rechazar"]["aceptar"], $mensaje_admin);
            $mensaje_admin = str_replace('[RECHAZAR]', $servicio["aceptar_rechazar"]["cancelar"], $mensaje_admin);
            $mensaje_admin = str_replace('[TOTALES]', $totales_plantilla, $mensaje_admin);

        /* Datos Cliente */

            $mensaje_admin = str_replace('[name_cliente]', $cliente["nombre"], $mensaje_admin);
            $mensaje_admin = str_replace('[avatar_cliente]', kmimos_get_foto($cliente["id"]), $mensaje_admin);
            $mensaje_admin = str_replace('[telefonos_cliente]', $cliente["telefono"], $mensaje_admin);
            $mensaje_admin = str_replace('[correo_cliente]', $cliente["email"], $mensaje_admin);

        /* Datos Cuidador */
        
            $mensaje_admin = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_admin);
            $mensaje_admin = str_replace('[avatar_cuidador]', kmimos_get_foto($cuidador["id"]), $mensaje_admin);
            $mensaje_admin = str_replace('[telefonos_cuidador]', $cuidador["telefono"], $mensaje_admin);
            $mensaje_admin = str_replace('[correo_cuidador]', $cuidador["email"], $mensaje_admin);
            $mensaje_admin = str_replace('[direccion_cuidador]', $cuidador["direccion"], $mensaje_admin);

        $mensaje_admin = get_email_html($mensaje_admin, false, true, $cliente["id"], false);

        if( isset($NO_ENVIAR) ){
            echo $mensaje_admin;
        }else{
            kmimos_mails_administradores_new('Nueva Reserva '.$inmediata.' - '.$servicio["tipo"].' por: '.$cliente["nombre"], $mensaje_admin);
        }
?>
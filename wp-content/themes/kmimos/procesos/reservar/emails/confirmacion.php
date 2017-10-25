<?php
    
    echo "
        <a href='".get_home_url()."/perfil-usuario/reservas/' style='
            border-top: solid 1px #CCC;
            border-bottom: solid 1px #CCC;
            margin: 10px auto;
            width: 600px;
            padding: 10px 0px;
            font-weight: 600;
            font-family: Arial;
            text-align: center;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            color: #000;
            display: block;
        '>
            Volver
        </a>
    ";
    
    /* Correo Cliente */


        $cuidador_file = $PATH_TEMPLATE.'/template/mail/reservar/confirmacion/confirmacion_cliente.php';
        $mensaje_cliente = file_get_contents($cuidador_file);

        $fin = strtotime( str_replace("/", "-", $_POST['service_end']) );

        $mensaje_cliente = str_replace('[mascotas]', $mascotas, $mensaje_cliente);
        $mensaje_cliente = str_replace('[desglose]', $desglose, $mensaje_cliente);
        
        $mensaje_cliente = str_replace('[ADICIONALES]', $adicionales, $mensaje_cliente);
        $mensaje_cliente = str_replace('[TRANSPORTE]', $transporte, $mensaje_cliente);
        

        $mensaje_cliente = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cliente);

        $mensaje_cliente = str_replace('[tipo_servicio]', trim($servicio["tipo"]), $mensaje_cliente);
        $mensaje_cliente = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_cliente);

        $mensaje_cliente = str_replace('[inicio]', date("d/m", $servicio["inicio"]), $mensaje_cliente);
        $mensaje_cliente = str_replace('[fin]', date("d/m", $servicio["fin"]), $mensaje_cliente);
        $mensaje_cliente = str_replace('[anio]', date("Y", $servicio["fin"]), $mensaje_cliente);
        $mensaje_cliente = str_replace('[tiempo]', $servicio["duracion"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[tipo_pago]', $servicio["metodo_pago"], $mensaje_cliente);

        $mensaje_cliente = str_replace('[name_cliente]', $cliente["nombre"], $mensaje_cliente);

        $mensaje_cliente = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[avatar]', kmimos_get_foto($cuidador["id"]), $mensaje_cliente);
        $mensaje_cliente = str_replace('[telefonos_cuidador]', $cuidador["telefono"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[correo_cuidador]', $cuidador["email"], $mensaje_cliente);
        $mensaje_cliente = str_replace('[direccion_cuidador]', $cuidador["direccion"], $mensaje_cliente);

        $mensaje_cliente = str_replace('[TOTALES]', $totales_plantilla, $mensaje_cliente);

        $mensaje_cliente = get_email_html($mensaje_cliente);

        wp_mail( $cuidador["email"], "Confirmación de Reserva", $mensaje_cliente);
    
    /* Correo Cliente */


        $cuidador_file = $PATH_TEMPLATE.'/template/mail/reservar/confirmacion/confirmacion_cuidador.php';
        $mensaje_cuidador = file_get_contents($cuidador_file);

        $fin = strtotime( str_replace("/", "-", $_POST['service_end']) );

        $mensaje_cuidador = str_replace('[mascotas]', $mascotas, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[desglose]', $desglose, $mensaje_cuidador);
        
        $mensaje_cuidador = str_replace('[ADICIONALES]', $adicionales, $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[TRANSPORTE]', $transporte, $mensaje_cuidador);
        

        $mensaje_cuidador = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cuidador);

        $mensaje_cuidador = str_replace('[tipo_servicio]', trim($servicio["tipo"]), $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[id_reserva]', $servicio["id_reserva"], $mensaje_cuidador);

        $mensaje_cuidador = str_replace('[inicio]', date("d/m", $servicio["inicio"]), $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[fin]', date("d/m", $servicio["fin"]), $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[anio]', date("Y", $servicio["fin"]), $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[tiempo]', $servicio["duracion"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[tipo_pago]', $servicio["metodo_pago"], $mensaje_cuidador);

        $mensaje_cuidador = str_replace('[name_cliente]', $cliente["nombre"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[avatar]', kmimos_get_foto($cliente["id"]), $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[telefonos_cliente]', $cliente["telefono"], $mensaje_cuidador);
        $mensaje_cuidador = str_replace('[correo_cliente]', $cliente["email"], $mensaje_cuidador);

        $mensaje_cuidador = str_replace('[name_cuidador]', $cuidador["nombre"], $mensaje_cuidador);

        $mensaje_cuidador = str_replace('[TOTALES]', $totales_plantilla, $mensaje_cuidador);

        echo $mensaje_cuidador = get_email_html($mensaje_cuidador);

        wp_mail( $cliente["email"], "Confirmación de Reserva", $mensaje_cuidador);

?>
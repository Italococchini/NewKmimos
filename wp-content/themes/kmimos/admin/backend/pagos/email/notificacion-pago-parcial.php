<?php
    $raiz = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
    include($raiz.'/wp-load.php');

    
    $mensaje = buildEmailTemplate(
        'reservar/cliente/nueva_tienda', 
        $INFORMACION
    );

    $mensaje = buildEmailHtml(
        $mensaje, 
        [
            'user_id' => $cliente["id"], 
            'barras_ayuda' => true,
            'test' => true
        ]
    );

    if( isset($NO_ENVIAR) ){
        // showEmail( $mensaje );
        // sendEmailTest( "Solicitud de reserva - CLIENTE", $mensaje );
    }else{
        wp_mail( $cliente["email"], "Solicitud de reserva", $mensaje);
    }
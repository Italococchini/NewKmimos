<?php

    date_default_timezone_set('America/Mexico_City');

    $raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
    include_once($raiz."/vlz_config.php");

    $tema = (dirname(dirname(dirname(dirname(__DIR__)))));

    include_once($tema."/procesos/funciones/db.php");
    include_once($tema."/procesos/funciones/generales.php");

    $db = new db( new mysqli($host, $user, $pass, $db) );

    $data["data"] = array();

    $r = $db->get_results("
        SELECT 
            terminos_aceptados.*,
            CONCAT(email.user_email, ' / ', movil.meta_value) AS contactos,
            CONCAT(nombre.meta_value, ' ', apellido.meta_value) AS usuario,
            tipo.meta_value AS tipo
        FROM 
            terminos_aceptados
        INNER JOIN wp_users AS email ON ( email.ID = terminos_aceptados.user_id ) 
        INNER JOIN wp_usermeta AS nombre ON ( nombre.user_id = terminos_aceptados.user_id AND nombre.meta_key = 'first_name' ) 
        INNER JOIN wp_usermeta AS apellido ON ( apellido.user_id = terminos_aceptados.user_id AND apellido.meta_key = 'last_name' ) 
        INNER JOIN wp_usermeta AS movil ON ( movil.user_id = terminos_aceptados.user_id AND movil.meta_key = 'user_mobile' ) 
        INNER JOIN wp_usermeta AS tipo ON ( tipo.user_id = terminos_aceptados.user_id AND tipo.meta_key = 'wp_capabilities' ) 
    ");

    if( $r != false ){

        foreach ($r as $key => $value) {

            $tipo = "";
            switch ( $value->tipo ) {
                case 'a:1:{s:10:"subscriber";b:1;}':
                    $tipo = "Cliente";
                break;
                case 'a:1:{s:6:"vendor";b:1;}':
                    $tipo = "Cuidador";
                break;
            }

            $value->fecha = date( "d/m/Y h:i a", strtotime($value->fecha) );

            $data["data"][] = array(
                $value->user_id,
                utf8_encode($value->usuario),
                utf8_encode($value->contactos),
                utf8_encode($tipo),
                $value->ip,
                $value->fecha
            );
        }
    }

    print_r($data);

    echo json_encode($data);

?>
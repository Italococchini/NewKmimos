<?php
    session_start();

    date_default_timezone_set('America/Mexico_City');

    $raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
    include_once($raiz."/vlz_config.php");

    $tema = (dirname(dirname(dirname(dirname(__DIR__)))));
    include_once($tema."/procesos/funciones/db.php");
    include_once($tema."/procesos/funciones/generales.php");

    $db = new db( new mysqli($host, $user, $pass, $db) );

    $code = $_POST['code'];

    $estatus_bloqueados = [
        'in_progress',
        'completed',
    ];

    $solicitud = $db->get_row("SELECT * FROM cuidadores_pagos WHERE md5(id) = '{$code}'" );

    if( isset($solicitud->estatus) && !empty($solicitud) ){
        if( empty($solicitud->openpay_id) && !in_array( $solicitud->estatus, $estatus_bloqueados) ){
            $db->query( "DELETE FROM cuidadores_pagos WHERE md5(id) = '{$code}'" );
        }else{
            echo 'No se puede procesar la solicitud';
        }
    }else{
        echo 'Registro no encontrado';
    }

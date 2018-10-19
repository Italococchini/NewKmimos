<?php
    session_start();
    date_default_timezone_set('America/Mexico_City');

    include_once('../lib/pagos.php');
 
    $actual = time();

    extract( $_POST );
    
    $pagos_lists = $pagos->getPagoGeneradosTotal( $desde, $hasta );
    if( isset($pagos_lists[0]->total) ){
        echo json_encode($pagos_lists[0], JSON_UNESCAPED_UNICODE);
        exit();
    }

    echo json_encode(['total'=>0], JSON_UNESCAPED_UNICODE);

?>
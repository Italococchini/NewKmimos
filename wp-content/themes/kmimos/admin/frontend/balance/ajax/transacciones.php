<?php

	require_once(dirname(dirname(dirname(dirname(__DIR__)))).'/lib/pagos/pagos_cuidador.php');

	$data = array(
        "data" => array()
    );

    $actual = time();

    extract( $_POST );

    $transacciones = $pagos->db->get_results( "
    	SELECT * 
    	FROM cuidadores_transacciones 
    	WHERE user_id = {$ID} and ( fecha >= '{$desde} 00:00:00' and  fecha <= '{$hasta} 23:59:00' ) ");

    if( !empty($transacciones) ){	
	    foreach ( $transacciones as $transaccion ) {
	    	$data['data'][] = array(
	    		date('Y-m-d', strtotime($transaccion->fecha)),
	    		$transaccion->id,
	    		$transaccion->descripcion,
	    		"$ ".$transaccion->monto,
                "$ -".$transaccion->comision,
	    	);
	    }
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
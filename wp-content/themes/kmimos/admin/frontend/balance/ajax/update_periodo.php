<?php

	require_once(dirname(dirname(dirname(dirname(__DIR__)))).'/lib/pagos/pagos_cuidador.php');

    extract( $_POST );

	$hoy = date( "Y/m/d H:i:s" );
	$proximo_pago = $pagos->dia_semana( $dia, $periodo );

	$cuidador_periodo = [
		'periodo'=> $periodo,
		'dia'=> $dia,
		'proximo_pago'=> $proximo_pago
	];

	$dato = serialize($cuidador_periodo);
    $result = $pagos->db->query( 
    	"UPDATE cuidadores SET pago_periodo = '{$dato}' WHERE user_id={$ID}" 
    );

    echo $proximo_pago;
<?php

	require_once(dirname(dirname(dirname(dirname(__DIR__)))).'/lib/pagos/pagos_cuidador.php');

    extract( $_POST );

	$hoy = date( "Y/m/d H:i:s" );

    if( $periodo == 'mensual' ){    
        if( date('d') <= $primera_quincena ){
            $dia = $primera_quincena;
        }else{
            $dia = $segunda_quincena;
        }
    }

	$proximo_pago = $pagos->dia_semana( $dia, $periodo );

	$cuidador_periodo = [
		'periodo'=> $periodo,
		'dia'=> $dia,
		'proximo_pago'=> $proximo_pago,
        'primera_quincena' => $primera_quincena, 
        'segunda_quincena' => $segunda_quincena, 
	];

	$dato = serialize($cuidador_periodo);
    $sql = "UPDATE cuidadores SET pago_periodo = '{$dato}' WHERE user_id={$ID}";
    $result = $pagos->db->query( $sql );
    
    echo $proximo_pago;

   // print_r($cuidador_periodo);
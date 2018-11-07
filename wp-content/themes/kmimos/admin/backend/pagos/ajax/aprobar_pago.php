<?php
	session_start();

    date_default_timezone_set('America/Mexico_City');

    $raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
    //include_once($raiz."/vlz_config.php");
    include_once($raiz."/wp-load.php");

    $tema = (dirname(dirname(dirname(dirname(__DIR__)))));
	include_once($tema.'/lib/pagos/pagos_cuidador.php');
	 
    $comentarios = $_POST['comentario'];
    $solicitudes = $_POST['users'];
    $admin_id = $_POST['ID'];
    $accion = $_POST['accion'];
    $_pagos = $_SESSION['pago_cuidador'];

    foreach ($solicitudes as $item) {

    	if( array_key_exists($item['user_id'], $_pagos) ){

            foreach ($item['reservas'] as $reserva_id) {
                $obj = $_pagos[ $item['user_id'] ];
                $detalle = $obj->detalle[ $reserva_id ];

                $existe  = $pagos->db->query( 'SELECT * FROM cuidadores_reservas WHERE reserva_id = '.$reserva_id );
                if( !isset( $existe->id ) ){
                    $sql = "INSERT INTO `cuidadores_reservas`( 
                        `user_id`,
                        `reserva_id`,
                        `checkin`,
                        `checkout`,
                        `total_dias`,
                        `disponible`,
                        `total_reserva`,
                        `estatus`
                    ) VALUES (
                        ".$item['user_id'].",
                        ".$reserva_id.",
                        '".$detalle['booking_start']."',
                        '".$detalle['booking_end']."',
                        0,
                        ".$detalle['monto'].",
                        '".$detalle['monto']."',
                        'pendiente'
                    );";
                    $pagos->db->query( $sql );
                }
            }
    	}
    }
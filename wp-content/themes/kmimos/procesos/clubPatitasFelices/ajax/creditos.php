<?php

	session_start();
	include ( '../../../../../../wp-load.php' );

    $data = array(
        "total" => 0
    );

	$user = wp_get_current_user();
	if( isset($user->ID) ){	
		$user_id = $user->ID;

		$creditos = $wpdb->get_var("select sum(monto) as total from cuidadores_transacciones where tipo='saldo_club' and user_id = ".$user_id);
		 
		if( $creditos > 0 ){
			$data['total'] = $creditos;
		}else{
			$data['total'] = 0;
		}
	}

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

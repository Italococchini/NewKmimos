<?php

	session_start();
	include ( '../../../../../../wp-load.php' );

	$creditos = $wpdb->get_results("select * from cuidadores_transacciones where tipo='saldo_club' and user_id = ".$user_id);

    $data = array(
        "data" => array()
    );

	if( !empty($creditos) ){
		$count=0;
		foreach ($creditos as $row) {
			$count++;
			$data["data"][] = array(
				$count,
                date('Y-m-d',strtotime($row->fecha)),
                utf8_encode($row->descripcion),
                $row->monto
            );
		}
	}

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

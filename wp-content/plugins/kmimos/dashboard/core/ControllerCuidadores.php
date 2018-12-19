<?php
require_once('base_db.php');
require_once('GlobalFunction.php');

function getmetaUser($user_id=0){
	$condicion = " AND m.meta_key IN ( 'nickname', 'first_name', 'last_name', 'user_phone', 'user_mobile', 'user_referred', 'user_address')";
	$result = get_metaUser($user_id, $condicion);
	$data = [
		'first_name' =>'', 
		'last_name' =>'', 
		'user_phone' =>'', 
		'user_mobile' =>'',
		'user_referred' =>'',
		'nickname' =>'',
		'user_address' =>'',
	];
	if( !empty($result) ){
		foreach ( $result['rows'] as $row ) {
			$data[$row['meta_key']] = utf8_encode( $row['meta_value'] );
		}
	}
	$data = merge_phone($data);
	return $data;
}

function getEstadoMunicipio($estados, $municipios){
	// buscar estados
	$resultado['estado'] = '';
	$resultado['municipio'] = '';
	if(!empty($estados)){
		$e = explode('=', $estados);
		$estado = '';
		foreach ($e as $value) {
			if( $value > 0 ){
				$sql_e = 'select * from states where country_id = 1 and id = '.$value;
				$result = get_fetch_assoc($sql_e);
				$resultado['estado'] = (isset($result['rows'][0]['name'])) ? $result['rows'][0]['name'] : '';
				break;
			}
		}
	}
	//buscar municipios
	if(!empty($municipios)){
		$e = explode('=', $municipios);
		$muni = '';
		foreach ($e as $value) {
			if( !empty( trim( $value) ) ){
				$sql_m = 'select * from locations where id = '.$value;
				$result = get_fetch_assoc($sql_m);
				$resultado['municipio'] = (isset($result['rows'][0]['name'])) ? $result['rows'][0]['name'] : '';
				break;
			}
		}
	}
	return $resultado;
}

function getDireccion( $user_id ){

	$sql = "
		SELECT b.* 
		FROM cuidadores as c 
			INNER JOIN ubicaciones as b ON b.cuidador = c.id
		WHERE c.user_id = {$user_id}
	";
	
	$result = get_fetch_assoc($sql);
	return $result;

}

function getUsers($desde="", $hasta=""){
	$filtro_adicional = "";
	if( !empty($desde) && !empty($hasta) ){
		$filtro_adicional .= (!empty($filtro_adicional))? ' AND ' : '' ;
		$filtro_adicional .= "u.user_registered >= '{$desde} 00:00:00' and u.user_registered <='{$hasta} 23:59:59'";
	}

	$filtro_adicional = (!empty($filtro_adicional))? ' WHERE '.$filtro_adicional : $filtro_adicional ;
	$sql = "
		SELECT u.*, c.activo as 'estatus', c.direccion, p.post_title as 'cuidador_title', p.ID as 'cuidador_post' 
		FROM wp_users as u
			INNER JOIN cuidadores as c ON c.user_id = u.ID
			INNER JOIN wp_posts as p ON p.post_author = u.ID AND p.post_type = 'petsitters'
		{$filtro_adicional}
		ORDER BY DATE_FORMAT(u.user_registered,'%d-%m-%Y') DESC;
	";
	
	$result = get_fetch_assoc($sql);
	return $result;
}

function getCountReservas( $author_id=0, $interval=12, $desde="", $hasta=""){

	$filtro_adicional = "";
	if( !empty($landing) ){
		$filtro_adicional = " source = '{$landing}'";
	}
	if( !empty($desde) && !empty($hasta) ){
		$filtro_adicional .= (!empty($filtro_adicional))? ' AND ' : '' ;
		$filtro_adicional .= " 
			DATE_FORMAT(post_date_gmt, '%m-%d-%Y') between DATE_FORMAT('{$desde}','%m-%d-%Y') and DATE_FORMAT('{$hasta}','%m-%d-%Y')
		";
	}else{
		$filtro_adicional .= (!empty($filtro_adicional))? ' AND ' : '' ;
		$filtro_adicional .= " MONTH(post_date_gmt) = MONTH(NOW()) AND YEAR(post_date_gmt) = YEAR(NOW()) ";
	}


	$filtro_adicional = ( !empty($filtro_adicional) )? " WHERE {$filtro_adicional}" : $filtro_adicional ;

	$result = [];
	$sql = "
		SELECT 
			count(c.ID) as cant
		FROM wp_posts as p
				left join wp_postmeta as o ON o.post_id = p.ID and o.meta_key = '_booking_product_id'
				left join wp_posts as c ON c.ID = o.meta_value 
		WHERE p.post_type = 'wc_booking' 
			AND not p.post_status like '%cart%'
			AND p.post_status = 'confirmed' 
			AND c.post_author = $author_id
			AND p.post_date_gmt > DATE_SUB(CURDATE(), INTERVAL {$interval} MONTH)
		GROUP BY c.post_author
	";

	$result = get_fetch_assoc($sql);
	return $result;
}
function getReservasByCuidador( $cuidador_id ){


}


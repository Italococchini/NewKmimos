<?php
require_once('base_db.php');
require_once('GlobalFunction.php');

function get_primera_reserva( $user_id = 0 ){
	$sql = "
		SELECT * 
		FROM wp_posts 
		WHERE post_type = 'wc_booking' AND post_author = 125222 
			ORDER BY post_date_gmt asc limit 1	
	"; 
	$result = get_fetch_assoc($sql);
	return $result;
}

function get_metaCuidador($user_id=0){
	$condicion = " AND m.meta_key IN ('first_name', 'last_name', 'user_phone', 'user_mobile', 'user_referred')";
	$result = get_metaUser($user_id, $condicion);
	$data = [
		'id' =>'',
		'email' =>'',
		'first_name' =>'', 
		'last_name' =>'', 
		'user_phone' =>'', 
		'user_mobile' =>'',
	];
	if( !empty($result) ){
		foreach ( $result['rows'] as $row ) {
			$data['email'] = utf8_encode( $row['user_email'] );
			$data['id'] = $row['user_id'];
			$data[$row['meta_key']] = utf8_encode( $row['meta_value'] );
		}
	}
	return $data;
}
function get_metaCliente($user_id=0){
	$condicion = " AND m.meta_key IN ('first_name', 'last_name', 'user_phone', 'user_mobile')";
	$result = get_metaUser($user_id, $condicion);
	$data = [
		'id' =>'',
		'email' =>'',
		'first_name' =>'', 
		'last_name' =>'', 
		'user_phone' =>'', 
		'user_mobile' =>'',
	];
	if( !empty($result) ){
		foreach ( $result['rows'] as $row ) {
			$data['email'] = utf8_encode( $row['user_email'] );
			$data['id'] = $row['user_id'];
			$data[$row['meta_key']] = utf8_encode( $row['meta_value'] );
		}
	}
	return $data;
}

function getSolicitud($desde="", $hasta=""){

	$filtro_adicional = "";

	if( !empty($desde) && !empty($hasta) ){
		$filtro_adicional = " AND ( p.post_date >= '{$desde} 00:00:00' and  p.post_date <= '{$hasta} 23:59:59' )";
	}else{
		$filtro_adicional = " AND MONTH(p.post_date) = MONTH(NOW()) AND YEAR(p.post_date) = YEAR(NOW()) ";
	}

	$sql = "
		SELECT 
			p.ID as Nro_solicitud,
			DATE_FORMAT(p.post_date,'%Y-%m-%d') as Fecha_solicitud,
			p.post_status as Estatus,

			fd.meta_value as Servicio_desde,
			fh.meta_value as Servicio_hasta,
			d.meta_value as Donde,
			w.meta_value as Cuando,
			t.meta_value as Hora,

			cl.meta_value as Cliente_id,
			cu.post_author as Cuidador_id
		FROM wp_postmeta as m
			LEFT JOIN wp_posts as p  ON p.ID = m.post_id 
			LEFT JOIN wp_postmeta as fd ON p.ID = fd.post_id and fd.meta_key = 'service_start' 	
			LEFT JOIN wp_postmeta as fh ON p.ID = fh.post_id and fh.meta_key = 'service_end' 		
			LEFT JOIN wp_postmeta as d  ON p.ID = d.post_id  and d.meta_key  = 'meeting_where' 	
			LEFT JOIN wp_postmeta as t  ON p.ID = t.post_id  and t.meta_key  = 'meeting_time' 	
			LEFT JOIN wp_postmeta as w  ON p.ID = w.post_id  and w.meta_key  = 'meeting_when' 	

			LEFT JOIN wp_postmeta as cl ON p.ID = cl.post_id and cl.meta_key = 'requester_user' 
			LEFT JOIN wp_postmeta as pc ON p.ID = pc.post_id and pc.meta_key = 'requested_petsitter' 
			LEFT JOIN wp_posts as cu ON cu.ID = pc.meta_value 
		WHERE 
			m.meta_key = 'request_status'
			{$filtro_adicional}
		ORDER BY p.ID DESC
		;
	";
 

	$result = get_fetch_assoc($sql);
	return $result;
}


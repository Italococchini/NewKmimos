<?php
require_once('base_db.php');
require_once('GlobalFunction.php');

function getmetaUser($user_id=0){
	//$condicion = " AND m.meta_key IN ( 'nickname', 'first_name', 'last_name', 'user_phone', 'user_mobile', 'user_referred')";
	//$result = get_metaUser($user_id, $condicion);
	$result = get_metaUser($user_id);
	$data = [
		'first_name' =>'', 
		'last_name' =>'', 
		'user_phone' =>'', 
		'user_mobile' =>'',
		'user_referred' =>'',
		'nickname' => '',
	];
	if( !empty($result) ){
		foreach ( $result['rows'] as $row ) {
			$data[$row['meta_key']] = utf8_encode( $row['meta_value'] );
		}
	}
	$data = merge_phone($data);
	return $data;
}

function getUsers($desde="", $hasta=""){
	$filtro_adicional = "";
	if( !empty($desde) && !empty($hasta) ){
		$filtro_adicional .= " 
			AND ( u.user_registered >= '{$desde}' AND u.user_registered <= '{$hasta}' )
		";
	}
	$sql = "
		SELECT u.*
		FROM wp_users as u
			LEFT JOIN cuidadores as c ON c.user_id = u.ID
		WHERE c.id is NULL
		{$filtro_adicional}
		ORDER BY DATE_FORMAT(u.user_registered,'%d-%m-%Y') DESC;
	";	
	$result = get_fetch_assoc($sql);
	return $result;
}

function getCountReservas( $author_id=0 ){

	$result = [];
	$sql = "
		SELECT 
			count(ID) as cant
		FROM wp_posts
		WHERE post_type = 'wc_booking' 
			AND not post_status like '%cart%'
			AND post_status = 'confirmed' 
			AND post_author = {$author_id}
	";
	$result = get_fetch_assoc($sql);
	return $result;
}

function getMascotas($user_id){
	if(!$user_id>0){ return []; }

	global $wpdb;
	$mascotas_cliente = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_author = '{$user_id}' AND post_type='pets' AND post_status = 'publish'");
    $mascotas = array();
    foreach ($mascotas_cliente as $key => $mascota) {
        $_anio = get_post_meta($mascota->ID, "birthdate_pet", true);
        $_anio = str_replace("/", "-", $_anio);
        $anio = strtotime($_anio);
        $anio = ceil ( time()-$anio );
        $edad = (@date("Y", $anio)-1970);
	
$tamanos[] = 'Pequeño';
$tamanos[] = 'Mediano';
$tamanos[] = 'Grande';
$tamanos[] = 'Gigante';
$tamano_pet = '--';
$r = get_post_meta($mascota->ID, "size_pet", true);
try{
	$tamano_pet = $tamanos[$r];
} catch(Exception $e){ $tamano_pet = ''; }

        $mascotas[] = array(
            "nombre" => $mascota->post_title,
            "raza" => get_post_meta($mascota->ID, "breed_pet", true),
            "edad" => $edad,
            "tamano" => $tamano_pet ,

        );
    }
	return $mascotas;
}

function getRazaDescripcion($id, $razas){
	$nombre = "[{$id}]";
	if($id > 0){
		if( !empty($razas) ){
			if(array_key_exists($id, $razas)){
				$nombre = $razas[$id];
			}
		}
	}
	return $nombre;
}
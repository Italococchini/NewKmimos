<?php 
	$raiz = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
	include_once("../funciones/generales.php");

	if($portada != ""){
	    $dir = $raiz."/wp-content/uploads/{$sub_path}/";
	    @mkdir($dir);
	    $path_origen = $raiz."/imgs/Temp/".$portada;
	    $path_destino = $dir.$portada;

	    if( file_exists($path_origen) ){
	        copy($path_origen, $path_destino);
	        unlink($path_origen);
	    }

	    $img_anterior = $db->get_var("SELECT meta_key FROM wp_usermeta WHERE user_id = {$user_id} AND meta_key = 'name_photo';");
	    if( $img_anterior != false ){
		    if( file_exists($dir.$img_anterior) ){
		        unlink($dir.$img_anterior);
		    }

		    $img_portada = "UPDATE wp_usermeta SET meta_value = '{$portada}' WHERE user_id = {$user_id} AND meta_key = 'name_photo';";
	    }else{
	    	$img_portada = "INSERT INTO wp_usermeta VALUES ( NULL, {$user_id}, 'name_photo', '{$portada}');";
	    }
	}


	kmimos_update_user_meta($user_id, "first_name", utf8_decode($first_name) );
	kmimos_update_user_meta($user_id, "last_name", utf8_decode($last_name));
	kmimos_update_user_meta($user_id, "user_phone", $phone);
	kmimos_update_user_meta($user_id, "user_mobile", $mobile);
	kmimos_update_user_meta($user_id, "mobile", $mobile);
	kmimos_update_user_meta($user_id, "user_referred", $referred);
	kmimos_update_user_meta($user_id, "description", utf8_decode($descr));
	kmimos_update_user_meta($user_id, "nickname", utf8_decode($nickname));
	kmimos_update_user_meta($user_id, "user_recibir_fotos", utf8_decode($user_recibir_fotos));


if( is_petsitters($user_id) ){
		if(isset($banco) && isset($banco_cuenta) && isset($titular)){
			if(!empty($banco) && !empty($banco_cuenta) && strlen($banco_cuenta)==18 && !empty($titular)){
				$datos_banco = serialize([ 
					'banco'=> utf8_decode($banco),
					'cuenta'=> $banco_cuenta,
					'titular'=> utf8_decode($titular)
				]);
				$db->query("UPDATE cuidadores SET banco = '{$datos_banco}' WHERE user_id = ".$user_id);
			}else{
				$datos_banco = serialize([ 
					'banco'=> '',
					'cuenta'=> '',
					'titular'=> ''
				]);
				$respuesta = array(
					"status" => "NO",
					"msg" => "Debe completar los datos de facturacion" 
				);
				$db->query("UPDATE cuidadores SET banco = '{$datos_banco}' WHERE user_id = ".$user_id);
				return;
			}	
		}
	}


				

	$sql  = "UPDATE wp_users SET display_name = '{$nickname}' WHERE ID = {$user_id}; ";
	if( isset($img_portada) ){
		$sql .= $img_portada;
	}

	$description = $db->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id = {$user_id} AND meta_key = 'description'");
	if( $description == false ){
		$sql .= "INSERT INTO wp_usermeta VALUES (NULL, '{$user_id}', 'description', '{$descr}');";
	}

	$pass_change = "";
	if( $password != '' ){
		$password = md5($password);
		$sql .= "UPDATE wp_users SET user_pass = '{$password}' WHERE ID = {$user_id}; ";
		$pass_change = "SI";
	}

	$db->query_multiple( utf8_decode($sql) );

	if( $password != '' ){
		include_once($raiz."/wp-load.php");

		$info = array();
	    $info['user_login']     = sanitize_user($username, true);
	    $info['user_password']  = sanitize_text_field($password2);

	    $user_signon = wp_signon( $info, true );
	    wp_set_auth_cookie($user_signon->ID);
	}

	$respuesta = array(
		"status" 	  => "OK",
		"username"	  => $username,
		"pass_change" => $pass_change
	);
?>

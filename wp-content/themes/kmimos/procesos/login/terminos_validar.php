<?php
	include("../../../../../wp-load.php");

	global $wpdb;

	extract($_POST);
	$user_id = $wpdb->get_var( "SELECT ID FROM wp_users WHERE user_email = '{$usu}' or user_login = '{$usu}'" );

	if( $user_id > 0 ){
		$existe = $wpdb->get_var("SELECT id FROM terminos_aceptados WHERE user_id = '{$user_id}' ");
	}

	if( isset($existe) && $existe > 0 ){
		echo json_encode(["sts" => "si"]);
	}else{
		echo json_encode(["sts" => "no"]);
	}
	

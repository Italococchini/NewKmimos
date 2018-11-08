<?php

    include(__DIR__."../../../../../../vlz_config.php");
    include_once("../funciones/db.php");
	include ('../generales/save_terms.php');

    extract($_POST);
    
    $db = new db( new mysqli($host, $user, $pass, $db) );

    $user_id = $db->get_var( "SELECT ID FROM wp_users WHERE user_email = '{$usu}'  or user_login = '{$usu}' " );
	$existe = null;
	if( $user_id > 0 ){
		save_user_accept_terms( $user_id, $db );
	}
	echo json_encode(['sts'=>'si']);

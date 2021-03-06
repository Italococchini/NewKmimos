<?php
	
	extract($_POST);
    $raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
    include_once($raiz."/wp-load.php");

    date_default_timezone_set('America/Mexico_City');

    global $wpdb;

    $PATH_TEMPLATE = dirname(dirname(dirname(dirname(__DIR__))));

    $_user_ID = $wpdb->get_var("SELECT ID FROM wp_users WHERE user_email = '{$email}' ");

    $ORIGINAL = get_user_meta($_user_ID, 'kmisaldo', true)+0;

    $CLIENTE = get_user_meta($_user_ID, 'first_name', true)." ".get_user_meta($_user_ID, 'last_name', true);
    $saldo += 0;

    if( $ORIGINAL == 0 ){
    	$wpdb->query("INSERT INTO wp_usermeta VALUES (NULL, '{$_user_ID}', 'kmisaldo', '{$saldo}') ");
    }else{
    	$wpdb->query("UPDATE wp_usermeta SET meta_value = '{$saldo}' WHERE user_id = {$_user_ID} AND meta_key = 'kmisaldo' ");
    }

    if( $ORIGINAL != $saldo ){
        $file = $PATH_TEMPLATE.'/template/mail/saldos/admin.php';
        $mensaje = file_get_contents($file);

        $mensaje = str_replace('[CLIENTE]', $CLIENTE, $mensaje);
        $mensaje = str_replace('[CLIENTE_EMAIL]', $email, $mensaje);
        $mensaje = str_replace('[ORIGINAL]', number_format( $ORIGINAL, 2, ',', '.'), $mensaje);
        $mensaje = str_replace('[FINAL]', number_format( $saldo, 2, ',', '.'), $mensaje);
        $mensaje = str_replace('[FECHA]', date("d/m/Y H:i a") , $mensaje);
        
        $mensaje = get_email_html($mensaje);    

        wp_mail( "a.veloz@kmimos.la", "Actualización de Saldo", $mensaje);
        wp_mail( "chaudaryy@gmail.com", "Actualización de Saldo", $mensaje);
    }

	exit;
?>
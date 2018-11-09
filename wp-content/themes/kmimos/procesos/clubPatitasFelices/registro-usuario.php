<?php

	session_start();
	include ( '../../../../../wp-load.php' );

// Parametros

	$nombre  = $_POST['nombre'];
	$apellido  = $_POST['apellido'];
	$email = $_POST['email'];
 	$meta = explode('@', $email);
	$username = $meta[0];
	$password='';

	// Verificar si existe el email
	$user = get_user_by( 'email', $email );	

	$mail_seccion_usuario ='';

	//$URL_SITE = get_home_url();
	$URL_SITE = 'http://kmimosmx.sytes.net/QA2/';

 	// Registro de Usuario en Kmimos
	if(!isset($user->ID)){
	    $password = md5(wp_generate_password( 5, false ));
	    $user_id  = wp_create_user( $username, $password, $email );
	
	    wp_update_user( array( 'ID' => $user_id, 'display_name' => "{$nombre}" ));		

		// Registrado desde el landing page
		update_user_meta( $user_id, 'first_name', $nombre );
		update_user_meta( $user_id, 'last_name', $apellido );
		update_user_meta( $user_id, 'user_referred', 'Amigo/Familiar' );
		update_user_meta( $user_id, 'user_mobile', '' );
		update_user_meta( $user_id, "landing-club-patitas", date('Y-m-d H:i:s') ); 		

	    $user = new WP_User( $user_id );
	    $user->set_role( 'subscriber' );

	    //MESSAGE
        $mail_file = realpath('../../template/mail/clubPatitas/nuevo_usuario.php');
        $mail_seccion_usuario = file_get_contents($mail_file);

        //USER LOGIN
        $user = get_user_by( 'ID', $user_id );
        wp_set_current_user($user_id, $user->user_login);
        wp_set_auth_cookie($user_id);
	}

 	// Registro de Usuario en Club de patitas felices
 	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );
 	if( empty($cupon) || $cupon == null ){
		// generar cupon
		$cupon = substr(trim($nombre), 0,1);
		$cupon .= substr(trim($apellido), 0,1);
		$cupon .= $user->ID;
		$cupon = strtoupper($cupon);
		$id = kmimos_crear_cupon( $cupon, 150 ); 		
		if( $id > 0 ){
			update_user_meta( $user->ID, 'club-patitas-cupon', utf8_encode($cupon) );

		    //MESSAGE
	        $mail_file = realpath('../../template/mail/clubPatitas/nuevo_miembro.php');

	        $message_mail = file_get_contents($mail_file);

	        $message_mail = str_replace('[NUEVOS_USUARIOS]', $mail_seccion_usuario, $message_mail);
	        $message_mail = str_replace('[URL_IMG]', $URL_SITE."/wp-content/themes/kmimos/images", $message_mail);

	        $message_mail = str_replace('[name]', $nombre.' '.$apellido, $message_mail);
	        $message_mail = str_replace('[email]', $email, $message_mail);
	        $message_mail = str_replace('[pass]', $password, $message_mail);
	        $message_mail = str_replace('[url]', site_url(), $message_mail);
	        $message_mail = str_replace('[CUPON]', $cupon, $message_mail);

	        wp_mail( 'italococchini@gmail.com', "¡Bienvenid@ al club!", $message_mail);

		}
 	}

 	echo $cupon;
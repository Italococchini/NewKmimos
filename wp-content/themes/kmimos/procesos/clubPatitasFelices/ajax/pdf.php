<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

	session_start();
	include_once( '../../../../../../wp-load.php' );

	// usuario
	$user = wp_get_current_user();

	$nombre  = get_user_meta( $user->ID, 'first_name', true );
	$apellido  = get_user_meta( $user->ID, 'last_name', true );
	$email = $user->user_email;

	$mail_seccion_usuario ='';

	$URL_SITE = get_home_url();

 	// Registro de Usuario en Club de patitas felices
 	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );
 	if( !empty($cupon) ){
		// generar cupon
		if( $user->ID > 0 ){

	        $mail_file = realpath('../../../template/mail/clubPatitas/nuevo_miembro.php');

	        $message_mail = file_get_contents($mail_file);

	        $message_mail = str_replace('[NUEVOS_USUARIOS]', '', $message_mail);
	        $message_mail = str_replace('[URL_IMG]', $URL_SITE."/wp-content/themes/kmimos/images", $message_mail);

	        $message_mail = str_replace('[name]', $nombre.' '.$apellido, $message_mail);
	        $message_mail = str_replace('[email]', $email, $message_mail);
	        $message_mail = str_replace('[pass]', $password, $message_mail);
	        $message_mail = str_replace('[url]', site_url(), $message_mail);
	        $message_mail = str_replace('[CUPON]', $cupon, $message_mail);

	        require_once '../../../lib/vendor/autoload.php';
			$mpdf = new \Mpdf\Mpdf(['tempDir' => '../../../../../../wp-content/uploads/temp']);

			$html = utf8_encode($message_mail);

			$mpdf->WriteHTML($html);

			// Genera el fichero y fuerza la descarga
			$mpdf->Output('nombre.pdf','D'); exit;

 

		}
 	}


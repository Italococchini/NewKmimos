<?php

	session_start();
	include ( '../../../../../wp-load.php' );

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

	        require_once '../../../lib/dompdf/autoload.inc.php';
			require_once '../../../lib/dompdf/lib/html5lib/Parser.php';
			require_once '../../../lib/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
			require_once '../../../lib/dompdf/lib/php-svg-lib/src/autoload.php';
			require_once '../../../lib/dompdf/src/Autoloader.php';
			Dompdf\Autoloader::register();

			use Dompdf\Dompdf;

			// instantiate and use the dompdf class
			$dompdf = new Dompdf();
			$dompdf->loadHtml( $message_mail );

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('letter', 'portrait');

			// Render the HTML as PDF
			$dompdf->render();

			// Output the generated PDF to Browser
			$dompdf->stream();

		}
 	}


 	echo $cupon;
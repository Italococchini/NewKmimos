<?php
ob_start();
require __DIR__.'/vendor/autoload.php';
require dirname(__DIR__).'/wp-load.php';

use Spipu\Html2Pdf\Html2Pdf;

// Cargar datos
//$user = wp_get_current_user();

$user = (object) array(
	'ID' => 125503,
	'user_email' => 'italococchini@gmail.com',
);

$nombre  = get_user_meta( $user->ID, 'first_name', true );
$apellido  = get_user_meta( $user->ID, 'last_name', true );
$email = $user->user_email;

$mail_seccion_usuario ='';

$html = '';

$URL_SITE = get_home_url();

// Registro de Usuario en Club de patitas felices
$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );

if( !empty($cupon) ){
	// generar cupon
	if( $user->ID > 0 ){

        $mail_file = realpath('../wp-content/themes/kmimos/template/mail/clubPatitas/nuevo_miembro.php');

        $message_mail = file_get_contents($mail_file);

        $message_mail = str_replace('[NUEVOS_USUARIOS]', '', $message_mail);
        $message_mail = str_replace('[IMG_URL]', realpath('../wp-content/themes/kmimos/images'), $message_mail);

        $message_mail = str_replace('[name]', $nombre.' '.$apellido, $message_mail);
        $message_mail = str_replace('[email]', $email, $message_mail);
        $message_mail = str_replace('[pass]', $password, $message_mail);
        $message_mail = str_replace('[url]', site_url(), $message_mail);
        $message_mail = str_replace('[CUPON]', $cupon, $message_mail);

        $html = $message_mail;
	}else{
		$html = 'sin ID';
	}
}else{
	$html = 'sin cupon';
}

//print_r($html);


// Generar PDF
ob_end_clean();
include_once( '../wp-content/themes/kmimos/page-clubpatitas-compartir.php' );
$html = ob_get_contents();
ob_end_clean();
$html2pdf = new Html2Pdf();
$html2pdf->writeHTML( $html );
$html2pdf->output();

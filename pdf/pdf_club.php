<?php
	ob_start();
	require __DIR__.'/vendor/autoload.php';
	require dirname(__DIR__).'/wp-load.php';
	use Spipu\Html2Pdf\Html2Pdf;

try{
	$user = wp_get_current_user();
	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );

	$html = '<!DOCTYPE html>
	<html>
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
			<link rel="stylesheet" type="text/css" media="all" href="'.getTema().'/css/bootstrap.min.css"></link>
		    <link rel="stylesheet" type="text/css" media="all" href="'.getTema().'/css/club-patitas-felices.css"></link>
		</head>
		<body>
		<div style="width:1080px; background: url('.getTema().'/images/club-patitas/club_pdf.jpg);background-repeat:no-repeat;margin-top:30px;">
				<div id="compartir-club-cover" class="col-xs-12 col-sm-12 col-md-5" style="
				 width: 500px; margin-left:500px;height:600px;padding-top:100px;" >
					<div style="text-align:center;">
						<img src="'.getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-6.png">
						<h2 class="titulo">¡Ya eres parte del club!</h2>
						<p style="
						font-weight: bold; 
						font-size: 18px; 
						text-align: center;
						margin-top: 10%;
						">Tu código único del club</p>
					</div>
						<div class="cupon" style=" text-align:center;padding: 20px 20px 20px 20px; 
						border-radius: 10px;">
						'.strtoupper($cupon).'
						</div>
					<div style="text-align:center;">
						<p style="font-weight: bold; font-size: 16px;">Hemos enviado tu código a la cuenta de correo regístrada</p>
						<p>Recuerda, por cada vez que alguien use tu código y complete una reservación con un Cuidador
						Kmimos <strong>tú ganas $150 MXN</strong> acumulables.</p>
						<p style="font-weight:bold;font-size:16px;color:#0D7AD8;">¡Más compartes, más ganas! </p>
					</div>
				</div>
		</div>
		</body>
	</html>';

	$home_dir = realpath(dirname(__DIR__));
	$html = str_replace(get_home_url(), $home_dir, $html);
	$html2pdf = new Html2Pdf('L', 'Letter', 'en');
	$html2pdf->writeHTML( $html );
	ob_end_clean();
	$html2pdf->output("club_patitas_felices.pdf",'D');
}catch(Exception $e){
	echo $e->getMessage();
}
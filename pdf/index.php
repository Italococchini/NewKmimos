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
			<div class="content-compartir-club">
				<div id="compartir-club-cover" class="col-xs-12 col-sm-12 col-md-5" style="
				height: 800px; width: 35%; float:left; background-position:top center;
					background-image: url('.getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-1.jpg);
				"></div>
				<div class="col-xs-12 col-sm-12 col-md-7 compartir-section" style="height: 500px; width: 40%; float:left;">
					
					<div class="col-md-8 col-md-offset-2 text-center">
						
						<div class="row">
							<img src="'.getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-6.png">
							<h2 class="titulo">¡Ya eres parte del club!</h2>
							<p style="
								font-weight: bold; 
								font-size: 18px; 
								text-align: center;
								margin-top: 10%;
								">Tu código único del club</p>

							<div class="cupon" style="margin:0 auto;">
								'.strtoupper($cupon).'
							</div>

							<p style="font-weight: bold; font-size: 16px;">Hemos enviado tu código a la cuenta de correo regístrada</p>
							<p>Recuerda, por cada vez que alguien use tu código y complete una reservación con un Cuidador
							Kmimos <strong>tú ganas $150 MXN</strong> acumulables.</p>
							<p style="font-weight:bold;font-size:16px;color:#0D7AD8;">¡Más compartes, más ganas! </p>

						</div>

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
	$html2pdf->output();
}catch(Exception $e){
	echo $e->getMessage();
}
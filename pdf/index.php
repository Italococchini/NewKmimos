<?php
	require __DIR__.'/vendor/autoload.php';
	require dirname(__DIR__).'/wp-load.php';
	use Spipu\Html2Pdf\Html2Pdf;


	$user = wp_get_current_user();
	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );
	
    $url_img = get_home_url() .'/wp-content/themes/kmimos/images/club-patitas/';
    $no_top_menu = false;

    wp_enqueue_style('club_style', getTema()."/css/club-patitas-felices.css", array(), '1.0.0');
    wp_enqueue_style('club_responsive', getTema()."/css/responsive/club-patitas-felices.css", array(), '1.0.0');
	wp_enqueue_script('club_script', getTema()."/js/club-patitas-felices.js", array(), '1.0.0');
	ob_start();
?>

	<div class="content-compartir-club">
		<div id="compartir-club-cover" class="col-xs-12 col-sm-12 col-md-5" style="background-image: url(<?php echo getTema();?>/images/club-patitas/Kmimos-Club-de-las-patitas-felices-1.jpg);">
		</div>
		<div class="col-xs-12 col-sm-12 col-md-7 compartir-section" style="<?php echo $center_content; ?>">
			
			<dir class="col-md-8 col-md-offset-2">
				
				<div class="row">
					<img src="<?php echo getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-6.png'; ?>">
					<h2 class="titulo">¡Ya eres parte del club!</h2>
					<p style="
						font-weight: bold; 
						font-size: 18px; 
						text-align: center;
						margin-top: 10%;
						">Tu código único del club</p>

					<div class="cupon">
						<?php echo strtoupper($cupon); ?>
					</div>

					<p style="font-weight: bold; font-size: 16px;">Hemos enviado tu código a la cuenta de correo regístrada</p>
					<p>Recuerda, por cada vez que alguien use tu código y complete una reservación con un Cuidador
					Kmimos <strong>tú ganas $150 MXN</strong> acumulables.</p>
					<p style="font-weight:bold;font-size:16px;color:#0D7AD8;">¡Más compartes, más ganas! </p>

				</div>

			</dir>

		</div>
	</div>
<?php
	$html = ob_get_contents();
	ob_end_clean();

echo $html;

	// $html2pdf = new Html2Pdf();
	// $html2pdf->writeHTML( $html );
	// $html2pdf->output();

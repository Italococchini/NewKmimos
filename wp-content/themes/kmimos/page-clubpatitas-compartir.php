<?php
	/*
        Template Name: Club patitas Compartir
    */

	$user = wp_get_current_user();
	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );
 	if( empty($cupon) ){
		header('location:'.get_home_url().'/club-patitas-felices');
	}
	
    $url_img = get_home_url() .'/wp-content/themes/kmimos/images/club-patitas/';
    $no_top_menu = false;

    wp_enqueue_style('club_style', getTema()."/css/club-patitas-felices.css", array(), '1.0.0');
    wp_enqueue_style('club_responsive', getTema()."/css/responsive/club-patitas-felices.css", array(), '1.0.0');
	wp_enqueue_script('club_script', getTema()."/js/club-patitas-felices.js", array(), '1.0.0');

	get_header();
?>
	
	<div class="content-compartir-club">
		<aside id="compartir-club-cover" class="col-xs-12 col-sm-12 col-md-5" style="background-image: url(<?php echo getTema();?>/images/club-patitas/Kmimos-Club-de-las-patitas-felices-1.jpg);">
		</aside>
		<section class="col-xs-12 col-sm-12 col-md-7 compartir-section" style="<?php echo $center_content; ?>">
			
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

				<!-- INI Compartir -->
				<div class="row text-center" style="margin: 30px 0px; ">
					<div class="col-md-4 col-sm-12"><i class="fa fa-twitter"></i></div>
					<div class="col-md-4 col-sm-12"><i class="fa fa-facebook"></i></div>
					<div class="col-md-4 col-sm-12"><i class="fa fa-mail"></i></div>
				</div>
				<!-- FIN Compartir -->

				<div class="row" style="margin: 30px 0px; ">
					<div class="col-md-6 col-sm-12 col-xs-12 text-center">
						<button class="btn btn-club-azul" onClick="downloadPDF();">Descargar PDF</button>
					</div>
					<div class="col-md-6 col-sm-12 col-xs-12 text-center">
						<button class="btn btn-club-azul">Compartir ahora</button>
					</div>
				</div>

			</dir>

		</section>
	</div>
<?php 
	$no_display_footer = true;
 	get_footer(); 
?>

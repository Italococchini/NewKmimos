<?php
	/*
        Template Name: Club patitas Compartir
    */

/*error_reporting(E_ERROR || E_WARNING);
ini_set('display_errors', '1');
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

	$metas = '
		<meta property="og:url"           content="https://www.kmimos.com.mx" />
		<meta property="og:type"          content="website" />
		<meta property="og:title"         content="Kmimos - Club de las Patitas Felices" />
		<meta property="og:description"   content="Suma huellas a nuestro club y gana descuentos CUPON '.strtoupper($cupon).'" />
		<meta property="og:image"         content="https://www.kmimos.com.mx" />
	';

	get_header();
?>

	<!-- Load Facebook SDK for JavaScript -->
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/es_LA/sdk.js#xfbml=1&version=v2.9";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>

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

				<div class="row" style="margin: 30px 0px; ">
					<div class="col-md-6 col-sm-12 col-xs-12 text-center">
						<button class="btn btn-club-azul" onClick="downloadPDF();">Descargar PDF</button>
					</div>
					<div class="col-md-6 col-sm-12 col-xs-12 text-center">
						<button id="compartir_now" class="btn btn-club-azul">Compartir ahora</button>
					</div>
				</div>

				<!-- INI Compartir -->
				<div id="redes-sociales" class="row text-center" style="margin: 30px 0px; display:none;">
					<div class="col-md-6 col-md-offset-3">
						<div class="col-md-6 col-sm-6 col-xs-12">
							<a class="btn btn-info twitter-share-button"
								style="color:#fff;"
						  		href="https://twitter.com/intent/tweet?text=Suma%20huellas%20y%20gana%20descuentos%20CUPON%20<?php echo strtoupper($cupon);?>"
						  		target="_blank">
								<i class="fa fa-twitter"></i> Tweet
							</a>
						</div>
						<div class="col-md-6 col-sm-6 col-xs-12">
							<div class="fb-share-button" data-href="<?php strtoupper($cupon);?>" data-layout="button" data-size="large" data-mobile-iframe="true"><a class="fb-xfbml-parse-ignore" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u&amp;src=sdkpreparse">Compartir</a></div>
						</div>
					</div>
				</div>
				<!-- FIN Compartir -->

			</dir>

		</section>
	</div>
<?php 
	$no_display_footer = true;
 	get_footer(); 
?>

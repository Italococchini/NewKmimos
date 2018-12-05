<?php
	/*
        Template Name: Club patitas Creditos
    */

	/*
	error_reporting(E_ERROR || E_WARNING);
	ini_set('display_errors', '1');
	*/

	$user = wp_get_current_user();
	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );
 	if( empty($cupon) ){
		header('location:'.get_home_url().'/club-patitas-felices');
	}
	
    $url_img = get_home_url() .'/wp-content/themes/kmimos/images/club-patitas/';
    //$no_top_menu = true;
	$nombre = "";
	if( $user->ID > 0 ){
		$nombre = get_user_meta( $user->ID, 'first_name', true );
		$nombre .= " ";
		$nombre .= get_user_meta( $user->ID, 'last_name', true );
	}


    wp_enqueue_style('club_style', getTema()."/css/club-patitas-felices.css", array(), '1.0.0');
    wp_enqueue_style('club_responsive', getTema()."/css/responsive/club-patitas-felices.css", array(), '1.0.0');
	wp_enqueue_script('club_script', getTema()."/js/club-patitas-felices.js", array(), '1.0.1');

	$metas = '
		<meta property="og:url"           content="https://www.kmimos.com.mx" />
		<meta property="og:type"          content="website" />
		<meta property="og:title"         content="Kmimos - Club de las Patitas Felices" />
		<meta property="og:description"   content="Suma huellas a nuestro club y gana descuentos CUPON '.strtoupper($cupon).'" />
		<meta property="og:image"         content="https://www.kmimos.com.mx" />
	';

	$salir = wp_logout_url( get_home_url().'/club-patitas-felices' );

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

	<div class="content-compartir-club" style="z-index:5!important;">
		<aside id="compartir-club-cover" class="col-xs-12 col-sm-12 col-md-5" style="background-image: url(<?php echo getTema();?>/images/club-patitas/Kmimos-Club-de-las-patitas-felices-1.jpg);">
		</aside>
		<section class="col-xs-12 col-sm-12 col-md-7 compartir-section" style="<?php echo $center_content; ?>" style="padding-right: 0px!important; padding-left: 0px!important;   ">
			<div style="width: 100%;">
				<section class="text-center col-md-6 col-md-offset-3" style="padding: 5% 0px!important; ">
		            <a href="<?php echo get_home_url(); ?>/club-patitas-felices">Como funciona</a>
					<span style="padding:0px 10px;">|</span>
					<a href="<?php echo get_home_url(); ?>/club-patitas-felices/creditos">Ver mis créditos</a>
					<br>
					<br>
					<div class="row">
						<img src="<?php echo getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-6.png'; ?>">
						<h2 class="titulo">¡Tus cr&eacute;ditos del club <?php echo $nombre; ?>!</h2><hr>
						<p style="
							font-weight: bold; 
							font-size: 24px; 
							text-align: center;
							margin-top: 10%;
							color: #337ab7;
							">Créditos disponibles para usar</p>
						<div class="cupon" style="background: transparent!important; border: 2px solid #4f4f4f;">
							$ <span id="total_creditos">0.00</span>
						</div>

						 
						<p>*Los créditos disponibles No son intercambiables por dinero en efectivo. válido dentro de la
plataforma Kmimos. Aplican para cualquier servicio ofrecido por tu Cuidador Favorito. </p>
						 
					</div>
				</section>
				<div class="col-md-12 bg-primary" style="text-align: justify;  font-size: 17px;padding: 25px 0px; position: absolute;  bottom: 0px;">
					<div class="col-md-10 col-md-offset-1">
					Recuerda, por cada ves que alguien use tu código y complete una reservación con un Cuidador Kmimos, tu <strong>ganarás $150 MXN acumulables</strong> para que los uses en cualquiera de nuestros servicios		
					</div>
				</div>
			</div>
		</section>
	</div>
<?php 
	$no_display_footer = true;
 	get_footer(); 
?>

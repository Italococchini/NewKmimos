<?php
	/*
        Template Name: Club patitas
    */
    $url_img = get_home_url() .'/wp-content/themes/kmimos/images/club-patitas/';
    
    $no_top_menu = false;

    wp_enqueue_style('club_style', getTema()."/css/club-patitas-felices.css", array(), '1.0.0');

	wp_enqueue_script('club_script', getTema()."/js/club-patitas-felices.js", array(), '1.0.0');

	get_header();

	$display_registro = '';
	$center_content = '';
	$user = wp_get_current_user();
	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );
 	if( !empty($cupon) ){
		$display_registro = 'hidden';
		$center_content = 'margin:0 auto!important;';
	}
	$email = '';
	$nombre = '';
	$apellido = '';
	$readonly='';	
	if( isset($user->ID) && $user->ID > 0 ){
		$email = $user->user_email;
		$nombre = $user->user_firstname ;
		$apellido = $user->user_lastname ;
		$readonly = 'readonly';
	}

?>
	
	<div class="km-ficha-bg" style="background-image: url(<?php echo getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-2.png'; ?>)">
		<div class="overlay">
			<header>
				<div>
					<a>Ver mis créditos</a>
					|
					<a>Obtener mi código</a>
				</div>
			</header>
			<div class="col-md-6 pull-right text-center align-bottom">
				<img src="<?php echo getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-6.png'; ?>">
				<h2>Club de las patitas felices</h2>
				<p class="subtitle">El club que te recompensa por que tus amigos reserven estadías con Kmimos</p>
			</div>			
		</div>
	</div>
	<div class="body-club">
		<aside id="sidebar" class="col-xs-12 col-sm-12 col-md-4 <?php echo $display_registro; ?>">
			<div class="text-center col-md-10 col-md-offset-1 text-center">
				<h3 class="title-secundario">¡Estás a un paso de ser parte del club!</h3>
				<form method="post" action="#" id="form-registro">
					<input required class="form-control col-md-6" style="margin:5px 0px; border-radius: 10px;" type="text" name="nombre" placeholder="Nombre" value="<?php echo $nombre; ?>" <?php echo $readonly; ?>>
					<input required class="form-control col-md-6" style="margin:5px 0px; border-radius: 10px;" type="text" name="apellido" placeholder="Apellido" value="<?php echo $apellido; ?>" <?php echo $readonly; ?>>
					<input required class="form-control col-md-6" style="margin:5px 0px; border-radius: 10px;" type="email" name="email" placeholder="Direccion correo electronico" value="<?php echo $email; ?>" <?php echo $readonly; ?>>
					 
				    <label>
				      <input name="terminos" required type="checkbox"> <strong>Acepto los <a href="">términos y condiciones</a> del club</strong>
				    </label>
					 
					<button type="submit" class="btn btn-club btn-lg btn-info">
						Genera tu c&oacute;digo aqu&iacute;
					</button>
				</form>
			</div>
		</aside>
		<section id="club-content" class="col-xs-12 col-sm-12 col-md-7 " style="<?php echo $center_content; ?>">
			 
			<h3 class=" text-left"><strong style="color:#0D7AD8;">¡Bienvenido al club!</strong></h3>
		 	<p class="text-justify">El club de las patitas felices te recompensa con $150 MXN para que los uses en cualquiera de nuestros servicios. Es muy sencillo, por cada vez que compartas tu código de las patitas felices tu referido obtendrá $150 MXN para utilizarlo en su primera reserva y una vez que complete su reservación a ti se te abonarán tus $150 MXN de crédito en Kmimos. </p>
			<div class="item col-md-10">	
				<div class="media">
				  <div class="media-left">
				    <a href="#">
				      <img width="70px" class="media-object" src="<?php echo $url_img; ?>Kmimos-Club-de-las-patitas-felices-7.png">
				    </a>
				  </div>
				  <div class="media-body text-left ">
				  	<p>Inscríbete al club de manera fácil y recibe tu código <br> único del club.</p>
				  </div>
				</div>			 	
							
				<div class="media">
				  <div class="media-left">
				    <a href="#">
				      <img width="70px" class="media-object" src="<?php echo $url_img; ?>Kmimos-Club-de-las-patitas-felices-8.png">
				    </a>
				  </div>
				  <div class="media-body text-left">
				  	<p>Comparte tu código con tus amigos, familiares, conocidos, etc.. Ellos obtendrán $150 MXN para realizar su primera reserva con Kmimos
					</p>
				  </div>
				</div>			 	
							
				<div class="media">
				  <div class="media-left">
				    <a href="#">
				      <img width="70px" class="media-object" src="<?php echo $url_img; ?>Kmimos-Club-de-las-patitas-felices-9.png">
				    </a>
				  </div>
				  <div class="media-body text-left">
				  	<p>Cada vez que alguien use tu código y complete una reserva con Kmimos recibirás $150 MXN en crédito para que lo uses en servicios de nuestra plataforma. ¡Lo mejor es que son totalmente acumulables!</p>
				  </div>
				</div>			 					
			</div>
		</section>
	</div>
<?php 
	$no_display_footer = true;
 	get_footer(); 
?>

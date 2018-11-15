<?php
	/*
        Template Name: Club patitas
    */
    $url_img = get_home_url() .'/wp-content/themes/kmimos/images/club-patitas/';
    
    $no_top_menu = false;

    wp_enqueue_style('club_style', getTema()."/css/club-patitas-felices.css", array(), '1.0.0');
    wp_enqueue_style('club_responsive', getTema()."/css/responsive/club-patitas-felices.css", array(), '1.0.0');

	wp_enqueue_script('club_script', getTema()."/js/club-patitas-felices.js", array(), '1.0.0');

	get_header();

	$display_registro = '';
	$center_content = '';
	$user = wp_get_current_user();

	$nombre = "";
	if( $user->ID > 0 ){
		$nombre = get_user_meta( $user->ID, 'first_name', true );
		$nombre .= " ";
		$nombre .= get_user_meta( $user->ID, 'last_name', true );
	}

	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );
 	if( !empty($cupon) ){
		$display_registro = 'hidden';
		$center_content = 'col-md-offset-2';
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
	

	
	
	<header class="row" style="background-image: url(<?php echo getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-2.jpg'; ?>)">
		<div class="col-xm-12 col-sm-12 col-md-12">
			<?php if( !is_user_logged_in() ){ ?>
				<a href="#" data-target="#popup-iniciar-sesion" style="padding-right: 15px" role="button" data-toggle="modal">Ver mis créditos</a>
				|
				<a href="#" data-target="#popup-iniciar-sesion" style="padding-right: 15px" role="button" data-toggle="modal">Obtener mi código</a>
			<?php }else{ ?>
				<a href="<?php echo get_home_url(); ?>/club-patitas-felices/creditos">Ver mis créditos</a>
				|
	            <a href="<?php echo get_home_url(); ?>/club-patitas-felices/compartir">Obtener mi código</a>
			<?php } ?>
		</div>
		<div class="col-sm-8 col-xs-8 col-md-6 text-center pull-right">
			<img src="<?php echo getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-6.png'; ?>">
			<h2>Club de las patitas felices</h2>
			<p class="subtitle">El club que te recompensa por que tus amigos reserven estadías con Kmimos</p>
		</div>
	</header>
	<div class="body-club">
		<aside class="col-xs-12 col-sm-12 col-md-7 hidden-md hidden-lg">
			<h3 class="text-center" style="margin:10px 0px;"><strong style="color:#0D7AD8;">¡Bienvenido al club  <?php echo $nombre; ?>!</strong></h3>
		</aside>
		<aside id="sidebar" class="col-xs-12 col-sm-12 col-md-4 <?php echo $display_registro; ?>">
			<div class="text-center col-md-10 col-md-offset-1 text-center">
				<h3 class="title-secundario">¡Estás a un paso de ser parte del club!</h3>
				<form method="post" action="#" id="form-registro">
					<input required class="form-control col-md-6" style="margin:5px 0px; border-radius: 10px;" type="text" name="nombre" placeholder="Nombre" value="<?php echo $nombre; ?>" <?php echo $readonly; ?>>
					<input required class="form-control col-md-6" style="margin:5px 0px; border-radius: 10px;" type="text" name="apellido" placeholder="Apellido" value="<?php echo $apellido; ?>" <?php echo $readonly; ?>>
					<input required class="form-control col-md-6" style="margin:5px 0px; border-radius: 10px;" type="email" name="email" placeholder="Direccion correo electronico" value="<?php echo $email; ?>" <?php echo $readonly; ?>>
					 
				    <label class="terminos">
				      <input name="terminos" required type="checkbox"> <strong>Acepto los <a href="">términos y condiciones</a> del club</strong>
				    </label>
					 
					<button type="submit" class="btn btn-club btn-lg btn-info">
						Genera tu c&oacute;digo aqu&iacute;
					</button>
				</form>
			</div>
		</aside>
		<section id="club-content" class="col-xs-12 col-sm-12 col-md-7 <?php echo $center_content; ?>">

			<h3 class="hidden-xs hidden-sm text-left"><strong style="color:#0D7AD8;">¡Bienvenido al club  <?php echo $nombre; ?>!</strong></h3>

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

<?php 
    /*
        Template Name: Testimonios
    */

	wp_enqueue_style( 'fontawesome4', getTema()."/css/font-awesome.css", array(), '1.0.0');
            
    get_header();

    wp_enqueue_style('home_kmimos', get_recurso("css")."testimonios.css", array(), '1.0.0');
    wp_enqueue_style('home_responsive', get_recurso("css")."responsive/testimonios.css", array(), '1.0.0');

    if( !is_user_logged_in() ){
		wp_enqueue_style( 'bootstrap.min', getTema()."/css/bootstrap.min.css", array(), "1.0.0" );
		wp_enqueue_style( 'datepicker.min', getTema()."/css/datepicker.min.css", array(), "1.0.0" );
		wp_enqueue_style( 'jquery.datepick', getTema()."/lib/datapicker/jquery.datepick.css", array(), "1.0.0" );


	    wp_enqueue_script('jquery.datepick', getTema()."/lib/datapicker/jquery.datepick.js", array("jquery"), '1.0.0');
	    wp_enqueue_script('jquery.plugin', getTema()."/lib/datapicker/jquery.plugin.js", array("jquery"), '1.0.0');
	}

	$HTML .= '
	<div id="testimonios">

		<div class="solo_PC pc_seccion_0" style="background-image:url('.get_recurso('img').'/TESTIMONIOS/Header.jpg);"></div>
		<div class="solo_movil pc_seccion_0" style="background-image:url('.get_recurso('img').'/TESTIMONIOS/RESPONSIVE/Header-responsive.jpg);"></div>

		<h1>Miles y miles de comentarios de dueños de mascotas felices</h1>

		<div class="container" style="margin: 0px auto 40px;">
			<h3>
				<img src="'.get_recurso('img').'/TESTIMONIOS/Estrella_1.svg">
				<span>Mira estos perfiles de Cuidadores recomendados para ti</span>
			</h3>
		</div>



		<img class="solo_PC banner_cuidador" src="'.get_recurso('img').'/TESTIMONIOS/Maru-S.png" />
		<img class="solo_movil banner_cuidador" src="'.get_recurso('img').'/TESTIMONIOS/RESPONSIVE/Maru-S-responsive.png" />
		<div class="container">
			<div class="izq">
				Mis dos hijos y yo vivimos en un departamento muy grande en compañía de nuestros perros Ortzi y Cholo, quienes son unos amables 
				y compartidos anfitriones. No uso jaulas así que los perros están libres, jugando por toda el área, siempre acompañados por 
				nosotros y sus nuevos camaradas perrunos<span class="ver_mas_PC">. Salimos a pasear tres veces al día y los alimentos se les dan en forma separada. 
				Solamente acepto perros consentidos, sociables y nada agresivos...<br></span><span class="ver_mas_movil">...</span> <a>Ver más</a>
			</div>
			<div class="der">
				<a class="boton boton_border_gris">Conocer cuidador</a>
				<a class="boton boton_verde">Reservar</a>
			</div>
		</div>
		<div style="text-align: center;">
			<a class="boton boton_morado">Ir al perfil de Maru S.</a>
		</div>







		<img class="solo_PC banner_cuidador" src="'.get_recurso('img').'/TESTIMONIOS/Claudia-R.png" />
		<img class="solo_movil banner_cuidador" src="'.get_recurso('img').'/TESTIMONIOS/RESPONSIVE/Claudia-R-responsive.png" />
		<div class="container">
			<div class="izq">
				Horario de ingreso partir de 8:30am. ¡¡Listos para jugar?!! ¿Reservación para navidad? Hola! Soy Claudia Ramírez. Zona tlalpan por tec 
				de Monterrey campus sur He sido cuidadora de perrihijos , ya por varios años y todo 
				comenzó cuando ingrese a trabajar en una clínica veterinaria, allá observe que los perros permanecían encerrados por días, algo que 
				a mí nunca me gusto, por lo cual comencé a llevármelos para darles el calor de hog... <a>Ver más</a><br>
			</div>
			<div class="der">
				<a class="boton boton_border_gris">Conocer cuidador</a>
				<a class="boton boton_verde">Reservar</a>
			</div>
		</div>
		<div style="text-align: center;">
			<a class="boton boton_morado">Ir al perfil de Claudia R.</a>
		</div>




		<img class="solo_PC banner_cuidador" src="'.get_recurso('img').'/TESTIMONIOS/Benjamin-G.png" />
		<img class="solo_movil banner_cuidador" src="'.get_recurso('img').'/TESTIMONIOS/RESPONSIVE/Benjamin-G-responsive.png" />
		<div class="container">
			<div class="izq">
				Benjamín González les da la bienvenidas a todos ustedes a este hogar donde vivimos mi familia y mis mascotas, mi profesión es médico veterinario 
				con más de 36 años de experiencia en el manejo de animales y en particular perros, en esta tu casa colaboran en el cuidado y mantenimiento de los 
				huéspedes caninos, mi hijo Benjamín que desde que nació convive con animales participando en concursos de entrenamiento y manejo de perros, así 
				como en foros de Bienestar Animal y Laura asistente de... <a>Ver más</a><br>
			</div>
			<div class="der">
				<a class="boton boton_border_gris">Conocer cuidador</a>
				<a class="boton boton_verde">Reservar</a>
			</div>
		</div>
		<div style="text-align: center;">
			<a class="boton boton_morado">Ir al perfil de Benjamín G.</a>
		</div>

		<div class="promesa">
			<span>Nuestra promesa</span> ¡Tu mascota regresa feliz!
		</div>

		<div class="img_footer">
			<img src="'.get_recurso('img').'/TESTIMONIOS/Banner-1.png" />

			<div class="btns_container">
				<a class="boton boton_verde">Buscar cuidador</a>
				<a class="boton boton_border_gris">Quiero ser cuidador</a>
			</div>
		</div>

		<div class="ir_home">
			<a>Ir al home</a>
		</div>
	</div>';

    echo comprimir($HTML);
    
    wp_enqueue_script('buscar_home', get_recurso("js")."home.js", array(), '1.0.0');

    get_footer(); 
?>



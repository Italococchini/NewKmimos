<?php 
    /*
        Template Name: Home
    */

    wp_enqueue_style('home_club_responsive', getTema()."/css/responsive/club_patitas_home.css", array(), '1.0.0');
    wp_enqueue_style('home_kmimos', getTema()."/css/home_kmimos.css", array(), '1.0.0');
    wp_enqueue_style('home_responsive', getTema()."/css/responsive/home_responsive.css", array(), '1.0.0');

    wp_enqueue_script('buscar_home', getTema()."/js/home.js", array(), '1.0.0');
    wp_enqueue_script('select_localidad', getTema()."/js/select_localidad.js", array(), '1.0.0');
    wp_enqueue_script('check_in_out', getTema()."/js/fecha_check_in_out.js", array(), '1.0.0');
            
    get_header();
        
	    $home = get_home_url();
	    $user_id = get_current_user_id();

	    $link = get_home_url()."/quiero-ser-cuidador-certificado-de-perros/";

	    $boton = "boton";
	    if( $_SESSION["wlabel"] != "" ){
	    	$boton = "boton_wlabel";
	    }

	    $BANNER_PUBLICIDAD = '
	    	<style>
	    		.banner_publicidad_container{
	    			position: relative;
	    			display: block;
	    		}
	    		.banner_publicidad_container video,
	    		.banner_publicidad_container img {
	    			width: 100%;
	    			height: auto;
	    		}
	    		.banner_publicidad_container img {
	    			display: none;
	    		}
	    		.banner_publicidad_container .boton_click {
	    			position: absolute;
	    			top: 0px;
	    			left: 0px;
	    			width: 100%;
	    			height: 100%;
	    			z-index: 1;

	    			background-size: contain;
	    			background-position: center;
    			    background-repeat: no-repeat;

    			    display: none;
	    		}
			    @media (max-width: 700px){
		    		.banner_publicidad_container video { display: none; }
		    		.banner_publicidad_container img, .banner_publicidad_container .boton_click { display: block; }
		    		.km-club { margin: 150px 0 30px !important; }
			    }
	    	</style>
	    	<a href="'.$link.'" target="_blank" class=" banner_publicidad_container">
				<video 
					loop 
					muted 
					autoplay 
					width="100%"
					src="'.getTema().'/videos/home/publicidad_ser_cuidador/pc.mp4" 
					poster="'.getTema().'/videos/home/publicidad_ser_cuidador/preview.png"
				></video>
				<img src="'.getTema().'/videos/home/publicidad_ser_cuidador/movil_final.gif" />
				<div class="boton_click" style="background-image: url('.getTema().'/videos/home/publicidad_ser_cuidador/'.$boton.'.png);"></div>
			</a>
	    ';


	    $HTML = '';
		if( $_SESSION["wlabel"] == "petco" ){
			$HTML .= '
				<!-- Adform Tracking Code BEGIN -->
				<script type="text/javascript">
				    window._adftrack = Array.isArray(window._adftrack) ? window._adftrack : (window._adftrack ? [window._adftrack] : []);
				    window._adftrack.push({
				        pm: 1453019,
				        divider: encodeURIComponent("|"),
				        pagename: encodeURIComponent("MX_Kmimos_Home_180907")
				    });
				    (function () { var s = document.createElement("script"); s.type = "text/javascript"; s.async = true; s.src = "https://a2.adform.net/serving/scripts/trackpoint/async/"; var x = document.getElementsByTagName("script")[0]; x.parentNode.insertBefore(s, x); })();

				</script>
				<noscript>
				    <p style="margin:0;padding:0;border:0;">
				        <img src="https://a2.adform.net/Serving/TrackPoint/?pm=1453019&ADFPageName=MX_Kmimos_Home_180907&ADFdivider=|" width="1" height="1" alt="" />
				    </p>
				</noscript>
				<!-- Adform Tracking Code END -->
			';
		}


	    $HTML .= '
	    <script type="text/javascript"> var URL_MUNICIPIOS ="'.getTema().'/procesos/generales/municipios.php"; </script>

	    <div class="km-video">
			<div class="container-fluid">
				<div class="row">
					<div class="km-video-bg">
						<video loop muted autoplay poster="'.getTema().'/images/new/km-hero-desktop.jpg" class="km-video-bgscreen"></video>
					</div>
				</div>
			</div>
		</div>
			
	    <!-- SECCIÓN 1 - PARTE FORMULARIO CUIDADOR -->
		<div class="km-credibilidad">

			<div class="container">
				<div class="km-credibilidad-titular">
					<h1 style="font-size: 0px;">No somos pensión para perros ni hotel para perros. Somos mucho mejor porque tenemos cuidadores certificados para perros.</h1>				
					<p style="text-transform: uppercase;">La red mas grande de cuidadores certificados. Cuidados en un hogar de familia.</p>
					<h2>tu mascota regresa feliz</h2>
				</div>
			</div>

			<form id="buscador" class="km-cuidador" method="POST" action="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php">
				<input type="hidden" name="redireccionar" value="1" />
				<input type="hidden" name="USER_ID" value="'.$user_id.'" />

				<input type="hidden" id="latitud" name="latitud" />
				<input type="hidden" id="longitud" name="longitud" />

				<div class="container">
					<div class="km-formulario-cuidador">
						<div class="row km-fechas">
							

							<div class="col-xs-12 col-sm-6">
	
								<div class="km-select-custom-home km-select-custom km-select-ubicacion btn-group" style="width:100%;">
									
									<i id="mi_ubicacion" class="fa fa-crosshairs icon_left"></i>

								    <input type="text" 
										id="ubicacion_txt"  
										style="background: transparent; border: 0px; padding: 0px 0px 0px 15px;"
										name="ubicacion_txt"
										placeholder="UBICACI&Oacute;N, ESTADO, MUNICIPIO" 
										value="'.$busqueda["ubicacion_txt"].'" 
										autocomplete="off" >

									<input type="hidden" 
										id="ubicacion" 
										name="ubicacion" 
										value="'.$busqueda["ubicacion"].'" />	

								    <div class="cerrar_list_box">
								    	<div class="cerrar_list">X</div>
								    	<ul id="ubicacion_list" class=""></ul>
								    </div>

								    
								</div>
								<small  class="hidden" data-error="ubicacion">Función disponible solo en México</small>
								
							</div>


							<div class="col-xs-12 col-sm-3 km-fechas">
								<input type="text" id="checkin" data-error="reset" data-valid="requerid" name="checkin" placeholder="DESDE CUANDO" value="" class="date_from" readonly>
								<small class="hidden" data-error="checkin">Debe seleccionar una fecha</small>
							</div>
							<div class="col-xs-12 col-sm-3 km-fechas">
								<input type="text" id="checkout" data-error="reset" name="checkout" data-valid="requerid" placeholder="HASTA CUANDO" value="" class="date_to" readonly>
								<small  class="hidden" data-error="checkin">Debe seleccionar una fecha</small>
							</div>


						</div>
						<div class="row km-servicios mtb-10">
							<div class="col-xs-12 col-sm-10" style="padding: 0px;">
								<div class="col-xs-12 col-sm-3">
									<div class="km-opcion">
										<input type="checkbox" name="servicios[]" value="hospedaje" >
										<img src="'.getTema().'/images/new/icon/icon-hospedaje.svg">
										HOSPEDAJE
										<span>DÍA Y NOCHE</span>
									</div>
								</div>
								<div class="col-xs-12 col-sm-3" onclick="evento_google(\'guarderia\'); evento_fbq("track", "traking_code_boton_guarderia"); ">
									<div class="km-opcion">
										<input type="checkbox" name="servicios[]" value="guarderia" >
										<img src="'.getTema().'/images/new/icon/icon-guarderia.svg">
										GUARDERÍA
										<span>SOLO DÍA</span>
									</div>
								</div>
								<div class="col-xs-12 col-sm-3" onclick="evento_google(\'paseos\'); evento_fbq("track", "traking_code_boton_paseos"); ">
									<div class="km-opcion">
										<input type="checkbox" name="servicios[]" value="paseos" >
										<img src="'.getTema().'/images/new/icon/icon-paseo.svg">PASEOS
									</div>
								</div>
								<div class="col-xs-12 col-sm-3" onclick="evento_google(\'entrenamiento\'); evento_fbq("track", "traking_code_boton_entrenamiento"); ">
									<div class="km-opcion">
										<input type="checkbox" name="servicios[]" value="adiestramiento" >
										<img src="'.getTema().'/images/new/icon/icon-entrenamiento.svg">ENTRENAMIENTO
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-2">	

								<div class="km-opcion" style=" padding: 11px 11px 12px;">
									<input type="checkbox" name="servicios[]" value="flash" >
									<i class="fa fa-bolt vlz_rayo" aria-hidden="true" style="vertical-align: middle;"></i>
									<div style="display: inline-block; width: calc( 100% - 30px ); vertical-align: middle;">RESERVA INMEDIATA</div>
								</div>

								<!--
								<i class="fa fa-bolt vlz_rayo" aria-hidden="true"></i>		
								<select id="flash" name="flash" class="select_normal">
									<option>Flash</option>
									<option>SI</option>
									<option>NO</option>
								</select>
								-->
							</div>
						</div>
						<div class="row">
							<div class="col-xs-12 col-sm-9">
								<div class="row km-tamanio">
									<div class="col-xs-6 col-sm-3">
										<div class="km-opcion">
											<input type="checkbox" name="tamanos[]" value="pequenos" >
											<img src="'.getTema().'/images/new/icon/icon-pequenio.svg"><div class="km-opcion-text">
											<b>PEQUEÑO</b><br>0 a 25 cm</div>
										</div>
									</div>
									<div class="col-xs-6 col-sm-3">
										<div class="km-opcion">
											<input type="checkbox" name="tamanos[]" value="medianos" >
											<img src="'.getTema().'/images/new/icon/icon-mediano.svg"><div class="km-opcion-text">
											<b>MEDIANO</b><br>25 a 58 cm</div>
										</div>
									</div>
									<div class="col-xs-6 col-sm-3">
										<div class="km-opcion">
											<input type="checkbox" name="tamanos[]" value="grandes" >
											<img src="'.getTema().'/images/new/icon/icon-grande.svg"><div class="km-opcion-text">
											<b>GRANDE</b><br>58 a 73 cm</div>
										</div>
									</div>
									<div class="col-xs-6 col-sm-3">
										<div class="km-opcion">
											<input type="checkbox" name="tamanos[]" value="gigantes" >
											<img src="'.getTema().'/images/new/icon/icon-gigante.svg"><div class="km-opcion-text">
											<b>GIGANTE</b><br>73 a 200 cm</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-sm-3 pd5">
								
								<a href="javascript:;" class="km-btn-primary" 
									data-target="#popup-servicios" 
									data-action="validate">ENCONTRAR CUIDADOR</a>

							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<!-- FIN SECCIÓN 1 - PARTE FORMULARIO CUIDADOR -->
		<!-- FIN SECCIÓN 1 -->

		<!-- SECCIÓN 2 - BENEFICIOS -->
		<div class="km-beneficios">
			<div class="container">
				<h4><div style="font-family: \'Gotham-Pro-Black\'; display: inline-block;">BENEFICIOS</div> DE DEJAR A TU MASCOTA CON CUIDADORES CERTIFICADOS</h4>
				<div class="row">
					<div class="col-xs-12 col-sm-4">
						<div class="row">
							<div class="col-xs-4 col-sm-12 km-beneficios-icon">
								<img src="'.getTema().'/images/new/km-certificado.svg">
							</div>
							<div class="col-xs-8 col-sm-12">
								<div class="km-beneficios-text">
									<h5>CUIDADORES CERTIFICADOS</h5>
									<p>Todos nuestros cuidadores deben aprobar pruebas psicométricas y de conocimientos veterinarios, así como inspección en su casa.</p>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-4">
						<div class="row">
							<div class="col-xs-4 col-sm-12 km-beneficios-icon">
								<img src="'.getTema().'/images/new/km-veterinaria.svg">
							</div>
							<div class="col-xs-8 col-sm-12">
								<div class="km-beneficios-text">
									<h5>COBERTURA VETERINARIA</h5>
									<p>Sabemos que tu mascota es un integrante de tu familia. Ten la certeza de que recibirá el cuidado necesario, teniendo cobertura contra accidentes durante su estadía.</p>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-4">
						<div class="row">
							<div class="col-xs-4 col-sm-12 km-beneficios-icon">
								<img src="'.getTema().'/images/new/km-fotografia.svg">
							</div>
							<div class="col-xs-8 col-sm-12">
								<div class="km-beneficios-text">
									<h5>FOTOGRAFÍAS Y VIDEOS DIARIOS</h5>
									<p>Acortando distancias entre tu mascota y tú. Kmimos monitorea a los cuidadores asociados asegurando la mejor experiencia para ambos.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- FIN SECCIÓN 2 - BENEFICIOS -->

		<!-- SECCIÓN 3 - TESTIMONIALES -->
		<div class="km-testimoniales">
			<div class="container-fluid">
				<div class="row">
					<ul class="bxslider">

						<li>
							<div>
								<div class="overlay control-video"></div>
								<div class="km-testimonial-text">
									<div class="km-video-testimonial">
										<a href="javascript:;" 
											data-video="https://www.youtube.com/embed/cSVXJhOqcqg"
											data-target="iframe-testimonio">
											<img src="'.getTema().'/images/new/icon/icon-video.svg" width="55">
										</a>
									</div>
									<div class="km-testimonial">“Kmimos es mi trabajo, mi pasión por los perros, mi casa, mi familia… lo representa todo.”</div>
									<div class="km-autor">
										<a href="'.get_user_slug(1404).'"> CLAUDIA R. </a>
										 - Ciudad de México</div>
									<div class="km-autor-descripcion ">Cuidador Certificado</div>
								</div>
								<img class="img-testimoniales filtro-oscuro" src="'.getTema().'/images/new/km-testimoniales/testimonial-3.jpg">
							</div>
						</li>

						<li>
							<div>
								<div class="overlay control-video"></div>
								<div class="km-testimonial-text">
									<div class="km-video-testimonial">
										<a href="javascript:;" 
											data-video="https://www.youtube.com/embed/XCl7PuBHP2g"
											data-target="iframe-testimonio">
											<img src="'.getTema().'/images/new/icon/icon-video.svg" width="55">
										</a>
									</div>
									<div class="km-testimonial">“Todos los cuidadores Kmimos tienen algo en común, la pasión y el amor por los perros.”</div>
									<div class="km-autor">
										<a href="'.get_user_slug(5011).'">MARU S.</a> - Ciudad de México</div>
									<div class="km-autor-descripcion ">Cuidador Certificado</div>
								</div>
								<img class="img-testimoniales filtro-oscuro" src="'.getTema().'/images/new/km-testimoniales/testimonial-2.jpg">
							</div>
						</li>

						<li>
							<div>
								<div class="overlay control-video"></div>
								<div class="km-testimonial-text">
									<div class="km-video-testimonial">
										<a href="javascript:;" 
											data-video="https://www.youtube.com/embed/6u3yC778DHw"
											data-target="iframe-testimonio">
											<img src="'.getTema().'/images/new/icon/icon-video.svg" width="55">
										</a>
									</div>
									<div class="km-testimonial">“Ahora tengo más ingresos, llegan bastantes visitas y es como tener un sueldo de ejecutivo”.</div>
									<div class="km-autor"><a href="'.get_user_slug(5738).'">KARLA S.</a> - Ciudad de México</div>
									<div class="km-autor-descripcion ">Cuidador Certificado</div>
								</div>
								<img class="img-testimoniales filtro-oscuro" 
									src="'.getTema().'/images/new/km-testimoniales/testimonial-1.jpg"
									>
							</div>
						</li>						


					</ul>
				</div>
			</div>
		</div>
		<!-- FIN SECCIÓN 3 - TESTIMONIALES -->

		<!-- SECCIÓN 4 - CLUB PATITAS FELICES -->
		<div class="km-club-patitas" style="background-image: url('.getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-3.png);">
			<div class="row">
				<header class="col-sm-12 col-xs-8 col-md-5 pull-right text-center">
					<img src="'.getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-6.png">
					<h2>
						Club de las patitas felices
					</h2>
					<p>
						Únete al club que te recompensa por cada amigo tuyo que reserve con un cuidador Kmimos
					</p>
				</header>
			</div>
			<div class="row">
				<div class="col-sm-6 col-xs-12 col-md-8 pull-right text-center">
					<a class="btn btn-club-patitas" href="'.get_home_url().'/club-patitas-felices">Ingresa aquí</a>
				</div>
			</div>
		</div>
		<!-- FIN SECCIÓN 4 - CLUB PATITAS FELICES -->

		<!-- SECCIÓN 5 - BANNER PUBLICIDAD -->
		
			'.$BANNER_PUBLICIDAD.'

		<!-- FIN SECCIÓN 5 - BANNER PUBLICIDAD -->

		<!-- SECCIÓN 6 - BENEFICIOS -->
		<div class="km-medios">
			<div class="container">
				<h4>NUESTRO RESPALDO</h4>
				<div class="row">
					<div class="col-xs-6 col-md-offset-1 col-md-2">
						<img src="'.getTema().'/images/new/km-medios/reforma.jpg">
					</div>
					<div class="col-xs-6 col-md-2">
						<img src="'.getTema().'/images/new/km-medios/mural.jpg">
					</div>
					<div class="col-xs-6 col-md-2">
						<img src="'.getTema().'/images/new/km-medios/norte.jpg">
					</div>
					<div class="col-xs-6 col-md-2">
						<img src="'.getTema().'/images/new/km-medios/financiero.jpg">
					</div>
					<div class="col-xs-12 col-md-2">
						<img src="'.getTema().'/images/new/km-medios/universal.jpg">
					</div>
				</div>
			</div>
		</div>
		<!-- FIN SECCIÓN 6 - BENEFICIOS -->
		<!-- SECCIÓN 7 - BENEFICIOS -->
		<div class="km-beneficios">
			<div class="container">
				<h4>KMIMOS TE OFRECE</h4>
				<div class="row">
					<div class="col-xs-4">
						<div class="km-beneficios-icon">
							<img src="'.getTema().'/images/new/km-pago.svg">
						</div>
						<div class="km-beneficios-text">
							<h5 class="h5-sub">PAGO EN EFECTIVO O CON TARJETA</h5>
						</div>
					</div>
					<div class="col-xs-4 brd-lr">
						<div class="km-beneficios-icon">
							<img src="'.getTema().'/images/new/km-certificado.svg">
						</div>
						<div class="km-beneficios-text">
							<h5 class="h5-sub">CUIDADORES CERTIFICADOS</h5>
						</div>
					</div>
					<div class="col-xs-4">
						<div class="km-beneficios-icon">
							<img src="'.getTema().'/images/new/km-veterinaria.svg">
						</div>
						<div class="km-beneficios-text">
							<h5 class="h5-sub">COBERTURA SERVICIOS VETERINARIOS</h5>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- FIN SECCIÓN 7 - BENEFICIOS -->
'; ?>



<?php		
$HTML .= '
		<!-- BEGIN MODAL TESTIMONIOS -->
		<div class="modal fade" tabindex="-1" data-backdrop="false" role="dialog" aria-labelledby="myModalLabel" 
			aria-hidden="true" id="testimonio">
			<div class="modal-dialog">
				<div class="modal-content" style="height:340px">
					<button type="button" class="close-white close" style="z-index:10; border:1px solid transparent;" aria-hidden="true" data-target="close-testimonio">×</button>
					<iframe style="background:transparent;" id="iframe-testimonio" width="100%" height="100%" src="" frameborder="0" allowfullscreen></iframe>
					<p>Cargando video</p>
				</div>
			</div>
		</div>
		<!-- END MODAL TESTIMONIOS -->


		<!-- BEGIN MODAL SERVICIOS ADICIONALES -->
		<div id="popup-servicios" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4><b>RECOMPENSA A TU MASCOTA. INCLUYE UN SERVICIO ADICIONAL</b></h4>
					<div class="km-servicios-adicionales">
						<div class="row">
							<div class="col-xs-12 col-sm-3">
								<div class="km-opcion"><input type="checkbox" name="servicios[]" value="corte" ><img src="'.getTema().'/images/new/icon/icon-corteypelo.svg"><div class="km-opcion-text">CORTE DE<br> PELO Y UÑAS</div></div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="km-opcion"><input type="checkbox" name="servicios[]" value="bano" ><img src="'.getTema().'/images/new/icon/icon-banoyseco.svg"><div class="km-opcion-text">BAÑO<br> Y SECADO</div></div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="km-opcion"><input type="checkbox" name="servicios[]" value="limpieza_dental" ><img src="'.getTema().'/images/new/icon/icon-dental.svg"><div class="km-opcion-text">LIMPIEZA<br> DENTAL</div></div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="km-opcion"><input type="checkbox" name="servicios[]" value="visita_al_veterinario" ><img src="'.getTema().'/images/new/icon/icon-veterinario.svg"><div class="km-opcion-text">VISITA AL<br> VETERINARIO</div></div>
							</div>
						</div>
						<div class="row mtb-10">
							<div class="col-xs-12 col-sm-3">
								<div class="km-opcion"><input type="checkbox" name="servicios[]" value="acupuntura" ><img src="'.getTema().'/images/new/icon/icon-acupuntura.svg"><div class="km-opcion-text">ACUPUNTURA</div></div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="km-opcion"><input type="checkbox" name="servicios[]" value="transportacion_sencilla" ><img src="'.getTema().'/images/new/icon/icon-transportesencillo.svg"><div class="km-opcion-text">TRANSPORTE<br> SENCILLO</div></div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<div class="km-opcion"><input type="checkbox" name="servicios[]" value="transportacion_redonda" ><img src="'.getTema().'/images/new/icon/icon-transporteredondo.svg"><div class="km-opcion-text">TRANSPORTE<br> REDONDO</div></div>
							</div>
							<div class="col-xs-12 col-sm-3">
								<a id="buscar" href="#" class="km-btn-primary" style="min-height: 79px; line-height: 40px;">AGREGAR SERVICIO</a>
							</div>
						</div>
					</div>
					<a href="javascript:;" id="buscar_no" class="km-link" style="color: black; display:block; margin-top: 30px;">NO DESEO POR AHORA, GRACIAS</a>
				</div>
			</div>
		</div>
		<!-- END MODAL SERVICIOS ADICIONALES -->

	    ';

	    echo comprimir_styles($HTML);

    get_footer(); 
?>



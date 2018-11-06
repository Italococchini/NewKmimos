<?php 
    /*
        Template Name: Busqueda
    */

    wp_enqueue_style('beneficios_kmimos', getTema()."/css/busqueda.css", array(), '1.0.0');
	wp_enqueue_style('beneficios_responsive', getTema()."/css/responsive/busqueda_responsive.css", array(), '1.0.0');

    wp_enqueue_style('conocer', getTema()."/css/conocer.css", array(), '1.0.0');
    wp_enqueue_style('conocer_responsive', getTema()."/css/responsive/conocer_responsive.css", array(), '1.0.0');

	wp_enqueue_script('buscar_home', getTema()."/js/busqueda.js", array(), '1.0.0');
    wp_enqueue_script('select_localidad', getTema()."/js/select_localidad.js", array(), '1.0.0');
    wp_enqueue_script('check_in_out', getTema()."/js/fecha_check_in_out.js", array(), '1.0.0');


    get_header();

	global $wpdb;

    $user_id = get_current_user_id();

    if( !isset($_SESSION)){ session_start(); }
	
	if( isset($_SESSION['busqueda'])){ 
		$_POST = unserialize($_SESSION['busqueda']); 
	}

	if( $_GET["new"] == true ){
		$POST_TEMP = array();
		if( $_POST["checkin"] != "" ){
			$POST_TEMP["checkin"] = $_POST["checkin"];
		}
		if( $_POST["checkout"] != "" ){
			$POST_TEMP["checkout"] = $_POST["checkout"];
		}
		$_SESSION['busqueda'] = serialize( $POST_TEMP );
		$_POST = $POST_TEMP;
	}

	// ini - condicion para retornar a la pagina #1 en nuevas busquedas
	if( $_SESSION['nueva_busqueda'] == 1 ){
		$pagina = 1;
	}else{
		$pagina = vlz_get_page();
	}
	$_SESSION['nueva_busqueda'] = 2;
	// fin - condicion para retornar a la pagina #1 en nuevas busquedas

    $pagina = vlz_get_page();

//	if(!$_POST || $_GET["new"] == true ){
	if( empty($_SESSION['resultado_busqueda']) || $_GET["new"] == true ){
		$_POST["USER_ID"] = $user_id;
		include('procesos/busqueda/buscar.php');
	}

	$home = get_home_url();
	$destacados = get_destacados();
	$total  = vlz_num_resultados();
	$resultados = $_SESSION['resultado_busqueda'];
	$paginacion = vlz_get_paginacion($total, $pagina, count($resultados) );
	$favoritos = get_favoritos();
	//$total--;

 	$TIPO_DISEÑO = "list";
	if( $total > 6 ){
		$TIPO_DISEÑO = "grid";
	}

	$CUIDADORES = "";
	if( $total > 0 ){
		for ($i=$paginacion["inicio"]; $i < $paginacion["fin"]; $i++) {
			$cuidador = $resultados[$i];
			$CUIDADORES .= get_ficha_cuidador($cuidador, $i, $favoritos, $TIPO_DISEÑO);

			// Agregar impresion en resultado de busqueda
			$_SESSION["views_search"] = ( isset( $_SESSION["views_search"] ) )?  $_SESSION["views_search"] : [];

			if( !in_array($cuidador->user_id, $_SESSION["views_search"]) ){
				$wpdb->query("UPDATE cuidadores SET total_impresiones = total_impresiones + 1 WHERE user_id = ".$cuidador->user_id);
				$_SESSION["views_search"][] = $cuidador->user_id;
			}

		}
	}else{
		//$CUIDADORES .= "<h2 style='padding-right: 20px!important; font-size: 21px; text-align: justify; margin: 10px 0px;'>No tenemos resultados para esta búsqueda, si quieres intentarlo de nuevo pícale <a  style='color: #00b69d; font-weight: 600;' href='".get_home_url()."/'>aquí,</a> o aplica otro filtro de búsqueda.</h2>";
	}
	$busqueda = getBusqueda();

/*	echo "<pre>";
		print_r( $busqueda );
		print_r( $_SESSION["sql"] );
	echo "</pre>";*/

	if( $destacados != "" ){
		$destacados_str = '
		<strong class="km-leyenda" style ="color: #000000;font-size: 20px;border-radius: 5px; display: inline-block;padding: 5px 20px 5px 0px;margin-bottom: 10px;">Cuidadores Destacados</strong>
		<div class="km-premium km-search-slider" style="min-height:300px; padding: 20px 20px 2px 20px;background: #33e1be;">
			<div style="height: 300px; overflow: hidden; background: transparent;">
 
				<div class="km-premium-slider" style="min-height:300px;">
					'.$destacados.'
				</div>
			</div>
		</div>';
	}

	if( $total > 6 ){
		$CUIDADORES_STR = '
		<div class="km-resultados-grid">
			'.$CUIDADORES.'
		</div>';
	}else{
		$CUIDADORES_STR = '
		<div class="km-resultados-lista">
			'.$CUIDADORES.'
			<div class="">
				<h2 class="pocos_resultados">Si quieres obtener más resultados, por favor pícale <a style="color:#6B1C9B;" href="'.get_home_url().'">aquí</a> para ajustar los filtros de búsqueda.</h2>
			</div>
		</div>';
	}

    $option_servicios_adicionales = '';
    $servicios_adicionales = servicios_adicionales();
    $servicios_adicionales_display = '';
    foreach ($servicios_adicionales as $opt_key => $opt_value) {
    	$check = (servicios_en_session($opt_key, $busqueda, 'servicios'))? 'checked' : '' ;
	    $option_servicios_adicionales .= '
		<li>
			<label data-target="checkbox" data-content="'.$opt_value['label'].'" >
				<input type="checkbox" name="servicios[]" value="'.$opt_key.'" '.$check.' content="'.$opt_value['label'].'">'.
					$opt_value['label']
			.'</label>
		</li>
	    ';
		if( $check != ''){
			$separador = (!empty($servicios_adicionales_display))? ', ' : '';
		    $servicios_adicionales_display .= $separador . $opt_value['label'];
		}
    }
    if( empty($servicios_adicionales_display) ){
	    $servicios_adicionales_display = 'SERVICIOS ADICIONALES';
    }

    $check = '';
    $option_tipo_mascota = '';
    $tipos_mascotas = get_tipo_mascotas();
    $tipo_mascota = '';
    foreach ($tipos_mascotas as $opt_key => $opt_value) {
    	$check = (servicios_en_session($opt_key, $busqueda, 'mascotas'))? 'checked' : '' ;
	    $option_tipo_mascota .= '
		<li> 
			<label data-target="checkbox" data-content="'.$opt_value['name'].'" >
				<input type="checkbox" name="mascotas[]" value="'.$opt_key.'" '.$check.' content="'.$opt_value['name'].'">'.
				$opt_value['name'].
			'</label>
		</li>
	    ';
		if( $check != ''){
			$separador = (!empty($tipo_mascota))? ', ' : '';
		    $tipo_mascota .= $separador . $opt_value['name'];
		}
    }
    if( empty($tipo_mascota) ){
	    $tipo_mascota = 'TIPO DE MASCOTA';
    }

    $check = '';
    $option_tipo_servicio = '';
    $tipo_servicio = get_tipo_servicios();
    $tipo_servicio_display = '';
    foreach ($tipo_servicio as $opt_key => $opt_value) {
    	$check = (servicios_en_session($opt_key, $busqueda, 'servicios'))? 'checked' : '' ;
	    $option_tipo_servicio .= '
		<li> 
			<label data-target="checkbox" data-content="'.$opt_value['name'].'" >
				<input type="checkbox" name="servicios[]" value="'.$opt_key.'" '.$check.' content="'.$opt_value['name'].'">'.
				$opt_value['name'].
			'</label>
		</li>
	    ';
		if( $check != ''){
			$separador = (!empty($tipo_servicio_display))? ', ' : '';
		    $tipo_servicio_display .= $separador . $opt_value['name'];
		}
    }
    if( empty($tipo_servicio_display) ){
	    $tipo_servicio_display = 'TIPO DE SERVICIO';
    }

    $check = '';
    $option_tamanos_mascotas = '';
    $tamanos_mascotas = kmimos_get_sizes_of_pets();
    $tamanos_mascotas_display = '';
    foreach ($tamanos_mascotas as $opt_key => $opt_value) {
    	$check = (servicios_en_session($opt_value['db'], $busqueda, 'tamanos'))? 'checked' : '' ;
	    $option_tamanos_mascotas .= '
		<li> 
			<label data-target="checkbox" data-content="'.$opt_value['name'].'" >
				<input type="checkbox" name="tamanos[]" value="'.$opt_value['db'].'" '.$check.' content="'.$opt_value['name'].'">'.
				$opt_value['name'].
			'</label>
		</li>
	    ';
		if( $check != ''){
			$separador = (!empty($tamanos_mascotas_display))? ', ' : '';
		    $tamanos_mascotas_display .= $separador . $opt_value['name'];
		}
    }
    if( empty($tamanos_mascotas_display) ){
	    $tamanos_mascotas_display = 'TAMAÑO DE MASCOTA';
    }

    $ordenamientos = array(
    	'rating_desc' => array(
    		'Valoración de mayor a menor',
    		'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=rating_desc">Valoración de mayor a menor</a></li>'
    	),
		'rating_asc' => array(
			'Valoración de menor a mayor',
			'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=rating_asc">Valoración de menor a mayor</a></li>'
		),
		'distance_asc' => array(
			'Distancia al cuidador de cerca a lejos',
			'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=distance_asc">Distancia al cuidador de cerca a lejos</a></li>'
		),
		'distance_desc' => array(
			'Distancia al cuidador de lejos a cerca',
			'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=distance_desc">Distancia al cuidador de lejos a cerca</a></li>'
		),
		'price_asc' => array(
			'Precio del Servicio de menor a mayor',
			'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=price_asc">Precio del Servicio de menor a mayor</a></li>'
		),
		'price_desc' => array(
			'Precio del Servicio de mayor a menor',
			'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=price_desc">Precio del Servicio de mayor a menor</a></li>'
		),
		'experience_desc' => array(
			'Experiencia de menos a más años',
			'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=experience_desc">Experiencia de menos a más años</a></li>'
		),
		'experience_asc' => array(
			'Experiencia de más a menos años',
			'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=experience_asc">Experiencia de más a menos años</a></li>'
		),
		'flash' => array(
			'Cuidadores con Reserva Inmediata',
			'<li><a href="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php?redireccionar=1&o=flash">Cuidadores con Reserva Inmediata</a></li>',						 
		) 
    );

    $titulo_ordenamiento = "ORDENAR POR";
    if( $_POST['orderby'] != "" ){
    	$titulo_ordenamiento = $ordenamientos[ $_POST['orderby'] ][0];
    }
    $ordenamiento = "";
    foreach ( $ordenamientos as $clave => $valor ) {
    	$ordenamiento .= $valor[1];
    }



	$HTML = '';
	if( $_SESSION["wlabel"] == "petco" ){
		$HTML .= '
	    	<!-- Adform Tracking Code BEGIN -->
			<script type="text/javascript">
			    window._adftrack.push({
			        pm: 1453019,
			        divider: encodeURIComponent("|"),
			        pagename: encodeURIComponent("MX_Kmimos_EncontrarCuidador_180907")
			    });
			</script>
			<noscript>
			    <p style="margin:0;padding:0;border:0;">
			        <img src="https://a2.adform.net/Serving/TrackPoint/?pm=1453019&ADFPageName=MX_Kmimos_EncontrarCuidador_180907&ADFdivider=|" width="1" height="1" alt="" />
			    </p>
			</noscript>
			<!-- Adform Tracking Code END -->
		';
	}

	$km5 = '';
	if( $_SESSION['km5'] == 'Yes' ){
		/*$km5 = '
			<div class="msg_km5">
				Mostrando cuidadores a menos de 5 km de su ubicación actual, si desea ver todos los resultados puede hacer click <a href="#" onclick="km5(\'No\');">aquí</a>
			</div>
		';*/
	}

    $HTML .= '

		<div class="header-search" style="background-image:url('.getTema().'/images/new/km-fondo-buscador.gif);">
			<div class="overlay"></div>
		</div>

		<div class="container contentenedor-buscador-todos content-wlabel-search">
			<div class="km-contentido-formulario-buscador">
				<form class="km-formulario-buscador" action="'.get_home_url().'/wp-content/themes/kmimos/procesos/busqueda/buscar.php" method="POST">
					<input type="hidden" name="USER_ID" value="'.$user_id.'" />
					
					<input type="hidden" id="latitud" name="latitud" value="19.4162262" />
					<input type="hidden" id="longitud" name="longitud" value="-99.1661392" />

					<input type="hidden" id="km5" name="km5" value="'.$busqueda["km5"].'" />
					
					<div class="km-bloque-cajas km-search-wlabel" >
						<div class="km-div-ubicacion">
						
							<div class="km-select-custom km-select-ubicacion btn-group" style="width:100%;border-right: 0px; height: 45px;border-top: 0px;">
								<i id="mi_ubicacion" class="fa fa-crosshairs icon_left"></i>
							    <input type="text" 
									id="ubicacion_txt" 
									class="km-fechas" 
									style="width: 100%;background: transparent; border: 0px; padding: 0px 0px 0px 15px;"
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
							<small class="hidden error_ubicacion" data-error="ubicacion">Función disponible solo en México</small>
						</div>
						<div class="km-div-fechas">
							<input type="text" id="checkin" name="checkin" placeholder="DESDE" value="'.$busqueda["checkin"].'" class="km-input-custom km-input-date date_from" readonly>
							<input type="text" id="checkout" name="checkout" placeholder="HASTA" value="'.$busqueda["checkout"].'" class="km-input-custom km-input-date date_to" readonly>
						</div>
						<div class="km-div-enviar" style="position: relative;">
							<button type="submit" class="km-submit-custom" name="button">
								BUSCAR
							</button>
							<div id="buscando_container">
								<i id="buscando" class="fa fa-spinner fa-spin" style=""></i>
							</div>
						</div>
						<div class="clear"></div>
					</div>

					<div class="km-div-filtro">
						<div class="km-titulo-filtro">
							FILTRAR BÚSQUEDA
						</div>
						<div class="km-cajas-filtro">
							
							<div class="form-group">
						    	<button class="btn km-select-custom-button" type="button" title="TIPO MASCOTA">
						    		'.$tipo_mascota.'
						    	</button>
						    	<ul class="list-unstyled km-select-custom-list">
							    	'.$option_tipo_mascota.'
								</li>
							</div>
							
							<div class="form-group">
						    	<button class="btn km-select-custom-button" type="button" title="TIPO SERVICIO">
						    		'.$tipo_servicio_display.'
						    	</button>
						    	<ul class="list-unstyled km-select-custom-list">
							    	'.$option_tipo_servicio.'
								</li>
							</div>

							<div class="form-group">
						    	<button class="btn km-select-custom-button" type="button" title="TAMAÑO DE MASCOTA">
						    		'.$tamanos_mascotas_display.'
						    	</button>
						    	<ul class="list-unstyled km-select-custom-list">
								    '.$option_tamanos_mascotas.'
								</li>
							</div>

							<div class="form-group">
						    	<button class="btn km-select-custom-button" type="button" title="SERVICIOS ADICIONALES">
						    		'.$servicios_adicionales_display.'
						    	</button>
						    	<ul class="list-unstyled km-select-custom-list">
								    '.$option_servicios_adicionales.'
								</li>
							</div>

							<div class="km-caja-filtro">
								<div class="input-group km-input-content">
									<input type="text" name="nombre" value="'.$busqueda["nombre"].'" placeholder="BUSCAR POR NOMBRE" class=" ">
									<span class="input-group-btn">
										<button type="submit">
										    <img src="'.getTema().'/images/new/km-buscador.svg" width="18px">
										</button>
									</span>
								</div>
							</div> 

						</div>
						<div class=" hidden-sm hidden-md hidden-lg" style="margin-top:15px; border-radius: 6px;">
							 
							<div class="dropdown">
							  	<button class="dropdown-order btn km-select-button-nojs btn-default dropdown-toggle" type="button" title="ORDENAR POR" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="padding: 10px 30px 10px 6px !important;border-radius: 6px!important;">
							    	'.$titulo_ordenamiento.'
							  	</button>
							  	<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">'.$ordenamiento.'</ul>
							</div>

						</div>

					</div>
				</form>
			</div>
	    	
	    	<div class="km-caja-resultados">
				<div class="km-columna-izq">
					'.$destacados_str.'
					<div class="km-superior-resultados">
						<span class="km-texto-resultados">
							<b>Resultado de búsqueda</b> '.$total.' cuidadores disponibles
						</span>

						<div class="km-opciones-resultados">
							<!-- 
							<div class="km-vista-resultados">
								<a href="./km-resultado.html" class="view-list active">
									List
								</a>
								<a href="./km-resultado-grid.html" class="view-grid">
									Gris
								</a>
							</div> -->

							<div class="km-orden-resultados">
								
								<div class="btn-group hidden-xs">
								  	<button class="km-select-custom dropdown-order km-cajas-filtro-dropdown dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 8px 30px 8px 20px !important;border-radius: 6px!important;">
								    	'.$titulo_ordenamiento.'</span>
								  	</button>
								  	<ul class="dropdown-menu">'.$ordenamiento.'</ul>
								</div>

							</div>
						</div>
					</div>

					'.$km5.'

					'.$CUIDADORES_STR.'

					<div class="navigation">
						<ul>
							'.$paginacion["html"].'
						</ul>
						<div class="message-nav">
							'.($paginacion["inicio"]+1).' - '.$paginacion["fin"].' de '.$total.' Cuidadores Certificados
						</div>
					</div>
					
				</div>
				<div class="km-columna-der">
					<div class="km-titulo-mapa">
						<B>UBICACIÓN DE RESULTADOS EN MAPA</B>
					</div>
					<strong class="km-leyenda" style ="color: #6b1c9b;">Pica en las patitas para ver los cuidadores</strong>
					<div id="mapa" class="km-mapa"></div>
					<div id="mapa-close"><i class="fa fa-close"></i></div>
				</div>
			</div>
		</div>
		<a href="#" class="km-btn-primary btnOpenPopup btnOpenPopupMap">VER UBICACIÓN EN MAPA</a>
		<script type="text/javascript" src="'.getTema().'/js/markerclusterer.js"></script>
		<script type="text/javascript" src="'.getTema().'/js/oms.min.js"></script>	
			

    ';
    include ('partes/cuidador/conocelo.php');
	echo comprimir_styles($HTML);

	global $margin_extra_footer;
	$margin_extra_footer = "footer-busqueda";
    get_footer(); 

/*
	foreach ($_SESSION as $key => $value) {
		unset($_SESSION[ $key ]);
	}*/

/*	echo "<pre>";
		print_r($_SESSION["sql"]);
	echo "</pre>";*/
?>
<?php 
    /*
        Template Name: Busqueda
    */

    wp_enqueue_style('busqueda', get_recurso("css")."busqueda.css", array(), '1.0.0');
    wp_enqueue_style('busqueda_responsive', get_recurso("css")."responsive/busqueda.css", array(), '1.0.0');

    wp_enqueue_style('conocer', getTema()."/css/conocer.css", array(), '1.0.0');
    wp_enqueue_style('conocer_responsive', getTema()."/css/responsive/conocer_responsive.css", array(), '1.0.0');

	wp_enqueue_style( 'bootstrap.min', getTema()."/css/bootstrap.min.css", array(), "1.0.0" );
	wp_enqueue_style( 'datepicker.min', getTema()."/css/datepicker.min.css", array(), "1.0.0" );
	wp_enqueue_style( 'jquery.datepick', getTema()."/lib/datapicker/jquery.datepick.css", array(), "1.0.0" );

	wp_enqueue_style( 'fontawesome4', getTema()."/css/font-awesome.css", array(), '1.0.0');

    wp_enqueue_script('jquery.datepick', getTema()."/lib/datapicker/jquery.datepick.js", array("jquery"), '1.0.0');
    wp_enqueue_script('jquery.plugin', getTema()."/lib/datapicker/jquery.plugin.js", array("jquery"), '1.0.0');

    wp_enqueue_script('markerclusterer_js', getTema()."/js/markerclusterer.js", array("jquery"), '1.0.0');
    wp_enqueue_script('oms_js', getTema()."/js/oms.min.js", array("jquery"), '1.0.0');

	wp_enqueue_script('buscar_home', get_recurso("js")."busqueda.js", array(), '1.0.0');
    wp_enqueue_script('select_localidad', getTema()."/js/select_localidad.js", array(), '1.0.0');
    wp_enqueue_script('check_in_out', getTema()."/js/fecha_check_in_out.js", array(), '1.0.0');


    get_header();

    $user_id = get_current_user_id();

    $tam = getTamanos();
    foreach ($tam as $key => $value) {
    	$check = ( is_array($_SESSION['busqueda']['tamanos']) && in_array($key, $_SESSION['busqueda']['tamanos']) ) ? 'checked': '';
    	$tam[ $key ] = $check;
    }

    $tipos = ['perros' => '', 'gatos' => ''];
    foreach ($tipos as $key => $value) {
    	$check = ( is_array($_SESSION['busqueda']['mascotas']) && in_array($key, $_SESSION['busqueda']['mascotas']) ) ? 'checked': '';
    	$tipos[ $key ] = $check;
    }


    $HTML = '
    	<div class="busqueda_container">

    		<div class="filtos_container">
    			
    			<form id="buscar" action="'.getTema().'/procesos/busqueda/buscar.php" method="POST">

					<input type="hidden" name="USER_ID" value="'.$user_id.'" />

					<input type="hidden" id="latitud" name="latitud" value="'.$_SESSION['busqueda']['latitud'].'" />
					<input type="hidden" id="longitud" name="longitud" value="'.$_SESSION['busqueda']['longitud'].'" />

					<div class="ubicacion_container">
						<img class="ubicacion_localizacion" src="'.get_recurso("img").'SVG/Localizacion.svg" />
						<input type="text" id="ubicacion_txt" name="ubicacion_txt" value="'.$_SESSION['busqueda']['ubicacion_txt'].'" placeholder="Ubicación estado municipio" autocomplete="off" />

						<input type="hidden" id="ubicacion" name="ubicacion" value="'.$_SESSION['busqueda']['ubicacion'].'" />	
					    <div class="cerrar_list_box">
					    	<div class="cerrar_list">X</div>
					    	<ul id="ubicacion_list" class=""></ul>
					    </div>

						<i id="mi_ubicacion" class="fa fa-crosshairs icon_left ubicacion_gps"></i>
						<div class="barra_ubicacion"></div>

						<small class="hidden" data-error="ubicacion">Función disponible solo en México</small>
					</div>

					<div class="fechas_container">
						<div id="desde_container">
							<img class="icon_fecha" src="'.get_recurso("img").'SVG/Fecha.svg" />
							<input type="text" id="checkin" name="checkin" placeholder="Desde" class="date_from" value="'.$_SESSION['busqueda']['checkin'].'" readonly>
							<small class="">Requerido</small>
						</div>
						<div>
							<img class="icon_fecha" src="'.get_recurso("img").'SVG/Fecha.svg" />
							<input type="text" id="checkout" name="checkout" placeholder="Hasta" class="date_to" value="'.$_SESSION['busqueda']['checkout'].'" readonly>
							<small class="">Requerido</small>
						</div>
					</div>

					<div class="tipo_mascota_container">
						<label class="input_check_box" for="perro">
							<input type="checkbox" id="perro" name="mascotas[]" value="perros" '.$tipos['perros'].' />
							<span>
								<div class="tam_label_pc">Perro</div>
							</span>
							<div class="top_check"></div>
						</label>

						<label class="input_check_box" for="gato">
							<input type="checkbox" id="gato" name="mascotas[]" value="gatos" '.$tipos['gatos'].' />
							<span>
								<div class="tam_label_pc">Gato</div>
							</span>
							<div class="top_check"></div>
						</label>
					</div>

					<div class="tamanios_container">
						<label class="input_check_box" for="paqueno">
							<input type="checkbox" id="paqueno" name="tamanos[]" value="paquenos" '.$tam['paquenos'].' />
							<span>
								<img class="icon_fecha" src="'.get_recurso("img").'RESPONSIVE/SVG/Pequenio.svg" />
								<div class="tam_label_pc">Peq.</div>
								<small>0 a 25 cm</small>
							</span>
							<div class="top_check"></div>
						</label>
						<label class="input_check_box" for="mediano">
							<input type="checkbox" id="mediano" name="tamanos[]" value="medianos" '.$tam['medianos'].' />
							<span>
								<img class="icon_fecha" src="'.get_recurso("img").'RESPONSIVE/SVG/Mediano.svg" />
								<div class="tam_label_pc">Med.</div>
								<small>25 a 58 cm</small>
							</span>
							<div class="top_check"></div>
						</label>
					</div>

					<div class="tamanios_container">
						<label class="input_check_box" for="grande">
							<input type="checkbox" id="grande" name="tamanos[]" value="grandes" '.$tam['grandes'].' />
							<span>
								<img class="icon_fecha" src="'.get_recurso("img").'RESPONSIVE/SVG/Grande.svg" />
								<div class="tam_label_pc">Gde</div>
								<small>58 a 73 cm</small>
							</span>
							<div class="top_check"></div>
						</label>
						<label class="input_check_box" for="gigante">
							<input type="checkbox" id="gigante" name="tamanos[]" value="gigantes" '.$tam['gigantes'].' />
							<span>
								<img class="icon_fecha" src="'.get_recurso("img").'RESPONSIVE/SVG/Gigante.svg" />
								<div class="tam_label_pc">Gte.</div>
								<small>73 a 200 cm</small>
							</span>
							<div class="top_check"></div>
						</label>
					</div>

					<a href="#" class="mas_filtros">Más filtros</a>
    			</form>

    		</div>
    		
    		<div class="resultados_container">
    			<pre>';

    				echo comprimir( $HTML );
    				print_r( $tipos );
    				print_r( $tam );
    				print_r( $_SESSION['busqueda'] );
    				print_r( $_SESSION['resultado_busqueda'] );


    $HTML = '</pre></div>
    		
    		<div class="mapa_container">
    			<label>
    				Actualizar al mover en el mapa 
    				<input type="checkbox" id="update_to_move" />
    			</label>
    			<div id="mapa"></div>
    		</div>

    	</div>
    ';

    echo comprimir( $HTML );
    
    get_footer();

?>
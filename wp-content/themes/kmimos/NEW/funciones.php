<?php
	function get_recurso($tipo){
		return getTema()."/recursos/".$tipo."/";
	}

	function get_destacados_new(){
		if( !isset($_SESSION) ){ session_start(); }
        global $wpdb;
        $_POST = $_SESSION['busqueda'];
        $resultados = $_SESSION['resultado_busqueda'];
        $lat = $_POST["latitud"];
        $lng = $_POST["longitud"];
        $top_destacados = ""; $cont = 0;
        if( $lat != "" && $lng != "" ){
            $sql_top = $wpdb->get_results("SELECT * FROM destacados");
            $destacados = [];
            foreach ($sql_top as $key => $value) { $destacados[] = $value->cuidador; }
            $DESTACADOS_ARRAY = [];
            $cont = 0;
            foreach ($resultados as $key => $_cuidador) {
                if( in_array($_cuidador->id, $destacados) && $cuidador->DISTANCIA <= 200 ){
                    $cont++;
                    $cuidador = $wpdb->get_row("SELECT * FROM cuidadores WHERE id = {$_cuidador->id}");
                    $data = $wpdb->get_row("SELECT post_title AS nom, post_name AS url FROM wp_posts WHERE ID = {$cuidador->id_post}");
                    $nombre = $data->nom;
                    $img_url = kmimos_get_foto($cuidador->user_id);
                    $url = get_home_url() . "/petsitters/" . $data->url;
                    $anios_exp = $cuidador->experiencia;
                    if( $anios_exp > 1900 ){
                        $anios_exp = date("Y")-$anios_exp;
                    }
                    $DESTACADOS_ARRAY[] = [
                    	"img" => $img_url,
                    	"nombre" => $nombre,
                    	"url" => $url,
                    	"desde" => ($cuidador->hospedaje_desde*getComision()),
                    	"distancia" => floor($_cuidador->DISTANCIA),
                    	"ranking" => kmimos_petsitter_rating($cuidador->id_post),
                    	"experiencia" => $anios_exp,
                		"valoraciones" => $cuidador->valoraciones
                    ];
                }
                if( $cont >= 4 ){ break; }
            }
        }else{
            $ubicacion = explode("_", $_POST["ubicacion"]);
            if( count($ubicacion) > 0 ){ $estado = $ubicacion[0]; }
            $estado_des = $wpdb->get_var("SELECT name FROM states WHERE id = ".$estado);
            $sql_top = "SELECT * FROM destacados WHERE estado = '{$estado}'";
            $tops = $wpdb->get_results($sql_top);
            foreach ($tops as $value) {
                $cuidador = $wpdb->get_row("SELECT * FROM cuidadores WHERE id = {$value->cuidador}");
                $data = $wpdb->get_row("SELECT post_title AS nom, post_name AS url FROM wp_posts WHERE ID = {$cuidador->id_post}");
                $nombre = $data->nom;
                $img_url = kmimos_get_foto($cuidador->user_id);
                $url = get_home_url() . "/petsitters/" . $data->url;
                $anios_exp = $cuidador->experiencia;
                if( $anios_exp > 1900 ){
                    $anios_exp = date("Y")-$anios_exp;
                }
                $DESTACADOS_ARRAY[] = [
                	"img" => $img_url,
                	"id_post" => $cuidador->id_post,
                	"nombre" => $nombre,
                	"url" => $url,
                	"desde" => ($cuidador->hospedaje_desde*getComision()),
                	"distancia" => floor($_cuidador->DISTANCIA),
                	"ranking" => kmimos_petsitter_rating($cuidador->id_post),
                	"experiencia" => $anios_exp,
                	"valoraciones" => $cuidador->valoraciones
                ];
            }
        }

        $user_id = get_current_user_id();
        $favoritos = get_favoritos();

        if( count($DESTACADOS_ARRAY) > 0 ){
	        foreach ($DESTACADOS_ARRAY as $key => $destacado) {

	        	$fav_check = 'false';
		        $fav_del = '';
		        $fav_img = 'Corazon';
		        if (in_array($destacado["id_post"], $favoritos)) {
		            $fav_check = 'true'; 
		            $favtitle_text = esc_html__('Quitar de mis favoritos','kmimos');
		            $fav_del = 'favoritos_delete';
		            $fav_img = 'Favorito';
		        }

		        $favorito_movil = '
		        	<img 
		        		class="favorito '.$fav_del.'" '.$style_icono.'" 
		        		data-reload="false"
			            data-user="'.$user_id.'" 
			            data-num="'.$destacado["id_post"].'" 
			            data-active="'.$fav_check.'"
			            data-favorito="'.$fav_check.'"
		        		src="'.get_recurso("img").'BUSQUEDA/SVG/iconos/'.$fav_img.'.svg" 
		        	/>
		        ';

	        	$top_destacados .= '
	        		<div class="destacados_item">
	        			<div class="desacado_img">
	        				<div class="desacado_img_interna" style="background-image: url( '.$destacado["img"].' );"></div>
	        			</div>
	        			<div class="desacado_img_normal" style="background-image: url( '.$destacado["img"].' );"></div>
        				'.$favorito_movil.'
	        			<div class="desacado_title">
	        				<span>Dest</span> '.$destacado["nombre"].'
	        			</div>
	        			<div class="desacado_experiencia">'.$destacado["experiencia"].' años de experiencia</div>
	        			<div class="desacado_monto">Desde <strong>MXN $ '.$destacado["desde"].'</strong></div>
	        			<div class="desacado_ranking_container">'.$destacado["ranking"].'</div>
	        			<div class="desacado_experiencia">'.$destacado["valoraciones"].' valoraciones</div>
	        			<a class="desacado_boton_reservar">Reservar</a>
	        		</div>
	            ';
	        }
	        $top_destacados = '
	        	<div class="destacados_container"  data-total="'.(count($DESTACADOS_ARRAY)).'" data-actual="0">
	        		<h2>Cuidadores destacados</h2>
	        		<div class="destacados_box">'.$top_destacados.'</div>
	        	</div>
				<div class="Flecha_Izquierda Ocultar_Flecha">
					<img onclick="destacadoAnterior( jQuery(this) );" src="'.get_recurso("img").'BUSQUEDA/SVG/iconos/Flecha_Izquierda.svg" />
				</div>
				<div class="Flecha_Derecha '.$ocultar_siguiente_img.'">
					<img onclick="destacadoSiguiente( jQuery(this) );" src="'.get_recurso("img").'BUSQUEDA/SVG/iconos/Flecha_Derecha.svg" />
				</div>';
        }

        return comprimir($top_destacados);
	}

	function get_resultados_new($PAGE = 0){
		if( !isset($_SESSION) ){ session_start(); }
        global $wpdb;
		$resultados = $_SESSION['resultado_busqueda'];
		$HTML = ""; $total = count($resultados);
		$fin = ( $total > ($PAGE+10) ) ? $PAGE+10 : $total;

		$user_id = get_current_user_id();

		$favoritos = get_favoritos();

		for ($i = $PAGE; $i < $fin; $i++ ) {
			$cuidador = $resultados[$i];

			if( isset($_SESSION["DATA_CUIDADORES"][ $cuidador->id ]) ){

				$_cuidador = $_SESSION["DATA_CUIDADORES"][ $cuidador->id ];

				$anios_exp = $_cuidador->experiencia;
	            if( $anios_exp > 1900 ){
	                $anios_exp = date("Y")-$anios_exp;
	            }

				$img_url = kmimos_get_foto($_cuidador->user_id);
				$desde = explode(".", number_format( ($_cuidador->hospedaje_desde*getComision()) , 2, '.', ',') );

				$direccion = $_cuidador->direccion;
				if( strlen($_cuidador->direccion) > 50 ){
					$direccion = mb_strcut($_cuidador->direccion, 0, 50, "UTF-8")."...";
				}

				if( $direccion == "0" ){ $direccion = ""; }

				$ocultar_flash = "ocultar_flash";
				$ocultar_flash_none = "ocultar_flash_none";
				$ocultar_descuento = "ocultar_descuento";
				if( $_cuidador->atributos["flash"] == 1 ){
					$ocultar_flash = "";
					$ocultar_flash_none = "";
				}
				if( $_cuidador->atributos["destacado"]+0 == 1 ){
					$ocultar_descuento = "";
				}

				$ocultar_todo = "";
				if( $ocultar_flash != "" && $ocultar_descuento != "" ){
					$ocultar_todo = "ocultar_flash_descuento";
				}

				$fav_check = 'false';
		        $fav_del = '';
		        $fav_img_pc = 'Corazon_Gris';
		        $fav_img_movil = 'Corazon';
		        if (in_array($_cuidador->id_post, $favoritos)) {
		            $fav_check = 'true'; 
		            $favtitle_text = esc_html__('Quitar de mis favoritos','kmimos');
		            $fav_del = 'favoritos_delete';
		            $fav_img_pc = 'Favorito';
		            $fav_img_movil = 'Favorito';
		        }
		        $favorito_movil = '
		        	<img 
		        		class="favorito '.$fav_del.'" '.$style_icono.'" 
		        		data-reload="false"
			            data-user="'.$user_id.'" 
			            data-num="'.$_cuidador->id_post.'" 
			            data-active="'.$fav_check.'"
			            data-favorito="'.$fav_check.'"
		        		src="'.get_recurso("img").'BUSQUEDA/SVG/iconos/'.$fav_img_movil.'.svg" 
		        	/>
		        ';

		        $favorito_pc = '
		        	<img 
		        		class="'.$fav_del.'" '.$style_icono.'" 
		        		data-reload="false"
			            data-user="'.$user_id.'" 
			            data-num="'.$_cuidador->id_post.'" 
			            data-active="'.$fav_check.'"
			            data-favorito="'.$fav_check.'"
		        		src="'.get_recurso("img").'BUSQUEDA/SVG/iconos/'.$fav_img_pc.'.svg" 
		        	/>
		        ';

				$comentario = '';
				if( isset($_cuidador->comentario->comment_author_email) ){
					if( strlen($_cuidador->comentario->comment_content) > 200 ){
						$_cuidador->comentario->comment_content = mb_strcut($_cuidador->comentario->comment_content, 0, 200, "UTF-8")."...";
					}
					$comentario = '
						<div class="resultados_item_comentario">
							<div class="resultados_item_comentario_img">
								<div class="resultados_item_comentario_avatar" style="background-image: url( '.$_cuidador->comentario->foto.' );"></div>
							</div>
							<div class="resultados_item_comentario_contenido">
								'.( $_cuidador->comentario->comment_content ).' <a href="#">(Ver más)</a>
							</div>
							<div class="resultados_item_comentario_favorito">
								<span>
									'.$favorito_pc.'
								</span>
							</div>
						</div>
					';
				}else{
					$comentario = '
						<div class="resultados_item_comentario">
							<div class="resultados_item_comentario_img"></div>
							<div class="resultados_item_comentario_contenido"></div>
							<div class="resultados_item_comentario_favorito">
								<span>
									'.$favorito_pc.'
								</span>
							</div>
						</div>
					';
				}

				$galeria = '<div class="resultados_item_info_img" style="background-image: url('.$img_url.');"></div>';
				if( is_array($_cuidador->galeria) ){
					foreach ($_cuidador->galeria as $key => $value) {
						$galeria .= '<div class="resultados_item_info_img" style="background-image: url('.get_home_url().'/wp-content/uploads/cuidadores/galerias/'.$value.');"></div>';
					}
				}

				$ocultar_siguiente_img = ( count($_cuidador->galeria) > 1 ) ? '': 'Ocultar_Flecha';

				$HTML .= '
					<div class="resultado_item">
						<div class="resultados_hover"></div>
						<div class="resultado_item_container">
							<div class="resultados_item_top">

								<div class="resultados_item_iconos_container '.$ocultar_todo.'">
									<div class="resultados_item_icono icono_disponibilidad '.$ocultar_flash.'">
										<span class="disponibilidad_PC">Disponibilidad inmediata</span>
										<span class="disponibilidad_MOVIl">Disponible</span>
									</div>
									<div class="resultados_item_icono icono_flash '.$ocultar_flash_none.'"><span></span></div>
									<div class="resultados_item_icono icono_descuento '.$ocultar_descuento.'"><span></span></div>
								</div>

							</div>
							<div class="resultados_item_middle">
								<div class="resultados_item_info_container">
									<div class="resultados_item_info_img_container" data-total="'.(count($_cuidador->galeria)+1).'" data-actual="0">
										<div class="resultados_item_info_img_box">
											'.$galeria.'
										</div>
										'.$favorito_movil.'
										<img onclick="imgAnterior( jQuery(this) );" class="Flechas Flecha_Izquierda Ocultar_Flecha" src="'.get_recurso("img").'BUSQUEDA/SVG/iconos/Flecha_2.svg" />
										<img onclick="imgSiguiente( jQuery(this) );" class="Flechas Flecha_Derecha '.$ocultar_siguiente_img.'" src="'.get_recurso("img").'BUSQUEDA/SVG/iconos/Flecha_1.svg" />
									</div>
									<div class="resultados_item_info">
										<div class="resultados_item_titulo"> <span>'.($i+1).'.</span> '.($_cuidador->titulo).'</div>
										<div class="resultados_item_subtitulo">"Tus mascotas se sentirán como en casa mietras se queden"</div>
										<div class="resultados_item_direccion" title="'.$_cuidador->direccion.'">'.($direccion).'</div>
										<div class="resultados_item_servicios">
											'.get_servicios_new($_cuidador->adicionales).'
											<div class="resultados_item_comentarios">
												'.$_cuidador->valoraciones.' comentarios
											</div>
											<div class="resultados_item_ranking">
												'.kmimos_petsitter_rating($_cuidador->id_post).'
											</div>
										</div>
										<div class="resultados_item_experiencia">
											'.$anios_exp.' años de experiencia
										</div>
										<div class="resultados_item_precio_container">
											<span>Desde</span>
											<div>MXN$ <strong>'.$desde[0].'<span>,'.$desde[1].'</span></strong></div>
											<span class="por_noche">Por noche</span>
										</div>
										<div class="resultados_item_ranking_movil">
											'.kmimos_petsitter_rating($_cuidador->id_post).'
										</div>
										<div class="resultados_item_valoraciones">
											'.$_cuidador->valoraciones.' valoraciones
										</div>
										'.$comentario.'
									</div>
								</div>
							</div>
							<div class="resultados_item_bottom">
								<a href="#" class="boton boton_border_gris">
									<span class="boton_conocer_PC">Solicitud de conocer</span>
									<span class="boton_conocer_MOVIl"><span class="boton_conocer_MOVIl">Conocer</span>
								</a>
								<a href="'.get_home_url().'/petsitters/'.$_cuidador->url.'" class="boton boton_verde">Reservar</a>
							</div>
						</div>
					</div>
				';
			}
		}

		return $HTML;
	}

	function get_list_servicios_adicionales(){
		return [
			"corte" => true,
			"bano" => true,
			"transportacion_sencilla" => true,
			"transportacion_redonda" => true,
			"visita_al_veterinario" => true,
			"limpieza_dental" => true,
			"acupuntura" => true
		];
	}


    if(!function_exists('get_servicios_new')){
        function get_servicios_new($adicionales){
            $r = "";
            
            //$adicionales = unserialize($adicionales);
            $adicionales_array = get_list_servicios_adicionales();

            if( count($adicionales) > 0 ){
                foreach($adicionales as $key => $value){
                    switch ($key) {
                        case 'corte':
                            if( $value > 0){
                                $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/MORADOS/Corte.svg' height='40' title='Corte de pelo y u&ntilde;as'> ";
                                $adicionales_array[$key] = false;
                            }
                        break;
                        case 'bano':
                            if( $value > 0){
                                $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/MORADOS/Banio.svg' height='40' title='Ba&ntilde;o'> ";
                                $adicionales_array[$key] = false;
                            }
                        break;
                        case 'transportacion_sencilla':
                        	$entro = false;
                        	foreach ($value as $_key => $precio) {
	                            if( $precio > 0){
	                                $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/MORADOS/Trans_Sencillo.svg' height='40' title='Transporte Sencillo'> ";
	                                $entro = true;
	                                break;
	                            }
	                        }

	                        if( $entro ){
	                            $adicionales_array[$key] = false;
	                        }
                        break;
                        case 'transportacion_redonda':
                        	$entro = false;
                        	foreach ($value as $_key => $precio) {
	                            if( $precio > 0){
	                                $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/MORADOS/Trans_Redondo.svg' height='40' title='Transporte Redondo'> ";
	                                $entro = true;
	                                break;
	                            }
	                        }

	                        if( $entro ){
	                            $adicionales_array[$key] = false;
	                        }
                        break;
                        case 'visita_al_veterinario':
                            if( $value > 0){
                                $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/MORADOS/Veterinario.svg' height='40' title='Visita al Veterinario'> ";
                                $adicionales_array[$key] = false;
                            }
                        break;
                        case 'limpieza_dental':
                            if( $value > 0){
                                $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/MORADOS/Dental.svg' height='40' title='Limpieza Dental'> ";
                                $adicionales_array[$key] = false;
                            }
                        break;
                        case 'acupuntura':
                            if( $value > 0){
                                $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/MORADOS/Acupuntura.svg' height='40' title='Acupuntura'> ";
                                $adicionales_array[$key] = false;
                            }
                        break;

                        /* Servicio  Principales */
	                        case 'paseos':
	                            
	                        break;
	                        case 'guarderia':
	                            
	                        break;
	                        case 'adiestramiento_basico':
	                            
	                        break;
	                        case 'adiestramiento_intermedio':
	                            
	                        break;
	                        case 'adiestramiento_avanzado':
	                            
	                        break;
                    }
                }
            }

            foreach ($adicionales_array as $key => $value) {
            	if( $value ){
	                switch ($key) {
	                    case 'corte':
	                        $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/GRISES/Corte.svg' height='40' title='Corte de pelo y u&ntilde;as'> ";
	                    break;
	                    case 'bano':
	                        $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/GRISES/Banio.svg' height='40' title='Ba&ntilde;o'> ";
	                    break;
	                    case 'transportacion_sencilla':
	                    	$r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/GRISES/Trans_Sencillo.svg' height='40' title='Transporte Sencillo'> ";
	                    break;
	                    case 'transportacion_redonda':
	                    	$r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/GRISES/Trans_Redondo.svg' height='40' title='Transporte Redondo'> ";
	                    break;
	                    case 'visita_al_veterinario':
	                        $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/GRISES/Veterinario.svg' height='40' title='Visita al Veterinario'> ";
	                    break;
	                    case 'limpieza_dental':
	                        $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/GRISES/Dental.svg' height='40' title='Limpieza Dental'> ";
	                    break;
	                    case 'acupuntura':
	                        $r .= "<img src='".get_recurso("img")."BUSQUEDA/SVG/servicios/GRISES/Acupuntura.svg' height='40' title='Acupuntura'> ";
	                    break;

	                }
                }
            }

            return $r;
        }
    }

    function update_ubicacion(){
    	global $wpdb;

    	$cuidadores = $wpdb->get_results("
    		SELECT 
    			c.id,
    			c.email,
    			u.estado,
    			u.municipios
    		FROM 
    			cuidadores AS c
    		INNER JOIN ubicaciones AS u  ON ( u.cuidador = c.id )
    	");

    	foreach ($cuidadores as $key => $value) {
    		$est = ( $value->estado == "==" ) ? "": $value->estado;
    		$mun = ( $value->municipios == "==" ) ? "": $value->municipios;
    		$wpdb->query("UPDATE cuidadores SET estados = '{$est}', municipios = '{$mun}' WHERE cuidadores.id = {$value->id};");
    	}
    }

    function update_titulo(){
    	global $wpdb;

    	$cuidadores = $wpdb->get_results("
    		SELECT 
    			c.id,
    			p.post_title AS titulo,
    			p.post_name AS url
    		FROM 
    			cuidadores AS c
    		INNER JOIN wp_posts AS p  ON ( p.ID = c.id_post )
    	");

    	foreach ($cuidadores as $key => $value) {
    		$wpdb->query("UPDATE cuidadores SET titulo = '{$value->titulo}', url = '{$value->url}' WHERE cuidadores.id = {$value->id};");
    	}
    }

    function pre_carga_data_cuidadores(){
    	global $wpdb;

    	$cuidadores = $wpdb->get_results("
    		SELECT 
    			cuidadores.id,
    			cuidadores.user_id,
    			cuidadores.id_post,
    			cuidadores.experiencia,
    			cuidadores.latitud,
    			cuidadores.longitud,
    			cuidadores.direccion,
    			cuidadores.hospedaje_desde,
    			cuidadores.adicionales,
    			cuidadores.atributos,
    			cuidadores.rating,
    			cuidadores.valoraciones,
    			cuidadores.titulo,
    			cuidadores.url
    		FROM 
    			cuidadores
    		WHERE 
    			activo = 1
    	");

    	$_cuidadores = [];
    	foreach ($cuidadores as $key => $value) {
    		$cuidadores[ $key ]->adicionales = unserialize($value->adicionales);
    		$cuidadores[ $key ]->atributos = unserialize($value->atributos);
    		$cuidadores[ $key ]->galeria = get_galeria($value->id);
    		$cuidadores[ $key ]->comentario = get_comment_cuidador($value->id_post);

    		$_cuidadores[ $value->id ] = $cuidadores[ $key ];
    	}

    	return $_cuidadores;

    }

    function get_galeria($cuidador_id){
		$id_cuidador = ($cuidador_id)-5000;
		$sub_path_galeria = "/".$id_cuidador."/mini/";
		$path_galeria = dirname(dirname(dirname(dirname(__DIR__))))."/wp-content/uploads/cuidadores/galerias/".$id_cuidador."/mini/";
		$galeria_array = array();
		if( is_dir($path_galeria) ){
			if ($dh = opendir($path_galeria)) { 
				$imagenes = array();
				$cont = 0;
		        while ( ( ($file = readdir($dh)) !== false ) && $cont <= 7 ) { 
		            if (!is_dir($path_galeria.$file) && $file!="." && $file!=".."){ 
		               	$imagenes[] = $sub_path_galeria.$file;
		            } 
		            $cont++;
		        } 
		      	closedir($dh);
		      	return $imagenes;
	  		} 
		}
		return "";
    }

    function get_comment_cuidador($cuidador_post_id){
    	global $wpdb;

    	$comentario = $wpdb->get_row("
    		SELECT 
    			c.comment_author_email,
    			c.comment_content 
    		FROM 
    			wp_comments AS c
    		INNER JOIN wp_commentmeta AS m ON ( m.comment_id = c.comment_ID AND m.meta_key = 'trust' )
    		WHERE 
    			c.comment_post_ID = {$cuidador_post_id} AND c.comment_approved = 1 AND c.comment_content != ''
    		ORDER BY 
    			c.comment_ID DESC
    		LIMIT 0, 1
    	");

    	if( !empty($comentario) ){
    		$user_id = $wpdb->get_var("SELECT ID FROM wp_users WHERE user_email = '{$comentario->comment_author_email}'");
			$comentario->foto = kmimos_get_foto( $user_id );
		}else{
			$comentario = false;
		}

    	return $comentario;
    }
?>
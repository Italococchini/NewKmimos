<?php

    date_default_timezone_set('America/Mexico_City');

	session_start();

	include(realpath(__DIR__."/../../../../../vlz_config.php"));
	include(realpath(__DIR__."/../funciones/db.php"));
	include(realpath(__DIR__."/../funciones/cambiar_char_especiales.php"));

	if( isset($_GET["flash"]) ){
		//$_POST = unserialize($_SESSION['busqueda']);

		$ini = $_POST["checkin"];
		$fin = $_POST["checkout"];

		$_POST = array(
			"checkin" => $ini,
			"checkout" => $fin,
			"servicios" => array(
				"flash"
			)
		);
	}

	$conn = new mysqli($host, $user, $pass, $db);
	$db = new db($conn); 

	$hoy = date("d/m/Y");
	$manana = date("d/m/Y", strtotime("+1 day") );

	if( empty($_POST) ){
		$_POST = unserialize($_SESSION['busqueda']);
	}

	$ubicaciones_inner = '';
	$nombre_inner = '';
	$ubicaciones_filtro = "";
	$latitud = (isset($latitud))? $latitud: "";
	$longitud = (isset($longitud))? $longitud: "";

	// Marca para retornar a la pagina #1 con nuevas busquedas
	$_SESSION['nueva_busqueda'] = 1;

	// Ordenar busqueda 
	if( isset($_GET['o']) ){
		$data = [];
		if( $_SESSION['busqueda'] != '' ){
			$data['orderby'] = $_GET['o'];
			$_POST = $data;
		}
	}

	if( isset($_GET['redireccionar']) ){
		$_POST['redireccionar'] = $_GET['redireccionar'];
	}

	extract($_POST);

	if( isset($_GET["q"]) ){
		$servicios = explode( '+', $_GET['q'] );
		$_POST['servicios'] = $servicios;
	}


    /* Filtros por Gatos */

    	$GATOS_CONDICION = "";
    	if( is_array($mascotas) ){
	    	if( in_array("gatos", $mascotas) ){
	    		$GATOS_CONDICION .= " AND atributos LIKE '%gatos\";s:2:\"Si%' "; 
	    	}
    	}

	if( $USER_ID != "" ){

		$filtros = array(
			"agresivo_mascotas" => 0,
			"agresivo_personas" => 0,
			"pequenos" => 0,
			"medianos" => 0,
			"grandes" => 0,
			"gigantes" => 0,
			"comportamiento_gatos" => []
		);

		$filtrar_tamanios = true;
		$solo_perros = false;
		$solo_gatos = false;
		if( is_array($mascotas) ){
	    	if( $mascotas[0] == "gatos" && count($mascotas) == 1 ){
				$filtrar_tamanios = false;
			}
		}else{
			$mascotas = [
				"perros"
			];
		}

		if( $mascotas[0] == "perros" && count($mascotas) == 1 ){
			$solo_perros = true;
		}

		if( $mascotas[0] == "gatos" && count($mascotas) == 1 ){
			$solo_gatos = true;
		}

		if( count($mascotas) == 2 ){
			$solo_gatos = false;
			$solo_perros = false;
		}

		$_mascotas = $db->get_results("SELECT * FROM wp_posts WHERE post_author = '{$USER_ID}' AND post_type = 'pets' AND post_status = 'publish' ");
		$perros = 0; $gatos = 0;
		foreach ($_mascotas as $key => $value) {
			$_metas = $db->get_results("SELECT * FROM wp_postmeta WHERE post_id = '{$value->ID}' AND meta_key IN ('aggressive_with_humans', 'aggressive_with_pets', 'size_pet', 'comportamiento_gatos', 'pet_type', 'pet_sociable')");
			$metas = array();
			foreach ($_metas as $key2 => $value2) {
				$metas[ $value2->meta_key ] = $value2->meta_value;
			}
			foreach ($_metas as $key2 => $value2) {
				switch ( $value2->meta_key ) {
					case 'aggressive_with_humans':
						if( $metas[ 'pet_type' ] == '2605' && $value2->meta_value == 1 ){
							$filtros["agresivo_personas"] = 1;
						}
					break;
					case 'aggressive_with_pets':
						if( $metas[ 'pet_type' ] == '2605' && $value2->meta_value == 1 ){
							$filtros["agresivo_mascotas"] = 1;
						}
					break;
					case 'pet_sociable':
						if( $metas[ 'pet_type' ] == '2605' && $value2->meta_value == 0 ){
							$filtros["pet_sociable"] = 1;
						}
					break;
					case 'size_pet':
						if( $metas[ 'pet_type' ] == '2605' && $filtrar_tamanios ){
							switch ($value2->meta_value) {
								case 0:
									$filtros["pequenos"] = 1;
								break;
								case 1:
									$filtros["medianos"] = 1;
								break;
								case 2:
									$filtros["grandes"] = 1;
								break;
								case 3:
									$filtros["gigantes"] = 1;
								break;
							}
						}
					break;
					case 'comportamiento_gatos':
						$temp_comp = json_decode($value2->meta_value);
						foreach ($temp_comp as $key => $value) {
							if( !in_array($key, $filtros["comportamiento_gatos"]) ){
								if( $value == 1 ){
									$filtros["comportamiento_gatos"][ $key ] = 1;
								}
							}
						}
					break;
					case 'pet_type':
						if( $value2->meta_value == '2605' ){
							$perros++;
						}
						if( $value2->meta_value == '2608' ){
							$gatos++;
						}
					break;
				}
			}
		}

		$FILTRO_ESPECIA = array();

		//if( $filtrar_perros ){
		if( $perros > 0 && $solo_gatos == false ){

			if( $filtros["agresivo_mascotas"] == 1 ){
				$FILTRO_ESPECIA[] = " (  cuidadores.comportamientos_aceptados LIKE '%agresivos_perros\";i:1%' OR  cuidadores.comportamientos_aceptados LIKE '%agresivos_perros\";s:1:\"1%' ) ";
			}

			if( $filtros["agresivo_personas"] == 1 ){
				$FILTRO_ESPECIA[] = " (  cuidadores.comportamientos_aceptados LIKE '%agresivos_personas\";i:1%' OR  cuidadores.comportamientos_aceptados LIKE '%agresivos_personas\";s:1:\"1%' ) ";
			}

			if( $filtros["pet_sociable"] == 1 ){
				$FILTRO_ESPECIA[] = " (  cuidadores.comportamientos_aceptados LIKE '%no_sociables\";i:1%' OR cuidadores.comportamientos_aceptados LIKE '%no_sociables\";s:1:\"1%' ) ";
			}

			if( $filtros["pequenos"] == 1 ){
				$FILTRO_ESPECIA[] = " (  cuidadores.tamanos_aceptados LIKE '%pequenos\";i:1%' OR  cuidadores.tamanos_aceptados LIKE '%pequenos\";s:1:\"1%' ) ";
			}

			if( $filtros["medianos"] == 1 ){
				$FILTRO_ESPECIA[] = " (  cuidadores.tamanos_aceptados LIKE '%medianos\";i:1%' OR  cuidadores.tamanos_aceptados LIKE '%medianos\";s:1:\"1%' ) ";
			}

			if( $filtros["grandes"] == 1 ){
				$FILTRO_ESPECIA[] = " (  cuidadores.tamanos_aceptados LIKE '%grandes\";i:1%' OR  cuidadores.tamanos_aceptados LIKE '%grandes\";s:1:\"1%' ) ";
			}

			if( $filtros["gigantes"] == 1 ){
				$FILTRO_ESPECIA[] = " (  cuidadores.tamanos_aceptados LIKE '%gigantes\";i:1%' OR  cuidadores.tamanos_aceptados LIKE '%gigantes\";s:1:\"1%' ) ";
			}

		}

		// if( $filtrar_gatos ){
		if( $gatos > 0 && $solo_perros == false ){
			if( $filtros["comportamiento_gatos"] != "" ){
				foreach ($filtros["comportamiento_gatos"] as $key_comportamiento_gato => $value_comportamiento_gato) {
					if( $value_comportamiento_gato == 1 ){
						$FILTRO_ESPECIA[] = " (  cuidadores.comportamientos_aceptados LIKE '%".$key_comportamiento_gato."\";s:1:\"1%' ) ";
					}
				}
			}
		}

		if( count($FILTRO_ESPECIA) > 0 ){
			$FILTRO_ESPECIA = " AND ( ".implode(" AND ", $FILTRO_ESPECIA)." )";
		}else{
			$FILTRO_ESPECIA = "";
		}

	}

	$condiciones = "";

    /* Filtros por fechas */

    	$FLASH = false;

	    if( isset($servicios) ){

	    	$servicios_extras = array(
		        "hospedaje",
		    	"guarderia",
		    	"paseos",
		    	"adiestramiento"
		    );

	    	$servicios_buscados = "";
			foreach ($servicios as $key => $value) {

				if( $value != "flash" ){ 
				
					if( in_array($value, $servicios_extras) ){ 
						if( $servicios_buscados == "" ){
							$servicios_buscados .= " cupos.tipo LIKE '%{$value}%' ";
						}else{
							$servicios_buscados .= " OR cupos.tipo LIKE '%{$value}%' ";
						}
					}
					if( $value != "hospedaje" ){
						// $condiciones .= " AND adicionales LIKE '%".$value."%'";
						// if( in_array($value, $servicios_extras) ){ 
							if( strpos($value,'adiestramiento') === false){
								$condiciones .= ' AND adicionales LIKE \'%status_'.$value.'";s:1:"1%\'';
							}else{
								$condiciones .= 'AND (';
								$condiciones .= ' 	adicionales LIKE \'%status_adiestramiento_basico";s:1:"1%\' 		OR ';
								$condiciones .= ' 	adicionales LIKE \'%status_adiestramiento_intermedio";s:1:"1%\' 	OR ';
								$condiciones .= ' 	adicionales LIKE \'%status_adiestramiento_avanzado";s:1:"1%\' 			';
								$condiciones .= ')';
							}
						// }
					}

				}else{
					$FLASH = true;
				}

			}
	    	
	    	if( $servicios_buscados != "" ){
	    		$servicios_buscados = "( ".$servicios_buscados." ) AND";
	    	}
		}

		if( isset($checkin)  && $checkin  != '' && isset($checkout) && $checkout != '' ){ 

			$checkin = date("Y-m-d", strtotime( str_replace("/", "-", $checkin) ) );
			$checkout = date("Y-m-d", strtotime( str_replace("/", "-", $checkout) ) );

	    	$_no_disponibles = $db->get_results("
			SELECT 
    				cuidador
    			FROM 
    				cupos 
    			WHERE 
    				{$servicios_buscados} 
    				cupos.fecha >= '{$checkin}' AND 
    				cupos.fecha <= '{$checkout}' AND (
    					cupos.full = 1 OR 
    					cupos.no_disponible = 1
    				)
			");
			$no_disponibles = array();
			if( $_no_disponibles !== false ){
				foreach ($_no_disponibles as $cuidador) {
					$no_disponibles[] = $cuidador->cuidador;
				}
			}
			if( count($no_disponibles) > 0 ){
				$condiciones .= " AND user_id NOT IN (".implode(",", $no_disponibles).") ";
			}
	   	}


    /* Fin Filtros por fechas */


    /* Filtros por Flash */

    	$FLASH_ORDEN = "";
    	if( $FLASH ){
    		$condiciones .= " AND atributos LIKE '%flash\";s:1:\"1%' "; 
    	}else{
    		if( $hoy == $_POST["checkin"] ||  $manana == $_POST["checkin"] ){
    			$FLASH_ORDEN = ", ( 
		    			SELECT 
		    				count(*)
		    			FROM 
		    				cuidadores AS cuidadores_2
		    			WHERE 
		    				cuidadores_2.id = cuidadores.id AND
		    				atributos LIKE '%flash\";s:1:\"1%'
		    		) AS FLASH
    			";
    		}
    	}

    /* Fin Filtros por Flash */

    /* Filtros por servicios y tamaños */
	  	
	  	if( is_array($tamanos) ){
	    	foreach ($tamanos as $key => $value) {
	     		$condiciones .= " AND ( tamanos_aceptados LIKE '%\"".$value."\";i:1%' || tamanos_aceptados LIKE '%\"".$value."\";s:1:\"1\"%' ) "; 
	     	} 
     	} 
     	
    /* Fin Filtros por servicios y tamaños */

    /* Filtro nombre  */
	    if( isset($nombre) ){ 
	    	if( $nombre != "" ){ 
	    		$query_by_nombre = "( post_cuidador.post_title LIKE '%".$nombre."%' "; 

	    		// Eliminar caracteres especiales
	    		$nombre_espacial = scanear_string( $nombre );
		    	if( $nombre_espacial != "" ){ 
		    		$query_by_nombre .= " OR post_cuidador.post_title LIKE '%".$nombre_espacial."%' "; 
	    		} 

	    		// Agregar a condicion
	    		$condiciones .= " AND ".$query_by_nombre." )";
	    	} 
	   	}
    /* Fin Filtro nombre */

    /* Filtros por rangos */
    if( isset($rangos) ){
	    if( $rangos[0] != "" ){ $condiciones .= " AND (hospedaje_desde*1.25) >= '".$rangos[0]."' "; }
	    if( $rangos[1] != "" ){ $condiciones .= " AND (hospedaje_desde*1.25) <= '".$rangos[1]."' "; }
	    if( $rangos[2] != "" ){ $anio_1 = date("Y")-$rangos[2]; $condiciones .= " AND experiencia <= '".$anio_1."' "; }
	    if( $rangos[3] != "" ){ $anio_2 = date("Y")-$rangos[3]; $condiciones .= " AND experiencia >= '".$anio_2."' "; }
	    if( $rangos[4] != "" ){ $condiciones .= " AND rating >= '".$rangos[4]."' "; }
	    if( $rangos[5] != "" ){ $condiciones .= " AND rating <= '".$rangos[5]."' "; }
	}
    /* Fin Filtros por rangos */

    /* Ordenamientos */
    	// $orderby = ( isset($orderby) )? $orderby : 'rating_desc' ;
	    switch ($orderby) {
	    	case 'rating_desc':
	    		$orderby = "valoraciones DESC, rating DESC";
	    	break;
	    	case 'rating_asc':
	    		$orderby = "valoraciones ASC, rating ASC";
	    	break;
	    	case 'distance_asc':
	    		$orderby = "DISTANCIA ASC";
	    	break;
	    	case 'distance_desc':
	    		$orderby = "DISTANCIA DESC";
	    	break;
	    	case 'price_asc':
	    		$orderby = "hospedaje_desde ASC";
	    	break;
	    	case 'price_desc':
	    		$orderby = "hospedaje_desde DESC";
	    	break;
	    	case 'experience_asc':
	    		$orderby = "experiencia ASC";
	    	break;
	    	case 'experience_desc':
	    		$orderby = "experiencia DESC";
	    	break;
	    	case 'flash':
	    		$FLASH_ORDEN = "
    				, ( 
		    			SELECT 
		    				count(*)
		    			FROM 
		    				cuidadores AS cuidadores_2
		    			WHERE 
		    				cuidadores_2.id = cuidadores.id AND
		    				atributos LIKE '%flash\";s:1:\"1%'
		    		) AS FLASH
    			";
	    		$orderby = "FLASH DESC, rating DESC, valoraciones DESC";
	    	break;
	    }

    /* Fin Ordenamientos */

    /* Filtro de busqueda */
    	$ubicacion = explode("_", $ubicacion);
    	$estados = (count($ubicacion)>0)? $ubicacion[0] : '';
    	$municipios = (count($ubicacion)>1)? $ubicacion[1]: '';
	    
	    if( $estados != "" && $municipios != "" ){
            $ubicaciones_inner = "INNER JOIN ubicaciones AS ubi ON ( cuidadores.id = ubi.cuidador )";
            $ubicaciones_filtro = "AND ( ubi.estado LIKE '%=".$estados."=%' AND ubi.municipios LIKE '%=".$municipios."=%'  )";   
            $_SESSION['km5'] = "No"; 
	    }else{ 
	        if( $estados != "" ){
	            $ubicaciones_inner = "INNER JOIN ubicaciones AS ubi ON ( cuidadores.id = ubi.cuidador )";
	            $ubicaciones_filtro = "AND ( ubi.estado LIKE '%=".$estados."=%' )";
	            $_SESSION['km5'] = "No";
	        }else{
	            if( $latitud != "" && $longitud != "" && $km5 != "No" ){
	       			$calculo_distancia 	= "( 6371 * acos( cos( radians({$latitud}) ) * cos( radians(latitud) ) * cos( radians(longitud) - radians({$longitud}) ) + sin( radians({$latitud}) ) * sin( radians(latitud) ) ) )";
	                $DISTANCIA 			= ", {$calculo_distancia} as DISTANCIA";
	               	//  $FILTRO_UBICACION = "HAVING DISTANCIA < 5";

	                $_SESSION['km5'] = "Yes";

	                if( $orderby == "" ){ $orderby = "DISTANCIA ASC"; }
	            }else{
	                $DISTANCIA = "";
	                $FILTRO_UBICACION = "";
	                $_SESSION['km5'] = "No";
	            }
	        }
	    }
    /* Fin Filtro de busqueda */

    /* Filtro predeterminado */
    	if( $orderby == "" ){ $orderby = "rating DESC, valoraciones DESC"; }
    /* Fin Filtro predeterminado */

    if( $FLASH_ORDEN != "" ){
    	$orderby = "FLASH DESC, ".$orderby;
    }

    $home = $db->get_var("SELECT option_value FROM wp_options WHERE option_name = 'siteurl'");

    /* SQL cuidadores */
    	if( !empty($orderby) ){
    		$orderby = ", {$orderby}";
    	}

	    $sql = "
	    SELECT 
	        cuidadores.id,
	        cuidadores.id_post,
	        cuidadores.user_id,
	        cuidadores.longitud,
	        cuidadores.latitud,
	        cuidadores.rating,
	        cuidadores.adicionales,
	        (cuidadores.hospedaje_desde*1.25) AS precio,
	        cuidadores.experiencia,
	        cuidadores.valoraciones,
	        post_cuidador.post_name AS slug,
	        post_cuidador.post_title AS titulo
	        {$DISTANCIA}
	        {$FLASH_ORDEN}
	    FROM 
	        cuidadores 
	    	INNER JOIN wp_posts AS post_cuidador ON ( cuidadores.id_post = post_cuidador.ID )
	    	{$ubicaciones_inner}
	    	{$nombre_inner}
	    WHERE 
	        activo = '1' and cuidadores.hospedaje_desde >= 1 {$condiciones} {$ubicaciones_filtro} {$FILTRO_UBICACION} {$FILTRO_ESPECIA}
	    	{$GATOS_CONDICION}
	    ORDER BY total_impresiones ASC {$orderby}";

	    /*
		echo "<pre>";
			print_r( $sql );
		echo "</pre>";
		*/
		


		// print_r( $FILTRO_ESPECIA );

    /* FIN SQL cuidadores */

    $cuidadores = $db->get_results($sql);

    $views_ids = []; 

    $pines = array(); $pines_ubicados = array();
    if( $cuidadores != false ){
	
		$rad = pi() / 180;
		$grados = 0;
		$longitud_init = 0.005;

		foreach ($cuidadores as $key => $cuidador) {
			$anios_exp = $cuidador->experiencia;
			if( $anios_exp > 1900 ){
				$anios_exp = date("Y")-$anios_exp;
			}
			$url = $home."/petsitters/".$cuidador->slug;

			$coord = $cuidador->latitud."_".$cuidador->longitud;
	    	if( array_key_exists($coord, $pines_ubicados) !== false ){
				$cuidador->latitud = $cuidador->latitud + $longitud_init*cos($grados*$rad);
				$cuidador->longitud = $cuidador->longitud + $longitud_init*sin($grados*$rad);
	    	}else{
	    		$pines_ubicados[$coord] = 0;
	    	}

	    	$grados += 20;

	    	if( $grados > 360 ){
	    		$longitud_init +=  0.002;
	    		$grados = 0;
	    	}

	    	$views_ids[] = $cuidador->id;

			$pines[] = array(
				"ID"   => $cuidador->id,
				"post_id"   => $cuidador->id_post,
				"user" => $cuidador->user_id,
				"lat"  => $cuidador->latitud,
				"lng"  => $cuidador->longitud,
				"nom"  => utf8_encode($cuidador->titulo),
				"url"  => utf8_encode($url), 
				"exp"  => $anios_exp,
				"adi"  => $cuidador->adicionales,
				//"ser"  => "",
				"rating" => ceil($cuidador->rating),
				"pre"  => $cuidador->precio
			);

		}
    }

    if( count($views_ids)>1 ){
    	foreach ($views_ids as $val) {
    		$count++;
    		$separador = ( !empty($views_id) )? ',', ''; 
    		$views_id .= $separador.$val;
    		if( $count == 10 ){
		    	$db->query( 
		    		"UPDATE cuidadores SET total_impresiones = total_impresiones + 1 WHERE id in ( {$views_id} )" 
		    	);
    		}
    	}
    }
	

	$pines_json = json_encode($pines);
    $pines_json = "<script>var pines = eval('".$pines_json."');</script>";
	$_SESSION['pines'] = $pines_json;
	$_SESSION['pines_array'] = serialize($pines);

	$temp_rangos = @array_filter($_POST["rangos"]);
	if( count($temp_rangos) > 0 ){
		$_POST["rangos"] = $temp_rangos;
	}else{
		unset($_POST["rangos"]);
	}

	$_POST = @array_filter($_POST);
	unset($_POST['redireccionar']);
	$_SESSION['busqueda'] = serialize($_POST);
	$_SESSION['sql'] = $sql;
    $_SESSION['resultado_busqueda'] = $cuidadores;

	if( isset($_GET["log"]) ){
		echo "<pre>";
			print_r( $sql );
			print_r( $_POST );
		echo "</pre>";
	}else{
       	if( $redireccionar == 1 ) {
		 	header("location: {$home}busqueda/");
		}
	}

?>
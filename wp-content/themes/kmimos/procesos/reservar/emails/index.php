<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

	extract($_GET);
	if( isset($_GET["id_orden"]) ){
		include((dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))))."/wp-load.php");
	}

	global $URL_LOCAL;

	$PATH_TEMPLATE = (dirname(dirname(dirname(__DIR__))));

	$info = kmimos_get_info_syte();
	add_filter( 'wp_mail_from_name', function( $name ) { global $info; return $info["titulo"]; });
    add_filter( 'wp_mail_from', function( $email ) { global $info; return $info["email"]; });

    global $wpdb;
	$id = $id_orden;

	if( $id_orden+0 == 0 ){ exit(); }
	$es_orden = $wpdb->get_var("SELECT post_type FROM wp_posts WHERE ID = '".$id."'");
	if( $es_orden != "shop_order" ){ exit(); }

	$data = kmimos_desglose_reserva_data($id, true);

	extract($data);

 	$modificacion_de = get_post_meta($servicio["id_reserva"], "modificacion_de", true);
    if( $modificacion_de != "" ){ 
    	$modificacion = "
    	<div style='width: 100%;
		    background-color: #e4e4e4;
		    margin: 0px auto;
		    font-family: Arial;
		    font-size: 15px;
		    letter-spacing: 0.3px;
		    color: #000000;
		    padding: 14px 0px;
		    text-align: center;'>
            Esta es una modificación de la reserva #: ".$modificacion_de."
        </div>";
 	}else{ $modificacion = ""; }

	$email_admin = $info["email"];

	$mascotas_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/mascotas.php';
    $mascotas_plantilla = file_get_contents($mascotas_plantilla);
    $mascotas = "";
	foreach ($cliente["mascotas"] as $mascota) {
		$tempEdad = explode(" ", $mascota["edad"]);
		$mascota["edad"] = ( count($tempEdad) == 4 ) ? $tempEdad[0]."<span style='color: #FFF;'>_</span>".$tempEdad[1]."<br>".$tempEdad[2]."<span style='color: #FFF;'>_</span>".$tempEdad[3] : $mascota["edad"];
		$temp = str_replace('[NOMBRE]', $mascota["nombre"], $mascotas_plantilla);
		$temp = str_replace('[TYPE]', $mascota["tipo"], $temp);
		$temp = str_replace('[RAZA]', $mascota["raza"], $temp);
		$temp = str_replace('[EDAD]', $mascota["edad"], $temp);
		$temp = str_replace('[TAMANO]', $mascota["tamano"], $temp);
		$temp = str_replace('[CONDUCTA]', $mascota["conducta"], $temp);
		$mascotas .= $temp;
	}
	
	$desglose_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/desglose.php';
    $desglose_plantilla = file_get_contents($desglose_plantilla);

    $desglose = ""; $gatos = 0;
	foreach ($servicio["variaciones"] as $variacion) {
		$plural = ""; if($variacion[0]>1){$plural="s";}
		if( strtoupper($variacion[1]) == 'GATOS' ){ $gatos++; }
		$temp = str_replace('[TAMANO]', strtoupper($variacion[1]), $desglose_plantilla);
		$temp = str_replace('[CANTIDAD]', $variacion[0]." mascota".$plural, $temp);
		$temp = str_replace('[TIEMPO]', $variacion[2], $temp);
		$temp = str_replace('[PRECIO_C_U]', "$ ".$variacion[3], $temp);
		$temp = str_replace('[SUBTOTAL]', "$ ".$variacion[4], $temp);
		$desglose .= $temp;
	}

	$adicionales_desglose_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/adicionales_desglose.php';
    $adicionales_desglose_plantilla = file_get_contents($adicionales_desglose_plantilla);

    $adicionales = "";
    foreach ($servicio["adicionales"] as $adicional) {
		$temp = str_replace('[SERVICIO]', $adicional[0], $adicionales_desglose_plantilla);
		$temp = str_replace('[CANTIDAD]', $adicional[1], $temp);
		$temp = str_replace('[PRECIO_C_U]', "$ ".$adicional[2], $temp);
		$temp = str_replace('[SUBTOTAL]', "$ ".$adicional[3], $temp);
		$adicionales .= $temp;
	}

	if( $adicionales != "" ){
		$adicionales_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/adicionales.php';
    	$adicionales_plantilla = file_get_contents($adicionales_plantilla);
    	$adicionales = $adicionales_plantilla.$adicionales;
	}
	
	$transporte_desglose_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/transporte_desglose.php';
    $transporte_desglose_plantilla = file_get_contents($transporte_desglose_plantilla);

    $transporte = "";
    foreach ($servicio["transporte"] as $valor) {
		$temp = str_replace('[SERVICIO]', $valor[0], $transporte_desglose_plantilla);
		$temp = str_replace('[SUBTOTAL]', "$ ".$valor[2], $temp);
		$transporte .= $temp;
	}

	if( $transporte != "" ){
		$transporte_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/transporte.php';
    	$transporte_plantilla = file_get_contents($transporte_plantilla);
    	$transporte = $transporte_plantilla.$transporte;
	}

	$totales_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/totales.php';
    $totales_plantilla = file_get_contents($totales_plantilla);
    $totales_plantilla = str_replace('[TIPO_PAGO]', $servicio["tipo_pago"], $totales_plantilla);

	if( $servicio["desglose"]["descuento"]+0 > 0 ){
		$descuento_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/descuento.php';
	    $descuento_plantilla = file_get_contents($descuento_plantilla);
	    $descuento_plantilla = str_replace('[DESCUENTO]', number_format( $servicio["desglose"]["descuento"], 2, ',', '.'), $descuento_plantilla);
	    $totales_plantilla = str_replace('[DESCUENTO]', $descuento_plantilla, $totales_plantilla);
	}else{
		$totales_plantilla = str_replace('[DESCUENTO]', "", $totales_plantilla);
	}

	$deposito_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/deposito.php';
	$deposito_plantilla = file_get_contents($deposito_plantilla);
    $MONTO = "";

    if( $servicio["desglose"]["enable"] == "yes" ){
    	//$servicio["desglose"]["remaining"] -= $servicio["desglose"]["descuento"];
    	$deposito_plantilla = str_replace('[REMANENTE]', number_format( $servicio["desglose"]["remaining"], 2, ',', '.'), $deposito_plantilla);
        $totales_plantilla = str_replace('[TOTAL]', number_format( $servicio["desglose"]["total"], 2, ',', '.'), $totales_plantilla);
    	$totales_plantilla = str_replace('[PAGO]', number_format( $servicio["desglose"]["deposit"], 2, ',', '.'), $totales_plantilla);
    	$totales_plantilla = str_replace('[DETALLES]', $deposito_plantilla, $totales_plantilla);
    	$MONTO = number_format( $servicio["desglose"]["deposit"], 2, ',', '.');
    }else{
    	$deposito_plantilla = str_replace('[REMANENTE]', number_format( 0, 2, ',', '.'), $deposito_plantilla);
        $totales_plantilla = str_replace('[TOTAL]', number_format( $servicio["desglose"]["total"], 2, ',', '.'), $totales_plantilla);
    	$totales_plantilla = str_replace('[PAGO]', number_format( $servicio["desglose"]["deposit"], 2, ',', '.'), $totales_plantilla);
    	$totales_plantilla = str_replace('[DETALLES]', $deposito_plantilla, $totales_plantilla);
    	// $MONTO = number_format( $servicio["desglose"]["deposit"]-$servicio["desglose"]["descuento"], 2, ',', '.');
    	$MONTO = number_format( $servicio["desglose"]["deposit"], 2, ',', '.');
    }
	
	if( $servicio["desglose"]["descuento"]+0 > 0 ){
		$descuento_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/descuento.php';
	    $descuento_plantilla = file_get_contents($descuento_plantilla);
	    $descuento_plantilla = str_replace('[DESCUENTO]', number_format( $servicio["desglose"]["descuento"], 2, ',', '.'), $descuento_plantilla);
	    $totales_plantilla = str_replace('[DESCUENTO]', $descuento_plantilla, $totales_plantilla);
	}else{
		$totales_plantilla = str_replace('[DESCUENTO]', "", $totales_plantilla);
	}
	
	$detalles_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/detalles_servicio.php';
    $detalles_plantilla = file_get_contents($detalles_plantilla);
	$detalles_plantilla = str_replace('[inicio]', date("d/m", $servicio["inicio"]), $detalles_plantilla);
    $detalles_plantilla = str_replace('[fin]', date("d/m", $servicio["fin"]), $detalles_plantilla);
    $detalles_plantilla = str_replace('[anio]', date("Y", $servicio["fin"]), $detalles_plantilla);
    $detalles_plantilla = str_replace('[tiempo]', $servicio["duracion"], $detalles_plantilla);
    $detalles_plantilla = str_replace('[tipo_pago]', $servicio["metodo_pago"], $detalles_plantilla);
    $detalles_plantilla = str_replace('[tipo_servicio]', $servicio["tipo"], $detalles_plantilla);
    $detalles_plantilla = str_replace('[hora_inicio]', $servicio["checkin"], $detalles_plantilla);
    $detalles_plantilla = str_replace('[hora_fin]', $servicio["checkout"], $detalles_plantilla);
    $detalles_plantilla = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $detalles_plantilla);


    if( $servicio["desglose"]["reembolsar"]+0 > 0 ){
        $reembolsar_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/reembolsar.php';
        $reembolsar_plantilla = file_get_contents($reembolsar_plantilla);
        $reembolsar_plantilla = str_replace('[DEVOLVER]', number_format( $servicio["desglose"]["reembolsar"], 2, ',', '.'), $reembolsar_plantilla);
    }else{
        $reembolsar_plantilla = "";
    }

	$servicios_plantilla = $PATH_TEMPLATE.'/template/mail/reservar/partes/servicios.php';
    $servicios_plantilla = file_get_contents($servicios_plantilla);

    if( $gatos == count($servicio["variaciones"]) ){
    	$servicios_plantilla = str_replace('dog_black', 'cat_black', $servicios_plantilla);
    	
    }

	$servicios_plantilla = str_replace('[inicio]', date("d/m", $servicio["inicio"]), $servicios_plantilla);
	$servicios_plantilla = str_replace('[desglose]', $desglose, $servicios_plantilla);
    $servicios_plantilla = str_replace('[ADICIONALES]', $adicionales, $servicios_plantilla);
    $servicios_plantilla = str_replace('[TRANSPORTE]', $transporte, $servicios_plantilla);

    $confirmacion_titulo = "Confirmación de Reserva";
    if( $servicio["flash"] == "SI" && $acc == "" ){
    	$status_reserva = $wpdb->get_var("SELECT post_status FROM wp_posts WHERE ID = ".$servicio["id_orden"]);
    	if ( strtolower($servicio["metodo_pago"]) == "tienda" && $status_reserva != "wc-on-hold" ){
	    	$acc = "CFM";
	    	$confirmacion_titulo = "Confirmación de Reserva Inmediata";
    	}
    	if ( strtolower($servicio["metodo_pago"]) == "tarjeta" && $status_reserva != "pending" ){
	    	$acc = "CFM";
	    	$confirmacion_titulo = "Confirmación de Reserva Inmediata";
    	}
    	if ( strtolower($servicio["metodo_pago"]) == "saldo y/o descuentos" && $status_reserva != "pending" ){
	    	$acc = "CFM";
	    	$confirmacion_titulo = "Confirmación de Reserva Inmediata";
    	}
    }




	$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
    $vence = strtotime( $servicio["vence"]);

    $fecha = date('d', $vence)." de ".$meses[date('n', $vence)-1]. " ".date('Y', $vence) ;
    $hora = "(".date('H:i A', $vence).")";

    $_datos_cliente = getTemplate("reservar/partes/datos_cliente");
    $_datos_cuidador = getTemplate("reservar/partes/datos_cuidador");
    $instrucciones = getTemplate("reservar/partes/instrucciones");

    $_SESSION["USER_ID_CLIENTE_CORREOS"] = $cliente["id"];
    $_SESSION["ID_RESERVA_CORREOS"] = $servicio["id_reserva"];

    $INFORMACION = [
        // GENERALES

            'HEADER'                => "",
            'ID_RESERVA'            => $servicio["id_reserva"],
            'SERVICIOS'             => $servicios_plantilla,
            'MASCOTAS'              => $mascotas,
            'DESGLOSE'              => $desglose,
            'ADICIONALES'           => $adicionales,
            'TRANSPORTE'            => $transporte,
            'MODIFICACION'          => $modificacion,
            'TIPO_SERVICIO'         => trim($servicio["tipo"]),
            'DETALLES_SERVICIO'     => $detalles_plantilla,
            'TOTALES'               => str_replace('[REEMBOLSAR]', "", $totales_plantilla),

            'ACEPTAR'               => $servicio["aceptar_rechazar"]["aceptar"],
            'RECHAZAR'              => $servicio["aceptar_rechazar"]["cancelar"],

            'INSTRUCCIONES'			=> $instrucciones,
            'CODIGO'				=> end( explode("/", $servicio["pdf"]) ),
            'MONTO'					=> $MONTO,
            'FECHA'					=> $fecha,
            'HORA'					=> $hora,
            'PDF'					=> $servicio["pdf"],

        // CLIENTE
            'DATOS_CLIENTE'         => $_datos_cliente,
            'NAME_CLIENTE'          => $cliente["nombre"],
            'AVATAR_CLIENTE'        => kmimos_get_foto($cliente["id"]),
            'TELEFONOS_CLIENTE'     => $cliente["telefono"],
            'CORREO_CLIENTE'        => $cliente["email"],
            
        // CUIDADOR
            'DATOS_CUIDADOR'        => $_datos_cuidador,
            'NAME_CUIDADOR'         => $cuidador["nombre"],
            'AVATAR_CUIDADOR'       => kmimos_get_foto($cuidador["id"]),
            'TELEFONOS_CUIDADOR'    => $cuidador["telefono"],
            'CORREO_CUIDADOR'       => $cuidador["email"],
            'DIRECCION_CUIDADOR'    => $cuidador["direccion"],
    ];


	if( $acc == "" || $confirmacion_titulo == "Confirmación de Reserva Inmediata" ){
		
		$status_reserva = $wpdb->get_var("SELECT post_status FROM wp_posts WHERE ID = ".$servicio["id_orden"]);
		if( strtolower($servicio["metodo_pago"]) == "tienda" && $status_reserva == "wc-on-hold"  ){
			include(__DIR__."/tienda.php");
		}else{
			include(__DIR__."/otro.php");
		}

	}

	if( $acc != ""  ){

		$status = $wpdb->get_var("SELECT post_status FROM wp_posts WHERE ID = '".$servicio["id_reserva"]."'");

		$continuar = true;

		$usuario = $cuidador["nombre"];

		if( $superAdmin == "" ){

			if( $usu == "CLI" ){ 
				$usuario = $cliente["nombre"]; 

				if( $status == "cancelled" || $status == "modified" ){
					$estado = array(
						"modified"  => "Modificada",
						"cancelled" => "Cancelada"
					);
					$msg = "
					<div class='msg_acciones'>
						<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
					    	Hola <strong>".$usuario."</strong>
					    </div>
						<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
					    	Te notificamos que la reserva N° <strong>".$servicio["id_reserva"]."</strong> ya ha sido <strong>".$estado[$status]."</strong> anteriormente.
					    </div>
						<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
					    	Por tal motivo ya no es posible realizar cambios en el estatus de la misma.
					    </div>
					</div>";
			   		
			   		$CONTENIDO .= $msg;
			   		$continuar = false;
				}

			}else{

				$mostrar_msgs = false;

				if(  $_SESSION['admin_sub_login'] != 'YES' && $status == "confirmed" ){
					$mostrar_msgs = true;
				}

				if(  $status == "cancelled" || $status == "modified" ){
					$mostrar_msgs = true;
				}

				if( $mostrar_msgs ){
					$estado = array(
						"confirmed" => "Confirmada",
						"modified"  => "Modificada",
						"cancelled" => "Cancelada"
					);
					$msg = "
					<div class='msg_acciones'>
						<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
					    	Hola <strong>".$usuario."</strong>
					    </div>
						<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
					    	Te notificamos que la reserva N° <strong>".$servicio["id_reserva"]."</strong> ya ha sido <strong>".$estado[$status]."</strong> anteriormente.
					    </div>
						<div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
					    	Por tal motivo ya no es posible realizar cambios en el estatus de la misma.
					    </div>
					</div>";
			   		
			   		$CONTENIDO .= $msg;
			   		$continuar = false;
				}

			}

		}

		if( $NO_ENVIAR != "" ){ $continuar = true; }
		
		if( $continuar ){

			if( $acc == "CFM" ){

				$wpdb->query("UPDATE wp_posts SET post_status = 'wc-confirmed' WHERE ID = '{$servicio["id_orden"]}';");
	    		$wpdb->query("UPDATE wp_posts SET post_status = 'confirmed' WHERE ID = '{$servicio["id_reserva"]}';");

				include("confirmacion.php");

try{	
wp_mail( 'italococchini@gmail.com', "prueba", "prueba envio email");
}catch(Exception $e){}

				$sql = "SELECT  
							count(ID) as cant
						FROM wp_posts
						WHERE post_type = 'wc_booking' 
							AND not post_status like '%cart%' AND post_status = 'confirmed' 
							AND post_author = ".$cliente["id"]."
							AND DATE_FORMAT(post_date, '%m-%d-%Y') between DATE_FORMAT('2017-05-12','%m-%d-%Y') and DATE_FORMAT(now(),'%m-%d-%Y')";
				$count_reservas = $wpdb->get_var( $sql );

try{	
wp_mail( 'italococchini@gmail.com', "prueba", $count_reservas."|".$cliente["id"] );
}catch(Exception $e){}

				$message_mail='';
				$paso = "1";
//				if(  $_SESSION['admin_sub_login'] != 'YES' && $count_reservas == 1){
				if( $count_reservas == 1){
					$paso .= "2";

			   		if(isset($cliente["id"])){
						$paso .= "3";

				   		// buscar cupones
				   		$cupones = $wpdb->get_results("SELECT items.order_item_name as name
				            FROM `wp_woocommerce_order_items` as items 
				                INNER JOIN wp_woocommerce_order_itemmeta as meta ON 
				                	meta.order_item_id = items.order_item_id
				                INNER JOIN wp_posts as p ON 
				                	p.ID = ".$reserva_id." and p.post_type = 'wc_booking' 
				                WHERE meta.meta_key = 'discount_amount'
				                    and items.`order_id` = p.post_parent
				                    and not items.order_item_name like ('saldo-%')
				            ;");

				   		// validar si son del club
				   		$propietario_id=0;
				   		$propietario_nombre = '';
				   		$propietario_apellido = '';
				   		$cupon_code = '';
				   		if( !empty($cupones) ){			   			
						$paso .= "4";

					   		// Validar si son del club 
					   		foreach ($cupones as $key => $cupon) {
					   			$propietario_id = $wpdb->get_var("
					   				select user_id from wp_usermeta where meta_key = 'club-patitas-cupon' and meta_value = '".$cupon->name."'
					   			");
					   			if( $propietario_id > 0 ){
								$paso .= "5";

					   				$propietario_nombre = get_user_meta( $propietario_id, 'first_name', true );
					   				$propietario_apellido = get_user_meta( $propietario_id, 'last_name', true );
					   				$cupon_code = $cupon->name;
					   				break;
					   			}else{

					   				$propietario_id = 0;
					   			}
					   		}
							if( $propietario_id > 0 ){

							$paso .= "6";
								// agregar saldo a favor
								$saldo = get_user_meta( $propietario_id, 'kmisaldo', true );
								$saldo += 150;
								if( update_user_meta( $propietario_id, 'kmisaldo', $saldo ) ){

							$paso .= "7";
									// agregar transaccion en balance
									$wpdb->query("INSERT INTO cuidadores_transacciones (
										tipo,
										user_id,
										fecha,
										referencia,
										descripcion,
										monto,
										reserva,
										comision
									)values(
										'saldo',
										{$propietario_id},
										NOW(),
										'',
										'Saldo a favor Club de las patitas felices ".$cupon_code."',
										150,
										'',
										0									
									) 
									");
								}

								// enviar email
								$mail_info = realpath( $PATH_TEMPLATE.'/template/mail/clubPatitas/partes/info_sin_perfil.php');
								if( !empty($propietario_nombre) && !empty($propietario_apellido) ){
									$mail_info = realpath( 
										$PATH_TEMPLATE.'/template/mail/clubPatitas/partes/info_con_perfil.php'
									);
								}
								$message_info = file_get_contents($mail_info);

								$mail_file = realpath( 
									$PATH_TEMPLATE.'/template/mail/clubPatitas/notificacion_de_uso.php'
								);
								$message_mail = file_get_contents($mail_file);

								$message_mail = str_replace('[INFO]', $message_info, $message_mail);
								$message_mail = str_replace('[URL_IMG]', site_url()."/wp-content/themes/kmimos/images", $message_mail);
								$message_mail = str_replace('[name]', $propietario_nombre.' '.$propietario_apellido, $message_mail);
								$message_mail = str_replace('[url]', site_url(), $message_mail);
								$message_mail = str_replace('[CUPON]', $cupon_code, $message_mail);

							$paso .= "8";

							}				   		

				   		}



				   		/*
				   		$user_referido = get_user_meta($cliente["id"], 'landing-referencia', true);
				   		if(!empty($user_referido)){
							$username = $cliente["nombre"];
							$http = (isset($_SERVER['HTTPS']))? 'https://' : 'http://' ;
							require_once( $PATH_TEMPLATE.'/template/mail/reservar/club-referido-primera-reserva.php');
							$user_participante = $wpdb->get_results( "SELECT ID, user_email FROM wp_users WHERE md5(user_email) = '{$user_referido}'" );
							$user_participante = (count($user_participante)>0)? $user_participante[0] : [];
							if(isset($user_participante->user_email)){
								wp_mail( $user_participante->user_email, "¡Felicidades, otro perrhijo moverá su colita de felicidad!", $html );
							}
						}
						*/
					}
				}

				wp_mail( 'italococchini@gmail.com', $paso."Confirmación de uso cupon Club Patitas Felices!", $message_mail);
			}

			if( $acc == "CCL" ){
				include(__DIR__."/cancelacion.php");
			}
		
		}


	}


?>
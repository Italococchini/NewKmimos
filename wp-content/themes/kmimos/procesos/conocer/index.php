<?php
	
	extract($_GET);
	if( isset($_GET["id_orden"]) ){
		include(((dirname(dirname(dirname(dirname(dirname(__DIR__)))))))."/wp-load.php");
	    echo "
	        <a href='".get_home_url()."/perfil-usuario/solicitudes/' style='
	            border-top: solid 1px #CCC;
	            border-bottom: solid 1px #CCC;
	            margin: 10px auto;
	            width: 600px;
	            padding: 10px 0px;
	            font-weight: 600;
	            font-family: Arial;
	            text-align: center;
	            cursor: pointer;
	            font-size: 13px;
	            text-decoration: none;
	            color: #000;
	            display: block;
	        '>
	            Volver
	        </a>
	    ";
	}

	global $wpdb;

	$PATH_TEMPLATE = ((dirname(dirname(__DIR__))));

	$info = kmimos_get_info_syte();
	add_filter( 'wp_mail_from_name', function( $name ) { global $info; return $info["titulo"]; });
    add_filter( 'wp_mail_from', function( $email ) { global $info; return $info["email"]; });

    $status = $wpdb->get_var("SELECT meta_value FROM wp_postmeta WHERE post_id = $id_orden AND meta_key = 'request_status';");
	if( $status != 1 ){
		$estado = array(
			2 => "Confirmada",
			3 => "Cancelada",
			4 => "Cancelada"
		);
		$msg = "
			<div style='text-align:center; margin-bottom: 34px;'>
				<img src='".get_home_url()."/wp-content/themes/kmimos/images/emails/confirmacion_conocer_cuidador.png' style='width: 100%;' >
			</div>

			<div style='padding: 0px; margin-bottom: 34px;'>

				<div style='margin-bottom: 15px; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000;'>
					<div style='font-family: Arial; font-size: 20px; font-weight: bold; letter-spacing: 0.4px; color: #6b1c9b; padding-bottom: 10px; text-align: left;'>
						Lo sentimos,</strong>
					</div>	
				    <div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px; text-align: left;'>
				    	Te notificamos que la solicitud N° <strong>".$id_orden."</strong> ya ha sido ".$estado[$status]." anteriormente.
				    </div>
				    <div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 0px; text-align: left;'>
				    	Por tal motivo ya no es posible realizar cambios en el estatus de la misma.
				    </div>
				</div>
			</div>
		";
   		echo get_email_html($msg, false);

   		exit;
	}

	$metas_solicitud = get_post_meta($id_orden); 

	/* Cuidador */
    	$cuidador_name 	= $wpdb->get_var("SELECT post_title FROM wp_posts WHERE ID = '".$metas_solicitud['requested_petsitter'][0]."'");
		$cuidador = $wpdb->get_row("SELECT * FROM cuidadores WHERE id_post = '".$metas_solicitud['requested_petsitter'][0]."'");
		$email_cuidador = $cuidador->email;

    /* Cliente */

	    $cliente = $metas_solicitud['requester_user'][0];
		$metas_cliente = get_user_meta($cliente);
		$cliente_name = $metas_cliente["first_name"][0];

		$user = get_user_by( 'id', $cliente );
		$email_cliente = $user->data->user_email;

    if( $acc == "CFM" ){
    	include("confirmar.php");
    }

    if( $acc == "CCL" ){
    	include("cancelar.php");
    }
?>
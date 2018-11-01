<?php 
    /*
        Template Name: Perfil
    */

    if( !is_user_logged_in() ){
    	header( "location: ".get_home_url() );
    }

    wp_enqueue_style('perfil', getTema()."/css/perfil.css", array(), '1.0.0');
	wp_enqueue_style('perfil_responsive', getTema()."/css/responsive/perfil_responsive.css", array(), '1.0.0');
	wp_enqueue_script('perfil_global', getTema()."/js/perfil_global.js", array("jquery", "global_js"), '1.0.0');

	$btn_txt = "Actualizar";

	$mostrar_btn = true;
	switch ( $post->post_name ) {
		case 'perfil-usuario':
			wp_enqueue_script('perfil', getTema()."/js/perfil.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'mascotas':
		    wp_enqueue_style('mascotas', getTema()."/css/mascotas.css", array(), '1.0.0');
			wp_enqueue_style('mascotas_responsive', getTema()."/css/responsive/mascotas_responsive.css", array(), '1.0.0');
			wp_enqueue_script('mascotas', getTema()."/js/mascotas.js", array("jquery", "global_js"), '1.0.0');

			$btn_txt = "Nueva Mascota";
		break;
		case 'valorar':
			$btn_txt = "Enviar valoración";
		    wp_enqueue_style('valorar_css', getTema()."/css/valorar.css", array(), '1.0.0');
		    wp_enqueue_script('valorar_js', getTema()."/js/valorar.js", array(), '1.0.0');
		break;
		case 'ver':
			$padre = $wpdb->get_var("SELECT post_name FROM wp_posts WHERE ID = {$post->post_parent}");
			switch ($padre) {
				case 'historial':
					$mostrar_btn = false;
					wp_enqueue_style('ver_historial', getTema()."/css/ver_historial.css", array(), '1.0.0');
					wp_enqueue_style('ver_historial_responsive', getTema()."/css/responsive/ver_historial_responsive.css", array(), '1.0.0');
				break;
				case 'reservas':
					$mostrar_btn = false;
					wp_enqueue_style('ver_historial', getTema()."/css/ver_historial.css", array(), '1.0.0');
					wp_enqueue_style('ver_historial_responsive', getTema()."/css/responsive/ver_historial_responsive.css", array(), '1.0.0');
				break;
				case 'solicitudes':
					$mostrar_btn = false;
					wp_enqueue_style('ver_historial', getTema()."/css/ver_historial.css", array(), '1.0.0');
				break;
				case 'mascotas':
					wp_enqueue_style('ver_mascotas', getTema()."/css/nueva_mascotas.css", array(), '1.0.0');
					wp_enqueue_style('ver_mascotas_responsive', getTema()."/css/responsive/nueva_mascotas_responsive.css", array(), '1.0.0');
					wp_enqueue_script('ver_mascotas', getTema()."/js/nueva_mascotas.js", array("jquery", "global_js"), '1.0.0');
					
				    wp_enqueue_style('checks', getTema()."/css/checks.css", array(), '1.0.0');
					wp_enqueue_script('checks', getTema()."/js/checks.js", array("jquery", "global_js"), '1.0.0');
				break;
			}
		break;
		case 'nueva':
			$padre = $wpdb->get_var("SELECT post_name FROM wp_posts WHERE ID = {$post->post_parent}");
			switch ($padre) {
				case 'galeria':
					wp_enqueue_style('nueva_galeria', getTema()."/css/nueva_galeria.css", array(), '1.0.0');
					wp_enqueue_style('nueva_galeria_responsive', getTema()."/css/responsive/nueva_galeria_responsive.css", array(), '1.0.0');
					wp_enqueue_script('nueva_galeria', getTema()."/js/nueva_galeria.js", array("jquery", "global_js"), '1.0.0');
					$btn_txt = "Subir Foto";
				break;
				case 'mascotas':
					wp_enqueue_style('nueva_mascotas', getTema()."/css/nueva_mascotas.css", array(), '1.0.0');
					wp_enqueue_style('nueva_mascotas_responsive', getTema()."/css/responsive/nueva_mascotas_responsive.css", array(), '1.0.0');
					wp_enqueue_script('nueva_mascotas', getTema()."/js/nueva_mascotas.js", array("jquery", "global_js"), '1.0.0');
					$btn_txt = "Crear Mascota";
					
				    wp_enqueue_style('checks', getTema()."/css/checks.css", array(), '1.0.0');
					wp_enqueue_script('checks', getTema()."/js/checks.js", array("jquery", "global_js"), '1.0.0');
				break;
			}
		break;
		case 'favoritos':
		    wp_enqueue_style('favoritos', getTema()."/css/favoritos.css", array(), '1.0.0');
			wp_enqueue_style('favoritos_responsive', getTema()."/css/responsive/favoritos_responsive.css", array(), '1.0.0');
			//wp_enqueue_script('favoritos', getTema()."/js/favoritos.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'historial':
		    wp_enqueue_style('historial', getTema()."/css/historial.css", array(), '1.0.0');
			wp_enqueue_style('historial_responsive', getTema()."/css/responsive/historial_responsive.css", array(), '1.0.0');
			wp_enqueue_script('historial_js', getTema()."/js/historial.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'cancelar':
			$padre = $wpdb->get_var("SELECT post_name FROM wp_posts WHERE ID = {$post->post_parent}");
			switch ($padre) {
				case 'historial':
					$mostrar_btn = false;
					wp_enqueue_script('factura', getTema()."/js/factura.js", array("jquery", "global_js"), '1.0.0');
				break;
			}
		break;
		case 'descripcion':
		    wp_enqueue_style('descripcion', getTema()."/css/descripcion.css", array(), '1.0.0');
			wp_enqueue_style('descripcion_responsive', getTema()."/css/responsive/descripcion_responsive.css", array(), '1.0.0');
			wp_enqueue_script('descripcion', getTema()."/js/descripcion.js", array("jquery", "global_js"), '1.0.0');
		    wp_enqueue_style('checks', getTema()."/css/checks.css", array(), '1.0.0');
			wp_enqueue_script('checks', getTema()."/js/checks.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'servicios':
		    wp_enqueue_style('servicios', getTema()."/css/servicios.css", array(), '1.0.0');
			wp_enqueue_style('servicios_responsive', getTema()."/css/responsive/servicios_responsive.css", array(), '1.0.0');
			wp_enqueue_script('servicios', getTema()."/js/servicios.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'disponibilidad':
			$mostrar_btn = false;
		    wp_enqueue_style('disponibilidad', getTema()."/css/disponibilidad.css", array(), '1.0.0');
			wp_enqueue_style('disponibilidad_responsive', getTema()."/css/responsive/disponibilidad_responsive.css", array(), '1.0.0');

			wp_enqueue_style('jquery_datepick', getTema()."/lib/datapicker/jquery.datepick.css", array(), '1.0.0');
			wp_enqueue_script('jquery_datepick', getTema()."/lib/datapicker/jquery.datepick.js", array("jquery", "global_js"), '1.0.0');

			wp_enqueue_script('disponibilidad', getTema()."/js/disponibilidad.js", array("jquery", "global_js", "jquery_datepick"), '1.0.0');
		break;
		case 'galeria':
		    wp_enqueue_style('galeria', getTema()."/css/galeria.css", array(), '1.0.0');
			wp_enqueue_style('galeria_responsive', getTema()."/css/responsive/galeria_responsive.css", array(), '1.0.0');
			wp_enqueue_script('galeria', getTema()."/js/galeria.js", array("jquery", "global_js"), '1.0.0');
			$btn_txt = "Nueva Foto";
		break;
		case 'reservas':
		    wp_enqueue_style('historial', getTema()."/css/historial.css", array(), '1.0.0');
			wp_enqueue_style('historial_responsive', getTema()."/css/responsive/historial_responsive.css", array(), '1.0.0');
			wp_enqueue_script('historial', getTema()."/js/historial.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'solicitudes':
		    wp_enqueue_style('historial', getTema()."/css/historial.css", array(), '1.0.0');
			wp_enqueue_style('historial_responsive', getTema()."/css/responsive/historial_responsive.css", array(), '1.0.0');
			wp_enqueue_script('historial', getTema()."/js/historial.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'subir':
		    wp_enqueue_style('subir', getTema()."/css/subir.css", array(), '1.0.0');
			wp_enqueue_style('subir_responsive', getTema()."/css/responsive/subir_responsive.css", array(), '1.0.0');
			wp_enqueue_script('subir', getTema()."/js/subir.js", array("jquery", "global_js"), '1.0.0');


			wp_enqueue_script('base64', getTema()."/lib/collage/base64.js", array("jquery", "global_js"), '1.0.0');
			wp_enqueue_script('canvas2image', getTema()."/lib/collage/canvas2image.js", array("jquery", "global_js"), '1.0.0');
			wp_enqueue_script('html2canvas', getTema()."/lib/collage/html2canvas.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'fotos':
		    wp_enqueue_style('historial', getTema()."/css/historial.css", array(), '1.0.0');
			wp_enqueue_style('historial_responsive', getTema()."/css/responsive/historial_responsive.css", array(), '1.0.0');
			wp_enqueue_script('historial', getTema()."/js/historial.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'ver-fotos':
		    wp_enqueue_style('ver_fotos', getTema()."/css/ver_fotos.css", array(), '1.0.0');
			wp_enqueue_script('ver_fotos', getTema()."/js/ver_fotos.js", array("jquery", "global_js"), '1.0.0');
		break;
		// para el cliente
		case 'factura':
			wp_enqueue_style('datos-de-facturacion-css', getTema()."/css/datos-de-facturacion.css", array(), '1.0.0');
			wp_enqueue_script('factura', getTema()."/js/factura.js", array("jquery", "global_js"), '1.0.0');
		break;
		// para el cuidador y cliente
		case 'mis-facturas':
		    wp_enqueue_style('misfacturas', getTema()."/css/misfacturas.css", array(), '1.0.0');
			wp_enqueue_style('misfacturas_responsive', getTema()."/css/responsive/misfacturas_responsive.css", array(), '1.0.0');
			wp_enqueue_script('misfacturas_js', getTema()."/js/misfacturas.js", array("jquery", "global_js"), '1.0.0');
		break;
		case 'datos-de-facturacion':
			wp_enqueue_style('datos-de-facturacion-panel-css', getTema()."/css/datos-de-facturacion.css", array(), '2.0.0');
			wp_enqueue_script('datos-de-facturacion-panel-js', getTema()."/js/datos-de-facturacion.js", array("jquery", "global_js"), '2.0.0');
		break;		
		case 'balance':
			wp_enqueue_style('balance0-css', getTema().'/css/balance.css', array(), '2.0.0');
			wp_enqueue_style('balance1-css', getTema().'/admin/recursos/css/dataTables.bootstrap4.min.css', array(), '2.0.0');
			wp_enqueue_style('balance2-css', getTema().'/admin/recursos/css/buttons.dataTables.min.css', array(), '2.0.0');

			wp_enqueue_script('balance0-js', getTema()."/js/balance.js", array("jquery", "global_js"), '2.0.0');			
			wp_enqueue_script('balance1-js', getTema().'/admin/recursos/js/jquery.dataTables.min.js', array("jquery", "global_js"), '2.0.0');
			wp_enqueue_script('balance2-js', getTema().'/admin/recursos/js/dataTables.bootstrap4.min.js', array("jquery", "global_js"), '2.0.0');
			wp_enqueue_script('balance3-js', getTema().'/admin/recursos/js/dataTables.buttons.min.js', array("jquery", "global_js"), '2.0.0');
			wp_enqueue_script('balance4-js', getTema().'/admin/recursos/js/buttons.flash.min.js', array("jquery", "global_js"), '2.0.0');
			wp_enqueue_script('balance5-js', getTema().'/admin/recursos/js/jszip.min.js', array("jquery", "global_js"), '2.0.0');


			wp_enqueue_script('balance6-js', get_home_url()."/panel/assets/vendor/datatables.net-buttons/js/dataTables.buttons.min.js", array("jquery", "global_js"), '2.0.0');
			wp_enqueue_script('balance7-js', get_home_url()."/panel/assets/vendor/datatables.net-buttons/js/buttons.flash.min.js", array("jquery", "global_js"), '2.0.0');
			wp_enqueue_script('balance8-js', get_home_url()."/panel/assets/vendor/datatables.net-buttons/js/buttons.html5.min.js", array("jquery", "global_js"), '2.0.0');
			wp_enqueue_script('balance9-js', get_home_url()."/panel/assets/vendor/datatables.net-buttons/js/buttons.print.min.js", array("jquery", "global_js"), '2.0.0');

		break;		
	}

	get_header();

		global $post;
		global $wpdb;

		$MENU = get_menu_header();

		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;

		$img_perfil = kmimos_get_foto($user_id, true);
		$avatar = $img_perfil["img"];

		include( "procesos/funciones/funciones_perfil.php");

		switch ( $post->post_name ) {
			case 'perfil-usuario':
				include("admin/frontend/perfil/perfil.php");
			break;
			case 'mascotas':
				echo '
					<script> 
						var URL_NUEVA_IMG = "'.get_home_url().'/perfil-usuario/mascotas/nueva/";
						var IMG_DEFAULT = "'.get_home_url().'/wp-content/themes/pointfinder/images/noimg.png";
					</script>';
				include("admin/frontend/mascotas/mascotas.php");
			break;
			case 'ver':
				$padre = $wpdb->get_var("SELECT post_name FROM wp_posts WHERE ID = {$post->post_parent}");
				switch ($padre) {
					case 'historial':
						$mostrar_btn = false;
						include("admin/frontend/historial/ver.php");
					break;
					case 'reservas':
						$mostrar_btn = false;
						include("admin/frontend/reservas/ver.php");
					break;
					case 'solicitudes':
						$mostrar_btn = false;
						include("admin/frontend/solicitudes/ver.php");
					break;
					case 'mascotas':
						include("admin/frontend/mascotas/ver.php");
					break;
				}
			break;
			case 'valorar':
				include("admin/frontend/historial/valorar.php");
			break;
			case 'cancelar':
				$padre = $wpdb->get_var("SELECT post_name FROM wp_posts WHERE ID = {$post->post_parent}");
				switch ($padre) {
					case 'historial':
						$mostrar_btn = false;
						include("admin/frontend/historial/cancelar.php");
					break;
					case 'reservas':
						$mostrar_btn = false;
						include("admin/frontend/reservas/cancelar.php");
					break;
					case 'solicitudes':
						$mostrar_btn = false;
						include("admin/frontend/solicitudes/cancelar.php");
					break;
				}
			break;
			case 'confirmar':
				$padre = $wpdb->get_var("SELECT post_name FROM wp_posts WHERE ID = {$post->post_parent}");
				switch ($padre) {
					case 'reservas':
						$mostrar_btn = false;
						include("admin/frontend/reservas/confirmar.php");
					break;
					case 'solicitudes':
						$mostrar_btn = false;
						include("admin/frontend/solicitudes/confirmar.php");
					break;
				}
			break;
			case 'nueva':
				$padre = $wpdb->get_var("SELECT post_name FROM wp_posts WHERE ID = {$post->post_parent}");
				switch ($padre) {
					case 'mascotas':
						echo '<script> var IMG_DEFAULT = "'.get_home_url().'/wp-content/themes/pointfinder/images/noimg.png"; </script>';
						include("admin/frontend/mascotas/nueva.php");
					break;
					case 'galeria':
						echo '<script> var IMG_DEFAULT = "'.get_home_url().'/wp-content/themes/pointfinder/images/noimg.png"; </script>';
						include("admin/frontend/galeria/nueva.php");
					break;
				}
			break;
			case 'favoritos':
				$mostrar_btn = false;
				include("admin/frontend/favoritos/favoritos.php");
			break;
			case 'historial':
				$mostrar_btn = false;
				include("admin/frontend/historial/historial.php");
			break;
			case 'descripcion':
				include("admin/frontend/descripcion/descripcion.php");
			break;
			case 'servicios':
				include("admin/frontend/servicios/servicios.php");
			break;
			case 'disponibilidad':
				$btn_txt = "Editar Disponibilidad";
				include("admin/frontend/disponibilidad/disponibilidad.php");
			break;
			case 'galeria':
				echo '
					<script> 
						var URL_NUEVA_IMG = "'.get_home_url().'/perfil-usuario/galeria/nueva/";
					</script>';
				include("admin/frontend/galeria/galeria.php");
			break;
			case 'reservas':
				$mostrar_btn = false;
				include("admin/frontend/reservas/reservas.php");
			break;
			case 'solicitudes':
				$mostrar_btn = false;
				include("admin/frontend/solicitudes/solicitudes.php");
			break;
			case 'subir':
				$mostrar_btn = false;
				include("admin/frontend/reservas/subir_fotos.php");
			break;
			case 'fotos':
				$mostrar_btn = false;
				include("admin/frontend/fotos/fotos.php");
			break;
			case 'ver-fotos':
				$mostrar_btn = false;
				  $CONTENIDO = "
			        <div class='vlz_modal_container'>
			            <div class='vlz_modal_box'>
			                <div style='position: relative; display: inline-block;'>
			                    <i class='fa fa-times' aria-hidden='true'></i>
			                    <img src='' />
			                </div>
			            </div>
			        </div>
			    ";    
			    echo comprimir_styles($CONTENIDO);
				include("admin/frontend/fotos/ver-fotos.php");
			break;
			case 'factura':
				$mostrar_btn = false;
				include("admin/frontend/historial/factura.php");
			break;
			case 'mis-facturas':
				$mostrar_btn = false;
				include("admin/frontend/misfacturas/misfacturas.php");
			break;
			case 'datos-de-facturacion':
				if( is_petsitters() ){
					$mostrar_btn = true;
					include("admin/frontend/datos-de-facturacion/cuidador.php");					
				}else{
					$mostrar_btn = true;
					include("admin/frontend/datos-de-facturacion/clientes.php");
				}
			break;
			case 'balance':
				$mostrar_btn = false;
				if( is_petsitters() ){
					include("admin/frontend/balance/index.php");
				}
			break;
		}

		$HTML_BTN = '';
		if( $mostrar_btn ){
			$HTML_BTN = '
			<div class="container_btn">
				<input type="submit" id="btn_actualizar" class="km-btn-primary" value="'.$btn_txt.'">
				<div class="perfil_cargando" style="background-image: url('.getTema().'/images/cargando.gif);" ></div>
			</div>';
		}

		$HTML = '
	 		<div class="km-ficha-bg" style="background-image:url('.getTema().'/images/new/km-ficha/km-bg-ficha.jpg);">
				<div class="overlay"></div>
			</div>
			<div class="body km-content-reservation">
				<div class="menu_perfil">
					<div class="vlz_img_portada">
						<div class="vlz_img_portada_fondo" style="background-image: url('.$avatar.'); filter:blur(2px);" ></div>
						<div class="vlz_img_portada_normal" style="background-image: url('.$avatar.');"></div>
					</div>
					<ul>
						'.$MENU["body"].'
					</ul>
				</div>
				<div class="main" >
					<form id="form_perfil" autocomplete="off" enctype="multipart/form-data">
						'.$CONTENIDO.'
						'.$HTML_BTN.'
					</form>
				</div>
	    	</div>
		';
		$HTML .= '
			<!-- Modal -->
			<div class="modal fade" id="info_facturacion" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			        <h5 class="modal-title" id="myModalLabel">INFORMACI&Oacute;N DE FACTURACI&Oacute;N</h5>
			      </div>
			      <div class="modal-body">
			        <p style="font-size:14px;">Tu comprobante fiscal será emitido una vez la reserva se haya completado</p>
			      </div>
			    </div>
			  </div>
			</div>
		';
		echo comprimir_styles($HTML);


	  

	get_footer();
?>
<?php
	/*
        Template Name: NPS Feedback
    */
    global $wpdb;

    extract($_GET);

	// o='.md5($encuesta_id).'
	// t=external
	// v=2
	// e=italococchini@gmail.com

    $show_mensaje = 'hidden';
    $show_encuesta= '';
    $code = '';
    $encuesta;
    $user_id = 0;
    if( isset($o) && !empty($o) ){
	    $encuesta = $wpdb->get_row( "SELECT * FROM nps_preguntas WHERE md5(id) = '{$o}'" );

		$respuesta = 0;
	    if( isset($v) && !empty($v) ){
			$respuesta = $v;
	    }

		$tipo = true;
	    if( isset($t) && !empty($t) ){
	    	if( strtolower($t) == 'external' ){
	    		$tipo = false;
	    	}
	    }
	    
	    if( isset($e) && !empty($e) ){
			$respuesta_id = $wpdb->get_var( "SELECT id FROM nps_respuestas WHERE email = '{$e}' AND pregunta = ".$encuesta->id );
			if( $respuesta_id > 0 ){
				$show_mensaje = '';
				$show_encuesta= 'hidden';
			}else{
				$tipo_nps = '';
		    	if( $respuesta > 0 && $respuesta <= 6 ){
	                $tipo_nps = 'detractores';
	            }else if( $respuesta == 7 || $respuesta == 8 ){
	                $tipo_nps = 'pasivos';
	            }else if( $respuesta == 9 || $respuesta == 10 ){
	                $tipo_nps = 'promoters';
	            }
	            $code = md5( $encuesta->id . $e );
				$sql = "INSERT INTO nps_respuestas ( email, pregunta, puntos, tipo, code ) VALUES ( '{$e}', ".$encuesta->id.", {$respuesta}, '{$tipo_nps}', '{$code}' )";	            				
		    	$wpdb->query( $sql );
				$respuesta_id = $wpdb->get_var( "SELECT id FROM nps_respuestas WHERE email='{$e}' AND pregunta=".$encuesta->id );
			}
		}else{
			$show_mensaje = '';
			$show_encuesta= 'hidden';
		}
    }else{
		$show_mensaje = '';
		$show_encuesta= 'hidden';
    } 

    $url_img = get_home_url() .'/wp-content/themes/kmimos/images/';

    wp_enqueue_style('nps_style', getTema()."/css/nps-feedback.css", array(), '1.0.0');
    wp_enqueue_style('nps_responsive', getTema()."/css/responsive/nps-feedback.css", array(), '1.0.0');
	wp_enqueue_script('nps_script2', getTema()."/js/nps-feedback.js", array(), '2.0.0');

	get_header();
?>

<header class="row" style="background-image: url(<?php echo $url_img; ?>club-patitas/Kmimos-Club-de-las-patitas-felices-2.jpg)">
</header>

<section class="<?php echo $show_mensaje; ?>">
	<h3 class="titulo">Su opinión es muy importante para nosotros.</h3>
	<h1>¡Gracias por compartirla!</h1>
	<a class="" href="<?php echo get_home_url(); ?>">Volver al Inicio</a>
	<br>
	<br>
</section>

<section class="<?php echo $show_encuesta; ?>">
	<div class="col-md-6 col-sm-12 col-xs-12 col-md-offset-3">
		<p>Tómese un minuto para dejarnos sus comentarios sinceros para que podamos continuar mejorando. Nuestro equipo leerá cada respuesta. No te contengas, queremos saber lo que realmente piensas.</p>
		<p>¡Su opinión es muy importante para nosotros, gracias!</p>
	</div>
	
	<div class="col-md-6 col-sm-12 col-xs-12 col-md-offset-3">
		<form id="feedback-form">		
			<div class="row feedback-container">
				<input type="hidden" name="respuesta_id" value="<?php echo $respuesta_id; ?>">
				<input type="hidden" name="respuesta" value="<?php echo $respuesta; ?>">
				<input type="hidden" name="email" value="<?php echo $e; ?>">
				<input type="hidden" name="code" value="<?php echo $code; ?>">

				<h3 class="titulo text-left"><?php echo utf8_decode($encuesta->pregunta); ?></h3>
				<div class="text-left" style="width: 100%; padding: 10px 0px;">
					<?php for( $i = 1; $i <= 10; $i++ ){ 
						$activo = ( $respuesta == $i )? 'col-item-active' : '';
					?>
						<div data-item="<?php echo $i; ?>" class="col-item <?php echo $activo; ?>"><?php echo $i; ?></div>
					<?php } ?>		
				</div>
				<div class="tag-nivel text-left">
					<div style="display:inline-block;width:30%;text-align:left;">Nada probable</div>
					<div style="display:inline-block;width:60%;text-align:right;">Extremadamente probable</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="row feedback-container">
				<h3 class="titulo text-left">¿Cuál es la razón más importante para tu puntuación?</h3>
				<textarea class="form-control col-md-12" name="observacion" style="height: 200px;"></textarea>
				<div class="clear"></div>
			</div>
			<div class="row feedback-container-button">
				<button type="submit" id="enviar-feedback" class="btn btn-primary">Enviar comentarios</button>
			</div>
		</form>
	</div>
</section>
<?php
	get_footer(); 
?>
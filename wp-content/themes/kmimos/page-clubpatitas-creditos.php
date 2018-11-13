<?php
	/*
        Template Name: Club patitas Creditos
    */

	$user = wp_get_current_user();
	$cupon = get_user_meta( $user->ID, 'club-patitas-cupon', true );
 	if( empty($cupon) ){
		header('location:'.get_home_url().'/club-patitas-felices');
	}
	
    $url_img = get_home_url() .'/wp-content/themes/kmimos/images/club-patitas/';
    $no_top_menu = false;

    wp_enqueue_style('club_style', getTema()."/css/club-patitas-felices.css", array(), '1.0.0');
    wp_enqueue_style('club_responsive', getTema()."/css/responsive/club-patitas-felices.css", array(), '1.0.0');
	wp_enqueue_script('club_script', getTema()."/js/club-patitas-felices.js", array(), '1.0.0');


	// datatable
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


	get_header();
?>
	
	<div class="content-compartir-club">
		<aside id="compartir-club-cover" class="col-xs-12 col-sm-12 col-md-5" style="background-image: url(<?php echo getTema();?>/images/club-patitas/Kmimos-Club-de-las-patitas-felices-1.jpg);">
		</aside>
		<section class="col-xs-12 col-sm-12 col-md-7 compartir-section" style="<?php echo $center_content; ?>">
			
			<dir class="col-md-8 col-md-offset-2">
				
				<div class="row">
					<img src="<?php echo getTema().'/images/club-patitas/Kmimos-Club-de-las-patitas-felices-6.png'; ?>">
					<h2 style="margin: 10px 0px;"class="titulo">¡Tus cr&eacute;ditos del club!</h2>
					<hr>
					<table id="example" class="table table-striped table-bordered nowrap" cellspacing="0" style="width: 100%;">
		                <thead>
		                    <tr>
		                        <th></th>
		                        <th>Fecha</th>
		                        <th>Descripci&oacute;n</th>
		                        <th>Monto</th>
		                    </tr>
		                </thead>
		                <tbody></tbody>
		            </table>
				</div>

			</dir>

		</section>
	</div>
	<script type="text/javascript">
		jQuery(document).ready( function (){
			loadTabla();
		});
	</script>
<?php 
	$no_display_footer = true;
 	get_footer(); 
?>

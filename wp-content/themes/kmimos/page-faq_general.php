<?php 
     /*
        Template Name: FAQ General
    */
    wp_enqueue_style('faq_style', getTema()."/css/faq.css", array(), '1.0.0');

	wp_enqueue_script('faq_script', getTema()."/js/faq.js", array(), '1.0.0');

    get_header();


	
	if(!isset($_SESSION['ayuda']['default'])){
		$redirect = true;
		include ( 'procesos/ayuda/filtrar.php' ); 
		$redirect = false;
	}
	unset($_SESSION['ayuda']['default']);

?>
	<style type="text/css">
		.km-ficha-bg{
			background-image: url(<?php echo getTema().'/images/new/ayuda/Ayuda-Kmimos.jpg'; ?>);
		}
		@media (max-width: 700px) {	
			.km-ficha-bg{
				background-image: url(<?php echo getTema().'/images/new/ayuda/Ayuda-Kmimos-responsive.jpg'; ?>);
			}
		}
	</style>

	<div class="km-ficha-bg">
		<div class="overlay"></div>
	</div>
	<div class="body-ayuda">

		<setion id="ayuda-content" class="col-sm-6 col-sm-offset-1">
			
			<!-- Busqueda -->
			<article id="form-ayuda-detalle" class="col-sm-8">
				<?php get_form_filtrar_ayuda(); ?>
			</article>
 
 			<article class="hidden col-sm-12 text-left titulo-secundario">
				<h3 class="titulo-secundario">SERVICIOS KMIMOS</h3>
			</article>

			<!-- Ayuda Cliente-Cuidadores -->
			<article class="col-sm-12">
				<?php
				/* *************************************** *
				 * Mostrar todos los temas de la ayuda
				 * *************************************** */
				if( !isset($_SESSION['ayuda']['filtro']) ){
				/* *************************************** *
				 * Resultado para las busquedas
				 * *************************************** */
					$resultado = ( isset($_SESSION['ayuda']['resultado']) )? $_SESSION['ayuda']['resultado'] : [] ;
					if(empty($resultado)){ 
				?>

						<div class="alert alert-info">
							<div>No se encontraron articulos relacionados con la busqueda <span style="font-style: italic;">"<?php echo $_SESSION['ayuda']['terminos']; ?>"</span></div>
						</div>
						<?php $_SESSION['ayuda']['filtro'] = get_ayuda_secciones(); ?>
						  
					<?php }else{ ?>
						<article class="col-xs-12 col-md-12 ayuda-group">
							<h3>
								<strong>Resultados de busqueda para <span style="font-style: italic;">"<?php echo $_SESSION['ayuda']['terminos']; ?>"</span></strong>
							</h3>
						</article>

						<?php unset($_SESSION['ayuda']['filtro']); ?>
					<?php } ?>


					<?php foreach ($resultado as $post) { 
						if(!empty($post->post_title) && !empty($post->post_content) ){?>
							<article class="col-xs-12 col-md-12 ayuda-items">
								<a style="text-decoration:none" href="<?php echo get_the_permalink($post->ID); ?>">
									 <h3 class="title-post">
										<?php echo kmimos_ucfirst($post->post_title); ?>
									</h3>
								</a> 
								<div><hr></div>
							</article>
						<?php }?>
					<?php }?>

				<?php } ?>

				<?php if( isset($_SESSION['ayuda']['filtro']) ){

					$secciones = $_SESSION['ayuda']['filtro'];
					foreach ($secciones as $seccion) {
						$seccion_ignore = ['ayuda-para-clientes','ayuda-para-cuidadores','destacado','destacado', 'destacados_cuidadores','sugeridos', 'sugeridos-cuidadores'];
						if( !in_array( $seccion->slug, $seccion_ignore ) ){
						?>
							<article class="col-xs-12 col-md-12 ayuda-group">

								<h3 class="title-category" role="button" data-toggle="collapse" href="#seccion<?php echo $seccion->term_id; ?>">
									<?php echo $seccion->name; ?>
								</h3>
								<div class="collapse" id="seccion<?php echo $seccion->term_id; ?>">
									
									<?php
										$posts = get_ayuda_postBySeccion( $seccion->slug ); 
									
										foreach ($posts as $post) {
									?>
										<article class="ayuda-items">
											<a style="text-decoration:none" href="<?php echo get_the_permalink($post->ID); ?>">
												 <h3 class="title-post">
													<?php echo $post->post_title; ?>
												</h3>
											</a>
											<!--<div class="collapse" id="item<?php echo $post->ID; ?>">
												<div class="well">
													<?php echo $post->post_content; ?>
												</div>
											</div>-->
											<div><hr></div>
										</article>
									<?php }?>
									
								</div>

							</article>
						<?php }?>
					<?php }?>
				

				<?php } ?>
			</article>
			
			<div class="text-center col-md-12">
				<a style="text-decoration:none" href="javascript:history.back();">
					<h3 class="title-post">Volver</h3>
				</a>
			</div>
		</setion>
		<aside id="sidebar" class="col-sm-4 col-sm-offset-1">
			<div class="sidebar-content">					
				<h3 class=" text-left">Temas sugeridos</h3>
				<?php get_ayuda_sugeridos($sugerido); ?>
				<article>
					<a href="<?php echo get_home_url(); ?>/preguntas-frecuentes/no-encontraste-lo-que-buscas"><h3 class="title-category">¿No encontraste lo que buscas?</h3></a>
					<a href="<?php echo get_home_url() ?>'/contacta-con-nosotros/">
						<h3 class="title-post contacta-con-nosotros">Contáctanos</h3>
					</a>
				</article>
			</div>
		</aside>
	</div>
<?php get_footer(); ?>


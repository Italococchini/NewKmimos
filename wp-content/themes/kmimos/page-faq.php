<?php 
    /*
        Template Name: FAQ
    */
    
    wp_enqueue_style('faq_style', getTema()."/css/faq.css", array(), '1.0.0');

	wp_enqueue_script('faq_script', getTema()."/js/faq.js", array(), '1.0.0');

    get_header();

    

    if($_GET['ayuda']){

    	if($_GET['ayuda']=="clientes"){
			$ayuda="destacado";
			$sugerido="sugeridos";

    	}else if ($_GET['ayuda']=="cuidadores"){
			$ayuda="destacados_cuidadores";
			$sugerido="sugeridos-cuidadores";

    	}

		

    }else{

    	$ayuda="destacado";
    	$sugerido="sugeridos";
    }



    /* Temas de ayuda Destacados */
	$destacados = get_posts(
	    array(
			'post_status' => 'publish', 
			'posts_per_page' => -1, 
	        'post_type' => 'faq',
	        'tax_query' => array(
		        array(
		            'taxonomy' => 'seccion',
		            'field'    => 'slug',
		            'terms'    => [ 'destacado', 'destacados_cuidadores' ]
		        )
		    )
	    )
	);



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

		<section id="ayuda-content" class="col-xs-12 col-sm-12 col-md-5 col-md-offset-1">
			<section class="text-left titulo-principal">
				<h3>¿C&Oacute;MO PODEMOS AYUDARTE?</h3>
			</section>
			
			<!-- Busqueda -->
			<article id="form-ayuda" class="col-md-12">
				<?php get_form_filtrar_ayuda(); ?>
			</article>

			<!-- Ayuda Cliente-Cuidadores -->
			<article class="col-md-12">

                <?php
                    foreach ($destacados as $post) { get_posts( $posts->ID ); ?>
		                <a href="<?php echo get_permalink(); ?>">
							<div class="media" id="ayudaclientes" onClick="cambiarAyuda(this.id);">
								<div class="media-left" >
									<img src="<?php echo get_the_post_thumbnail_url(); ?>" class="img-responsive">
								</div>
								<div class="item-container">
									<p><?php the_title(); ?></p>
								</div>
							</div>
						</a>
                <?php } ?>

				<!-- 
				<div class="media" id="ayudaclientes" onClick="cambiarAyuda(this.id);">
					<div class="media-left" >
						<img src="<?php echo getTema(); ?>/images/new/ayuda/cliente.png" class="img-responsive">
					</div>
					<div class="item-container">
						<p>¿Necesitas que alguien cuide a tu peludo?</p>
					</div>
				</div>
				<div class="media" id="ayudacuidador" onClick="cambiarAyuda(this.id);">
					<div class="media-left">
						<img src="<?php echo getTema(); ?>/images/new/ayuda/cuidador.png" class="img-responsive">
					</div>
					<div class="item-container">
						<p>¿Quieres ser cuidador certificado kmimos?</p>
					</div>
				</div>
				-->
			</article>
			
		</section>
		<aside id="sidebar" class="col-xs-12 col-sm-12 col-md-4 col-sm-offset-0 col-md-offset-2">
			<div class="sidebar-content">			
				<h3 class=" text-left">Temas sugeridos</h3>
				<?php get_ayuda_sugeridos($sugerido); ?>
			</div>
		</aside>
	</div>
<?php get_footer(); ?>


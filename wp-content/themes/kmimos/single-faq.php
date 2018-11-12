<?php 
#error_reporting(E_ALL);
#ini_set('display_errors', '1');
  
    wp_enqueue_style('faq_style', getTema()."/css/faq.css", array(), '1.0.0');

	wp_enqueue_script('faq_script', getTema()."/js/faq.js", array(), '1.0.0');

    get_header();



	//$parents = get_ayuda_categoria( get_the_ID() );


	function replace_linktext( $text ){
		$link_text = [
			[
				"find" => '¿Quieres ser cuidador certificado kmimos?',
				"replace" => '<a href="https://www.kmimos.com.mx/quiero-ser-cuidador-certificado-de-perros/">¿Quieres ser cuidador certificado kmimos?</a>',
			],
			[
				"find" => '¿Necesitas que alguien cuide a tu peludo?',
				"replace" => '<a href="https://www.kmimos.com.mx/">¿Necesitas que alguien cuide a tu peludo?</a>',
			],
			[
				"find" => '¿Cómo empezar?',
				"replace" => '<a href="https://www.kmimos.com.mx/">¿Cómo empezar?</a>',
			],
			[
				"find" => '¿Qué es Kmimos?',
				"replace" => '<a href="https://www.kmimos.com.mx/">¿Qué es Kmimos?</a>',
			],
		];	
		foreach ($link_text as $item) {
			$text = str_replace( $item['find'], $item['replace'], $text );
		}
		return $text;
	}
	
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

	<div class="km-ficha-bg" >
		<div class="overlay"></div>
	</div>
	<div class="body-ayuda">

		<setion id="ayuda-content" class="col-sm-6 col-sm-offset-1">
			
			<!-- Busqueda -->
			<article id="form-ayuda-detalle" class="col-sm-8">
				<?php get_form_filtrar_ayuda(); ?>
			</article>
 
 			<!-- article class="col-sm-12 text-left titulo-secundario">
				<h3>SERVICIOS KMIMOS</h3>
			</article -->

			<!-- Ayuda Cliente-Cuidadores -->
			<article class="col-sm-12">
				<!-- h3 class="title-category"><?php get_categoria_pregunta(get_the_ID()); ?></h3 -->
				<?php if ( have_posts() ){ the_post(); ?>
					<h3 class="title-post">
						<strong>
							<?php echo replace_linktext( kmimos_ucfirst( get_the_title() ) ); ?>
						</strong>
					</h3>
					<div class="col-md-12 text-justify text-content">	
						<?php the_content(); ?>
					</div>
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
				<?php get_ayuda_relacionados(get_the_ID()); ?>
				<article>
					<a href="<?php echo get_home_url(); ?>/preguntas-frecuentes/no-encontraste-lo-que-buscas"><h3 class="title-category">¿No encontraste lo que buscas?</h3></a>
					<a href="<?php echo get_home_url() ?>/contacta-con-nosotros/">
						<h3 class="title-post contacta-con-nosotros">Contáctanos</h3>
					</a>
				</article>
			</div>
		</aside>
	</div>

<?php get_footer(); ?>
<?php 
     /*
        Template Name: FAQ Ver Mas
    */
    wp_enqueue_style('faq_style', getTema()."/css/faq.css", array(), '1.0.0');

	wp_enqueue_script('faq_script', getTema()."/js/faq.js", array(), '1.0.0');

    get_header();
	
	$categoria= $_GET['categoria'];

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
	<div class="body body-ayuda container">
		<div id="ayuda-content" class="main">
			
			<section class="row">
				<h1 class="titulo-principal">¿C&oacute;mo podemos ayudarte?</h1>
			</section>
			
			
			<!-- Busqueda -->
			<?php get_form_filtrar_ayuda(); ?>

			<!-- SubTitulos -->
			<section class="row text-center titulo-servicios">
				<span>SERVICIOS KMIMOS</span>
			</section>


			<section class="row text-left">
			<?php get_preguntas_categoria($categoria); ?>	
			
			</section>

			<a style="text-decoration:none" href="javascript:history.back();"><h3 style="color: #2196F3;"><b>Volver</b></h3></a>
		</div>
	</div>

	<section class="temas-sugeridos">
		<div class="sugeridos-content text-center">
			<div class="container">
			<a href="<?php echo get_home_url(); ?>/preguntas-frecuentes/no-encontraste-lo-que-buscas"><h3 class="title-category">¿No encontraste lo que buscas?</h3></a><br> 
			<a href="<?php echo get_home_url(); ?>/contacta-con-nosotros/"><b> Cont&aacute;ctanos</b></a>
			</div>
		</div>
	</section>

<?php
	get_footer(); 
?>


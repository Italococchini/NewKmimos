<?php
	function get_balance( $bufer ){
		global $CONTENIDO;
		$CONTENIDO = $bufer;
	} 
?>

<?php ob_start("get_balance"); ?>
<section class='balance-container'>
	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist">

		<li role="presentation" class="active">
			<a href="#balance" aria-controls="balance" role="tab" data-toggle="tab">
				Balance
			</a>
		</li>
		<!-- li role="presentation" class="">
			<a href="#notas_creditos" aria-controls="notas_creditos" role="tab" data-toggle="tab">
				Notas de Cr&eacute;ditos
			</a>
		</li -->

	</ul>

	<!-- Tab panes -->
	<div class="tab-content">
		<!--div role="tabpanel" class="tab-pane active" id="notas_creditos">
			<?php #include_once("notas-creditos.php");?>
		</div-->
		<div role="tabpanel" class="tab-pane active" id="balance">
			<?php include_once("balance.php");?>
		</div>
	</div>
	<div class="clear"></div>
</section>	
<?php ob_end_flush(); ?>

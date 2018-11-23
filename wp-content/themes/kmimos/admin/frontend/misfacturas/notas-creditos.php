<?php
	global $wpdb;
	$user_id = get_current_user_id();
	$notas_creditos = $wpdb->get_results("SELECT * FROM notas_creditos WHERE user_id = {$user_id}");

	function get_notas_credito_html( $bufer ){
		global $NotasCredito;
		$NotasCredito = $bufer;
	} 
?>

<?php ob_start("get_notas_credito_html"); ?>

<h1 class="titulo">Notas de Cr&eacute;ditos</h1>

<div class="vlz_tabla_box">
<?php if( count($notas_creditos) > 0 ){
		foreach( $notas_creditos as $item ){ 
			$detalle = unserialize($item->detalle); ?>

			<?php 
				$sql = "SELECT numeroReferencia FROM facturas WHERE reserva_id = ".$item->factura;
				$numeroReferencia = $wpdb->get_var($sql);

				$codigo_factura = $item->factura;
				if( !empty($numeroReferencia) ){
					$codigo_factura = $item->factura.'_'.$numeroReferencia;
				}
			?>

			<div class="vlz_tabla vlz_desplegado col-md-12">
				<div class="vlz_tabla_superior" style="margin-left:10px; width: 98%;">
			    	<div class="vlz_tabla_cuidador vlz_celda" style="width: 20%;">
			    		<span>Fecha</span>
			    		<div><?php echo date('Y-m-d', strtotime($item->fecha)); ?></div>
			    	</div>
			    	<div class="vlz_tabla_cuidador vlz_cerrar" style="width: 20%;">
			    		<span>Reserva</span>
			    		<div><?php echo $item->reserva_id; ?></div>
			    	</div>
			    	<div class="vlz_tabla_cuidador vlz_cerrar" style="width: 20%;">
			    		<span>Monto</span>
			    		<div>$ <?php echo number_format($item->monto, 2, ',', '.'); ?></div>
			    	</div>
			    	<div class="vlz_tabla_cuidador vlz_cerrar" style="width: 20%;">
			    		<span># documento</span>
			    		<div><?php echo (!empty($item->factura))? $item->factura : "---"; ?></div>
			    	</div>
			    	<div class="vlz_tabla_cuidador vlz_botones boton_interno" style="width: 95%; display:block!important; margin:0px!important;">
			    		<br> 
						<a data-pdfxml="<?php echo $codigo_factura; ?>" class="vlz_accion vlz_ver"> <i class="fa fa-cloud-download" aria-hidden="true"></i> PDF y XML</a>
			    	</div>
				</div>
				<div class="vlz_tabla_cuidador vlz_botones vlz_celda boton_interno" style="margin-left:10px; width: 98%;">
					<i class="fa fa-times ver_reserva_init_closet inactive_control" aria-hidden="true"></i>
					<a class="ver_reserva_init_fuera">Ver</a>
				</div>
				<div class="vlz_tabla_inferior inactive_control" style="margin-left:10px; width: 98%;">
					<div class="desglose_reserva">
		    			<div class="text-left"><strong>Detalle</strong></div>
		    			<?php foreach( $detalle as $det ){ ?>
				    		<div class="item_desglose">
				    			<div><?php echo $det['titulo']; ?></div>
				    			<span>$ <?php echo number_format($det['costo'], 2, ',', '.'); ?></span>
				    		</div>
			    		<?php } ?>
					</div>
					<div class="total_reserva">
			    		<div class="item_desglose">
			    			<div>TOTAL</div>
			    			<span>$ <?php echo number_format($item->monto, 2, ',', '.'); ?></span>
			    		</div>
					</div>
				</div>
				<div style="display: none;" data-italo><?php echo $sql; ?></div>
			</div>
<?php } }else{ ?>
	<div class="alert alert-info">No hay datos disponibles para mostrar.</div>
<?php } ?>
</div>
<?php ob_end_flush(); ?>
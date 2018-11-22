<?php
	$raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
	include_once($raiz.'/wp-load.php');
	global $wpdb;

	extract($_POST);

	$pedido_id = $wpdb->get_var("SELECT post_parent FROM wp_posts where ID = {$ID} and post_status in ( 'confirmed', 'complete', 'completed' )");

	// Solo reservas en progreso
		$reserva_start = get_post_meta( $ID, '_booking_start', true );
		$fecha_start = date('Y-m-d', strtotime($reserva_start));
		if( $pedido_id > 0 && date('Y-m-d') < $fecha_start ){
			$pedido_id = null;
	 	}

	/* // Solo reservas completadas
		$reserva_end = get_post_meta( $ID, '_booking_end', true );
		if( $pedido_id > 0 && date('Y-m-d') < date('Y-m-d', strtotime($reserva_end)) ){
			$pedido_id = 0;
	 	}
	*/

    $factura_id = $wpdb->get_var( "SELECT id FROM facturas WHERE receptor = 'cliente' and reserva_id = {$ID}" );

    $nc_id = $wpdb->get_var( "SELECT id FROM notas_creditos WHERE reserva_id = {$ID}" );

	$show_nc = false;

	$reserva = [];
	if( $nc_id > 0 ){
		$msg = 'Ya existe una Nota de Credito asignada a la reserva #'.$ID;
	}else if( empty($ID) ){
		$msg = 'Ingrese un n&uacute;mero de reserva para generar la nota de cr&eacute;dito';
	}else if( $factura_id > 0 ){
		$msg = 'No se puede generar la nota de cr&eacute;dito por que la reserva ya esta facturada.';
	}else if( $pedido_id == null ){
		$msg = 'No se puede generar la nota de cr&eacute;dito, la reserva inicia el '.$fecha_start;
	}else if( $pedido_id > 0 ){
		$show_nc = true;
		$reserva = kmimos_desglose_reserva_data( $pedido_id, true );	
		$hoy = date('Y-m-d');
		$ini = date('Y-m-d', $reserva['servicio']['inicio']);
		$fin = date('Y-m-d', $reserva['servicio']['fin']);
		$rango_inicio = ( $hoy >= $ini )? $hoy : $ini;
	}else{
		$msg = 'No se puede generar la nota de cr&eacute;dito, estatus de la reserva no permitido.';
	}
?>
<script>
	var tipo_servicio = "<?php echo strtolower($reserva['servicio']['tipo']) ?>";
</script> 

<?php if( !$show_nc ){ ?>
	<div class="text-center" style="display: <?php echo $show_msg; ?>">
		<p style=" font-weight: bold; padding: 20px 0px 0px 0px;">
			<?php echo $msg; ?>
		</p>
	</div>
<?php }else{ ?>

	<div>
		
		<!-- Navegacion -->
		<header></header>

		<!-- Resumen Reserva -->
		<section id="seccion-1" class="row">
			
			<!-- Mostrar Info de Cuidado y Cliente  -->
			<section class="col-md-12" style="margin: 5px 0px;">
				<article class="cliente contenedor" style="display:none;">
					<h1><strong>Cliente: <?php echo $reserva['cliente']['nombre']; ?></strong></h1>
					<div>Email:  <?php echo $reserva['cliente']['email']; ?></div>
					<div>Tel&eacute;fono: <?php echo $reserva['cliente']['telefono']; ?></div>
				</article>
				<article class="cuidador contenedor" style="display:none;">
					<h1><strong>Cuidador: <?php echo $reserva['cuidador']['nombre']; ?></strong></h1>
					<div>Email:  <?php echo $reserva['cuidador']['email']; ?></div>
					<div>Tel&eacute;fono: <?php echo $reserva['cuidador']['telefono']; ?></div>
				</article>
				<div style="margin: 10px 0px;">
					<h1 style="vertical-align: middle;"><strong>Quien solicita la modificaci&oacute;n?</strong></h1>
					<select name="tipo_usuario" class="form-control">
						<option value="">Seleccione una opci&oacute;n</option>
						<option value="cliente">Cliente</option>
						<option value="cuidador">Cuidador</option>
					</select>
				</div>
			</section>

			<!-- Mostrar Resumen de Reserva  -->
			<section class="col-md-12" style="margin: 5px 0px;">
				<h2 style="padding: 5px;background: #ccc; color:#4d4d4d;">POR RESERVA</h2>
				<div class="row">
					<div class="col-md-12">
						<strong>Servicio: </strong><?php echo  strtoupper($reserva['servicio']['tipo']); ?>
					</div>
					<article class="col-md-3">
						<strong>Desde: </strong> 
						<br> <?php echo date('d/m/Y', $reserva['servicio']['inicio']); ?>						
					</article>
					<article class="col-md-3">
						<strong>Hasta: </strong>
						<br> <?php echo date('d/m/Y', $reserva['servicio']['inicio']); ?>
					</article>
					<article class="col-md-3">
						<strong>Total Reserva: </strong>
						<br> $ <?php echo $reserva['servicio']['desglose']['total']; ?>
					</article>
					<article class="col-md-3">
						<button class="btn btn-sm btn-primary" style="width: 100%;">Editar reserva</button>
					</article>
				</div>
			</section>

			<!-- Mostrar Mascotas ( Eliminar - Editar ) -->
			<section class="col-md-12" style="margin: 5px 0px;">
				<h2 style="padding: 5px;background: #ccc; color:#4d4d4d;">POR MASCOTA</h2>
				<div class="row">				

					<?php foreach( $reserva['servicio']['variaciones'] as $row ){ ?>
						<?php for ($i=1; $i <= $row[0]; $i++) { ?>
							<article class="col-md-3">
								<div style="border:1px solid #ccc;">
									<div style="text-align: right;">
										<button style="border-radius: 0px 0px 0px 10px;" class="btn btn-sm">x</button>
									</div>
									<h2 style="margin:10px;text-align: center; font-weight:bold;">
										Mascota <?php echo $row[1] . " #".$i; ?>											
									</h2>
									<button style="width: 100%; border-radius: 0px;" class="btn btn-sm btn-primary">Editar</button>
								</div>
							</article>
						<?php } ?>
					<?php } ?>

				</div>
			</section>

		</section>

		<!-- Detalle Por Mascota -->

		<!-- Detalle Por Reserva -->
		
		<hr> Seccion para editar por reserva <hr>

		<section id="seccion-2" class="row">
			<input type="hidden" name="" data-tipo="" data-value="" >  <!-- Reserva | Mascota -->
			<section class="col-md-12">
				<h1 style="padding: 5px;background: #ccc; color:#4d4d4d;">
					<?php echo strtoupper($reserva['servicio']['tipo']); ?>
				</h1>
				<article class="row" style="margin-bottom:20px; ">
 			
					<div data-target="reserva_prorrateo" class="col-sm-4">
						<label>Hasta: </label> 
						<input type="date" name="noches" class="form-control"   value="0.00" style="margin: 0px;">
					</div>
					<div data-target="reserva_prorrateo" class="col-sm-4">
						<label>Noches/D&iacute;as Restantes: </label>
						<input type="text" name="noches" class="form-control" readonly value="0.00">
					</div>
					<div data-target="reserva_prorrateo" class="col-sm-4">	
						<label>Monto: </label>
						<input type="text" name="prorrateo" class="form-control" readonly value="0.00">
					</div>

				</article>
			</section>

			<?php if( !empty($reserva['servicio']['adicionales']) ){ ?>
			<section class="col-md-12">
				<h1 style="padding: 5px;background: #ccc; color:#4d4d4d;">SERVICIOS ADICIONALES</h1>
				<?php foreach( $reserva['servicio']['adicionales'] as $item ){ ?>
				<article class="row">		
					<div class="col-md-8">
						<label>
							<input 
								type="checkbox" name="servicios[]" 
								value="<?php echo md5($item[0]); ?>"  
								data-monto="<?php echo str_replace(',','.', str_replace('.', '', $item[3]) ); ?>">
							<?php echo "{$item[0]} - {$item[1]} x {$item[2]}"; ?>
						</label>
					</div>
					<div class="col-md-4 monto text-right">
						$ <?php echo $item[3]; ?>
					</div>
				</article>
				<?php } ?>
			</section>
			<?php } ?>

			<?php if( !empty($reserva['servicio']['transporte']) ){ ?>
			<section class="col-md-12">
				<h1 style="padding: 5px;background: #ccc; color:#4d4d4d;">TRANSPORTACIÓN</h1>
				<?php foreach( $reserva['servicio']['transporte'] as $item){ ?>
				<article>
					<div class="row">		
						<div class="col-md-8">
							<label>
								<input type="checkbox" 
									name="transporte[]" 
									value="<?php echo md5($item[0]); ?>"
									data-monto="<?php echo  str_replace(',','.', str_replace('.', '', $item[3]) ); ?>"
								> 
								<?php echo str_replace('<br>', ' - ', $item[0] ); ?>
							</label>
						</div>
						<div class="col-md-4 monto text-right">
							$ <?php echo $item[3]; ?>
						</div>
					</div>
				</article>
				<?php } ?>
			</section>
			<?php } ?>
		</section>

		<!-- Verificacion y Aprobacion -->
		<section id="seccion-3" class="row">

		</section>

		<!-- Mensaje y Opciones -->
		<footer></footer>

	</div>

<?php } ?>


<?php

	/*	Estructura

		[
			inicio:
			fin: 
			
		]
	
	*/
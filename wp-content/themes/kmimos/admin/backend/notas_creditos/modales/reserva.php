<?php
	$raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
	include_once($raiz.'/wp-load.php');
	global $wpdb;

	extract($_POST);

	$total_mascotas = 0;

	$pedido_id = $wpdb->get_var("SELECT post_parent FROM wp_posts where ID = {$ID} and post_status in ( 'confirmed', 'complete', 'completed' )");

	// Solo reservas en progreso
		$reserva_start = get_post_meta( $ID, '_booking_start', true );
		$fecha_start = date('Y-m-d', strtotime($reserva_start));
		if( $pedido_id > 0 && date('Y-m-d') < $fecha_start ){
//			$pedido_id = null;
	 	}

	// Solo reservas completadas
		// $reserva_end = get_post_meta( $ID, '_booking_end', true );
		// if( $pedido_id > 0 && date('Y-m-d') < date('Y-m-d', strtotime($reserva_end)) ){
		// 	$pedido_id = 0;
		// }
	

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
	}else if( $pedido_id == null && date('Y-m-d') < $fecha_start ){
		$msg = 'No se puede generar la nota de cr&eacute;dito, la reserva inicia el '.$fecha_start;
	}else if( $pedido_id == null ){
		$msg = 'No se puede generar la nota de cr&eacute;dito, Reserva no encontrada';
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

	<div style="display: <?php echo $show_nc; ?>">
		<form name="form-nc" action="#" method="post">

			<input type="hidden" name="reserva_id" value="<?php echo $ID; ?>">
			<input type="hidden" name="pedido_id" value="<?php echo $pedido_id; ?>">

			<section id="mas_info" style="display: none;">
				<article class="cliente contenedor">
					<h1><strong>Cliente</strong></h1>
					<div>Nombre: <?php echo $reserva['cliente']['nombre']; ?></div>
					<div>Email:  <?php echo $reserva['cliente']['email']; ?></div>
					<div>Tel&eacute;fono: <?php echo $reserva['cliente']['telefono']; ?></div>
				</article>
				<article class="cuidador contenedor">
					<h1><strong>Cuidador</strong></h1>
					<div>Nombre: <?php echo $reserva['cuidador']['nombre']; ?></div>
					<div>Email:  <?php echo $reserva['cuidador']['email']; ?></div>
					<div>Tel&eacute;fono: <?php echo $reserva['cuidador']['telefono']; ?></div>
				</article> 
			</section>

			<div class="mas_info">
				<span>Info cliente y cuidador</span>
			</div>

			<section class="total-top">
				<div class="contenedor">
					Reserva #: 
					<div><?php echo $reserva['servicio']["id_reserva"]?></div>
				</div>
				<div class="contenedor">
					Total Nota de Cr&eacute;dito: 
					<div data-target="total">0,00</div>
				</div>
				<div>Desde: <?php echo date('d/m/Y', $reserva['servicio']['inicio']); ?> Hasta: <?php echo date('d/m/Y', $reserva['servicio']['fin']); ?></div>
			</section>

			<section>
				<h1 class="popup-titulo">QUIEN SOLICITA LA MODIFICACI&Oacute;N?</h1>
				<select name="tipo_usuario" class="form-control">
					<option value="">Seleccione una opci&oacute;n</option>
					<option value="cliente">Cliente</option>
					<option value="cuidador">Cuidador</option>
				</select>
			</section>

    
			<?php if( !empty($reserva['servicio']['variaciones']) ){ ?>
			<section class="servicios">
				<h1 class="popup-titulo">SERVICIO: <?php echo strtoupper($reserva['servicio']['tipo']); ?></h1>
				<article>
				<?php foreach( $reserva['servicio']['variaciones'] as $key => $s_principal ){ 
					$total_mascotas += $s_principal[0];
					$code = md5($s_principal[1]);
				?>
					<div class="row" style="margin-bottom:20px; ">
						<div class="col-md-8">
							<label>
								<input type="checkbox" 
								name="s_principal[]" 
								value="<?php echo $code; ?>" 
								data-group="prorrateo_<?php echo $code; ?>"
								data-code="<?php echo $code; ?>"
								> 
								<?php echo "{$s_principal[0]} {$s_principal[1]} x {$s_principal[2]} x {$s_principal[3]}"; ?>
							</label>
						</div>
						<div class="col-md-4 monto" >$ <?php echo $s_principal[4]; ?></div>
				
						<div data-target="prorrateo_<?php echo $code; ?>" class="col-sm-2">
							<label>Mascotas: </label> 
							<select 
								class="form-control" 
								data-name="cant_mascotas" 
								name="mascotas_<?php echo $code; ?>"
								data-code="<?php echo $code; ?>"
								>
							<?php for ($i=$s_principal[0]; $i > 0; $i--) { ?>
								<option value="<?php echo $i; ?>"><?php echo $i; ?></option>			
							<?php } ?>
							</select>
						</div>
						<div data-target="prorrateo_<?php echo $code; ?>" class="col-sm-4" style="padding: 0px 10px;">
							<label>Hasta: </label> 
							<input type="date" data-name="hasta" name="hasta_<?php echo $code; ?>" 
							 data-code="<?php echo $code; ?>" 
							 data-monto="<?php echo str_replace(',','.', str_replace('.', '', $s_principal[3]) ); ?>" 
							 value=""
							 min="<?php echo $ini; ?>"
							 max="<?php echo $fin; ?>">
						</div>
						<div data-target="prorrateo_<?php echo $code; ?>" class="col-sm-3">
							<label>Noches/D&iacute;as: </label>
							<input type="text" name="noches_<?php echo $code; ?>" class="form-control" readonly value="0.00">
						</div>
						<div data-target="prorrateo_<?php echo $code; ?>" class="col-sm-3">	
							<label>Monto: </label>
							<input type="text" name="prorrateo_<?php echo $code; ?>" class="form-control" readonly value="0.00">
						</div>
					</div>			
				<?php } ?>
				</article>
			</section>
			<?php } ?>

			<?php if( !empty($reserva['servicio']['adicionales']) ){ ?>
			<section class="servicios">
				<h1 class="popup-titulo">SERVICIOS ADICIONALES</h1>
						<?php foreach( $reserva['servicio']['adicionales'] as $item ){ ?>
						<tr>
							<td>
								<label>
									<input 
										type="checkbox" name="servicios[]" 
										value="<?php echo md5($item[0]); ?>"  
										data-code="<?php echo $code; ?>"
										data-monto="<?php echo str_replace(',','.', str_replace('.', '', $item[3]) ); ?>">
									<?php echo "{$item[0]} "; ?>
								</label>
							</td>
							<td>
								$ <?php echo $item[2]; ?>
							</td>
							<td>
								<select 
									class="form-control" 
									data-name="cant_mascotas" 
									name="mascotas_<?php echo $code; ?>"
									data-code="<?php echo $code; ?>"
									>
								<?php for ($i=$total_mascotas; $i > 0; $i--) { ?>
									<option value="<?php echo $i; ?>"><?php echo $i; ?></option>			
								<?php } ?>
								</select>
							</td>
							<td> 
								<div class="monto">$ <?php echo $item[3]; ?></div>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</section>
			<?php } ?>

			<?php if( !empty($reserva['servicio']['transporte']) ){ ?>
			<section class="servicios">
				<h1 class="popup-titulo">TRANSPORTACIÓN</h1>
				<table width="100%">
					<thead>
						<tr>
							<td width="80%">Descripci&oacute;n</td>
							<td width="20%" class="text-right">Total</td>
						</tr>
					</thead>
					<tbody>
						<?php foreach( $reserva['servicio']['transporte'] as $item){ ?>
						<tr>
							<td>
								<label>
									<input type="checkbox" 
										name="transporte[]" 
										value="<?php echo md5($item[0]); ?>"
										data-code="<?php echo $code; ?>"
										data-monto="<?php echo  str_replace(',','.', str_replace('.', '', $item[3]) ); ?>"
									> 
									<?php echo $item[0]; ?>
								</label>
							</td>
							<td>
								<div class="monto">$ <?php echo $item[3]; ?></div>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</section>
			<?php } ?>

			<section class="row">
				<article class="col-md-12">
					<hr>
					<label>Observaciones</label>
					<textarea row="4" style="width: 100%;" name="observaciones"></textarea>
				</article>
			</section>
			<section class="totales">
				<div>Total Nota de Cr&eacute;dito:</div> 
				<div data-target="total">$ 0,00</div>
			</section>

		</form>
		<section class="text-right">
			<hr>
			<button class="btn btn-success" id="nc_save">Guardar</button>
		</section>
	</div>

<?php } ?>

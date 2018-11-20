 
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
				<h2 style="padding: 5px;background: #ccc; color:#fff;">Por Reserva</h2>
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
						<button style="width: 100%" class="btn btn-primary">Editar</button>
					</article>
				</div>
			</section>

			<!-- Mostrar Mascotas ( Eliminar - Editar ) -->
			<section class="col-md-12" style="margin: 5px 0px;">
				<h2 style="padding: 5px;background: #ccc; color:#fff;">Por Mascota</h2>
				<div class="row">				

					<article class="col-md-3">
						<div style="border:1px solid #ccc;">
							<button style="width: 20%; position: absolute; border-radius:0px 0px 0px 10px; right: 15px; top: 0px;" class="btn btn-sm btn-danger">x</button>
							<img src="" width="100%" height="100px">
							<h2 style="margin:10px;text-align: center;">Mascota</h2>
							<button style="width: 100%;  border-radius:0px!important;" class="btn btn-sm btn-primary">Editar</button>
						</div>
					</article>

					<article class="col-md-3">
						<div style="border:1px solid #ccc;">
							<button style="width: 20%; position: absolute; border-radius:0px 0px 0px 10px; right: 15px; top: 0px;" class="btn btn-sm btn-danger">x</button>
							<img src="" width="100%" height="100px">
							<h2 style="margin:10px;text-align: center;">Mascota</h2>
							<button style="width: 100%;  border-radius:0px!important;" class="btn btn-sm btn-primary">Editar</button>
						</div>
					</article>

					<article class="col-md-3">
						<div style="border:1px solid #ccc;">
							<button style="width: 20%; position: absolute; border-radius:0px 0px 0px 10px; right: 15px; top: 0px;" class="btn btn-sm btn-danger">x</button>
							<img src="" width="100%" height="100px">
							<h2 style="margin:10px;text-align: center;">Mascota</h2>
							<button style="width: 100%;  border-radius:0px!important;" class="btn btn-sm btn-primary">Editar</button>
						</div>
					</article>

					<article class="col-md-3">
						<div style="border:1px solid #ccc;">
							<button style="width: 20%; position: absolute; border-radius:0px 0px 0px 10px; right: 15px; top: 0px;" class="btn btn-sm btn-danger">x</button>
							<img src="" width="100%" height="100px">
							<h2 style="margin:10px;text-align: center;">Mascota</h2>
							<button style="width: 100%;  border-radius:0px!important;" class="btn btn-sm btn-primary">Editar</button>
						</div>
					</article>

				</div>
			</section>

		</section>

		<!-- Detalle -->
		<section id="seccion-2" class="row">

		</section>

		<!-- Verificacion y Aprobacion -->
		<section id="seccion-3" class="row">

		</section>

		<!-- Mensaje y Opciones -->
		<footer></footer>

	</div>

<?php } ?>
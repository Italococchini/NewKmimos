<?php 
require_once('core/ControllerConocerCuidador.php');
$date = getdate();
$desde = date("Y-m-01", $date[0] );
$hasta = date("Y-m-d", $date[0]);
if(	!empty($_POST['desde']) && !empty($_POST['hasta']) ){
	$desde = (!empty($_POST['desde']))? $_POST['desde']: "";
	$hasta = (!empty($_POST['hasta']))? $_POST['hasta']: "";
}


$solicitudes = getSolicitud($desde, $hasta);
?>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
  <div class="x_title">
    <h2>Panel de Control <small>Solicitud de Conocer a Cuidador</small></h2>
    <hr>
    <div class="clearfix"></div>
  </div>
  <div class="col-sm-12">  	
	<!-- Filtros -->
    <div class="row text-right"> 
    	<div class="col-sm-12">
	    	<form class="form-inline" action="<?php echo get_home_url(); ?>/wp-admin/admin.php?page=bp_conocer_cuidador" method="POST">
			  <label>Filtrar:</label>
			  <div class="form-group">
			    <div class="input-group">
			      <div class="input-group-addon">Desde</div>
			      <input type="date" class="form-control" name="desde" value="<?php echo $desde; ?>">
			    </div>
			  </div>
			  <div class="form-group">
			    <div class="input-group">
			      <div class="input-group-addon">Hasta</div>
			      <input type="date" class="form-control" name="hasta" value="<?php echo $hasta ?>">
			    </div>
			  </div>
				<button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Buscar</button>			  
		    </form>
			<hr>  
		</div>
    </div>

  	<?php if( empty($solicitudes) ){ ?>
  		<!-- Mensaje Sin Datos -->
	    <div class="row alert alert-info"> No existen registros </div>
  	<?php }else{ ?>  		
	    <div class="row"> 
	    	<div class="col-sm-12" id="table-container" 
	    		style="font-size: 10px!important;">
	  		<!-- Listado de Conocer Cuidador-->
			<table id="tblConocerCuidador" class="table table-striped table-bordered dt-responsive table-hover table-responsive nowrap datatable-buttons" 
					cellspacing="0" width="100%">
			  <thead>
			    <tr>
					<th>#</th>
					<th># Solicitud</th>
					<th>Fecha</th>
					<th>Desde</th>
					<th>Hasta</th>
					<th>Lugar</th>
					<th>Cuando</th>
					<!-- nombre de la(s) mascota -->
					<!-- tamaño -->
					<th>Nombre del cliente</th>
					<th>Teléfono del cliente</th>
					<th>Correo del cliente</th>

					<th>Nombre del cuidador</th>
					<th>Teléfono del cuidador</th>
					<th>Correo del cuidador</th>


					<th>Donde nos conocio?</th>
					<th>Servicio</th>
					<th># Noches</th>

					<th>Estatus</th>
			    </tr>
			  </thead>
			  <tbody>
			  	<?php $count=0; ?>
			  	<?php foreach($solicitudes['rows'] as $solicitud ){ ?>
 
				  	<?php 
				  		// *************************************
				  		// Buscar Reservas
				  		// *************************************
				  		$_reserva = get_primera_reserva( $solicitud['Cliente_id'] );
				  		$reserva = $_reserva['rows'][0];
				  		$detalle = kmimos_desglose_reserva_data( $reserva['post_parent'], true);
				  		
				  		print_r($detalle);

				  		// *************************************
				  		// Cargar Metadatos
				  		// *************************************
						$separador = ' / ';
				  		$cuidador = get_metaCuidador($solicitud['Cuidador_id']);
				  		$cliente = get_metaCliente($solicitud['Cliente_id']);

				  		$cuidador = merge_phone($cuidador);
				  		$cliente = merge_phone($cliente);
				  	?> 
				    <tr>
				    	<th class="text-center"><?php echo ++$count; ?></th>
						<th><?php echo $solicitud['Nro_solicitud']; ?></th>
						<th><?php echo $solicitud['Fecha_solicitud']; ?></th>

						<th><?php echo $solicitud['Servicio_desde']; ?></th>
						<th><?php echo $solicitud['Servicio_hasta']; ?></th>
						<th><?php echo utf8_encode($solicitud['Donde']); ?></th>
						<th><?php echo $solicitud['Cuando'].' '.$solicitud['Hora']; ?></th>

						<!-- nombre de la(s) mascota -->
						<!-- tamaño -->

						<th><?php echo "{$cliente['first_name']} {$cliente['last_name']}"; ?></th>
						<th><?php echo $cliente['phone'];?></th>
						<th><?php echo $cliente['email'];?></th>

						<th><?php echo "{$cuidador['first_name']} {$cuidador['last_name']}"; ?></th>
						<th><?php echo $cuidador['phone'];?></th>
						<th><?php echo $cuidador['email'];?></th>

						<th><?php echo $cuidador['user_referred'];?></th>
						<th><?php echo $detalle['servicio']['tipo'];?></th>
						<th><?php echo $detalle['servicio']['duracion'];?></th>

						<th><?php echo $solicitud['Estatus'];?></th>
				    </tr>
			   	<?php } ?>
			  </tbody>
			</table>
			</div>
		</div>
	<?php } ?>	
  </div>
</div>
</div>
<div class="clearfix"></div>	

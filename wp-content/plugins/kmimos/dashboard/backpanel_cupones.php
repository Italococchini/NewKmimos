<?php global $wpdb;
// Reservas 
require_once('core/ControllerReservas.php');
// Parametros: Filtro por fecha
$date = getdate(); 
$desde = date("Y-m-01", $date[0] );
$hasta = date("Y-m-d", $date[0]);
$_desde = "";
$_hasta = "";
if(	!empty($_POST['desde']) && !empty($_POST['hasta']) ){
	$_desde = (!empty($_POST['desde']))? $_POST['desde']: "";
	$_hasta = (!empty($_POST['hasta']))? $_POST['hasta']: "";
}
$razas = get_razas();
// Buscar Reservas
$reservas = getReservas($_desde, $_hasta);


?>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_title">
		<h2>Panel de Control <small>Cupones</small></h2>
		<hr>
		<div class="clearfix"></div>
		</div>
		<!-- Filtros -->
		<div class="row text-right"> 
			<div class="col-sm-12">
		    	<form class="form-inline" action="<?php echo get_home_url(); ?>/wp-admin/admin.php?page=bp_cupones" method="POST">
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
		  		<div class="clearfix"></div>
			</div>
		</div>
  	</div>
	<div class="clearfix"></div>
  	<div class="col-sm-12">  	

  	<?php if( empty($reservas) ){ ?>
  		<!-- Mensaje Sin Datos -->
	    <div class="row">
	    	<div class="col-sm-12">
	    		<div class="alert alert-info">
		    		No existen registros
	    		</div>
		    </div>
	    </div> 
  	<?php }else{ ?>  		
	    <div class="row"> 
	    	<div class="col-sm-12" id="table-container" 
	    		style="font-size: 10px!important;">
	  		<!-- Listado de Reservas -->
			<table id="tblReservas" class="table table-striped table-bordered dt-responsive table-hover table-responsive nowrap datatable-buttons" 
					cellspacing="0" width="100%">
			  <thead>
			    <tr>
			      <th>#</th>
			      <th># Reserva</th>
			      <th>Estatus</th>
			      <th>Fecha Reservacion</th>
			      <th>Check-In</th>
			      <th>Check-Out</th>
			      <th>Noches</th>
			      <th># Mascotas</th>
			      <th># Noches Totales</th>
			      <th>Nombre Cliente</th>
			      <th>Apellido Cliente</th>
 
			      <th>Cupon (es)</th>
			    </tr>
			  </thead>
			  <tbody>
			  	<?php 
			  		$total_a_pagar=0;
			  		$total_pagado=0;
			  		$total_remanente=0;
			  	 ?>
			  	<?php $count=0; ?>
			  	<?php foreach( $reservas as $reserva ){ ?>
 
				  	<?php 
				  		// *************************************
				  		// Cargar Metadatos
				  		// *************************************
				  		# MetaDatos del Cuidador
				  		$meta_cuidador = getMetaCuidador($reserva->cuidador_id);
				  		# MetaDatos del Cliente
				  		$cliente = getMetaCliente($reserva->cliente_id);

				  		# Recompra 12 Meses
				  		$cliente_n_reserva = getCountReservas($reserva->cliente_id, "12");
				  		if(array_key_exists('rows', $cliente_n_reserva)){
					  		foreach ($cliente_n_reserva["rows"] as $value) {
				  				$recompra_12M = ($value['cant']>1)? "SI" : "NO" ;
					  		}
					  	}
				  		# Recompra 1 Meses
				  		$cliente_n_reserva = getCountReservas($reserva->cliente_id, "1");
				  		if(array_key_exists('rows', $cliente_n_reserva)){
					  		foreach ($cliente_n_reserva["rows"] as $value) {
				  				$recompra_1M = ($value['cant']>1)? "SI" : "NO" ;
					  		}
					  	}
				  		# Recompra 3 Meses
				  		$cliente_n_reserva = getCountReservas($reserva->cliente_id, "3");
				  		if(array_key_exists('rows', $cliente_n_reserva)){
					  		foreach ($cliente_n_reserva["rows"] as $value) {
				  				$recompra_3M = ($value['cant']>1)? "SI" : "NO" ;
					  		}
					  	}
				  		# Recompra 6 Meses
				  		$cliente_n_reserva = getCountReservas($reserva->cliente_id, "6");
				  		if(array_key_exists('rows', $cliente_n_reserva)){
					  		foreach ($cliente_n_reserva["rows"] as $value) {
				  				$recompra_6M = ($value['cant']>1)? "SI" : "NO" ;
					  		}
					  	}

				  		# MetaDatos del Reserva
				  		$meta_reserva = getMetaReserva($reserva->nro_reserva);
				  		# MetaDatos del Pedido
				  		$meta_Pedido = getMetaPedido($reserva->nro_pedido);
				  		# Mascotas del Cliente
				  		$mypets = getMascotas($reserva->cliente_id); 
				  		# Estado y Municipio del cuidador
				  		$ubicacion = get_ubicacion_cuidador($reserva->cuidador_id);
				  		# Servicios de la Reserva
				  		$services = getServices($reserva->nro_reserva);
				  		# Status
				  		$estatus = get_status(
				  			$reserva->estatus_reserva, 
				  			$reserva->estatus_pago, 
				  			$meta_Pedido['_payment_method'],
				  			$reserva->nro_reserva // Modificacion Ángel Veloz
				  		);

				  		if($estatus['addTotal'] == 1){
							$total_a_pagar += currency_format($meta_reserva['_booking_cost'], "");
					  		$total_pagado += currency_format($meta_Pedido['_order_total'], "", "", ".");
					  		$total_remanente += currency_format($meta_Pedido['_wc_deposits_remaining'], "", "", ".");
				  		}

				  		$pets_nombre = array();
				  		$pets_razas  = array();
				  		$pets_edad	 = array();

						foreach( $mypets as $pet_id => $pet) { 
							$pets_nombre[] = $pet['nombre'];
							$pets_razas[] = $razas[ $pet['raza'] ];
							$pets_edad[] = $pet['edad'];
						} 

				  		$pets_nombre = implode("<br>", $pets_nombre);
				  		$pets_razas  = implode("<br>", $pets_razas);
				  		$pets_edad	 = implode("<br>", $pets_edad);

						$nro_noches = dias_transcurridos(
								date_convert($meta_reserva['_booking_end'], 'd-m-Y'), 
								date_convert($meta_reserva['_booking_start'], 'd-m-Y') 
							);					
						if(!in_array('hospedaje', explode("-", $reserva->post_name))){
							$nro_noches += 1;
							
						}


						$Day = "";
						$list_service = [ 'hospedaje' ]; // Excluir los servicios del Signo "D"
						$temp_option = explode("-", $reserva->producto_name);
						if( count($temp_option) > 0 ){
							$key = strtolower($temp_option[0]);
							if( !in_array($key, $list_service) ){
								$Day = "-D";



							}
						}

						$cupones = Get_NameCouponCode($reserva->nro_pedido,'');
				  	?>
				  	<?php if(!empty($cupones) ){ ?>
					    <tr>
				    	<th class="text-center"><?php echo ++$count; ?></th>
						<th><?php echo $reserva->nro_reserva; ?></th>
						<th class="text-center"><?php echo $estatus['sts_corto']; ?></th>
						<th class="text-center"><?php echo $reserva->fecha_solicitud; ?></th>

						<th><?php echo date_convert($meta_reserva['_booking_start'], 'Y-m-d', true); ?></th>
						<th><?php echo date_convert($meta_reserva['_booking_end'], 'Y-m-d', true); ?></th>

						<th class="text-center"><?php echo $nro_noches . $Day; ?></th>
						<th class="text-center"><?php echo $reserva->nro_mascotas; ?></th>
						<th><?php echo $nro_noches * $reserva->nro_mascotas; ?></th>
						<th><?php echo "<a href='".get_home_url()."/?i=".md5($reserva->cliente_id)."'>".$cliente['first_name'];?></a></th>
						<th><?php echo "<a href='".get_home_url()."/?i=".md5($reserva->cliente_id)."'>".$cliente['last_name']; ?></a></th>
						<th> 
							<?php echo $cupones; ?>
						</th>
					    </tr>			    
				   	<?php } ?>
			   	<?php } ?>
			  </tbody>
			</table>
			</div>
		</div>
	<?php } ?>

	<div class="hidden">	
		<div class="col-xs-12 col-sm-12 col-md-2" style="margin:5px; padding:10px; ">
			<strong>Reservas Confirmadas</strong>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-3" style="margin:5px;background: #e8e8e8; padding:10px; ">
			<span>Total a pagar: <?php echo currency_format($total_a_pagar); ?> </span>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-3" style="margin:5px;background: #e8e8e8; padding:10px; ">
			<span>Total pagado: <?php echo currency_format($total_pagado); ?></span>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-3" style="margin:5px;background: #e8e8e8; padding:10px; ">
			<span>Total Remanente: <?php echo currency_format($total_remanente); ?></span>
		</div>	
	</div>
  </div>
</div>
</div>
<div class="clearfix"></div>	

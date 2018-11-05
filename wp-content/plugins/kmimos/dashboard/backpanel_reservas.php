<?php  

	if ( ! defined( 'ABSPATH' ) ) { exit; } 

	// Reservas 
	require_once('core/ControllerReservas.php');

	// Parametros: Filtro por fecha
	$date = getdate(); 

	$mes_actual = date("Y-m");
	$mes_anterior = date("Y-m", strtotime("-1 month", time() ) );

	$desde  = ""; $hasta  = "";
	$_desde = ""; $_hasta = "";

	global $wpdb;

	if(	!empty($_POST['desde']) ){
		$_desde = $_POST['desde'];
	}else{
		$_desde = date("Y-m")."-01";
		$_hasta = date("Y-m-d");
	}

	if(	!empty($_POST['hasta']) ){
		$_hasta = $_POST['hasta'];
	}else{
		$_hasta = date("Y-m-d");
	}

	
	$desde = date( "Y-m", strtotime($_desde) );

	$registros = $wpdb->get_row("SELECT * FROM migracion_reporte_reservas WHERE mes = '{$desde}' ");
	$actualizar = false;
	if( $registros != null && $registros != false ){
		if( ($mes_actual == $registros->mes) || ($mes_anterior == $registros->mes) ){
			$actualizar = true;
		}
	}else{
		$actualizar = true;
		$wpdb->query("INSERT INTO migracion_reporte_reservas VALUES (NULL, '{$desde}', '0')");
	}
	if( $actualizar){
		$razas = get_razas();
		$reservas = getReservas($_desde, $_hasta);
	}else{
		$reservas = $wpdb->get_results("SELECT * FROM reporte_reserva WHERE fecha_reservacion >= '{$_desde}' AND fecha_reservacion <= '{$_hasta}' ");
	}  ?>

	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="row">
			<div class="col-md-12 col-sm-12 col-xs-12">
				<div class="x_title">
					<h2>Panel de Control <small>Reservas</small></h2>
					<hr>
					<div class="clearfix"></div>
				</div>
				<!-- Filtros -->
				<div class="row text-right"> 
					<div class="col-sm-12">
				    	<form class="form-inline" action="<?php echo get_home_url(); ?>/wp-admin/admin.php?page=bp_reservas" method="POST">
						  	<label>Filtrar:</label>
						  	<div class="form-group">
						    	<div class="input-group">
						      		<div class="input-group-addon">Desde</div>
						      		<input type="date" class="form-control" name="desde" value="<?php echo $_desde; ?>">
						    	</div>
						  	</div>
						  	<div class="form-group">
						    	<div class="input-group">
						      		<div class="input-group-addon">Hasta</div>
						      		<input type="date" class="form-control" name="hasta" value="<?php echo $_hasta ?>">
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
  			<div class="col-sm-12"><?php 
  				if( empty($reservas) ){ ?>
			  		<!-- Mensaje Sin Datos -->
				    <div class="row">
				    	<div class="col-sm-12">
				    		<div class="alert alert-info">
					    		No existen registros
				    		</div>
					    </div>
				    </div><?php 
				}else{ ?>  		
	    			<div class="row"> 
	    				<div class="col-sm-12" id="table-container" style="font-size: 10px!important;">
	  						<!-- Listado de Reservas -->
							<table id="tblReservas" class="table table-striped table-bordered dt-responsive table-hover table-responsive nowrap datatable-buttons" cellspacing="0" width="100%">
			  					<thead>
			    					<tr>
										<th>#</th>
										<th># Reserva</th>
										<th>Flash</th>
										<th>Estatus</th>
										<th>Fecha Reservacion</th>
										<th>Check-In</th>
										<th>Check-Out</th>
										<th>Noches</th>
										<th># Mascotas</th>
										<th># Noches Totales</th>
										<th>Cliente</th>
										<th>Correo Cliente</th>
										<th>Tel&eacute;fono Cliente</th>
										<th>Recompra (1Mes)</th>
										<th>Recompra (3Meses)</th>
										<th>Recompra (6Meses)</th>
										<th>Recompra (12Meses)</th>
										<th>Donde nos conocio?</th>
										<th>Mascotas</th>
										<th>Razas</th>
										<th>Edad</th>
										<th>Cuidador</th>
										<th>Correo Cuidador</th>
										<th>Tel&eacute;fono Cuidador</th>
										<th>Servicio Principal</th> 
										<th>Servicios Especiales</th> <!-- Servicios adicionales -->
										<th>Estado</th>
										<th>Municipio</th>
										<th>Forma de Pago</th>
										<th>Tipo de Pago</th>
										<th>Total a pagar ($)</th>
										<th>Monto Pagado ($)</th>
										<th>Monto Remanente ($)</th>
										<th>Cupones kmimos</th>
										<th>Cupones Cuidador</th>
										<th># Pedido</th>
										<th>Observaci&oacute;n</th>
			    					</tr>
			  					</thead>
			  					<tbody> <?php 
			  						
									$cupones['kmimos'] = '---';
									$cupones['cuidador'] = '---';

			  						if( $actualizar ){

								  		$total_a_pagar=0;
								  		$total_pagado=0;
								  		$total_remanente=0;
				  	 					$count=0;
				  	 					foreach( $reservas as $reserva ){ 

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

												$flash = "";
												if( $meta_reserva['_booking_flash'] == "SI" ){
													$flash = '
														<i 
															class="fa fa-bolt" 
															aria-hidden="true"
															style="
																padding: 2px 4px;
															    border-radius: 50%;
															    background: #00c500;
															    color: #FFF;
															    margin-right: 2px;
															"
														></i> Flash
													';
												}

												if( isset($meta_reserva["modificacion_de"]) || isset($meta_reserva["reserva_modificada"]) ){
													switch ( $estatus['sts_corto'] ) {
														case 'Modificado':
															if( $meta_reserva["modificacion_de"] != "" && $meta_reserva["reserva_modificada"] != "" ){
																$estatus['sts_corto'] = 'Modificada-I';
															}else{
																if( $meta_reserva["reserva_modificada"] != "" ){
																	$estatus['sts_corto'] = 'Modificada-O';
																}
																if( $meta_reserva["modificacion_de"] != "" ){
																	$estatus['sts_corto'] = 'Modificada-F';
																}
															}
														break;
														case 'Confirmado':
															if( $meta_reserva["modificacion_de"] != "" ){
																// $estatus['sts_corto'] = 'Modificada-F';
															}
														break;
													}
												}

												$telf_cliente = array();
												if( $cliente["user_mobile"] != "" ){ $telf_cliente[] = $cliente["user_mobile"]; }
												if( $cliente["user_phone"] != "" ){ $telf_cliente[] = $cliente["user_phone"]; }

												$telf_cuidador = array();
												if( $meta_cuidador["user_mobile"] != "" ){ $telf_cuidador[] = $meta_cuidador["user_mobile"]; }
												if( $meta_cuidador["user_phone"] != "" ){ $telf_cuidador[] = $meta_cuidador["user_phone"]; }

												$servicios_adicionales = "";

												foreach( $services as $service ){ 

													$__servicio = $service->descripcion . $service->servicio;
													$__servicio = str_replace("(precio por mascota)", "", $__servicio); 
													$__servicio = str_replace("(precio por grupo)", "", $__servicio); 
													$__servicio = str_replace("Servicios Adicionales", "", $__servicio); 
													$__servicio = str_replace("Servicios de Transportación", "", $__servicio); 

													if( strlen(trim($__servicio)) != 7 ){
														$servicios_adicionales .= $__servicio."<br>";
													}
												}

												$metodo_pago = "";
												if( !empty($meta_Pedido['_payment_method_title']) ){
													$metodo_pago = $meta_Pedido['_payment_method_title']; 
												}else{
													if( !empty($meta_reserva['modificacion_de']) ){
														$metodo_pago = 'Saldo a favor' ; 
													}else{
														$metodo_pago = 'Saldo a favor y/o cupones'; 
													}
												}

												$deposito = $wpdb->get_var("SELECT meta_value FROM wp_woocommerce_order_itemmeta WHERE order_item_id = {$meta_reserva['_booking_order_item_id']} AND meta_key = '_wc_deposit_meta' ");
												$deposito = unserialize($deposito);
												$tipo_pago = "";
												if( $deposito["enable"] == "yes" ){
													$tipo_pago = "Pago 20%";
												}else{
													$tipo_pago = "Pago Total";
												}

												$nos_conocio = ( empty($cliente['user_referred']) ) ? 'Otros' : $cliente['user_referred'];

												$count++;

											$cupones = get_cupones_reserva( $reserva->nro_reserva );

											$data = [
												array(
													"valor" => $count,
													"clase" => "text-center",
												),
												$reserva->nro_reserva,
												$flash,
												array(
													"valor" => $estatus['sts_corto'],
													"clase" => "text-center",
												),
												array(
													"valor" => $reserva->fecha_solicitud,
													"clase" => "text-center",
												),

												array(
													"valor" => $meta_reserva['_booking_start'],
													"tipo" => "date",
												),
												array(
													"valor" => $meta_reserva['_booking_end'],
													"tipo" => "date",
												),

												$nro_noches . $Day,
												$reserva->nro_mascotas,
												$nro_noches * $reserva->nro_mascotas,
												"<a href='".get_home_url()."/?i=".md5($reserva->cliente_id)."'>".$cliente['first_name']." ".$cliente['last_name']."</a>",
												$wpdb->get_var("SELECT user_email FROM wp_users WHERE ID = ".$reserva->cliente_id),
												implode(", ", $telf_cliente),

												array(
													"valor" => $recompra_1M,
													"clase" => "text-center",
												),
												array(
													"valor" => $recompra_3M,
													"clase" => "text-center",
												),
												array(
													"valor" => $recompra_6M,
													"clase" => "text-center",
												),
												array(
													"valor" => $recompra_12M,
													"clase" => "text-center",
												),

												$nos_conocio,
												$pets_nombre,
												$pets_razas,
												$pets_edad,
												$meta_cuidador['first_name'] . ' ' . $meta_cuidador['last_name'],
												$wpdb->get_var("SELECT user_email FROM wp_users WHERE ID = ".$reserva->cuidador_id),
												implode(", ", $telf_cuidador),
												$reserva->producto_title,
												$servicios_adicionales,
												utf8_decode( $ubicacion['estado'] ),
												utf8_decode( $ubicacion['municipio'] ),
												$metodo_pago,
												$tipo_pago,
												$meta_reserva['_booking_cost'],
												$meta_Pedido['_order_total'],
												$meta_Pedido['_wc_deposits_remaining'],
												$cupones['kmimos'],
												$cupones['cuidador'],
												$reserva->nro_pedido,
												$estatus['sts_largo'],
											];

											$data_sql = [
												$reserva->nro_reserva,
												$flash,
												$estatus['sts_corto'],
												$reserva->fecha_solicitud,
												date("Y-m-d H:i:s", strtotime($meta_reserva['_booking_start'])),
												date("Y-m-d H:i:s", strtotime($meta_reserva['_booking_end'])),
												$nro_noches . $Day,
												$reserva->nro_mascotas,
												$nro_noches * $reserva->nro_mascotas,
												"<a href=\'".get_home_url()."/?i=".md5($reserva->cliente_id)."\'>".$cliente['first_name']." ".$cliente['last_name']."</a>",
												$wpdb->get_var("SELECT user_email FROM wp_users WHERE ID = ".$reserva->cliente_id),
												implode(", ", $telf_cliente),
												$recompra_1M,
												$recompra_3M,
												$recompra_6M,
												$recompra_12M,
												$nos_conocio,
												$pets_nombre,
												$pets_razas,
												$pets_edad,
												$meta_cuidador['first_name'] . ' ' . $meta_cuidador['last_name'],
												$wpdb->get_var("SELECT user_email FROM wp_users WHERE ID = ".$reserva->cuidador_id),
												implode(", ", $telf_cuidador),
												$reserva->producto_title,
												$servicios_adicionales,
												utf8_decode( $ubicacion['estado'] ),
												utf8_decode( $ubicacion['municipio'] ),
												$metodo_pago,
												$tipo_pago,
												$meta_reserva['_booking_cost'],
												$meta_Pedido['_order_total'],
												$meta_Pedido['_wc_deposits_remaining'],
												$reserva->nro_pedido,
												$estatus['sts_largo'],
												$cupones['kmimos'],
												$cupones['cuidador'],
											];

											echo "<tr>";
												foreach ($data as $key => $value) {
													if( is_array($value) ){
														echo "<th class='{$value['clase']}'>";
															switch ( $value["tipo"] ) {
																case 'date':
																	echo date_convert($value["valor"], 'Y-m-d', true);
																break;
																
																default:
																	echo $value["valor"];
																break;
															}
														echo "</th>";
													}else{
														echo "<th>".$value."</th>";
													}
													
												}
											echo "</tr>";

											$existe = $wpdb->get_row("SELECT id FROM reporte_reserva WHERE reserva_id = ".$reserva->nro_reserva);
											if( $existe == null || $existe == false ){
												$SQL = "INSERT INTO reporte_reserva VALUES (NULL,'".implode("','", $data_sql)."');";
												$wpdb->query( $SQL );
											}else{
												if( $existe->status != $estatus['sts_corto'] ){
													$SQL = "
														UPDATE 
															reporte_reserva 
														SET 
															status = '{$estatus['sts_corto']}',
															observacion = '{$estatus['sts_largo']}'
														WHERE 
															id = {$existe->id};
													";
													$wpdb->query( $SQL );
												}
											}

											if( 
												( empty($existe->cupones_kmimos)   && !empty($cupones['kmimos']) ) || 
												( empty($existe->cupones_cuidador) && !empty($cupones['cuidador']) )
											){

												// update registro db
												$SQL = "
													UPDATE 
														reporte_reserva 
													SET 
														cupones_kmimos = '".$cupones['kmimos']."',
														cupones_cuidador = '".$cupones['cuidador']."'
													WHERE 
														id = ".$existe->id.";";
												$wpdb->query( $SQL );

											}
				   						} 

				   					}else{

										$count = 0;
										foreach ($reservas as $key => $value) {
											$count++;
											
											if( 
												( empty($value->cupones_kmimos)   && !empty($cupones['kmimos']) ) || 
												( empty($value->cupones_cuidador) && !empty($cupones['cuidador']) )
											){
												$cupones = get_cupones_reserva( $value->reserva_id );
												$value->cupones_cuidador = $cupones['cuidador'];
												$value->cupones_kmimos = $cupones['kmimos'];

												// update registro db
												$SQL = "
													UPDATE 
														reporte_reserva 
													SET 
														cupones_kmimos = '{$cupones['kmimos']}',
														cupones_cuidador = '{$cupones['cuidador']}'
													WHERE 
														reserva_id = ".$value->reserva_id;
												$wpdb->query( $SQL );

											}

											$data = [
												array(
													"valor" => $count,
													"clase" => "text-center",
												),
												$value->reserva_id,
												$value->flash,
												array(
													"valor" => $value->status,
													"clase" => "text-center",
												),
												array(
													"valor" => $value->fecha_reservacion,
													"clase" => "text-center",
												),

												array(
													"valor" => $value->check_in,
													"tipo" => "date",
												),
												array(
													"valor" => $value->check_out,
													"tipo" => "date",
												),
												$value->noches,
												$value->num_mascotas,
												$value->num_noches_totales,
												$value->cliente,
												$value->correo_cliente,
												$value->telefono_cliente,

												array(
													"valor" => $value->recompra_1_mes,
													"clase" => "text-center",
												),
												array(
													"valor" => $value->recompra_3_meses,
													"clase" => "text-center",
												),
												array(
													"valor" => $value->recompra_6_meses,
													"clase" => "text-center",
												),
												array(
													"valor" => $value->recompra_12_meses,
													"clase" => "text-center",
												),

												$value->donde_nos_conocio,
												$value->mascotas,
												$value->razas,
												$value->edad,
												$value->cuidador,
												$value->correo_cuidador,
												$value->telefono_cuidador,
												$value->servicio_principal,
												$value->servicios_especiales,
												$value->estado,
												$value->municipio,
												$value->forma_de_pago,
												$value->tipo_de_pago,
												$value->total_a_pagar,
												$value->monto_pagado,
												$value->monto_remanente,
												$value->cupones_kmimos,
												$value->cupones_cuidador,
												$value->pedido,
												$value->observacion,
											];

											echo "<tr>";
												foreach ($data as $key => $value) {
													if( is_array($value) ){
														echo "<th class='{$value['clase']}'>";
															switch ( $value["tipo"] ) {
																case 'date':
																	echo date_convert($value["valor"], 'Y-m-d', true);
																break;
																
																default:
																	echo $value["valor"];
																break;
															}
														echo "</th>";
													}else{
														echo "<th>".$value."</th>";
													}
													
												}
											echo "</tr>";
										}
				   					}?>
			  					</tbody>
							</table>
						</div>
					</div> <?php 
				} ?>

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

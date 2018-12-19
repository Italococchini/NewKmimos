<?php global $wpdb;
// Usuarios 
require_once('core/ControllerClientes.php');
// Parametros: Filtro por fecha
$landing = '';
$date = getdate();
$desde = '';//date("Y-m-01", $date[0] );
$hasta = '';//date("Y-m-d", $date[0]);


$mostrar_total_reserva = (!empty($_POST['mostrar_total_reserva']))? true : false;
if(	!empty($_POST['desde']) && !empty($_POST['hasta']) ){
	$desde = (!empty($_POST['desde']))? $_POST['desde']: "";
	$hasta = (!empty($_POST['hasta']))? $_POST['hasta']: "";
}
// Buscar Reservas
$razas = get_razas();
$users = getUsers($desde, $hasta);
?>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_title">
			<h2>Panel de Control <small>Lista de clientes</small></h2>
			<hr>
			<div class="clearfix"></div>
		</div>
		<!-- Filtros -->
		<div class="row text-left pull-right"> 
			<div class="col-sm-12">
		    	<form class="form-inline" action="<?php echo get_home_url(); ?>/wp-admin/admin.php?page=bp_clientes" method="POST">

					<div class="col-sm-1">
						<label>Filtrar:</label>
					</div>
					<div class="col-sm-10">
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
					</div>
					<div class="col-sm-10 col-sm-offset-1" style="padding-top:10px;">
						<div class="checkbox">
						    <label>
						      <input type="checkbox" name="mostrar_total_reserva" <?php echo ($mostrar_total_reserva)? 'checked' : ''; ?>> Incluir Total de reservas generadas 
						    </label>
						</div>
					</div>

			    </form>
			</div>
		</div>
		<div class="clear"></div>
		<hr>
	</div>
  	<div class="col-sm-12">  	

  	<?php if( empty($users) ){ ?>
  		<!-- Mensaje Sin Datos -->
	    <div class="row alert alert-info"> No existen datos para mostrar</div>
  	<?php }else{ ?>
	    <div class="row">
	    	<div class="col-sm-12" id="table-container" 
	    		style="font-size: 10px!important;">
	  		<!-- Listado de users -->
			<table id="tblusers" class="table table-striped table-bordered dt-responsive table-hover table-responsive nowrap datatable-buttons" 
					cellspacing="0" width="100%">
			  <thead>
			    <tr>
			      	<th>#</th>
			      	<th>Fecha Registro</th>
			      	<th>Nombre</th>
			      	<th>Apellido</th>
			      	<th>Email</th>
			      	<th>Teléfono</th>
			      	<th>Donde nos conocio?</th>
			      	<th>Sexo</th>
			      	<th>Edad</th>
			      	<?php if( $mostrar_total_reserva ){ ?>
			      		<th>Cant. Reservas</th>
			      	<?php } ?>
			      	<!--
			      		<th>Mascota(s)</th>
			      		<th>Raza(s)</th>
			  		-->
			      	<th>Primera Sol. Conocer </th>

			      	<th>Primera Reserva </th>

			      	<!-- th>Depurar datos (Testing)</th -->

			    </tr>
			  </thead>
			  <tbody>
			  	<?php $count=0; ?>
			  	<?php foreach( $users['rows'] as $row ){ ?>
			  		<?php
			  			// Metadata usuarios
			  			$usermeta = getmetaUser( $row['ID'] );

			  			if( $usermeta['user_age'] == "" ){
			  				$usermeta['user_age'] = "25-35 A&ntilde;os";
			  			}else{
			  				$usermeta['user_age'] .= " A&ntilde;os";
			  			}

			  			if( $usermeta['phone'] == "" ){
			  				if( $usermeta['user_referred'] != "Petco-CPF" ){
			  					$usermeta['user_referred'] = "CPF";
			  				}
			  			}

			  			$link_login = get_home_url()."/?i=".md5($row['ID']);

			  			$name = "{$usermeta['first_name']}";
			  			$lastname = "{$usermeta['last_name']}";
			  			if(empty( trim($name)) ){
			  			 	$name = $usermeta['nickname'];
			  			}

			  			$cant_reservas = 0;
				        if( $mostrar_total_reserva ){ 
				  			$cant_reservas = getCountReservas( $row['ID'] );
				  		}


						$reserva_15 = '';
						$_reserva_15 = '';
						$p_reserva = get_primera_reservas(  $row['ID'] );
						$dif = null;
						if( isset($p_reserva['rows'][0]['post_date_gmt']) ){
							$dif = diferenciaDias($row['user_registered'], $p_reserva['rows'][0]['post_date_gmt']);
							if( $dif['dia'] >= 0 && $dif['dia'] <= 15 ){
								$reserva_15 = '15 Dias';
							}else if( $dif['dia'] >= 16 && $dif['dia'] <= 30 ){
								$reserva_15 = '30 Dias';
							}else if( $dif['dia'] >= 16 && $dif['dia'] <= 45 ){
								$reserva_15 = '45 Dias';
							}else if( $dif['dia'] >= 16 && $dif['dia'] <= 60 ){
								$reserva_15 = '60 Dias';
							}else {
								$reserva_15 = '+60 Dias';
							}
							$_reserva_15 = $dif['dia'];
						}

						$conocer_15 = '';
						$_conocer_15 = '';
						$p_conocer = get_primera_conocer(  $row['ID'] );
						$dif_conocer = null;
						if( isset($p_conocer['rows'][0]['post_date_gmt']) ){

							$dif_conocer = diferenciaDias($row['user_registered'], $p_conocer['rows'][0]['post_date_gmt']);
							if( $dif_conocer['dia'] >= 0 && $dif_conocer['dia'] <= 15 ){
								$conocer_15 = '15 Dias';
							}else if( $dif_conocer['dia'] >= 16 && $dif_conocer['dia'] <= 30 ){
								$conocer_15 = '30 Dias';
							}else if( $dif_conocer['dia'] >= 16 && $dif_conocer['dia'] <= 45 ){
								$conocer_15 = '45 Dias';
							}else if( $dif_conocer['dia'] >= 16 && $dif_conocer['dia'] <= 60 ){
								$conocer_15 = '60 Dias';
							}else {
								$conocer_15 = '+60 Dias';
							}
							$_conocer_15 = $dif_conocer['dia'];

						}


			  		?>
				    <tr>
				    	<th class="text-center"><?php echo $row['ID']; ?></th>
						<th><?php echo date_convert($row['user_registered'], 'Y-m-d') ; ?></th>
						<th><?php echo $name; ?></th>
						<th><?php echo $lastname; ?></th>
						<th>
					  		<a href="<?php echo $link_login; ?>">
								<?php echo $row['user_email']; ?>
							</a>
						</th>
						<th><?php echo $usermeta['phone']; ?></th>
						<th><?php echo (!empty($usermeta['user_referred']))? $usermeta['user_referred'] : 'Otros' ; ?></th>
				        
				        <?php if( $mostrar_total_reserva ){ ?>
							<th><?php print_r( $cant_reservas['rows'][0]['cant'] ); ?></th>
				        <?php } ?>

						<th style="text-transform: capitalize;"><?php echo $usermeta['user_gender']; ?></th>
						<th><?php echo $usermeta['user_age']; ?></th>
						

						<th><?php echo $conocer_15 ; ?></th>

						<th><?php echo $reserva_15 ; ?></th>

						<!-- th><?php echo 'Reserva:'.$_reserva_15.' Conocer: '.$_conocer_15 ; ?></th -->

						<?php 
							/*
					  		# Mascotas del Cliente
					  		$mypets = getMascotas($row['ID']); 
					  		$pets_nombre = array();
					  		$pets_razas  = array();
					  		$pets_edad	 = array();
							foreach( $mypets as $pet_id => $pet) { 
								$pets_nombre[] = $pet['nombre'];
								$pets_razas[] = $pet['raza'];
								$pets_edad[] = $pet['edad'];
							} 

							if( count($pets_nombre) > 0 ){

						  		$raza = "Bien";
						  		foreach ($pets_razas as $key => $value) {
						  			if( $value == "" || $value == 0 ){
						  				$raza = "Malos";
						  				break;
						  			}
						  		}
						  		$pets_razas = $raza;

						  		$edad = $pets_edad[0];
						  		foreach ($pets_edad as $value) {
						  			if( $edad < $value ){
						  				$edad = $value;
						  			}
						  		}
						  		$pets_edad = $edad;


						  		$pets_nombre = implode(", ", $pets_nombre);
							}else{
						  		$pets_nombre = "_";
						  		$pets_razas  = "_";
						  		$pets_edad	 = "_";
							}
						?>
						<th><?php echo $pets_nombre; ?></th>
						<th><?php echo $pets_razas; ?></th>
						<th><?php echo $pets_edad; ?></th>
						*/ ?>

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

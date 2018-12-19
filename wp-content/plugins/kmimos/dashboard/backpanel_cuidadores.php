<?php global $wpdb;
// Usuarios 
require_once('core/ControllerCuidadores.php');
// Parametros: Filtro por fecha
$landing = '';
$date = getdate();
$desde = '';//date("Y-m-01", $date[0] );
$hasta = '';//date("Y-m-d", $date[0]);
if(	!empty($_POST['desde']) && !empty($_POST['hasta']) ){
	$desde = (!empty($_POST['desde']))? $_POST['desde']: "";
	$hasta = (!empty($_POST['hasta']))? $_POST['hasta']: "";
}
// Buscar Reservas
$users = getUsers($desde, $hasta);
?>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="x_title">
			<h2>Panel de Control <small>Lista de cuidadores</small></h2>
			<hr>
			<div class="clearfix"></div>
		</div>
		<!-- Filtros -->
		<div class="row text-right"> 
			<div class="col-sm-12">
		    	<form class="form-inline" action="<?php echo get_home_url(); ?>/wp-admin/admin.php?page=bp_cuidadores" method="POST">
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
			      <th>ID</th>
			      <th>Flash</th>
			      <th>Fecha Registro</th>
			      <th>Fecha de Nacimiento</th>
			      <th>Nombre y Apellido</th>
			      <th>Nombre</th>
			      <th>Apellido</th>
			      <th>Cuidador</th>

			     <!--  <th>Recompra ( 1M )</th>
			      <th>Recompra ( 3M )</th>
			      <th>Recompra ( 6M )</th>
			      <th>Recompra ( 12M )</th> -->

			      <th>Email</th>
			      <th>Estado</th>
			      <th>Municipio</th>
			      <th>Direcci&oacute;n</th>
			      <th>Teléfono</th>
			      <th>Donde nos conocio?</th>
			      <th>Estatus</th>
			    </tr>
			  </thead>
			  <tbody>
			  	<?php $count=0; ?>
			  	<?php foreach( $users['rows'] as $row ){ ?>
			  		<?php
			  			// Metadata usuarios
			  			$usermeta = getmetaUser( $row['ID'] );
			  			//QUITAR ESTA FUNCION PARA LA SUBIDA A PRODUCCION
			  			$link_login = get_home_url()."/?i=".md5($row['ID']);

			  			$name = "{$usermeta['first_name']} {$usermeta['last_name']}";
			  			if(empty($name)){
			  				$name = $usermeta['nickname'];
			  			}

			  			$direccion = getDireccion( $row['ID'] );

			  			$ubicacion = getEstadoMunicipio($direccion['estado'], $direccion['municipios']);

/*
			  			# Recompra 1 Meses
				  		$cliente_n_reserva = getCountReservas($row['ID'], "1");
				  		if(array_key_exists('rows', $cliente_n_reserva)){
					  		foreach ($cliente_n_reserva["rows"] as $value) {
				  				$r1 = ($value['cant']>1)? "SI" : "NO" ;
					  		}
					  	}

			  			# Recompra 3 Meses
				  		$cliente_n_reserva = getCountReservas($row['ID'], "3");
				  		if(array_key_exists('rows', $cliente_n_reserva)){
					  		foreach ($cliente_n_reserva["rows"] as $value) {
				  				$r3 = ($value['cant']>1)? "SI" : "NO" ;
					  		}
					  	}

			  			# Recompra 6 Meses
				  		$cliente_n_reserva = getCountReservas($row['ID'], "6");
				  		if(array_key_exists('rows', $cliente_n_reserva)){
					  		foreach ($cliente_n_reserva["rows"] as $value) {
				  				$r6 = ($value['cant']>1)? "SI" : "NO" ;
					  		}
					  	}

			  			# Recompra 12 Meses
				  		$cliente_n_reserva = getCountReservas($row['ID'], "12");
				  		if(array_key_exists('rows', $cliente_n_reserva)){
					  		foreach ($cliente_n_reserva["rows"] as $value) {
				  				$r12 = ($value['cant']>1)? "SI" : "NO" ;
					  		}
					  	}
*/	
					  	$atributos = $wpdb->get_var("SELECT atributos FROM cuidadores WHERE user_id = ".$row['ID']);
					  	$atributos = unserialize($atributos);

				  	    if( !isset($atributos["nacimiento"]) ){
					        $atributos["nacimiento"] = "No disponible";
					    }

					  	$flash = "";
						if( $atributos['flash'] == 1 ){
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
			  		?>
				    <tr>
				    	<th class="text-center"><?php echo $row['ID']; ?></th>
						<th><?php echo $flash; ?></th>
						<th><?php echo date_convert($row['user_registered'], 'Y-m-d') ; ?></th>
						<th><?php echo $atributos["nacimiento"] ; ?></th>
						<th><?php echo $name; ?></th>
						<th><?php echo $usermeta["first_name"]; ?></th>
						<th><?php echo $usermeta["last_name"]; ?></th>
						<th>
					  		<a href="<?php echo get_home_url().'/wp-admin/post.php?action=edit&post='.$row['cuidador_post']; ?>">
								<?php echo $row["cuidador_title"]; ?>	
							</a>
						</th>
 

						<!-- <th><?php echo $r1; ?></th>
						<th><?php echo $r3; ?></th>
						<th><?php echo $r6; ?></th>
						<th><?php echo $r12; ?></th> -->
 
						<th>
					  		<a href="<?php echo $link_login; ?>">
								<?php echo $row['user_email']; ?>
							</a>
						</th>
						<th><?php echo $ubicacion['estado']; ?></th>
						<th><?php echo $ubicacion['municipio']; ?></th>						
						<th><?php echo utf8_encode($row['direccion']); ?></th>						
						<th><?php echo $usermeta['phone']; ?></th>
						<th><?php echo (!empty($usermeta['user_referred']))? $usermeta['user_referred'] : 'Otros' ; ?></th>
						<th><?php echo ($row['estatus']==1)? 'Activo' : 'Inactivo' ; ?></th>
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

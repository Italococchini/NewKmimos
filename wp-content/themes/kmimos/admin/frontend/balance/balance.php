<?php
	require_once( dirname(dirname(dirname(__DIR__)))."/lib/pagos/pagos_cuidador.php" );
	$user_id = get_current_user_id();

	$hoy = date("Y-m-d H:i:s");
	$desde = date("Y-m-01", strtotime($hoy." -30 days"));
	$hasta = date("Y-m-d");

	$pay = $pagos->balance( $user_id );

	$cuidador = $pagos->db->get_row(" SELECT pago_periodo FROM cuidadores WHERE user_id = {$user_id}");

	$cuidador_periodo = ['periodo'=>'','dia'=>'','proximo_pago'=>'00/00/0000'];
	if( !empty($cuidador->pago_periodo) ){
		$cuidador_periodo = unserialize($cuidador->pago_periodo);
	}

	$periodo_retiro = [
		'semanal',
		'quincenal',
		'mensual',
	];

	$periodo_dias = [
		'lunes',
		'martes',
		'miercoles',
		'jueves',
		'viernes',
	];

	// Tiempo restante para retiro
	$fecha1 = new DateTime($hoy);
	$fecha2 = new DateTime($pay->retiro->tiempo_restante);
	$intervalo = $fecha1->diff($fecha2);

?>

<h1 class="titulo">Balance</h1>

<section class="row text-right" style="margin-bottom: 10px;">

	<h4 class="text-left col-md-12" style="margin-bottom: 0px; font-weight: bold">
		Puedes programar tus pagos semanal, quincenal o mensual de manera gratuita
	</h4>
	<dir class="col-md-4 text-left">
		<!-- Periodo de pago -->
		<div class="input-group">
			<span class="input-group-addon" id="basic-addon1">Periodo de retiro: </span>
			<select class="form-control" name="periodo">
				<?php 
			  	foreach( $periodo_retiro as $periodo ){ 
			  		$select = ( $periodo == $cuidador_periodo['periodo'] )? 'selected':'';
					echo "<option value='{$periodo}' {$select}>".ucfirst($periodo)."</option>";
				} 
			?>
			</select>
		</div>
	</dir>
	<dir class="col-md-4 text-left" id="retiro_dia" style="<?php echo ($cuidador_periodo['periodo']=='semanal')? 'display:block;' : 'display:none;' ; ?>">
		<!-- Periodo de pago -->
		<div class="input-group">
			<span class="input-group-addon" id="basic-addon1">D&iacute;a de retiro: </span>	
			<select class="form-control" name="periodo_dia">	
				<?php 
			  	foreach( $periodo_dias as $periodo ){ 
			  		$select = ( $periodo == $cuidador_periodo['dia'] )? 'selected':'';
					echo "<option value='{$periodo}' {$select}>".ucfirst($periodo)."</option>";
				} 
			?>
			</select>
		</div>
	</dir>
</section>

<section class="row">

	<!-- Disponible -->
	<article class="col-md-3">
		<div class="alert bg-kmimos">
			<i class="fa balance-help fa-question-circle" aria-hidden="true" data-action="popover" data-content="<strong>DISPONIBLE: </strong> Saldo disponible en cuenta"></i>
			<span>DISPONIBLE</span> 
			<div style="padding:5px 0px; font-size: 18px;">$ <?php echo number_format($pay->disponible, 2, ',','.'); ?></div>
			<small>Saldo disponible <br> para pagos y retiros</small>
		</div>
	</article>

	<!-- Proximo pago -->
	<article class="col-md-3">
		<div class="alert bg-kmimos">
			<i class="fa balance-help fa-question-circle" data-action="popover" data-content="<strong>PROXIMO PAGO: </strong> Monto a pagar en la proxima periodo de pago" aria-hidden="true"></i>
			<span>PROXIMO PAGO</span> 
			<div style="padding:5px 0px; font-size: 18px;">$ <?php echo number_format($pay->proximo_pago, 2, ',','.'); ?></div>
			<small>Fecha de pago <br> <span id="fecha_pago"><?php echo $cuidador_periodo['proximo_pago']; ?></span></small>
		</div>
	</article>

	<!-- En progreso -->
	<article class="col-md-3">
		<div class="alert bg-kmimos">
			<i class="fa balance-help fa-question-circle" aria-hidden="true" data-action="popover" data-content="<strong>EN TRANSITO: </strong>Pagos realizados pendientes por aprobaci&oacute;n del banco, el estatus puede tardar dos horas en cambiar"></i>
			<span>EN TRANSITO</span> 
			<div  style="padding:5px 0px; font-size: 18px;">$ <?php echo number_format($pay->en_progreso, 2, ',','.'); ?></div>
			<small>El estatus puede tardar <br> dos horas en cambiar</small>
		</div>
	</article>

	<!-- Retenido -->
	<article class="col-md-3">
		<div class="alert bg-kmimos">
			<i class="fa balance-help fa-question-circle" aria-hidden="true" data-action="popover" data-content="<strong>RETENIDO: </strong>Saldo pendientes por asignar en cuenta"></i>
			<span>RETENIDO</span> 
			<div  style="padding:5px 0px; font-size: 18px;">$ <?php echo number_format($pay->retenido, 2, ',','.'); ?></div>
			<small>Pendiente por <br> asignar en cuenta</small>
		</div>
	</article>

	<!-- Mensaje de ayuda -->
	<article class="col-md-12 text-left">
		<div class="alert alert-info" style="display:none;" role="alert" id='help'></div>
	</article>

	<article class="col-md-12 text-center">

		<h4 class="text-left col-md-12" style="margin-bottom: 5px; font-weight: bold">
			Puede seleccionar su pago ahora, y se hara un cobro de comision por transferencia bancaria
		</h4>

		<!-- Tiempo restante -->
		<label id="tiempo_restante_parent" class="btn btn-default <?php echo (!$pay->retiro->habilitado)? '':'hidden'; ?>">
			Tiempo restante: 
			<span id="hour"></span>  
			<span id="minute"></span>
			<span id="second"></span>
		</label> 

		<!-- Boton de retiro -->
		<div class="col-md-12">
			<a id="<?php echo ($pay->disponible>0)? '':'disabled_'; ?>boton-retiro" class="<?php echo ($pay->disponible>0)? '':'disabled'; ?> btn btn-primary btn-lg <?php echo ($pay->retiro->habilitado)? '':'hidden'; ?>" data-target="modal-retiros">
				<i class="fa fa-money"></i> Retirar ahora
			</a>
		</div>
		<!-- Ultimo retiro -->
		<div class="col-md-12" style="margin-top:5px;">
			<label class="purple">ULTIMO RETIRO: <?php echo (!empty($pay->retiro->ultimo_retiro)) ? $pay->retiro->ultimo_retiro : 'NO POSEE' ; ?></label><br>
		</div>

	</article>

	<!-- Transacciones -->
	<article class="col-md-12">	
		<hr>
		<div class="row ">		

			<dir class="col-md-4 subtitulo">
				Transacciones
			</dir>
		
			<!-- Filtros -->
			<dir class="col-md-8 date-container text-right">
				<span>Desde: </span>
				<input type="date" name="ini" value="<?php echo $desde; ?>">
				
				<span>Hasta: </span>
				<input type="date" name="fin" value="<?php echo $hasta; ?>">
				
				<button class="btn btn-default" id="search-transacciones"><i class="fa fa-search"></i> Buscar</button>
			</div>

			<!-- Transacciones -->
			<div id="table">			
				<table id="example" class="table table-striped table-bordered nowrap" cellspacing="0">
	                <thead>
	                    <tr>
	                        <th width="90">Fecha</th>
	                        <th width="90">Referencia</th>
	                        <th>Descripci&oacute;n</th>
	                        <th width="150">Monto</th>
	                    </tr>
	                </thead>
	                <tbody></tbody>
	            </table>
			</div>
		</div>
	</article>
</section>

<div class="modal fade" id="retiros" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="z-index:999999999999999999!important;top:100px;" data-backdrop="false" >
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><label>Retirar ahora</label></h4>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">Costo de comisión por transacción $10 </div>

        <h4 class="modal-title" id="myModalLabel" style="margin-bottom:10px;"><label>Saldo disponible: $ <?php echo number_format($pay->disponible, 2, ',','.'); ?></label></h4>

        <div style="margin-bottom: 10px;">
	        <label>Monto a retirar: </label>
	        <input type="text" name="monto" maxlength="100" class="form-control" value="" data-value="<?php echo $pay->disponible; ?>">
        </div>
        <div>    	
	        <label>Descripci&oacute;n: </label>
	        <input type="text" name="descripcion" maxlength="100" class="form-control" value=""data-value="<?php echo $pay->disponible; ?>">
        </div>
        <div class="text-right">
	        <h4 style="color:#000;">Monto a retirar: $ <span id="modal-subtotal">0</span></h4>
	        <h4 style="color:#000;">Comisi&oacute;n: $ -10,00</h4>
	        <h4><strong>Total a transferir: $ <span class="purple" id="modal-total">0</span></strong></h4>
        </div>

      </div>
      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary " id="retirar">Procesar</button>
      </div>
    </div>
  </div>
</div>






<script>
    var fecha = new Date('<?php echo $pay->retiro->tiempo_restante; ?>');
    var user_id = <?php echo $user_id; ?>;

    var tiempo_corriendo = null;
    var tiempo = {
        hora: <?php echo $intervalo->format('%H') ?>,
        minuto: <?php echo $intervalo->format('%i') ?>,
        segundo: <?php echo $intervalo->format('%s') ?>
    };
</script>

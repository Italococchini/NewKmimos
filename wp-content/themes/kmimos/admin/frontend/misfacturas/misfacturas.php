<?php

	global $wpdb;

	$NotasCredito = '';
	include( "notas-creditos.php" );

	$user_id = get_current_user_id();
	$ocultar_comisiones = 'block';
	if( !is_petsitters() ){
		$sql = "SELECT * FROM facturas WHERE cliente_id = {$user_id} ORDER BY fechaGeneracion DESC";
		$ocultar_comisiones = 'none';
	}else{
		$sql = "SELECT * FROM facturas WHERE cuidador_id = {$user_id} ORDER BY fechaGeneracion DESC";
	}

	$facturas = $wpdb->get_results($sql);

	$listado_fecha = [];
	$archivos = [
		'comision' => [],
		'liquidacion' => [],
	];

	if( count($facturas) > 0 ){

		$factura_array = array(
			"cuidador" => array(
				"titulo" => 'Facturas Recibidas',
				"facturas" => array()
			),
			"cliente" => array(
				"titulo" => 'Facturas Emitidas',
				"facturas" => array()
			),
		);

		foreach ($facturas as $factura) {
			
			$reserva_detalle = kmimos_desglose_reserva_data( $factura->pedido_id,  true);
			$foto = kmimos_get_foto( $factura->cliente_id ) ;
			$cliente_nombre = $reserva_detalle['cliente']['nombre'];

			$mes = date('m', strtotime($factura->fechaGeneracion));
			$anio = date('Y', strtotime($factura->fechaGeneracion));

			$listado_fecha['mes'][$mes] = '<option value="'.$mes.'">'.$mes.'</option>';
			$listado_fecha['anio'][$anio] = '<option value="'.$anio.'">'.$anio.'</option>';

			switch ($factura->receptor) {
				case 'cuidador':

					$archivos['comision'][] = $factura->reserva_id.'_'.$factura->numeroReferencia;

					$factura_array["cuidador"]["facturas"][] = array(
						'id' => $factura->id, 
						'fecha_mes' => $mes,
						'fecha_anio' => $anio,
						'fecha_creacion' => $factura->fechaGeneracion, 
						'cliente' => strtoupper('Kmimos'), 
						'reserva_id' => $factura->reserva_id, 
						'serie' => $factura->serie, 
						'estado' => $factura->estado, 
						'foto' => $foto,
						'numeroReferencia' => $factura->numeroReferencia, 

						'archivo_name' => $factura->reserva_id.'_'.$factura->numeroReferencia,

						'QR' => $factura->urlQR, 
						'total' => $factura->total,
						'acciones' => array(
							"factura_PdfXml" => $factura->reserva_id.'_'.$factura->numeroReferencia,
						),
					);
					break;
				case 'cliente':

					$archivos['liquidacion'][] = $factura->reserva_id.'_'.$factura->numeroReferencia;

					$factura_array["cliente"]["facturas"][] = array(
						'id' => $factura->id, 
						'fecha_mes' => $mes,
						'fecha_anio' => $anio,
						'fecha_creacion' => $factura->fechaGeneracion, 
						'cliente' => strtoupper($cliente_nombre), 
						'reserva_id' => $factura->reserva_id, 
						'serie' => $factura->serie, 
						'estado' => $factura->estado, 
						'foto' => $foto,
						'numeroReferencia' => $factura->numeroReferencia, 
						'archivo_name' => $factura->reserva_id.'_'.$factura->numeroReferencia,
						'servicio' => $factura->servicio, 
						'QR' => $factura->urlQR, 
						'total' => $factura->total,						
						'acciones' => array(
							"factura_PdfXml" => $factura->reserva_id.'_'.$factura->numeroReferencia,
						),
					);
					break;
			}

		}
		
		$Comisiones = construir_listado(['cuidador'=>$factura_array['cuidador']]);
		$Liquidaciones = construir_listado(['cliente'=>$factura_array['cliente']]);

		$Comisiones = ( !empty($Comisiones) )? $Comisiones : '<h1 class="titulo titulo_pequenio">Sin datos para mostrar</h1>';
		$Liquidaciones = ( !empty($Liquidaciones) )? $Liquidaciones : '<h1 class="titulo titulo_pequenio">Sin datos para mostrar</h1>';

		sort($listado_fecha['mes']);
		sort($listado_fecha['anio']);
		$select_mes = '<option value="0">Mes</option>';
		$select_mes .= implode("",$listado_fecha['mes']);

		$select_anio = '<option value="0">Año</option>';
		$select_anio .= implode("",$listado_fecha['anio']);

		$ocultar = '';
		if( empty($select_mes) && empty($select_anio) ){
			$ocultar = 'hidden';
		}
	}

		//BUILD TABLE
		$CONTENIDO .= '

			<script type="text/javascript">
				jQuery(document).ready(function(){
					listado_liquidacion = ["'.implode('","', $archivos['liquidacion']).'"];
					listado_comision = ["'.implode('","', $archivos['comision']).'"];
				});
			</script>

			<h1 style="margin: 0px; padding: 0px;">Mis Facturas</h1><hr style="margin: 5px 0px 10px;">

			<div class="contenedor-botones '.$ocultar.'">
				<label>Filtrar por: </label>
				<select data-action="filtro" name="filtro_mes" >
					<option value="0">Mes</option>
					<option value="01">Enero</option>
					<option value="02">Febrero</option>
					<option value="03">Marzo</option>
					<option value="04">Abril</option>
					<option value="05">Mayo</option>
					<option value="06">Junio</option>
					<option value="07">Julio</option>
					<option value="08">Agosto</option>
					<option value="09">Septiembre</option>
					<option value="10">Octubre</option>
					<option value="11">Noviembre</option>
					<option value="12">Diciembre</option>
				</select>
				<select data-action="filtro" name="filtro_anio" >'.$select_anio.'</select>

				<button id="download-selected"><i class="fa fa-cloud-download"></i> Descargar en Zip</button>
				<button id="download-todo"><i class="fa fa-cloud-download"></i> Descargar Todo</button>
			</div>

			<div>

			  <!-- Nav tabs -->
			  <ul class="nav nav-tabs" role="tablist">
			    <li role="presentation" class="active">
			    	<a href="#Liquidaciones" aria-controls="Liquidaciones" role="tab" data-toggle="tab">Liquidaciones</a>
			    </li>
			    <li role="presentation" style="display:'.$ocultar_comisiones.';"><a href="#Comisiones" aria-controls="Comisiones" role="tab" data-toggle="tab">Comisiones</a>
			    </li>
			    <li role="presentation">
			    	<a href="#notas_credito" aria-controls="notas_credito" role="tab" data-toggle="tab">Notas de Cr&eacute;dito</a>
			    </li>
			  </ul>

			  <!-- Tab panes -->
			  <div class="tab-content">
			    <div role="tabpanel" class="tab-pane active" id="Liquidaciones">'.$Liquidaciones.'</div>
			    <div role="tabpanel" class="tab-pane" id="Comisiones">'.$Comisiones.'</div>
			    <div role="tabpanel" class="tab-pane" id="notas_credito">'.$NotasCredito.'</div>
			  </div>

			</div>

		';


?>


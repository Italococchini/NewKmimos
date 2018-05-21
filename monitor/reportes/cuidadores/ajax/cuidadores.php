<?php

require_once( dirname(dirname(__DIR__)).'/class/general.php' );
require_once( dirname(dirname(__DIR__)).'/class/cuidador.php' );

// ******************************************
// Procesar datos
// ******************************************

	$hoy = date('Y-m-d');

	$desde = date('Y-m-d', strtotime( '-12 month', strtotime($hoy) ));
	if( isset($_POST['desde']) && !empty($_POST['desde']) ){
		$desde = $_POST['desde'];
	}

	$hasta = $hoy;
	if( isset($_POST['hasta']) && !empty($_POST['hasta']) ){
		$hasta = $_POST['hasta'];
	}

	$g = new general();
	$c = new cuidador();

	// Datos para mostrar
	$data = [];

	// Plataformas
	$plataformas = $g->get_plataforma();

print_r($plataformas);
exit();

	// Cargar datos de la plataforma seleccionada
	$sucursal = 'global';
	$_action = explode('.', $_POST['sucursal']);

	foreach ($plataformas as $plataforma) {
		$sts = 0;
		switch( $_action[0] ){
			case 'bygroup':
				if( $_action[1] == $plataforma['grupo'] ){
					$sts = 1;
					$sucursal = $plataforma['grupo'];
				}
				break;
			case 'byname':
				if( $_action[1] == $plataforma['name'] ){
					$sts = 1;
					$sucursal = $plataforma['descripcion'];
				}
				break;
			default: // global
				$sts = 1;
				break;
		}
		if( $sts == 1 ){

			try{
				/*
				$datos = $c->request( 
					$plataforma['dominio']."/monitor/services/getData.php", 
					['desde'=>$desde, 'hasta'=>$hasta] 
				);
				*/

				$datos = $c->get_datos( $desde, $hasta );
				$month = $c->by_month( $datos );

				$data = $c->merge_branch( $month, $data );

			}catch(Exception $e){
				$error[] = $plataforma['descripcion'];
			}

		}
	}

	//$s = $c->procesar( $data, $desde, $hasta  );
 

	// Meses en letras
	$meses = $c->getMeses();


// ******************************************
// Construir datos para la table y graficos
// ******************************************

if( !empty($data) ){

	$error = 0;

	// Rows: orden y descripcion de la tabla
	$tbl_body['total']  = "1, '<strong>Total Cuidadores certificados</strong>'";
	$tbl_body['nuevos'] = "2, 'Nuevos Cuidadores certificados'";
	$tbl_body['costos_por_campana'] = "3, '<strong>Costo por cuidador (CAC)</strong>'";
	$tbl_body['costo']  = "4, 'Costo por cuidador (CAC)USD'";

	$_meses = array_keys($data);
	$graficos_data = [];
	$tbl_header = '<th></th><th>Descripci&oacute;n</th>';
	foreach ($_meses as $key => $value) {

		$anio_corto = substr($value, 4, 2);
		$mes_corto = $meses[substr($value, 0, 2)-1];
		$anio_largo = substr($value, 2, 4);
		$mes = $mes_corto.$anio_largo;

		// Grafico
		$data[$value]['date'] = $mes_corto.$anio_corto;
		$graficos_data[] = $data[$value];

	 	// tabla
		$tbl_header .= "<th>".$mes."</th>";

		$tbl_body['total']  .= ", '0'";
		$tbl_body['nuevos'] .= ", '0'";
		$tbl_body['costos_por_campana'] .= ", '0'";
		$tbl_body['costo']  .= ", '0'";

	}

}else{
	$error = 1;
}

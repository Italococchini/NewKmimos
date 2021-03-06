<?php
	header('Access-Control-Allow-Origin: *');
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

	$c = new cuidador();

	$cuidadores = $c->get_cuidadores( $desde, $hasta );

	$marketing = $c->sumar_campanas( $desde, $hasta );

	$estatus = 0;
	if( count($cuidadores) > 0 ){
		$estatus = 1;
	}

	print_r(json_encode([
		'estatus' => $estatus,
		'cuidadores' => $cuidadores,
		'marketing' => $marketing
	]));



// ******************************************
// Construir datos para la table y graficos
// ******************************************
/*
if( !empty($data) ){

	$error = 0;

	// Rows: orden y descripcion de la tabla
	$tbl_body['total']  = "1, '<strong>Total Cuidadores certificados</strong>'";
	$tbl_body['nuevos'] = "2, 'Nuevos Cuidadores certificados'";
	$tbl_body['costos_por_campana'] = "3, '<strong>Costo por cuidador (CAC)</strong>'";
	$tbl_body['costo']  = "4, 'Costo por cuidador (CAC) - USD'";

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

		$tbl_body['total']  .= ", '".number_format($data[$value]['total'],0,',','.')."'";
		$tbl_body['nuevos'] .= ", '".number_format($data[$value]['nuevos'],0,',','.')."'";
		$tbl_body['costos_por_campana'] .= ", '".number_format($data[$value]['costos_por_campana'],2,',','.')."'";
		$tbl_body['costo']  .= ", '$ ".number_format($data[$value]['costo'],2,',','.')."'";

	}

}else{
	$error = 1;
}
*/
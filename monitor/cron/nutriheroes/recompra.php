<?php

	require_once('funciones.php');

	$desde = date('Y-m-d');
	if( isset($_GET['d']) && !empty($_GET['d']) ){
		$desde = $_GET['d'];
	}

	$hasta = $desde;
	if( isset($_GET['h']) && !empty($_GET['h']) ){
		$hasta = $_GET['h'];
	}

	$recompras = getRecompras( $desde, $hasta );

	if( !isset($recompras['info']->nums_rows) ){
		$recompras = [];
	}
	print_r(json_encode($recompras));	

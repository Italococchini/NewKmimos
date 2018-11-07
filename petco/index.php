<?php

	$param = ( !empty($_SERVER['QUERY_STRING']) && isset($_GET['utm_campaign']) )? '&'.$_SERVER['QUERY_STRING'] : '&utm_source=web&utm_medium=banner&utm_campaign=petco_kmimos&utm_term=alimento_mascotas_nutricion_cuidado_hospedaje' ;	

	$url = 'https://www.kmimos.com.mx/?wlabel=petco'.$param;	

	header('Location:'.$url );
	exit();
?>

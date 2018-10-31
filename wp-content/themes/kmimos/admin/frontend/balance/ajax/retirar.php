<?php
	
require_once(dirname(dirname(dirname(dirname(__DIR__)))).'/lib/pagos/pagos_cuidador.php');
extract($_POST);

$pagos->cargar_retiros( $ID, $monto, $descripcion );


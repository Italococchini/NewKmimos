<?php
	
include_once('general.php');

class marketing extends general{
	
	public function get_datos( $where='' ){
		return $this->select( "SELECT * FROM monitor_marketing $where " );
	}

	public function get_total_gastos( $desde, $hasta, $plataforma, $tipo='cliente' ){

		$where = '';
		if( !empty($plataforma) ){
			$where = " AND plataforma = '{$plataforma}' ";
		}

		return $this->select( "
			SELECT 
				sum(costo) as costo,
				canal,
				CONCAT(LPAD(MONTH(fecha), 2, '0'), YEAR(fecha)) as fecha
			FROM monitor_marketing 
			WHERE tipo like '%{$tipo}%'
				$where
			GROUP BY canal, CONCAT(LPAD(MONTH(fecha), 2, '0'), YEAR(fecha)) 
		" );
	}
}
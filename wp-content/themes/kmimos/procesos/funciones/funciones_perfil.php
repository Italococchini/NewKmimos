<?php
	if(!function_exists('construir_botones')){
	    function construir_botones($botones){
	    	$respuesta = "";
	    	foreach ($botones as $boton => $accion) {
	    		switch ($boton) {
	    			case 'ver':
	    				$respuesta .= '<a data-accion="ver/'.$accion.'" class="vlz_accion vlz_ver"> <i class="fa fa-info" aria-hidden="true"></i> Ver</a>';
    				break;
	    			case 'confirmar':
	    				$respuesta .= '<a data-accion="'.get_home_url()."/wp-content/plugins/kmimos/order.php?s=1&o=".$accion.''.$accion.'" class="vlz_accion vlz_confirmar"> <i class="fa fa-check" aria-hidden="true"></i> Confirmar </a>';
    				break;
	    			case 'cancelar':
	    				$respuesta .= '<a data-accion="'.get_home_url()."/wp-content/plugins/kmimos/".$accion.'" class="vlz_accion vlz_cancelar"> <i class="fa fa-trash-o" aria-hidden="true"></i> Cancelar</a>';
    				break;
	    			case 'modificar':
	    				$respuesta .= '<a data-accion="'.$accion.'" class="vlz_accion vlz_modificar"> <i class="fa fa-pencil" aria-hidden="true"></i> Modificar </a>';
    				break;
	    			case 'pdf':
	    				$respuesta .= '<a data-accion="'.$accion.'" class="vlz_accion vlz_pdf"> <i class="fa fa-download" aria-hidden="true"></i> ¿Com&oacute; pagar? </a>';
    				break;
	    			case 'valorar':
	    				$respuesta .= '<a data-accion="'.$accion.'" class="vlz_accion vlz_valorar"> <i class="fa fa-thumbs-o-up" aria-hidden="true"></i> Valorar </a>';
    				break;
	    			
	    		}
	    	}
	    	return $respuesta;
	    }
	}

	if(!function_exists('construir_listado')){
	    function construir_listado($args = array()){
	        $table='';
	        $avatar_img = get_home_url()."/wp-content/themes/kmimos/images/noimg.png";
	        foreach($args as $reservas){
	        	if( count($reservas['reservas']) > 0 ){
	        		if( $reservas['titulo'] == "Reservas pendientes por pagar en tienda por conveniencia" ){
		                $table.='
		                	<h1 class="titulo titulo_pequenio">'.$reservas['titulo'].'</h1>
		                	<div class="vlz_tabla_box">
		                ';
	        		}else{
		                $table.='
		                	<h1 class="titulo">'.$reservas['titulo'].'</h1>
		                	<div class="vlz_tabla_box">
		                ';
	        		}
		                foreach ($reservas['reservas'] as $reserva) {

		                	$cancelar = '';
		                	if( isset($reserva["acciones"]["cancelar"]) ){
		                		//$cancelar = '<a data-accion="'.get_home_url().'/wp-content/plugins/kmimos/'.$reserva["acciones"]["cancelar"].'" class="vlz_accion vlz_cancelar cancelar"> <i class="fa fa-trash-o" aria-hidden="true"></i></a>';
		                	}
		                	$botones = construir_botones($reserva["acciones"]);

		                	$vlz_tabla_inferior = "";
		                	$descuento = "";
		                	if( $reserva["desglose"]["descuento"]+0 > 0){
		                		$descuento = '
		                			<div class="item_desglose">
			                			<div>Descuento</div>
			                			<span>$'.$reserva["desglose"]["descuento"].'</span>
			                		</div>
		                		';
		                	}
		                	if( $reserva["desglose"]["enable"] == "yes" ){
		                		$vlz_tabla_inferior = '
			                		<div class="desglose_reserva">
				                		<div class="item_desglose vlz_bold">
				                			<div>MÉTODO DE PAGO</div>
				                			<span>DEPÓSITO DEL 17%</span>
				                		</div>
				                		<div class="item_desglose vlz_bold">
				                			<div style="color: #6b1c9b;" >Monto Restante a Pagar en EFECTIVO al cuidador</div>
				                			<span style="color: #6b1c9b;">$'.number_format( ($reserva["desglose"]["remaining"]-$reserva["desglose"]["descuento"]), 2, ',', '.').'</span>
				                		</div>
				                		'.$descuento.'
				                		<div class="item_desglose">
				                			<div>Pagó</div>
				                			<span>$'.number_format( $reserva["desglose"]["deposit"], 2, ',', '.').'</span>
				                		</div>
			                		</div>
			                		<div class="total_reserva">
				                		<div class="item_desglose">
				                			<div>TOTAL</div>
				                			<span>$'.number_format( $reserva["desglose"]["total"], 2, ',', '.').'</span>
				                		</div>
			                		</div>
			                	';
		                	}else{
		                		$vlz_tabla_inferior = '
			                		<div class="desglose_reserva">
				                		<div class="item_desglose vlz_bold">
				                			<div>MÉTODO DE PAGO</div>
				                			<span>PAGO TOTAL</span>
				                		</div>
				                		'.$descuento.'
				                		<div class="item_desglose">
				                			<div>Pagó</div>
				                			<span>$'.number_format( ($reserva["desglose"]["total"]-$reserva["desglose"]["descuento"]), 2, ',', '.') .'</span>
				                		</div>
			                		</div>
			                		<div class="total_reserva">
				                		<div class="item_desglose">
				                			<div>TOTAL</div>
				                			<span>$'.number_format( $reserva["desglose"]["total"], 2, ',', '.') .'</span>
				                		</div>
			                		</div>
			                	';
		                	}

			                $table.='
			                <div class="vlz_tabla">
			                	<div class="vlz_img">
			                		<span style="background-image: url('.$reserva["foto"].');"></span>
			                	</div>
			                	<div class="vlz_tabla_superior">
				                	<div class="vlz_tabla_cuidador vlz_celda">
				                		<span>Servicio</span>
				                		<div><a href="'.get_home_url().'/reservar/'.$reserva["servicio_id"].'/">'.$reserva["servicio"].'</a></div>
				                	</div>
				                	<div class="vlz_tabla_cuidador vlz_celda">
				                		<span>Fecha</span>
				                		<div>'.$reserva["inicio"].' <b> > </b> '.$reserva["fin"].'</div>
				                	</div>
				                	<div class="vlz_tabla_cuidador vlz_botones vlz_celda boton_interno">
				                		'.$cancelar.'
				                		<a class="ver_reserva_init">Ver Reserva</a>
				                	</div>
				                	<div class="vlz_tabla_cuidador vlz_cerrar">
				                		<span>Reserva</span>
				                		<div>'.$reserva["id"].'</div>
				                	</div>
			                	</div>
		                		<i class="fa fa-times ver_reserva_init_closet" aria-hidden="true"></i>
			                	<div class="vlz_tabla_cuidador vlz_botones vlz_celda boton_fuera">
			                		<a class="ver_reserva_init_fuera">Ver Reserva</a>
			                	</div>
			                	<div class="vlz_tabla_inferior">
			                		'.$vlz_tabla_inferior.'
			                		<div class="ver_reserva_botones">
				                		'.$botones.'
			                		</div>
			                	</div>
			                </div>';
		                }

	                $table.='</div>';
	        	}
	        }
	        return $table;
	    }
	}
?>
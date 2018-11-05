<?php
require_once(__DIR__.'/base_db.php');
require_once(__DIR__.'/GlobalFunction.php');

// ***************************************
// Cargar listados de Reservas
// ***************************************
function getRazaDescripcion($id, $razas){
	$nombre = "[{$id}]";
	if($id > 0){
		if( !empty($razas) ){
			if(array_key_exists($id, $razas)){
				$nombre = $razas[$id];
			}
		}
	}
	return $nombre;
}

function get_cupones_reserva( $reserva_id ){
	global $wpdb;

    // Cargar cupones 
    $cupon_sql = "SELECT items.order_item_name as name, meta.meta_value as monto  
    FROM `wp_woocommerce_order_items` as items 
    INNER JOIN wp_woocommerce_order_itemmeta as meta ON meta.order_item_id = items.order_item_id
    INNER JOIN wp_posts as p ON p.ID = ".$reserva_id." and p.post_type = 'wc_booking' 
    WHERE 
    meta.meta_key = 'discount_amount'
    and items.`order_id` = p.post_parent";
    $cupones = $wpdb->get_results($cupon_sql);

    $info = '';
    $color = $colores['normal'];
    $tipo_cupon='';
    if( !empty($cupones) ){                    
        foreach ($cupones as $cupon) {
            if( $cupon->monto > 0 ){
                $tipo = $wpdb->get_var("SELECT m.meta_value 
                    FROM wp_posts as p 
                    INNER JOIN wp_postmeta as m ON m.post_id = p.ID AND m.meta_key = 'descuento_tipo' 
                    WHERE post_title = '".$cupon->name."' AND post_type = 'shop_coupon'");
                $cupon_tipo = ( $tipo != '' ) ? " Tipo: ".$tipo : '' ;

                // Saldo a favor
                $es_saldo=false;
                if( strpos($cupon->name, 'saldo-') !== false ){

	                $es_saldo=true;
                    $info = " [ ".$cupon->name .": $ " .$cupon->monto . " Tipo: Saldo a favor ] ";
				    $tooltip = (!empty($info))? 'data-toggle="tooltip" data-placement="top" title="'.$info.'"' : '' ;
		    	    $detalle[ 'kmimos' ] = '<small class="items-span" '.$tooltip.' style="color:#fff; background:#88e093!important; padding: 10px;">
				            <label style="margin-bottom: 0px;">
				                    <strong>Saldo a favor </strong>
				                    <span class="badge" style="margin-left: 10px;">
				                        $ '.number_format($cupon->monto, 2, ",", ".").'
				                    </span>
				            </label>
				        </small>
				    ';
                }

                // Cupones a Kmimos
                if( ($tipo == 'compartido' ||  $tipo == 'kmimos') || ( empty($tipo) && !$es_saldo ) ) {
                	$monto_kmimos = $cupon->monto;	                
	                if( $tipo == 'compartido' ){
		                $percent_kmimos = $wpdb->get_var("SELECT m.meta_value 
		                    FROM wp_posts as p 
		                    INNER JOIN wp_postmeta as m ON m.post_id = p.ID AND m.meta_key = 'descuento_kmimos' 
		                    WHERE post_title = '".$cupon->name."' AND post_type = 'shop_coupon'");
		                if( $cupon->monto > 0 && $percent_kmimos > 0 ){
		                    $monto_kmimos = ( $cupon->monto * $percent_kmimos ) / 100;
		                }
	                }

                    $info = " [ ".$cupon->name .": $ " .$cupon->monto . " Tipo: ".$tipo . " ] ";
				    $tooltip = (!empty($info))? 'data-toggle="tooltip" data-placement="top" title="'.$info.'"' : '' ;
		    	    $detalle[ 'kmimos' ] .= '<small class="items-span" '.$tooltip.' style="color:#fff; background:#8d88e0!important; padding: 10px;">
				            <label style="margin-bottom: 0px;">
				                    <strong>'.$cupon->name .'</strong>
				                    <span class="badge" style="margin-left: 10px;">
				                        $ '.number_format($monto_kmimos, 2, ",", ".").'
				                    </span>
				            </label>
				        </small>
				    ';
                }

                // Cupones a cuidador
                if( $tipo == 'compartido' ||  $tipo == 'cuidador' ){
                	$monto_cuidador = $cupon->monto;
	                if( $tipo == 'compartido' ){
		                $percent_cuidador = $wpdb->get_var("SELECT m.meta_value 
		                    FROM wp_posts as p 
		                    INNER JOIN wp_postmeta as m ON m.post_id = p.ID AND m.meta_key = 'descuento_cuidador' 
		                    WHERE post_title = '".$cupon->name."' AND post_type = 'shop_coupon'");
		                if( $cupon->monto > 0 && $percent_cuidador > 0 ){
		                    $monto_cuidador = ( $cupon->monto * $percent_cuidador ) / 100;
		                }
		            }
	                
                    $info = " [ ".$cupon->name .": $ " .$cupon->monto . " Tipo: ".$tipo . " ] ";
				    $tooltip = (!empty($info))? 'data-toggle="tooltip" data-placement="top" title="'.$info.'"' : '' ;
		    	    $detalle[ 'cuidador' ] = '<small class="items-span" '.$tooltip.' style="color:#fff; background:#e0888c!important; padding: 10px;">
				            <label style="margin-bottom: 0px;">
				                    <strong>'.$cupon->name .'</strong>
				                    <span class="badge" style="margin-left: 10px;">
				                        $ '.number_format($monto_cuidador, 2, ",", ".").'
				                    </span>
				            </label>
				        </small>
				    ';
                }


            }
        }
    }

    return $detalle;

}

/*
function __calculo_pago_cuidador( $reserva_id, $total ){

	$pago_cuidador = $total / 1.25;
	$pago_kmimos = $total - $pago_cuidador;

	// Cupones de la reserva
		$cupones = $this->db->get_results("SELECT items.order_item_name as name, meta.meta_value as monto  
        FROM `wp_woocommerce_order_items` as items 
            INNER JOIN wp_woocommerce_order_itemmeta as meta ON meta.order_item_id = items.order_item_id
            INNER JOIN wp_posts as p ON p.ID = ".$reserva_id." and p.post_type = 'wc_booking' 
            WHERE meta.meta_key = 'discount_amount'
                and items.`order_id` = p.post_parent
                and not items.order_item_name like ('saldo-%')
        ;");

	// Datos de los cupones
		$meta_cupon = [];
		if( !empty($cupones) ){
			foreach ($cupones as $cupon) {


				$cupon_id = $this->db->get_var("SELECT ID FROM wp_posts WHERE post_title = '".$cupon->name."' ");
				$metas =  $this->db->get_results("SELECT meta_key, meta_value FROM wp_postmeta WHERE meta_key like 'descuento%' and post_id = ".$cupon_id );

				$meta_cupon[ $cupon->name ][ 'total' ] = $cupon->monto; 
				if( $cupon->monto > 0 ){
					if( !empty($metas) ){
						foreach ($metas as $meta) {
							$meta_cupon[ $cupon->name ][ $meta->meta_key ] = $meta->meta_value;
						}
					}

					// tipo de descuento
					$_cupon = $meta_cupon[ $cupon->name ];

					switch ( strtolower($_cupon['descuento_tipo']) ) {
						case 'kmimos':
							if( $pago_kmimos < $_cupon['total'] ){
								$diferencia = $_cupon['total'] - $pago_kmimos;
								$pago_cuidador -= $diferencia;
							}else{
								$pago_kmimos -= $_cupon['total'];
							}
							break;
						case 'cuidador':
							if( $pago_cuidador < $_cupon['total'] ){
								$pago_cuidador = 0;
							}else{
								$pago_cuidador -= $_cupon['total'];
							}
							break;
						
						case 'compartido':
							// Calculo de descuentos
							$descuento_kmimos = ( $_cupon['descuento_kmimos'] * $_cupon['total'] ) / 100;
							$descuento_cuidador = ( $_cupon['descuento_cuidador'] * $_cupon['total'] ) / 100;
							if( $pago_cuidador <= $descuento_cuidador ){
								$pago_cuidador = 0;
							}else{
								// validar si el monto de kmimos es superior a la comision
								$diferencia = 0;
								if( $pago_kmimos < $descuento_kmimos ){
									$diferencia = $descuento_kmimos - $pago_kmimos;
									$descuento_cuidador += $diferencia;
									$pago_kmimos = 0;
								}

								if( $descuento_cuidador >= $pago_cuidador ){
									$pago_cuidador = 0;
								}else{
									$pago_cuidador -= $descuento_cuidador;
								}
							}
							break;
						default:
							$pago_cuidador -= $_cupon['total'];
							break;
					}
				}
			}
		}

	return $pago_cuidador ; 
}
*/


function getCountReservas( $author_id=0, $interval=12, $desde="", $hasta=""){

	$filtro_adicional = "";
	if( !empty($landing) ){
		$filtro_adicional = " source = '{$landing}'";
	}
	if( !empty($desde) && !empty($hasta) ){
		$filtro_adicional .= (!empty($filtro_adicional))? ' AND ' : '' ;
		$filtro_adicional .= " 
			DATE_FORMAT(post_date_gmt, '%m-%d-%Y') between DATE_FORMAT('{$desde}','%m-%d-%Y') and DATE_FORMAT('{$hasta}','%m-%d-%Y')
		";
	}else{
		$filtro_adicional .= (!empty($filtro_adicional))? ' AND ' : '' ;
		$filtro_adicional .= " MONTH(post_date_gmt) = MONTH(NOW()) AND YEAR(post_date_gmt) = YEAR(NOW()) ";
	}


	$filtro_adicional = ( !empty($filtro_adicional) )? " WHERE {$filtro_adicional}" : $filtro_adicional ;

	$result = [];
	$sql = "
		SELECT 
			count(ID) as cant
		FROM wp_posts
		WHERE post_type = 'wc_booking' 
			AND not post_status like '%cart%'
			AND post_status = 'confirmed' 
			AND post_author = {$author_id}
			AND post_date_gmt > DATE_SUB(CURDATE(), INTERVAL {$interval} MONTH)
	";

	$result = get_fetch_assoc($sql);
	return $result;
}

function get_status($sts_reserva, $sts_pedido, $forma_pago="", $id_reserva){
	
	// Cargar a totales
	$addTotal = 0;
	// Resultado
	$sts_corto = "---";
	$sts_largo = "Estatus Reserva: {$sts_reserva}  /  Estatus Pedido: {$sts_pedido}";
	//===============================================================
	// BEGIN PaymentMethod
	// Nota: Agregar la equivalencia de estatus de las pasarelas de pago
	//===============================================================
	$payment_method_cards = [ // pagos por TDC / TDD
		'openpay_cards',
		'tarjeta',
	]; 
	$payment_method_store = [ // pagos por Tienda por conveniencia
		'openpay_stores',
		'tienda',
	]; 
	//===============================================================
	// END PaymentMethod
	//===============================================================

	// Pedidos
	switch ($sts_reserva) {
		case 'unpaid':
			$sts_corto = "Pendiente";
			if( $sts_pedido == 'wc-on-hold'){
				if( in_array($forma_pago, $payment_method_cards) ){
					$sts_largo = "Pendiente por confirmar el cuidador"; // metodo de pago es por TDC / TDD ( parcial )
					$sts_corto = "Pago fallido";
				}elseif( in_array($forma_pago, $payment_method_store) ){
					$sts_largo = "Pendiente de pago en tienda"; // Tienda por conv
				}else{
					$sts_largo = "Estatus Pedido: {$sts_pedido}"; 
				}
			}
			if( $sts_pedido == 'wc-pending'){
				$sts_largo = 'Pendiente de pago';
				if( in_array($forma_pago, $payment_method_cards) ){
					$sts_corto = "Pago fallido";
				}
			}
		break;
		case 'wc-partially-paid':
			$sts_largo = "Estatus Reserva: Pago Parcial  /  Estatus Pedido: {$sts_pedido}";
			if( $sts_pedido == 'unpaid'){
				$sts_corto = 'Por confirmar (cuidador)';
				$sts_largo = 'Por confirmar (cuidador)';
			}
		break;		
		case 'confirmed':
			$sts_corto = 'Confirmado';
			$sts_largo = 'Confirmado';
			$addTotal  = 1;
		break;
		case 'paid':
			$sts_corto = 'Pagado';
			$sts_largo = 'Pagado';
		break;
		case 'cancelled':
			$sts_corto = 'Cancelado';
			$sts_largo = 'Cancelado';

			$penalizado = get_post_meta( $id_reserva, 'penalizado', true );
			if( $penalizado == "YES" ){
				$sts_corto = 'Penalizado';
				$sts_largo = 'Cancelado con penalización';
			}
		break;
		// Modificacion Ángel Veloz
		case 'modified':
			$por = get_post_meta( $id_reserva, 'reserva_modificada', true );
			$sts_corto = 'Modificado';
			$sts_largo = 'Modificado por la reserva: '.$por;
		break;
	}

	return 	$result = [ 
		"reserva"  => $sts_reserva, 
		"pedido"   => $sts_pedido,
		"sts_corto"=> $sts_corto,
		"sts_largo"=> $sts_largo,
		"addTotal" => $addTotal,
	];

}

function photo_exists($path=""){
	$photo = (file_exists('../'.$path) && !empty($path))? 
		get_option('siteurl').'/'.$path : 
		get_option('siteurl')."/wp-content/themes/kmimos/images/noimg.png";
	return $photo;
}




function getMascotas($user_id){
	if(!$user_id>0){ return []; }

	global $wpdb;
	$mascotas_cliente = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_author = '{$user_id}' AND post_type='pets' AND post_status = 'publish'");
    $mascotas = array();
    foreach ($mascotas_cliente as $key => $mascota) {
        $metas = get_post_meta($mascota->ID);

        $anio = $metas["birthdate_pet"][0];
        $anio = str_replace("/", "-", $anio);
        $anio = strtotime($anio);
        $edad_time = time()-$anio;

        $edad = '';
        if( (date("Y", $edad_time)-1970) > 0 ){
	        $edad = (date("Y", $edad_time)-1970)." año(s) ";
        }
        $edad .= date("m", $edad_time)." mes(es)";
 
        $mascotas[] = array(
            "nombre" => $mascota->post_title,
            "raza" => $metas["breed_pet"][0],
            "edad" => $edad
        );
    }
	return $mascotas;
}

function getProduct( $num_reserva = 0 ){
	$services = [];

	global $wpdb;
	$sql = "	
		SELECT 
			i.meta_key as 'servicio',
			i.meta_value as 'descripcion'
		FROM wp_woocommerce_order_itemmeta as i
			-- Order_item_id
			LEFT JOIN wp_woocommerce_order_itemmeta as o ON ( o.meta_key = 'Reserva ID' and o.meta_value = $num_reserva )
			-- Reserva
			LEFT JOIN wp_posts as re ON re.ID = i.meta_value -- No. Reserva
		WHERE	
			i.meta_key like 'Servicios Adicionales%'
			and i.order_item_id = o.order_item_id
	";
	$services = $wpdb->get_results($sql);

	return $services;	
}

function getServices( $num_reserva = 0 ){
	$services = [];

	global $wpdb;
	$sql = "	
		SELECT 
			i.meta_key as 'servicio',
			i.meta_value as 'descripcion'
		FROM wp_woocommerce_order_itemmeta as i
			-- Order_item_id
			LEFT JOIN wp_woocommerce_order_itemmeta as o ON ( o.meta_key = 'Reserva ID' and o.meta_value = $num_reserva )
			-- Reserva
			LEFT JOIN wp_posts as re ON re.ID = i.meta_value -- No. Reserva
		WHERE	
			( i.meta_key like 'Servicios Adicionales%' or i.meta_key like 'Servicios de %' ) 
			and i.order_item_id = o.order_item_id
	";
	$services = $wpdb->get_results($sql);

	return $services;
}

function getMetaCliente( $user_id ){
	//$condicion = " AND m.meta_key IN ('first_name', 'last_name', 'user_referred')";
	//$result = get_metaUser($user_id, $condicion);
	$result = get_metaUser($user_id);
	$data = [
		'first_name' =>'', 
		'last_name' =>'', 
		'user_referred' =>'', 
	];
	if( !empty($result) ){
		foreach ($result['rows'] as $row) {
			$data[$row['meta_key']] = utf8_encode( $row['meta_value'] );
			//$data['cliente_nombre'] = utf8_encode( $row['meta_value'] );
		}
	}
	$data = merge_phone($data);
	return $data;
}

function getMetaCuidador( $user_id ){
	// $condicion = " AND m.meta_key IN ('first_name', 'last_name', 'user_referred')";
	// $result = get_metaUser($user_id, $condicion);
	$result = get_metaUser($user_id);
	$data = [
		'first_name' =>'', 
		'last_name' =>'', 
		'user_referred' =>'', 
	];
	if( !empty($result) ){
		foreach ($result['rows'] as $row) {
			$data[$row['meta_key']] = utf8_encode( $row['meta_value'] );
		}
	}
	$data = merge_phone($data);
	return $data;
}

function getMetaReserva( $post_id ){
	//$condicion = " AND meta_key IN ( '_booking_start', '_booking_end', '_booking_cost', 'modificacion_de', '_booking_order_item_id' )";
	$result = get_metaPost($post_id, $condicion);

	$data = [
		'_booking_start' =>'', 
		'_booking_end' =>'', 
		'_booking_cost' =>'', 
		'modificacion_de' =>'', 
		'_booking_order_item_id' =>'', 
	];
	if( !empty($result) ){
		foreach ($result['rows'] as $row) {
			$data[$row['meta_key']] = utf8_encode( $row['meta_value'] );
		}
	}
	return $data;	
}

function getMetaPedido( $post_id ){
	$condicion = " AND meta_key IN ( '_payment_method','_payment_method_title','_order_total','_wc_deposits_remaining' )";
	$result = get_metaPost($post_id, $condicion);
	$data = [
		'_payment_method' => '',
		'_payment_method_title' => '',
		'_order_total' => '',
		'_wc_deposits_remaining' => '',
	];
	if( !empty($result) ){
		foreach ($result['rows'] as $row) {
			$data[$row['meta_key']] = utf8_encode( $row['meta_value'] );
		}
	}
	return $data;	
}

function get_ubicacion_cuidador( $user_id ){
	global $wpdb;
	$sql = "
		SELECT ub.*
		from  ubicaciones as ub
			inner join cuidadores as u ON u.id = ub.cuidador  
 	 	WHERE u.user_id = $user_id
 	";
	$ubi = $wpdb->get_results($sql);
	$ubicacion=$ubi;

	$data = [
		"estado" => '',
		"municipio" => '',
		"sql" => $sql,
	];
	if(count($ubi)>0){
		$ubicacion = $ubi[0];

		$estado = explode('=', $ubicacion->estado);
		$munici = explode('=', $ubicacion->municipios);

		$est = $wpdb->get_results("select * from states as est where est.id = ".$estado[1]);
		if(count($est)>0){ 
			$est = $est[0];
			$data['estado'] = $est->name; 
		}

		$mun = $wpdb->get_results("select * from locations as mun where mun.id = ".$munici[1]);
		if(count($mun)>0){ 
			$mun = $mun[0];
			$data['municipio'] = $mun->name; 
		}

	}

	return $data;
}

function getReservas($desde="", $hasta=""){

	$filtro_adicional = "";

	if( !empty($desde) && !empty($hasta) ){
		$filtro_adicional = " 
			AND ( r.post_date_gmt >= '{$desde} 00:00:00' and  r.post_date_gmt <= '{$hasta} 23:59:59' )
		";
	}else{
		$filtro_adicional = " AND MONTH(r.post_date_gmt) = MONTH(NOW()) AND YEAR(r.post_date_gmt) = YEAR(NOW()) ";
	}

	global $wpdb;
	$sql = "
		SELECT 
			r.ID as 'nro_reserva',
 			DATE_FORMAT(r.post_date_gmt,'%Y-%m-%d') as 'fecha_solicitud',
 			r.post_status as 'estatus_reserva',
 			p.ID as 'nro_pedido',
 			p.post_status as 'estatus_pago', 			
			pr.post_title as 'producto_title',
			pr.post_name as 'producto_name',			
 			(du.meta_value -1) as  'nro_noches',
 			(IFNULL(mpe.meta_value,0) + IFNULL(mme.meta_value,0) + IFNULL(mgr.meta_value,0) + IFNULL(mgi.meta_value,0)) as nro_mascotas,
 			((du.meta_value -1) * ( IFNULL(mpe.meta_value,0) + IFNULL(mme.meta_value,0) + IFNULL(mgr.meta_value,0) + IFNULL(mgi.meta_value,0) )) as 'total_noches',

			pr.ID as producto_id,
			pr.post_name as post_name,
 			us.user_id as cuidador_id,
 			cl.ID as cliente_id

		from wp_posts as r
			LEFT JOIN wp_postmeta as rm ON rm.post_id = r.ID and rm.meta_key = '_booking_order_item_id' 
			LEFT JOIN wp_posts as p ON p.ID = r.post_parent

			LEFT JOIN wp_woocommerce_order_itemmeta as fe  ON (fe.order_item_id  = rm.meta_value and fe.meta_key  = 'Fecha de Reserva')
			LEFT JOIN wp_woocommerce_order_itemmeta as du  ON (du.order_item_id  = rm.meta_value and du.meta_key  = 'Duración')
			LEFT JOIN wp_woocommerce_order_itemmeta as mpe ON mpe.order_item_id = rm.meta_value and (mpe.meta_key = 'Mascotas Pequeños' or mpe.meta_key = 'Mascotas Pequeñas')
			LEFT JOIN wp_woocommerce_order_itemmeta as mme ON mme.order_item_id = rm.meta_value and (mme.meta_key = 'Mascotas Medianos' or mme.meta_key = 'Mascotas Medianas')
			LEFT JOIN wp_woocommerce_order_itemmeta as mgr ON (mgr.order_item_id = rm.meta_value and mgr.meta_key = 'Mascotas Grandes')
			LEFT JOIN wp_woocommerce_order_itemmeta as mgi ON (mgi.order_item_id = rm.meta_value and mgi.meta_key = 'Mascotas Gigantes')
			LEFT JOIN wp_woocommerce_order_itemmeta as pri ON (pri.order_item_id = rm.meta_value and pri.meta_key = '_product_id')
			LEFT JOIN wp_posts as pr ON pr.ID = pri.meta_value
			LEFT JOIN cuidadores as us ON us.user_id = pr.post_author
			LEFT JOIN wp_users as cl ON cl.ID = r.post_author
		WHERE r.post_type = 'wc_booking' 
			and not r.post_status like '%cart%' 
			and cl.ID > 0 
			and p.ID > 0
			{$filtro_adicional}
		ORDER BY r.ID desc
		;";

	$reservas = $wpdb->get_results($sql);
	return $reservas;
}


function Get_CouponCode($order_id,$coupon_code) {
	global $wpdb;
	$return = array();

	$query = "SELECT DISTINCT
        wc_items.order_item_name AS coupon_name,
        wc_itemmeta.meta_value AS coupon_discount_amount,
        postmeta.*

        FROM
        {$wpdb->prefix}woocommerce_order_items AS wc_items
		LEFT JOIN
        {$wpdb->prefix}woocommerce_order_itemmeta AS wc_itemmeta ON wc_items.order_item_id = wc_itemmeta.order_item_id
        LEFT JOIN
        {$wpdb->prefix}posts AS post ON post.post_title = wc_items.order_item_name
        LEFT JOIN
        {$wpdb->prefix}postmeta AS postmeta ON post.ID = postmeta.post_id

        WHERE
        wc_items.order_id = '{$order_id}' AND
        wc_items.order_item_type = 'coupon' AND
        wc_items.order_item_name LIKE '%{$coupon_code}%' AND
		wc_itemmeta.meta_key = 'discount_amount' ";
/*

	*/
	$coupons = $wpdb->get_results($query);

	if (!empty($coupons)) {
		foreach ($coupons as $key => $coupon) {
			//var_dump($coupon);
			$coupon_name = $coupon->coupon_name;

			if($coupon->meta_key=='coupon_amount'){
				if(!array_key_exists($coupon_name,$return)){
					$return[$coupon_name]=array();
				}
				$return[$coupon_name]['coupon_name'] = $coupon_name;
				$return[$coupon_name]['coupon_amount'] = $coupon->meta_value;
				//$return[$coupon_name]['coupon_amount'] = $coupon->meta_value;

			}else if($coupon->meta_key=='discount_type'){
				if(!array_key_exists($coupon_name,$return)){
					$return[$coupon_name]=array();
				}

				$return[$coupon_name]['discount_type'] = $coupon->meta_value;
			}

			//AMOUNT DISCOUNT
			$return[$coupon_name]['coupon_amount'] = $coupon->coupon_discount_amount;
		}
	}

	//var_dump($return);
	return $return;
}


function Get_SumCouponCode($order_id,$coupon_code,$total=0) {
	$coupons = Get_CouponCode($order_id,$coupon_code);
	$amount = 0;

	if(count($coupons)){
		foreach($coupons as $coupon){
			if($coupon['discount_type'] != 'percent'){
				$coupon_amount = $coupon['coupon_amount'];

			}else{
				$coupon_amount = $total*($coupon['coupon_amount']/100);
			}

			$coupon_amount = $coupon['coupon_amount'];
			$amount = $amount+$coupon_amount;
		}
	}
	return $amount;
}


function Get_NameCouponCode($order_id,$coupon_code) {
	$coupons = Get_CouponCode($order_id,$coupon_code);
	$name = array();

	if(count($coupons)){
		foreach($coupons as $coupon){
			if( $coupon['coupon_amount'] > 0 ){
				$name[] = $coupon['coupon_name'];
			}
		}
	}

	return implode(',',$name);
}


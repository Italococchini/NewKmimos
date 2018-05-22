<?php

require_once ( dirname(dirname(__DIR__)).'/conf/database.php' );

function get_fetch_assoc($sql){
	$db = new db();
	$data['rows'] = $db->select($sql);
	
	/*$data = ['info'=>[], 'rows'=>[]];
	if(isset($rows->num_rows)){
		if( $rows->num_rows > 0){
			$data['info'] = $rows;
			$data['rows'] = mysqli_fetch_all( $rows,MYSQLI_ASSOC);
		}
	}*/
	return $data;
}

function save( $tipo, $fecha, $param ){

	$id = 0;
	$sql = "select * from monitor_diario where fecha = '{$fecha}'";
	$data = get_fetch_assoc($sql);	
	$param = json_encode($param);

	// Actualizar registros
	if( isset($data['rows'][0]['id']) && $data['rows'][0]['id'] > 0 ){	
		$id = $data['rows'][0]['id'];
	}
	switch ($tipo) {
		case 'ventas':
			if( $id > 0 ){
				$sql = "update monitor_diario set reserva = '{$param}' where id = {$id}";	
			}else{
				$sql = "insert into monitor_diario (reserva, fecha) VALUES ('{$param}','{$fecha}')";	
			}
			break;
		case 'usuario':
			if( $id > 0 ){
				$sql = "update monitor_diario set cliente = '{$param}' where id = {$id}";	
			}else{
				$sql = "insert into monitor_diario (cliente, fecha) VALUES ('{$param}','{$fecha}')";	
			}
			break;
	}
	get_fetch_assoc( $sql );
	return $data;
}

// ***************************************
// Cargar listados de Reservas
// ***************************************
function getTotalClientes($desde="", $hasta=""){
	$sql = "
		SELECT 
			count(u.ID) as cant
		FROM wp_users as u
			LEFT JOIN cuidadores as c ON c.user_id = u.ID
		WHERE c.id is null 
			AND ( u.user_registered >= '{$desde} 00:00:00' 
				and  u.user_registered  <= '{$hasta} 23:59:59' )
	";

	$result = get_fetch_assoc($sql);
	if( isset($result['rows'][0]['cant']) ){
		return $result['rows'][0]['cant'];
	}else{
		return 0;
	}
}
function getUsuarios($desde="", $hasta=""){
	$sql = "
		SELECT 
			u.ID,
			u.user_email,
			u.user_registered, 
			c.id as cuidador_id
		FROM wp_users as u
			LEFT JOIN cuidadores as c ON c.user_id = u.ID
		WHERE c.id is null and (u.user_registered >= '{$desde} 00:00:00' 
				and  u.user_registered  <= '{$hasta} 23:59:59')
	";

	$result = get_fetch_assoc($sql);
	return $result;
}

function getReservasRecompra( $desde, $hasta ){
	
	//echo date('Y-m-d H:i:s');	

	$sql = "
		SELECT 
			DATE_FORMAT(r.post_date_gmt,'%Y-%m-%d') as 'fecha_solicitud',
			u.user_registered,
			u.ID,
			r.ID as reserva,
			r.post_parent as pedido
		FROM wp_posts as r
			LEFT JOIN wp_users as u ON u.ID = r.post_author
		WHERE r.post_type = 'wc_booking' 
			AND not r.post_status like '%cart%' 
			AND r.post_status = 'confirmed'
			AND r.post_date_gmt >= '{$desde}'
			AND r.post_date_gmt <= '{$hasta}'
			AND u.user_registered >= '{$desde}' 
			AND u.user_registered <= '{$hasta}'  
		";
	$reservas = get_fetch_assoc($sql);

	$_resultado = [];
	if( !empty($reservas['rows']) ){
		foreach ($reservas['rows'] as $reserva) {

			// buscar metadatos reservas
				$meta_reserva = get_fetch_assoc("SELECT * FROM wp_postmeta WHERE 
					post_id = {$reserva['reserva']} 
					AND meta_key in ( '_booking_order_item_id', '_booking_start', '_booking_end' )
				");
				foreach( $meta_reserva['rows'] as $meta ){
					$meta_reserva[$meta['meta_key']] = $meta['meta_value'];
				}
				unset($meta_reserva['info']);
				unset($meta_reserva['rows']);

			// buscar metadatos No. Mascotas
				$order_item_id = 0;
				if( isset($meta_reserva['_booking_order_item_id']) && $meta_reserva['_booking_order_item_id']>0 ){
					$meta_order_item = get_fetch_assoc("SELECT * FROM wp_woocommerce_order_itemmeta WHERE 
						order_item_id = {$meta_reserva['_booking_order_item_id']} 
						AND meta_key in ( 
							'Mascotas Pequeños', 
							'Mascotas Pequeñas',
							'Mascotas Medianos',
							'Mascotas Medianas',
							'Mascotas Grandes',
							'Mascotas Gigantes',
							'_product_id'
						)
					");
					$meta_order_item['total_mascotas'] = 0;
					foreach( $meta_order_item['rows'] as $meta ){
						switch ($meta['meta_key']) {
							case 'Mascotas Pequeños':
							case 'Mascotas Pequeñas':
								$meta_order_item['Mascotas_Pequenos'] = $meta['meta_value'];
								$meta_order_item['total_mascotas'] += $meta['meta_value'];
								break;
							case 'Mascotas Medianos':
							case 'Mascotas Medianas':
								$meta_order_item['Mascotas_Medianos'] = $meta['meta_value'];
								$meta_order_item['total_mascotas'] += $meta['meta_value'];
								break;
							case 'Mascotas Grandes':
								$meta_order_item['Mascotas_Grandes'] = $meta['meta_value'];
								$meta_order_item['total_mascotas'] += $meta['meta_value'];
								break;
							case 'Mascotas Gigantes':
								$meta_order_item['Mascotas_Gigantes'] = $meta['meta_value'];
								$meta_order_item['total_mascotas'] += $meta['meta_value'];
								break;
							default:
								$meta_order_item[$meta['meta_key']] = $meta['meta_value'];
								break;
						}
					}
				}
				unset($meta_order_item['info']);
				unset($meta_order_item['rows']);

			// buscar producto
				$producto = [];
				if( isset($meta_order_item['_product_id']) && $meta_order_item['_product_id']>0 ){
					$producto = get_fetch_assoc("SELECT post_name FROM wp_posts WHERE 
						ID = {$meta_order_item['_product_id']}
					");
					$producto = (isset($producto['rows'][0]))? $producto['rows'][0]: [];
				}		

			# ** *************************** **
			# Calcular
			# ** *************************** **
				$num_noches = dias_transcurridos(
						date_convert($meta_reserva['_booking_end'], 'd-m-Y'), 
						date_convert($meta_reserva['_booking_start'], 'd-m-Y') 
					);					

				if( isset($producto['post_name']) && !in_array('hospedaje', explode("-", $producto['post_name']))){
					$num_noches += 1;
				}

				$num_total_noches = $num_noches * $meta_order_item['total_mascotas'];

				$_fecha = date('mY', strtotime($reserva['fecha_solicitud']));
				if( isset($_resultado[ $_fecha ]) ){
					$_resultado[ $_fecha ] += $num_total_noches;
				}else{
					$_resultado[ $_fecha ] = $num_total_noches;
				}
		}
	}
	//echo date('Y-m-d H:i:s');	

	return $_resultado;
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
 			(IFNULL(mpe.meta_value,0) + IFNULL(mme.meta_value,0) + IFNULL(mgr.meta_value,0) + IFNULL(mgi.meta_value,0)) as nro_mascotas,
 			

			pr.ID as producto_id,
			pr.post_name as post_name,
 			us.user_id as cuidador_id,
 			cl.ID as cliente_id

		from wp_posts as r
			LEFT JOIN wp_postmeta as rm ON rm.post_id = r.ID and rm.meta_key = '_booking_order_item_id' 
			LEFT JOIN wp_posts as p ON p.ID = r.post_parent

			LEFT JOIN wp_woocommerce_order_itemmeta as fe  ON (fe.order_item_id  = rm.meta_value and fe.meta_key  = 'Fecha de Reserva')
			
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

	$reservas = get_fetch_assoc($sql);
	return $reservas;
}

function getMetaUsuario( $user_id ){ 
	$condicion = " AND meta_key IN ( 'user_gender', 'user_age', 'user_referred' )";
	$result = get_metaUser($user_id, $condicion);
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

function getMetaReserva( $post_id ){
	$condicion = " AND meta_key IN ( '_booking_start', '_booking_end', '_booking_cost', 'modificacion_de', '_booking_order_item_id', 'reserva_modificada', 'penalizado', '_booking_flash' )";
	$result = get_metaPost($post_id, $condicion);

	$data = [
		'_booking_start' =>'', 
		'_booking_end' =>'', 
		'_booking_cost' =>'', 
		'_booking_order_item_id' =>'', 
		'_booking_flash'=>'',
		'penalizado' =>'', 
		'reserva_modificada'=>'',
		'modificacion_de' =>'', 
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

function getTipoPagoReserva( $meta_reserva ){
	$deposito = get_fetch_assoc("
				SELECT meta_value 
				FROM wp_woocommerce_order_itemmeta 
				WHERE 
					order_item_id = {$meta_reserva['_booking_order_item_id']} 
					AND meta_key = '_wc_deposit_meta' 
			");

	foreach ($deposito['rows'] as $key => $value) {
		$deposito = unserialize($value['meta_value']);
	}

	return $deposito;
}

function getNochesRecomprasClientesNuevos( $desde, $hasta ){


}

function getRecompras( $desde="", $hasta="" ){
 
	$result = [];
	$sql = "
		SELECT 
			count(cant) as cant, 
			DATE_FORMAT( post_date_gmt, '%Y-%m-01' ) as mes
		FROM (SELECT 
				count(ID) as cant,
				post_author,
				post_date_gmt
			FROM wp_posts
			WHERE post_type = 'wc_booking' 
				AND not post_status like '%cart%'
				AND post_status = 'confirmed' 
				AND post_date_gmt >= '{$desde}' 
				AND post_date_gmt <= '{$hasta}'  
			GROUP BY post_author ) as temp
		WHERE temp.cant > 1
		GROUP BY DATE_FORMAT( post_date_gmt, '%Y-%m-01' )
	";

	$result = get_fetch_assoc($sql);
	return $result;
}

function getCountReservas( $author_id=0, $interval=12, $desde="", $hasta="" ){

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

function get_status($sts_reserva, $sts_pedido, $forma_pago="", $meta=[]){
	
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

			if( $meta['penalizado'] == "YES" ){
				$sts_corto = 'Penalizado';
				$sts_largo = 'Cancelado con penalización';
			}
		break;
		// Modificacion Ángel Veloz
		case 'modified':
			$por = $meta['reserva_modificada'];
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
			i.meta_key like 'Servicios Adicionales%'
			and i.order_item_id = o.order_item_id
	";
	$services = $wpdb->get_results($sql);

	return $services;
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

function getUserByEmail($user_email=""){
	$sql = "
		SELECT *
		FROM wp_users
		WHERE user_email = '{$user_email}'
	";
	$result = get_fetch_assoc($sql);
	return $result;	
}

function getEdad($fecha){
	$fecha = str_replace("/","-",$fecha);
	$hoy = date('Y/m/d');

	$diff = abs(strtotime($hoy) - strtotime($fecha) );
	$years = floor($diff / (365*60*60*24)); 
	$desc = " Años";
	$edad = $years;
	if($edad==0){
		$months  = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 
		$edad = $months;
		$desc = ($edad > 1) ? " Meses" : " Mes";
	}
	if($edad==0){
		$days  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
		$edad = $days;
		$desc = " Días";
	}

	return $edad . $desc;
}

function get_razas(){
	global $wpdb;
	$sql = "SELECT * FROM razas ";
	$result = $wpdb->get_results($sql);
	$razas = [];
	foreach ($result as $raza) {
		$razas[$raza->id] = $raza->nombre;
	}
	return $razas;
}

function dias_transcurridos($fecha_i,$fecha_f){
	$dias	= (strtotime($fecha_i)-strtotime($fecha_f))/86400;
	$dias 	= abs($dias); $dias = floor($dias);		
	return $dias;
}

function get_metaUser($user_id=0, $condicion=''){
	$sql = "
		SELECT u.user_email, m.*
		FROM wp_users as u 
			INNER JOIN wp_usermeta as m ON m.user_id = u.ID
		WHERE 
			m.user_id = {$user_id} 
			{$condicion}
	";
	$result = get_fetch_assoc($sql);
	return $result;	
}

function date_convert( $str_date, $format = 'd-m-Y H:i:s', $totime=true ){
	$fecha = $str_date;
	if(!empty($str_date)){
		if($totime){
			$time = strtotime($str_date);
		}
		$fecha = date($format,$time);
	}
	return $fecha;
}

function currency_format( $str, $signo="$ ", $miles=",", $decimal="." ){
	if(!empty($str)){
		$str = $signo.number_format($str, 2, $decimal, $miles);
	}else{
		$str = $signo."0";
	}
	return $str;
}

function get_metaPost($post_id=0, $condicion=''){
	$sql = "
		SELECT u.meta_key, u.meta_value, u.post_id
		FROM wp_postmeta as u 
		WHERE 
			u.post_id = {$post_id} 
			{$condicion}
	";	
	$result = get_fetch_assoc($sql);
	return $result;	
}

function merge_phone($param, $separador=' / '){
	$param['phone'] = isset($param['user_phone']) ? 
		$param['user_phone'] : ''; 
	if(isset($param['user_mobile'])){ 
		$param['phone'] .= (!empty($param['phone']))? $separador : '' ;
		$param['phone'] .= $param['user_mobile'];
	}

	return $param;
}

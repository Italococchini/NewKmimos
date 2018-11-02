<?php

    if(!function_exists('kmimos_session')){
        function kmimos_session(){
            if( !isset($_SESSION) ){ session_start(); }
            global $current_user;
            $user_id = md5($current_user->ID);

            if( isset( $_SESSION["MR_".$user_id] ) ){
                return $_SESSION["MR_".$user_id];
            }else{
                return false;
            }

        }
    }

    if(!function_exists('kmimos_set_session')){
        function kmimos_set_session($DS){
            if( !isset($_SESSION) ){ session_start(); }
            global $current_user;
            $user_id = md5($current_user->ID);
            $_SESSION["MR_".$user_id] = $DS;
        }
    }

    if(!function_exists('kmimos_quitar_session')){
        function kmimos_quitar_session(){
            if( !isset($_SESSION) ){ session_start(); }
            global $current_user;
            $user_id = md5($current_user->ID);
            unset($_SESSION["MR_".$user_id]);
        }
    }

    if(!function_exists('kmimos_get_kmisaldo')){
        function kmimos_get_kmisaldo(){
            global $current_user;
            return get_user_meta($current_user->ID, "kmisaldo", true)+0;
            
        }
    }

    if(!function_exists('kmimos_set_kmisaldo')){
        function kmimos_set_kmisaldo($id_cliente, $id_orden, $id_reserva, $usuario = ""){
            global $wpdb;

            $status = $wpdb->get_var("SELECT post_status FROM wp_posts WHERE ID = {$id_orden}");
            $metas_orden = get_post_meta($id_orden);
            $metas_reserva  = get_post_meta( $id_reserva );

            $hoy = time();
            $inicia = strtotime( $metas_reserva["_booking_start"][0] );
            $total_reserva = $metas_reserva["_booking_cost"][0];
            $penalizar = false;
            if( $inicia-$hoy <= 86400 && $usuario == "CLI" ){
                $penalizar = true;
            }

            $itemmetas = $wpdb->get_results("SELECT * FROM wp_woocommerce_order_itemmeta WHERE order_item_id = '{$metas_reserva['_booking_order_item_id'][0]}' AND (meta_key = '_wc_deposit_meta' OR meta_key = '_line_total' OR meta_key = '_line_subtotal' )"); 

            $items = array();
            foreach ($itemmetas as $key => $value) {
                $items[ $value->meta_key ] = $value->meta_value;
            }

            $deposito = unserialize( $items['_wc_deposit_meta'] );
            $saldo = 0;
            
            if( $deposito['enable'] == 'yes' ){
                $saldo = $deposito['deposit'];
            }else{
                $saldo = $items['_line_subtotal'];
                $saldo -= $metas_orden['_cart_discount'][0];
            }
        
            $descuento = 0;
            $order_item_id = $wpdb->get_var("SELECT order_item_id FROM wp_woocommerce_order_items WHERE order_id = '{$id_orden}' AND order_item_type = 'coupon' AND order_item_name LIKE '%saldo-%'"); 
            if( $order_item_id != '' ){
                $descuento = $wpdb->get_var("SELECT meta_value FROM wp_woocommerce_order_itemmeta WHERE order_item_id = '{$order_item_id}' AND meta_key = 'discount_amount' ");
            }

            $otros_cupones = $wpdb->get_results("SELECT * FROM wp_woocommerce_order_items WHERE order_id = '{$id_orden}' AND order_item_type = 'coupon' AND order_item_name NOT LIKE '%saldo-%'");
            foreach ($otros_cupones as $key => $value) {
                $cupon_id = $wpdb->get_var("SELECT ID FROM wp_posts WHERE post_title = '{$value->order_item_name}'");
                $wpdb->query("DELETE FROM wp_postmeta WHERE post_id = '{$cupon_id}' AND meta_key = '_used_by' AND meta_value = '{$id_cliente}'");
            }

            if($status == 'wc-on-hold' && $metas_orden['_payment_method'][0] == 'tienda'){
                $saldo = $descuento; 
            }else{
                $saldo += $descuento;                
            }

            if( $status != "wc-confirmed" ){
                $penalizar = false;
            }

            if( !isset($_SESSION) ){ session_start(); }
            if(  $_SESSION['admin_sub_login'] == 'YES' ){
                $penalizar = false;
            }

            $saldo_persistente = get_user_meta($id_cliente, "kmisaldo", true)+0;
            
            $cfdi = false;
            if($penalizar){
                $comision = $total_reserva-($total_reserva/getComision());
                $saldo -= $comision;
                update_post_meta($id_reserva, "penalizado", "YES");
                $cfdi = factura_penalizacion( $id_cliente, $id_orden, $id_reserva, $comision );
            }

            $__saldo = $saldo_persistente+$saldo;
            $__saldo = ( $__saldo < 0 )? $saldo_persistente : $__saldo ;

            update_user_meta($id_cliente, "kmisaldo", $__saldo);

            return $cfdi;
        }
    }



    if( !function_exists('factura_penalizacion') ){
        function factura_penalizacion( $user_id, $id_orden, $id_reserva , $monto ){
            global $wpdb;

            return false;

//            include( dirname(dirname(dirname(dirname(__DIR__))))."/themes/kmimos/lib/enlaceFiscal/CFDI.php" );
            
            $cfdi = false;
            $consecutivo = 0;
            $factura = $CFDI->db->get_results( "select * from facturas where reserva_id = {$id_reserva}");
            if( !empty($factura) ){
                $consecutivo = count($factura);
            }

            // Desglose de reserva
            $data_reserva = kmimos_desglose_reserva_data($id_orden, true);

            if( validar_datos_facturacion( $data_reserva['cliente']['id'] ) ){
    
                // Datos complementarios CFDI
                    $data_reserva['receptor']['rfc'] = get_user_meta( $user_id, 'billing_rfc', true );
                    $data_reserva['receptor']['razon_social'] = get_user_meta( $user_id, 'billing_razon_social', true );
                    $data_reserva['receptor']['uso_cfdi'] = get_user_meta( $user_id, "billing_uso_cfdi", true); 
                    $data_reserva['receptor']['regimen_fiscal'] = get_user_meta( $user_id, "billing_regimen_fiscal", true); 
                    $data_reserva['receptor']['calle'] = get_user_meta( $user_id, "billing_calle", true); 
                    $data_reserva['receptor']['postcode'] = get_user_meta( $user_id, "billing_postcode", true); 
                    $data_reserva['receptor']['noExterior'] = get_user_meta( $user_id, "billing_noExterior", true); 
                    $data_reserva['receptor']['noInterior'] = get_user_meta( $user_id, "billing_noInterior", true); 
                    $data_reserva['receptor']['estado'] = get_user_meta( $user_id, "billing_state", true);
                    $data_reserva['receptor']['city'] = get_user_meta( $user_id, "billing_city", true);
                    $data_reserva['receptor']['colonia'] = get_user_meta( $user_id, "billing_colonia", true);
                    $data_reserva['receptor']['localidad'] = get_user_meta( $user_id, "billing_localidad", true);
                    $data_reserva['receptor']['estado'] = $CFDI->db->get_var('select name from states where country_id=1 and id = '.$data_reserva['receptor']['estado'], 'name' );

                    $user_id = $data_reserva['cliente']['id'];  
        
                // Quitar servicios de variaciones y adicionales
                    unset( $data_reserva['servicio']['variaciones'] );
                    unset( $data_reserva['servicio']['adicionales'] );
                    unset( $data_reserva['servicio']['transporte'] );

                // Editar monto de facturacion
                    $data_reserva['servicio']['desglose']['total'] = $monto;
                    $data_reserva['servicio']['desglose']['deposit'] = $monto;
                    $data_reserva['servicio']['desglose']['reembolsar'] = 0;
                    $data_reserva['servicio']['desglose']['descuento'] = 0;

                // buscar consecutivo de facturas
                    $data_reserva['consecutivo'] = $consecutivo;

                // Agregar partida de penalizacion
                    $data_reserva['servicio']['penalizado'] = [
                        '0'=> [ 'total' => $monto ],
                    ];

                // Facturar
                    $AckEnlaceFiscal = $CFDI->generar_Cfdi_Cliente($data_reserva);
                    if( !empty($AckEnlaceFiscal['ack']) ){
                        $ack = json_decode($AckEnlaceFiscal['ack']);

                        // Datos complementarios
                        $datos['comentario'] = 'Cobro de comision por penalizacion';
                        $datos['subtotal'] = $AckEnlaceFiscal['data']['CFDi']['subTotal'];
                        $datos['impuesto'] = $AckEnlaceFiscal['data']['CFDi']['Impuestos']['Totales']['traslados'];
                        $datos['total'] = $AckEnlaceFiscal['data']['CFDi']['total'];

                        $CFDI->guardarCfdi( 'cliente', $data_reserva, $ack );

                        $cfdi = $ack;
                    }

            }else{
                $cfdi = [
                    'error'=>'SI', 
                    'msg'=>'Error al emitir la factura, datos invalidos o incompletos'
                ];
            }

            return $cfdi;
        }
    }

    if(!function_exists('kmimos_cupon_saldo')){
        function kmimos_cupon_saldo($monto_cupon){
            global $wpdb;
            
            global $current_user;
            $id_cupon = $wpdb->get_var("SELECT ID FROM wp_posts WHERE post_name='saldo-{$current_user->ID}'");
            if( $id_cupon == NULL ){
                date_default_timezone_set('America/Mexico_City');
                $hoy = date("Y-m-d H:i:s");
                $id_cupon = $wpdb->insert('wp_posts', array(
                    "ID" => NULL,
                    "post_author" => $current_user->ID,
                    "post_date" => $hoy,
                    "post_date_gmt" => $hoy,
                    "post_content" => "",
                    "post_title" => "saldo-".$current_user->ID,
                    "post_excerpt" => "",
                    "post_status" => "publish",
                    "comment_status" => "closed",
                    "ping_status" => "closed",
                    "post_password" => "",
                    "post_name" => "saldo-".$current_user->ID,
                    "to_ping" => "",
                    "pinged" => "",
                    "post_modified" => $hoy,
                    "post_modified_gmt" => $hoy,
                    "post_content_filtered" => "",
                    "post_parent" => 0,
                    "guid" => get_home_url()."/?post_type=shop_coupon&#038;p=",
                    "menu_order" => 0,
                    "post_type" => "shop_coupon",
                    "post_mime_type" => "",
                    "comment_count" => 0
                ));
                $id_cupon = $wpdb->get_var("SELECT ID FROM wp_posts WHERE post_name='saldo-{$current_user->ID}'");
                $wpdb->query("UPDATE wp_posts SET guid = '".get_home_url()."/?post_type=shop_coupon&#038;p=".$id_cupon."' WHERE ID = ".$id_cupon);
                $wpdb->query("
                    INSERT INTO wp_postmeta VALUES
                        (NULL, ".$id_cupon.", 'discount_type', 'fixed_cart'),
                        (NULL, ".$id_cupon.", 'coupon_amount', '{$monto_cupon}'),
                        (NULL, ".$id_cupon.", 'individual_use', 'no'),
                        (NULL, ".$id_cupon.", 'product_ids', ''),
                        (NULL, ".$id_cupon.", 'exclude_product_ids', ''),
                        (NULL, ".$id_cupon.", 'usage_limit', '0'),
                        (NULL, ".$id_cupon.", 'usage_limit_per_user', '0'),
                        (NULL, ".$id_cupon.", 'limit_usage_to_x_items', ''),
                        (NULL, ".$id_cupon.", 'expiry_date', ''),
                        (NULL, ".$id_cupon.", 'free_shipping', 'no'),
                        (NULL, ".$id_cupon.", 'exclude_sale_items', 'no'),
                        (NULL, ".$id_cupon.", 'product_categories', 'a:0:{}'),
                        (NULL, ".$id_cupon.", 'exclude_product_categories', 'a:0:{}'),
                        (NULL, ".$id_cupon.", 'minimum_amount', ''),
                        (NULL, ".$id_cupon.", 'maximum_amount', ''),                    
                        (NULL, ".$id_cupon.", 'customer_email', 'a:0:{}');
                ");
            }else{
                $sqls = array(
                    "UPDATE wp_postmeta SET meta_value = '0' WHERE post_id = ".$id_cupon." AND meta_key = 'usage_limit'",
                    "UPDATE wp_postmeta SET meta_value = '0' WHERE post_id = ".$id_cupon." AND meta_key = 'usage_limit_per_user'",
                    "UPDATE wp_postmeta SET meta_value = '{$monto_cupon}' WHERE post_id = ".$id_cupon." AND meta_key = 'coupon_amount'"
                );
                foreach ($sqls as $sql) {
                    $wpdb->query($sql);
                }
            }

            return "saldo-".$current_user->ID;
        }
    }
        
    if( !function_exists('kmimos_aplicar_cupon') ){
        function kmimos_aplicar_cupon(){
            $DS = kmimos_session();
            if( $DS ){
                $cupones = array();

                // Generador automatico de cupon de saldo
                    if( $DS["monto_cupon"] > 0){ 
                        $params = array(
                            "monto_cupon" => $DS["monto_cupon"],
                            "servicio" => $DS["servicio"],
                            "manana" => date('Y-m-d', time()+84600)." 00:00:00"
                        );
                        $cupones[] = kmimos_cupon_saldo($params);
                        
                    }
                // Fin Generador automatico de cupon de saldo

                if( count($cupones) > 0 ){
                    foreach ($cupones as $cupon) {
                         if( !WC()->cart->has_discount( $cupon ) ){
                            WC()->cart->add_discount( $cupon );
                        }
                    }
                }
            }
        }
    }
        
    if( !function_exists('kmimos_vista_cupones') ){
        function kmimos_vista_cupones(){
            foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                    <th>
                        <?php 
                            if( substr($coupon->code, 0, 5) != "saldo" ){
                                wc_cart_totals_coupon_label( $coupon ); 
                            }else{
                                echo "<span class='texto_kmimos'>Descuento por saldo a favor: </span>";
                            }
                        ?>
                    </th>
                    <td data-title="<?php wc_cart_totals_coupon_label( $coupon ); ?>">
                        <?php 

                            if( substr($coupon->code, 0, 5) == "saldo" ){
                                if ( $amount = WC()->cart->get_coupon_discount_amount( $coupon->code, WC()->cart->display_cart_ex_tax ) ) {
                                    $discount_html = wc_price( $amount );
                                    echo "<span class='texto_kmimos' style='font-weight: 800;'>".$discount_html."</span>";
                                } else {
                                    $discount_html = '';
                                }
                            }else{
                                wc_cart_totals_coupon_html( $coupon ); 
                            }
                        ?>
                    </td>
                </tr>
            <?php endforeach;
        }
    }
        
    if( !function_exists('kmimos_saldo_titulo') ){
        function kmimos_saldo_titulo(){
            return "Saldo a favor";
        }
    }

    if(!function_exists('kmimos_desglose_reserva')){

        function kmimos_borrar_formato_numerico($valor){
            /*$valor = str_replace(",", "", $valor);
            $valor = str_replace(".", ",", $valor);*/
            return $valor+0;
        }

        function kmimos_format_adicionales($valor, $txt){
            preg_match_all("#;(.*?)\)#", $valor, $matches);
            return array(
                $txt,
                kmimos_borrar_formato_numerico( $matches[1][0] )
            );
        }

        function kmimos_adicionales($orden_items){
            $adicionales_array = array();
            $transporte  = array();
            foreach ($orden_items as $key => $value) {

                switch ($value->meta_value) {

                    case 'Transp. Sencillo - Rutas Cortas':
                        $transporte[] = kmimos_format_adicionales($value->meta_key, $value->meta_value);
                    break;

                    case 'Transp. Sencillo - Rutas Medias':
                        $transporte[] = kmimos_format_adicionales($value->meta_key, $value->meta_value);
                    break;

                    case 'Transp. Sencillo - Rutas Largas':
                        $transporte[] = kmimos_format_adicionales($value->meta_key, $value->meta_value);
                    break;

                    case 'Transp. Redondo - Rutas Cortas':
                        $transporte[] = kmimos_format_adicionales($value->meta_key, $value->meta_value);
                    break;

                    case 'Transp. Redondo - Rutas Medias':
                        $transporte[] = kmimos_format_adicionales($value->meta_key, $value->meta_value);
                    break;

                    case 'Transp. Redondo - Rutas Largas':
                        $transporte[] = kmimos_format_adicionales($value->meta_key, $value->meta_value);
                    break;

                    case 'Baño (precio por mascota)':
                        $adicionales_array[] = kmimos_format_adicionales($value->meta_key, 'Baño');
                    break;

                    case 'Ba&ntilde;o (precio por mascota)':
                        $adicionales_array[] = kmimos_format_adicionales($value->meta_key, 'Baño');
                    break;
                    
                    case 'Corte de Pelo y U&ntilde;as (precio por mascota)':
                        $adicionales_array[] = kmimos_format_adicionales($value->meta_key, 'Corte de Pelo y Uñas');
                    break;
                    
                    case 'Corte de Pelo y Uñas (precio por mascota)':
                        $adicionales_array[] = kmimos_format_adicionales($value->meta_key, 'Corte de Pelo y Uñas');
                    break;
                    
                    case 'Visita al Veterinario (precio por mascota)':
                        $adicionales_array[] = kmimos_format_adicionales($value->meta_key, 'Visita al Veterinario');
                    break;
                    
                    case 'Limpieza Dental (precio por mascota)':
                        $adicionales_array[] = kmimos_format_adicionales($value->meta_key, 'Limpieza Dental');
                    break;
                    
                    case 'Acupuntura (precio por mascota)':
                        $adicionales_array[] = kmimos_format_adicionales($value->meta_key, 'Acupuntura');
                    break;

                }
            }

            return array(
                "adicionales" => $adicionales_array,
                "transporte" => $transporte
            );
        }

        function kmimos_desglose_reserva($id, $is_mail = false){

            global $wpdb;

            /* Reserva y Orden */
                $reserva = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_type = 'wc_booking' AND post_parent = '".$id."'");

                $metas_orden = get_post_meta($id);
                $metas_reserva = get_post_meta( $reserva->ID );

            /* Producto */
                $producto = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = '".$metas_reserva['_booking_product_id'][0]."'");

                $tipo_servicio = explode("-", $producto->post_title);
                $tipo_servicio = $tipo_servicio[0];

                $metas_producto = get_post_meta( $producto->ID );


            $inicio = $metas_reserva['_booking_start'][0];
            $fin    = $metas_reserva['_booking_end'][0];

            $xini = strtotime( substr($inicio, 0, 4)."-".substr($inicio, 4, 2)."-".substr($inicio, 6, 2) );
            $xfin = strtotime( substr($fin, 0, 4)."-".substr($fin, 4, 2)."-".substr($fin, 6, 2) );

            $inicio = substr($inicio, 6, 2) ."/".substr($inicio, 4, 2)."/".substr($inicio, 0, 4);
            $fin    = substr($fin, 6, 2)    ."/".substr($fin, 4, 2)   ."/".substr($fin, 0, 4);

            $id_orden_item = $metas_reserva['_booking_order_item_id'][0];

            $orden_item = $wpdb->get_results("SELECT * FROM wp_woocommerce_order_itemmeta WHERE order_item_id = '".$id_orden_item."'");

            $data_temp = kmimos_adicionales($orden_item);
            $adicionales_array = $data_temp["adicionales"];
            $transporte  = $data_temp["transporte"];

            $detalles_reserva = array();
            foreach ($orden_item as $key => $value) {
                $detalles_reserva[$value->meta_key] = $value->meta_value;
            }

            $variaciones_array = array(
                "pequen"  => 'Mascotas Pequeños', 
                "median"  => 'Mascotas Medianos', 
                "grandes"   => "Mascotas Grandes", 
                "gigantes"  => "Mascotas Gigantes",
                "peque" => "Mascotas Pequeñas", 
                "media" => "Mascotas Medianas"
            );

            $txts = array(
                "pequen"  => 'Mascotas Pequeños', 
                "median"  => 'Mascotas Medianos', 
                "grandes"   => "Mascotas Grandes", 
                "gigantes"  => "Mascotas Gigantes",
                "peque" => "Mascotas Pequeñas", 
                "media" => "Mascotas Medianas"
            );

            $dias = ceil(((($xfin - $xini)/60)/60)/24);

            $dias_noches = "Noche(s)";
            if( trim($tipo_servicio) != "Hospedaje" ){
                $dias_noches = "Día(s)";
                $dias++;
            }

            $styles_celdas_left   = "padding: 3px; border-bottom: solid 1px #cccccc;";
            $styles_celdas_center = "padding: 3px; border-bottom: solid 1px #cccccc;";
            $styles_celdas_right  = "padding: 3px; border-bottom: solid 1px #cccccc;";
            $styles_celdas_title  = "padding: 3px; border-bottom: solid 1px #cccccc;  font-weight: 600;";


            if( $is_mail ){
                $styles_celdas_left   = "padding: 3px; border-bottom: solid 1px #00d2b7; border-left: solid 1px #00d2b7; text-align: left;";
                $styles_celdas_center = "padding: 3px; border-bottom: solid 1px #00d2b7;";
                $styles_celdas_right  = "padding: 3px; border-bottom: solid 1px #00d2b7; border-right: solid 1px #00d2b7;";
                $styles_celdas_title  = "padding: 3px; background: #00d2b7; border-left: solid 1px #00d2b7; font-weight: 600;";

                $nota_cuidador = "color: #60cbac;";
            }

            $info = kmimos_get_info_syte();

            $detalle_largo = true;
            if( isset($metas_reserva['_booking_type']) ){
                $detalle_largo = false;
            }

            $variaciones = ''; $grupo = 0;
            foreach ($variaciones_array as $key => $value) {
                if( isset( $detalles_reserva[$value] ) ){

                    $variacion_ID = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_parent={$producto->ID} AND post_name LIKE '%{$key}%' ");
                    $metas_variacion = get_post_meta($variacion_ID);

                    $unitario = $metas_producto['_price'][0]+$metas_variacion['block_cost'][0];

                    if( $detalle_largo ){
                        $precios_detalle = '
                            <td style="'.$styles_celdas_center.'" align="right"> '.$info["mon_izq"].' '.number_format( $unitario, 2, ',', '.').' '.$info["mon_der"].' </td>
                            <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( ($unitario*$detalles_reserva[$value]*$dias), 2, ',', '.').' '.$info["mon_der"].' </td>';
                    }else{
                        $precios_detalle = '
                            <td style="'.$styles_celdas_center.'" align="right"> </td>
                            <td style="'.$styles_celdas_right.'" align="right"> </td>';
                    }

                    $variaciones .= '
                    <tr>
                        <td style="'.$styles_celdas_left.'"> '.$txts[$key].' </td>
                        <td style="'.$styles_celdas_center.'" align="center"> '.$detalles_reserva[$value].' </td>
                        <td style="'.$styles_celdas_center.'" align="center"> '.$dias.' '.$dias_noches.' </td>
                        '.$precios_detalle.'
                    </tr>';

                    $grupo += $detalles_reserva[$value];
                }
            }

            if( count($adicionales_array) > 0 ){

                $adicionales = '<tr> <td style="'.$styles_celdas_title.'" colspan=5> Servicios Adicionales </td> </tr>';
                foreach ($adicionales_array as $key => $value) {
                    $servicio = $value[0];
                    $costo = ($value[1]);

                    if( $detalle_largo ){
                        $precios_detalle = '
                            <td style="'.$styles_celdas_center.'" align="right"> '.$info["mon_izq"].' '.number_format( $costo, 2, ',', '.').' '.$info["mon_der"].' </td>
                            <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( ($costo*$grupo), 2, ',', '.').' '.$info["mon_der"].' </td>';
                    }else{
                        $precios_detalle = '
                            <td style="'.$styles_celdas_center.'" align="right"> </td>
                            <td style="'.$styles_celdas_right.'" align="right"> </td>';
                    }

                    $adicionales .= '
                    <tr>
                        <td style="'.$styles_celdas_left.'" colspan=2> '.$servicio.' </td>
                        <td style="'.$styles_celdas_center.'" align="center"> '.$grupo.' Mascota(s) </td>
                        '.$precios_detalle.'
                    </tr>';
                }

            }

            if( count($transporte) > 0 ){
                
                $transporte_str = '<tr> <td style="'.$styles_celdas_title.'" colspan=5> Servicio de Transporte </td> </tr>';
                foreach ($transporte as $key => $value) {
                    $servicio = $value[0];
                    $costo = ($value[1]);

                    if( $detalle_largo ){
                        $precios_detalle = '
                            <td style="'.$styles_celdas_center.'" align="right"> '.$info["mon_izq"].' '.number_format( $costo, 2, ',', '.').' '.$info["mon_der"].' </td>
                            <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $costo, 2, ',', '.').' '.$info["mon_der"].' </td>';
                    }else{
                        $precios_detalle = '
                            <td style="'.$styles_celdas_center.'" align="right"> </td>
                            <td style="'.$styles_celdas_right.'" align="right"> </td>';
                    }
                    $transporte_str .= '
                    <tr>
                        <td style="'.$styles_celdas_left.'" colspan=2> '.$servicio.' </td>
                        <td style="'.$styles_celdas_center.'" align="center"> Precio por Grupo </td>
                        '.$precios_detalle.'
                    </tr>';
                }

            }

            $pago = ($detalles_reserva['_line_subtotal']);
            $remanente = unserialize($detalles_reserva['_wc_deposit_meta']);

            $reembolsar = "";

            if( $remanente['enable'] == "no" ){
                $remanente['deposit'] = $pago;
                if( $metas_orden["_cart_discount"][0]+0 > 0 ){
                    $descuento_total = '
                        <tr>
                            <td></td>
                            <td></td>
                            <th colspan=2 style="'.$styles_celdas_left.'">Descuento</th>
                            <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $metas_orden["_cart_discount"][0], 2, ',', '.').' '.$info["mon_der"].' </td>
                        </tr>
                    ';

                    $remanente['deposit'] = $remanente['deposit']-$metas_orden["_cart_discount"][0];
                }
            }else{
                if( $metas_orden["_cart_discount"][0]+0 > 0 ){
                    $descuento_parcial = '
                        <tr>
                            <td></td>
                            <td></td>
                            <th colspan=2 style="'.$styles_celdas_left.'">Descuento</th>
                            <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $metas_orden["_cart_discount"][0], 2, ',', '.').' '.$info["mon_der"].' </td>
                        </tr>
                    ';

                    $remanente['remaining'] = $remanente['remaining']-$metas_orden["_cart_discount"][0];

                    $diferencia = ( ($pago/1.2) - $remanente['remaining'] );

                    if( $diferencia > 0 ){
                        $reembolsar = '
                            <tr>
                                <td></td>
                                <td></td>
                                <th colspan=2 class="texto_kmimos" style="'.$styles_celdas_left.' '.$nota_cuidador.'">Kmimos te reembolsará</th>
                                <td class="texto_kmimos" style="'.$styles_celdas_right.' '.$nota_cuidador.' font-weight: 600;" align="right"> '.$info["mon_izq"].' '.number_format( $diferencia, 2, ',', '.').' '.$info["mon_der"].' </td>
                            </tr>
                        ';
                    }
                        
                }
            }

            if( $metas_orden["_payment_method"][0] == "openpay_stores" ){
                $totales = '
                    <tr>
                        <td></td>
                        <td></td>
                        <th colspan=2 style="'.$styles_celdas_left.'">Total</th>
                        <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $pago, 2, ',', '.').' '.$info["mon_der"].' </td>
                    </tr>
                    '.$descuento_total.'
                    <tr>
                        <td></td>
                        <td></td>
                        <th colspan=2 style="'.$styles_celdas_left.'">Pago en Tienda</th>
                        <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $remanente['deposit'], 2, ',', '.').' '.$info["mon_der"].' </td>
                    </tr>
                    '.$descuento_parcial.'
                    <tr>
                        <td></td>
                        <td></td>
                        <th colspan=2 style="'.$styles_celdas_left.'">Cliente debe pagar al Cuidador:<div style="color: red;">en efectivo, al llevar a la mascota</div></th>
                        <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $remanente['remaining'], 2, ',', '.').' '.$info["mon_der"].' </td>
                    </tr>
                ';
            }else{

                if( $detalle_largo ){
                    $precios_detalle = '
                        <tr>
                            <td></td>
                            <td></td>
                            <th colspan=2 style="'.$styles_celdas_left.'">Cliente debe pagar al Cuidador:<div style="color: red;">en efectivo, al llevar a la mascota</div></th>
                            <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $remanente['remaining'], 2, ',', '.').' '.$info["mon_der"].' </td>
                        </tr>';
                }else{
                    $precios_detalle = '';
                }

                if( $remanente['deposit']+0 > 0 ){
                    $pagado = '
                    <tr>
                        <td></td>
                        <td></td>
                        <th colspan=2 style="'.$styles_celdas_left.'">Pagado</th>
                        <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $remanente['deposit'], 2, ',', '.').' '.$info["mon_der"].' </td>
                    </tr>';
                }

                $totales = '
                    <tr>
                        <td></td>
                        <td></td>
                        <th colspan=2 style="'.$styles_celdas_left.'">Total</th>
                        <td style="'.$styles_celdas_right.'" align="right"> '.$info["mon_izq"].' '.number_format( $pago, 2, ',', '.').' '.$info["mon_der"].' </td>
                    </tr>
                    '.$descuento_total.'
                    '.$pagado.'
                    '.$descuento_parcial.'
                    '.$precios_detalle.'
                ';
            }

            if( $metas_orden['_payment_method_title'][0] != "" ){
                $pagado_con = "
                    <tr>
                        <td> <strong>Pagado con:</strong> </td> <td> ".$metas_orden['_payment_method_title'][0]." </td>
                    </tr>
                ";
            }else{
                $pagado_con = "";
            }

            $detalles_servicio = '
                <table>
                    <tr>
                        <td> <strong>Servicio:</strong> </td> <td> '.$tipo_servicio.' </td>
                    </tr>
                    <tr>
                        <td> <strong>Desde:</strong> </td> <td> '.$inicio.' </td>
                    </tr>
                    <tr>
                        <td> <strong>Hasta:</strong> </td> <td> '.$fin.' </td>
                    </tr>
                    <tr>
                        <td> <strong>Duración:</strong> </td> <td> '.$dias.' '.$dias_noches.' </td>
                    </tr>
                    '.$pagado_con.'
                </table>
            ';

            if( $detalle_largo ){
                $precios_detalle = '
                    <th style="'.$styles_celdas_center.' width: 150px;"> Precio Unitario </th>
                    <th style="'.$styles_celdas_right.'"> Precio Total </th>';
            }else{
                $precios_detalle = '
                    <th style="'.$styles_celdas_center.' width: 150px;">  </th>
                    <th style="'.$styles_celdas_right.'">  </th>';
            }

            $detalles_factura .= '
                <table style="width:100%" cellspacing=0 cellpadding=0>
                    <tr>
                        <th style="'.$styles_celdas_left.'"> Tamaño </th>
                        <th style="'.$styles_celdas_center.'"> Num. Mascotas </th>
                        <th style="'.$styles_celdas_center.'"> Tiempo </th>
                        '.$precios_detalle.'
                    </tr>
                    '.$variaciones.'
                    '.$transporte_str.'
                    '.$adicionales.'
                    '.$totales.'
                </table>
            ';

            $detalles_factura_cuidador = '
                <table style="width:100%" cellspacing=0 cellpadding=0>
                    <tr>
                        <th style="'.$styles_celdas_left.'"> Tamaño </th>
                        <th style="'.$styles_celdas_center.'"> Num. Mascotas </th>
                        <th style="'.$styles_celdas_center.'"> Tiempo </th>
                        '.$precios_detalle.'
                    </tr>
                    '.$variaciones.'
                    '.$transporte_str.'
                    '.$adicionales.'
                    '.$totales.'
                    '.$reembolsar.'
                </table>
            ';

            $msg_id_reserva ='<p>Reserva #: <strong>'.$reserva->ID.'</strong> </p>';

            $aceptar_rechazar = '
                <center>
                    <p><strong>¿ACEPTAS ESTA RESERVA?</strong></p>
                    <table>
                        <tr>
                            <td>
                                <a href="'.get_home_url().'/wp-content/plugins/kmimos/order.php?o='.$id.'&s=1&t=1" style="text-decoration: none; padding: 7px 0px; border-bottom: solid 1px #cccccc; color: #FFF; font-size: 16px; font-weight: 500; border-radius: 5px; width: 100px; display: inline-block; text-align: center;">Aceptar</a>
                            </td>
                            <td>
                                <a href="'.get_home_url().'/wp-content/plugins/kmimos/order.php?o='.$id.'&s=0&t=1" style="text-decoration: none; padding: 7px 0px; background: #dc2222; color: #FFF; font-size: 16px; font-weight: 500; border-radius: 5px; width: 100px; display: inline-block; text-align: center;">Rechazar</a>
                            </td>
                        </tr>
                    </table>
                </center>
            ';

            $titulo = '<h2>Detalles de la solicitud:</h2>';

            if( $is_mail ){
                
                $_detalles_servicio = $detalles_servicio;

                $detalles_servicio = '
                    <p style="color:#557da1; font-size: 16px;font-weight: 600;">Detalles del Servicio Reservado</p>
                    '.$_detalles_servicio.'
                    <br>
                    <table style="width:100%" cellspacing=0 cellpadding=0>
                        <tr>
                            <th style="padding: 3px; background: #00d2b7; border-left: solid 1px #00d2b7;"> Tamaño </th>
                            <th style="padding: 3px; background: #00d2b7;"> Num. Mascotas </th>
                            <th style="padding: 3px; background: #00d2b7;"> Tiempo </th>
                            <th style="padding: 3px; background: #00d2b7; width: 150px;"> Precio Unitario </th>
                            <th style="padding: 3px; background: #00d2b7; border-right: solid 1px #00d2b7;"> Precio Total </th>
                        </tr>
                        '.$variaciones.'
                        '.$transporte_str.'
                        '.$adicionales.'
                        '.$totales.'
                    </table>
                ';

                $detalles_servicio_cuidador = '
                    <p style="color:#557da1; font-size: 16px;font-weight: 600;">Detalles del Servicio Reservado</p>
                    '.$_detalles_servicio.'
                    <br>
                    <table style="width:100%" cellspacing=0 cellpadding=0>
                        <tr>
                            <th style="padding: 3px; background: #00d2b7; border-left: solid 1px #00d2b7;"> Tamaño </th>
                            <th style="padding: 3px; background: #00d2b7;"> Num. Mascotas </th>
                            <th style="padding: 3px; background: #00d2b7;"> Tiempo </th>
                            <th style="padding: 3px; background: #00d2b7; width: 150px;"> Precio Unitario </th>
                            <th style="padding: 3px; background: #00d2b7; border-right: solid 1px #00d2b7;"> Precio Total </th>
                        </tr>
                        '.$variaciones.'
                        '.$transporte_str.'
                        '.$adicionales.'
                        '.$totales.'
                        '.$reembolsar.'
                    </table>
                ';
            }

            return array(
                "titulo" => $titulo,
                "aceptar_rechazar" => $aceptar_rechazar,
                "msg_id_reserva" => $msg_id_reserva,
                "detalles_servicio" => $detalles_servicio,
                "detalles_factura" => $detalles_factura,

                "detalles_servicio_cuidador" => $detalles_servicio_cuidador,
                "detalles_factura_cuidador" => $detalles_factura_cuidador,

                "metodo_pago" => $metas_orden['_payment_method'][0],
                "pdf" => $metas_orden['_openpay_pdf'][0],

                "servicio" => $producto->ID
            );

        }
    }










































    if(!function_exists('kmimos_datos_generales_desglose')){

        function kmimos_datos_generales_desglose($ID_ORDEN, $is_mail = false, $direccion = false){
            global $wpdb;

            $ID_RESERVA   = $wpdb->get_var("SELECT ID FROM wp_posts WHERE post_parent = {$ID_ORDEN} AND post_type = 'wc_booking'");

            /* Orden y Reserva  */

                $reserva_id = $ID_RESERVA;
                $orden_id   = $ID_ORDEN;

                $metas_orden    = get_post_meta($orden_id);
                $metas_reserva  = get_post_meta( $reserva_id );

            /* Producto  */

                $producto       = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = '".$metas_reserva['_booking_product_id'][0]."'");
                $metas_producto = get_post_meta( $producto->ID );

                $tipo_servicio = explode("-", $producto->post_title);
                $tipo_servicio = $tipo_servicio[0];

            /* Cuidador  */

                $cuidador_id = $producto->post_parent;

                $cuidador_post  = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = '".$producto->post_parent."'");
                $cuidador = $wpdb->get_row("SELECT * FROM cuidadores WHERE user_id = '".$producto->post_author."'");

                $metas_cuidador = get_user_meta($cuidador->user_id);

                $dir = $metas_cuidador["user_address"][0];

                if( strlen($dir) < 2 ){
                    $dir = $cuidador->direccion;
                }

                $email_cuidador = $cuidador->email;

                $movil = $metas_cuidador["user_mobile"][0];
                $telfo = $metas_cuidador["user_phone"][0];

                $telefono = "";
                if( $movil != "" && $telfo != "" ){
                    $telefono .= $movil." / ".$telfo;
                }else{
                    if( $movil != "" ){
                        $telefono = $movil;
                    }
                    
                    if( $telfo != "" ){
                        $telefono = $telfo;
                    }
                    
                    if( $telefono == "" ){
                        $telefono = "No registrado";
                    }
                }

                if( strlen($dir) < 2 ){
                    $dir = "No registrada";
                }

                if($direccion){
                    $dir = "
                    <tr>
                        <td valign='top'> <strong>Dirección: </strong> </td>
                        <td valign='top'> ".$dir."</td>
                    </tr>";
                }else{
                    $dir = "";
                }

                $detalles_cuidador = '
                    <table cellspacing=0 cellpadding=0>
                        <tr>
                            <td style="width: 70px;" valign="top"> <strong>Nombre:</strong> </td>
                            <td valign="top">'.$cuidador_post->post_title.'</td>
                        </tr>
                        <tr>
                            <td valign="top"> <strong>Teléfono:</strong> </td>
                            <td valign="top">'.$telefono.'</td>
                        </tr>
                        <tr>
                            <td valign="top"> <strong>Correo:</strong> </td>
                            <td valign="top">'.$email_cuidador.'</td>
                        </tr>
                        '.$dir.'
                    </table>
                ';

            /* Cliente */

                $cliente = $metas_reserva["_booking_customer_id"][0];
                $xcliente = $cliente;

                if( $cliente == 0 ){
                    $temp_email = $metas_orden["_billing_email"][0];
                    $cliente = $wpdb->get_var("SELECT ID FROM wp_users WHERE user_email = '{$temp_email}'");
                }

                $metas_cliente = get_user_meta($cliente);

                $nombre = $metas_cliente["first_name"][0];
                $apellido = $metas_cliente["last_name"][0];

                $nom = $nombre." ".$apellido;
                $dir = $metas_cliente["user_address"][0];

                $movil = $metas_cliente["user_mobile"][0];
                $telfo = $metas_cliente["user_phone"][0];

                $telefono_cliente = "";
                if( $movil != "" && $telfo != "" ){
                    $telefono_cliente .= $movil." / ".$telfo;
                }else{
                    if( $movil != "" ){
                        $telefono_cliente = $movil;
                    }
                    
                    if( $telfo != "" ){
                        $telefono_cliente = $telfo;
                    }
                    
                    if( $telefono_cliente == "" ){
                        $telefono_cliente = "No registrado";
                    }
                }

                if( $dir == "" || $dir == 0 ){
                    $dir = "No registrada";
                }

                $user = get_user_by( 'id', $cliente );

                $email_cliente = $user->data->user_email;

                $detalles_cliente = '
                    <table cellspacing=0 cellpadding=0>
                        <tr>
                            <td style="width: 70px;" valign="top"> <strong>Nombre:</strong> </td>
                            <td valign="top">'.$nom.'</td>
                        </tr>
                        <tr>
                            <td valign="top"> <strong>Teléfono:</strong> </td>
                            <td valign="top">'.$telefono_cliente.'</td>
                        </tr>
                        <tr>
                            <td valign="top"> <strong>Correo:</strong> </td>
                            <td valign="top">'.$email_cliente.'</td>
                        </tr>
                        <tr>
                            <td valign="top"> <strong>Dirección: </strong> </td>
                            <td valign="top"> '.$dir.'</td>
                        </tr>
                    </table>
                ';

            /*  Mascotas    */

                $mascotas = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_author = '{$cliente}' AND post_type='pets' AND post_status = 'publish'");
                $detalles_mascotas = "";

                if( $is_mail ){
                    $detalles_mascotas = '
                        <table style="width:100%" cellspacing=0 cellpadding=0>
                            <tr>
                                <th style="padding: 3px; background: #00d2b7; text-align: left;"> <strong>Nombre </strong> </th>
                                <th style="padding: 3px; background: #00d2b7; text-align: left;"> <strong>Detalles</strong> </th>
                            </tr>';
                }else{
                    $detalles_mascotas = '
                        <table style="width:100%" cellspacing=0 cellpadding=0>
                            <tr>
                                <th style="padding: 3px; text-align: left;"> <strong>Nombre</strong> </th>
                                <th style="padding: 3px; text-align: left;"> <strong>Detalles</strong> </th>
                            </tr>';
                }


                $comportamientos_array = array(
                    "pet_sociable"           => "Sociables",
                    "pet_sociable2"          => "No sociables",
                    "aggressive_with_pets"   => "Agresivos con perros",
                    "aggressive_with_humans" => "Agresivos con humanos",
                );
                $tamanos_array = array(
                    "Pequeño",
                    "Mediano",
                    "Grande",
                    "Gigante"
                );
                if( count($mascotas) > 0 ){
                    foreach ($mascotas as $key => $mascota) {
                        $data_mascota = get_post_meta($mascota->ID);

                        $temp = array();
                        foreach ($data_mascota as $key => $value) {

                            switch ($key) {
                                case 'pet_sociable':
                                    if( $value[0] == 1 ){
                                        $temp[] = "Sociable";
                                    }else{
                                        $temp[] = "No sociable";
                                    }
                                break;
                                case 'aggressive_with_pets':
                                    if( $value[0] == 1 ){
                                        $temp[] = "Agresivo con perros";
                                    }
                                break;
                                case 'aggressive_with_humans':
                                    if( $value[0] == 1 ){
                                        $temp[] = "Agresivo con humanos";
                                    }
                                break;
                            }

                        }

                        $anio = explode("-", $data_mascota['birthdate_pet'][0]);
                        $edad = date("Y")-$anio[0];

                        $raza = $wpdb->get_var("SELECT nombre FROM razas WHERE id=".$data_mascota['breed_pet'][0]);
                    	if( $raza == "" ){ $raza = "Affenpinscher"; }
                        if( $is_mail ){
                            $border = "border-bottom: solid 1px #00d2b7;";
                        }else{
                            $border = "border-top: solid 1px #00d2b7;";
                        }

                        $detalles_mascotas .= '
                            <tr>
                                <td style="'.$border.' padding: 3px;" valign="top"> '.$data_mascota['name_pet'][0].'</td>
                                <td style="padding: 5px; '.$border.';" valign="top">
                                    <strong>Raza:</strong> '.$raza.'<br>
                                    <strong>Edad:</strong> '.$edad.' año(s)<br>
                                    <strong>Tamaño:</strong> '.$tamanos_array[ $data_mascota['size_pet'][0] ].'<br>
                                    <strong>Comportamiento:</strong> '.implode("<br>", $temp).'<br>
                                </td>
                            </tr>
                        ';
                    }
                }else{
                    $detalles_mascotas .= '<tr> <td colspan="2">No tiene mascotas registradas.</td> </tr>';
                }
                $detalles_mascotas .= '</table>';

                if( $is_mail ){
                    $detalles_cuidador = '<p style="color:#557da1;font-size: 16px;font-weight: 600; font-size: 16px;">Datos Cuidador</p>'.$detalles_cuidador;
                    $detalles_cliente  = '<p align="justify" style="color:#557da1; font-size: 16px; font-weight: 600;">Datos Cliente</p>'.$detalles_cliente;
                    $detalles_mascotas = '<p style="color: #557da1; font-size: 16px; font-weight: 600;">Detalles de las mascotas: </p>'.$detalles_mascotas;
                }

                $cantidad = 0; // 
                $temp_person = unserialize($metas_reserva['_booking_persons'][0]);
                foreach ($temp_person as $key => $value) {
                    $cantidad += $value;
                }

                return array(
                    "cliente"        => $detalles_cliente,
                    "cuidador"       => $detalles_cuidador,
                    "mascotas"       => $detalles_mascotas,
                    "cliente_email"  => $email_cliente,
                    "cuidador_email" => $email_cuidador,

                    "cuidador_obj" => $cuidador,

                    "booking" => $ID_RESERVA,
                    "orden"   => $ID_ORDEN,

                    "cliente_id"  => $cliente,
                    "cuidador_id"  => $cuidador_id,

                    "nombre_cuidador" => $cuidador_post->post_title,
                    "nombre_cliente"  => $nom,

                    "tipo_servicio"   => $tipo_servicio,
                    "producto_name"   => $producto->post_title,
                    "servicio"        => $metas_reserva['_booking_product_id'][0],
                    "inicio"          => $metas_reserva['_booking_start'][0],
                    "fin"             => $metas_reserva['_booking_end'][0],
                    "cantidad"        => $cantidad
                );
        }

    }



    if(!function_exists('kmimos_guardar_desglose_reserva_data')){
        function kmimos_guardar_desglose_reserva_data( $reserva_id, $data_reserva ){
            $desglose = get_post_meta( $reserva_id, "_booking_desglose", true );
            if( empty($desglose) ){
                $data = [
                    "variaciones" => $data_reserva['servicio']['variaciones'],
                    "transporte" => $data_reserva['servicio']['transporte'],
                    "adicionales" => $data_reserva['servicio']['adicionales'],
                ];
                update_post_meta( $reserva_id, '_booking_desglose', $data );
                return true;
            }
            return false;
        }
    }

    if(!function_exists('kmimos_desglose_reserva_data')){

        function kmimos_desglose_reserva_data($id, $email = false){

            $cats = array(
                "paseos"                    => 2601,
                "adiestramiento_basico"     => 2602,
                "adiestramiento_intermedio" => 2606,
                "adiestramiento_avanzado"   => 2607,
                "guarderia"                 => 2599,
                "hospedaje"                 => 2598
            );

            $cats_2 = array(
                "paseos"                    => "paseos",
                "adiestramiento-basico"     => "adiestramiento_basico",
                "adiestramiento-intermedio" => "adiestramiento_intermedio",
                "adiestramiento-avanzado"   => "adiestramiento_avanzado",
                "guarderia"                 => "guarderia",
                "hospedaje"                 => "hospedaje"
            );

            global $wpdb;

            /* Reserva y Orden */

            $reserva = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_type = 'wc_booking' AND post_parent = '".$id."'");

            $metas_orden = get_post_meta($id);
            $metas_reserva = get_post_meta( $reserva->ID );

            /* Producto */

            $producto = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = '".$metas_reserva['_booking_product_id'][0]."'");
            $precio_base = get_post_meta( $producto->ID, "_price", true );
            $cuidador = $wpdb->get_row("SELECT * FROM cuidadores WHERE user_id = ".$producto->post_author);

            $tipo = $wpdb->get_var("
                SELECT
                    tipo_servicio.slug AS tipo
                FROM 
                    wp_term_relationships AS relacion
                LEFT JOIN wp_terms as tipo_servicio ON ( tipo_servicio.term_id = relacion.term_taxonomy_id )
                WHERE 
                    relacion.object_id = '{$producto->ID}' AND
                    relacion.term_taxonomy_id != 28
            ");

            if( !isset($metas_reserva["_booking_precios_bk"]) ){
                $_precios = array(
                    "precio_base" => $precio_base,
                    "hospedaje" => unserialize($cuidador->hospedaje),
                    "adicionales" => unserialize($cuidador->adicionales)
                );

                update_post_meta($reserva->ID, "_booking_precios_bk", serialize($_precios));

                $metas_reserva['_booking_precios_bk'][0] = serialize( serialize($_precios ) );
            }

            $tipo_servicio = explode("-", $producto->post_title);
            $tipo_servicio = $tipo_servicio[0];

            if( isset($metas_reserva['_booking_precios_bk'][0]) ){

                $preciosCuidador = unserialize( unserialize($metas_reserva['_booking_precios_bk'][0]));

                $precios = $preciosCuidador["hospedaje"];
                if( trim($tipo_servicio) != "Hospedaje" ){
                    $data = $preciosCuidador["adicionales"];
                    $precios = $data[ $cats_2[ $tipo ] ];
                }

                $precio_base = $preciosCuidador["precio_base"];
            }else{
                $precios = unserialize($cuidador->hospedaje);
                if( trim($tipo_servicio) != "Hospedaje" ){
                    $data = unserialize($cuidador->adicionales);
                    $precios = $data[ $cats_2[ $tipo ] ];
                }
            }


            $inicio = date("d/m/Y", strtotime($metas_reserva['_booking_start'][0]));
            $fin    = date("d/m/Y", strtotime($metas_reserva['_booking_end'][0]));

            $xini = strtotime($metas_reserva['_booking_start'][0]);
            $xfin = strtotime($metas_reserva['_booking_end'][0]);

            $id_orden_item = $metas_reserva['_booking_order_item_id'][0];

            $orden_item = $wpdb->get_results("SELECT * FROM wp_woocommerce_order_itemmeta WHERE order_item_id = '".$id_orden_item."'");

            $data_temp = kmimos_adicionales($orden_item);
            $adicionales_array = $data_temp["adicionales"];
            $transporte  = $data_temp["transporte"];

            $detalles_reserva = array();
            $mascotas = array();
            foreach ( $orden_item as $key => $value ) {
                $detalles_reserva[$value->meta_key] = $value->meta_value;
                if( strpos($value->meta_key, "Mascotas") > -1 ){
                    $mascota = substr(end(explode(" ", $value->meta_key)), 0, 5);
                    $mascotas[ $mascota ] = $value->meta_value;
                }
            }

            $variaciones_array = array(
                "pequenos" => "Peque", 
                "medianos" => "Media",
                "grandes"   => "Grand", 
                "gigantes"  => "Gigan",
                "gatos"     => "Gato"
            );

            $txts = array(
                "pequenos"  => 'Pequeña', 
                "medianos"  => 'Mediana', 
                "grandes"   => "Grande", 
                "gigantes"  => "Gigante",
                "gatos"     => "Gato"
            );

            $dias = ceil(((($xfin - $xini)/60)/60)/24);

            $dias_noches = "Noche"; if( trim($tipo_servicio) != "Hospedaje" ){ $dias_noches = "Día"; }else{ $dias--; }
            $plural_dias = ""; if( $dias > 1 ){ $plural_dias = "s"; } $dias_noches .= $plural_dias;

            $info = kmimos_get_info_syte();

            $variaciones = array(); $grupo = 0;
            foreach ($variaciones_array as $key => $value) {
                if( isset( $mascotas[$value] ) ){
                    $plural_tamanos = ""; if( $detalles_reserva[$value] > 1 ){ $plural_tamanos = "s"; }
                    
                    $unitario = $precios[$key]*getComision();
                    
                    $variaciones[] = array(
                        $mascotas[$value],
                        $txts[$key].$plural_tamanos,
                        $dias.' '.$dias_noches,
                        number_format( $unitario, 2, ',', '.'),
                        number_format( ($unitario*$mascotas[$value]*$dias), 2, ',', '.')
                    );
                    if( $key != "gatos" ){
                        $grupo += $mascotas[$value];
                    }
                }
            }

            $adicionales_desglose = array();
            if( count($adicionales_array) > 0 ){
                $plural_tamanos = ""; if( $grupo > 1 ){ $plural_tamanos = "s"; }
                foreach ($adicionales_array as $key => $value) {
                    $servicio = $value[0];
                    $costo = ($value[1]);

                    $adicionales_desglose[] = array(
                        $servicio,
                        $grupo.' Mascota'.$plural_tamanos,
                        number_format( $costo, 2, ',', '.'),
                        number_format( ($costo*$grupo), 2, ',', '.')
                    );
                }
            }

            $transporte_desglose = array();
            if( count($transporte) > 0 ){
                foreach ($transporte as $key => $value) {
                    $servicio = $value[0];
                    $costo = ($value[1]);

                    $transporte_desglose[] = array(
                        str_replace(" - ", "<br>", $servicio),
                        'Precio por Grupo',
                        number_format( $costo, 2, ',', '.'),
                        number_format( $costo, 2, ',', '.')
                    );
                }
            }

            $pago = ($detalles_reserva['_line_subtotal']);
            $desglose = unserialize($detalles_reserva['_wc_deposit_meta']);
            $descuento = $metas_orden["_cart_discount"][0];

            if( $desglose['enable'] == "no" ){
                $desglose['deposit'] = $pago;
                if( $metas_orden["_cart_discount"][0]+0 > 0 ){
                    $desglose['deposit'] = $pago-$metas_orden["_cart_discount"][0];
                }
            }else{
                $deposito = $desglose['deposit'];
            }

            $diferencia = 0;
            $pago_descuentos = $desglose['deposit']+$metas_orden["_cart_discount"][0];
            $comision = ($pago-($pago/getComision()));

            if( $comision < $pago_descuentos ){
                $diferencia = $pago_descuentos-$comision;
            }

            $diferencia = kmimos_calculo_pago_cuidador( $reserva->ID, $pago );

            $desglose['total'] = $pago;
            
            if( $metas_orden['_payment_method_title'][0] != "" ){
                $pagado_con = $metas_orden['_payment_method_title'][0];
            }else{
                $pagado_con = "";
            }

            $aceptar_rechazar = array(
                "aceptar" => get_home_url().'/perfil-usuario/reservas/confirmar/'.$id,
                "cancelar" => get_home_url().'/perfil-usuario/reservas/cancelar/'.$id
            );

            /* DATA CUIDADOR */

                $cuidador_id = $producto->post_author;
                $cuidador = $wpdb->get_row("SELECT * FROM cuidadores WHERE user_id = '".$cuidador_id."'");
                $metas_cuidador = get_user_meta($cuidador_id);
                $name_cuidador = $wpdb->get_var("SELECT post_title FROM wp_posts WHERE ID='{$cuidador->id_post}'");
                $email_cuidador = $wpdb->get_var("SELECT user_email FROM wp_users WHERE ID='{$cuidador_id}'");
                $telefonos_cuidador = $metas_cuidador["user_phone"][0]." / ".$metas_cuidador["user_mobile"][0];
                $dir_cuidador = $cuidador->direccion;

            /* DATA CLIENTE */

                $cliente_id = $metas_reserva["_booking_customer_id"][0];
                $metas_cliente = get_user_meta($cliente_id);

                $nombre = $metas_cliente["first_name"][0];
                $apellido = $metas_cliente["last_name"][0];
                $name_cliente = $nombre." ".$apellido;

                $email_cliente = $wpdb->get_var("SELECT user_email FROM wp_users WHERE ID='{$cliente_id}'");
                $telefonos_cliente = $metas_cliente["user_phone"][0]." / ".$metas_cliente["user_mobile"][0];

                $mascotas_cliente = $wpdb->get_results("SELECT * FROM wp_posts WHERE post_author = '{$cliente_id}' AND post_type='pets' AND post_status = 'publish'");

                $comportamientos_array = array(
                    "pet_sociable"           => "Sociables",
                    "pet_sociable2"          => "No sociables",
                    "aggressive_with_pets"   => "Agresivos con perros",
                    "aggressive_with_humans" => "Agresivos con humanos",
                );
                $tamanos_array = array(
                    "Pequeño",
                    "Mediano",
                    "Grande",
                    "Gigante",
                    "Gato"
                );
                
                $mascotas = array();

                foreach ($mascotas_cliente as $key => $mascota) {
                    $data_mascota = get_post_meta($mascota->ID);
                    $temp = array();
                    switch ( $data_mascota["pet_type"][0] ){
                        case '2605':
                            foreach ($data_mascota as $key => $value) {
                                switch ($key) {
                                    case 'pet_sociable':
                                        if( $value[0] == 1 ){ $temp[] = "Sociable"; }else{ $temp[] = "No sociable"; }
                                    break;
                                    case 'aggressive_with_pets':
                                        if( $value[0] == 1 ){ $temp[] = "Agresivo con perros"; }
                                    break;
                                    case 'aggressive_with_humans':
                                        if( $value[0] == 1 ){ $temp[] = "Agresivo con humanos"; }
                                    break;
                                }
                            }
                            $tamanio_mascota = $tamanos_array[ $data_mascota['size_pet'][0] ];
                            $raza = $wpdb->get_var("SELECT nombre FROM razas WHERE id=".$data_mascota['breed_pet'][0]);
                            $tipo = "dog";
                        break;
                        case '2608':
                            $temp[] = "Sociable";
                            $tamanio_mascota = "N/A";
                            $raza = "Gato";
                            $tipo = "cat_2";
                        break;
                    }
                    $data_mascota['birthdate_pet'][0] = str_replace("/", "-", $data_mascota['birthdate_pet'][0]);
                    $anio = strtotime($data_mascota['birthdate_pet'][0]);
                    $edad_time = strtotime(date("Y-m-d"))-$anio;

                    $edad = (date("Y", $edad_time)-1970)." año(s) ".date("m", $edad_time)." mes(es)";

                    if( $raza == "" ){ $raza = "Affenpinscher"; }
                    $mascotas[] = array(
                        "tipo" => $tipo,
                        "nombre" => $data_mascota['name_pet'][0],
                        "raza" => $raza,
                        "edad" => $edad,
                        "tamano" => $tamanio_mascota,
                        "conducta" => implode("<br>", $temp)
                    );
                }

            $detalle = array(
                "aceptar_rechazar" => $aceptar_rechazar,
                "id_reserva" => $reserva->ID,
                "id_orden" => $id,


                "variaciones" => $variaciones,
                "transporte" => $transporte_desglose,
                "adicionales" => $adicionales_desglose,

                "desglose" => $desglose,
                "reembolsar" => $diferencia,
                "descuento" => $descuento,


                "metodo_pago" => $metas_orden['_payment_method_title'][0],
                "pdf" => $metas_orden['_openpay_pdf'][0],

                "servicio" => $producto->ID,
                "servicio_titulo" => $producto->post_title,
                "cuidador" => $producto->post_author,
                "cliente" => $metas_reserva["_booking_customer_id"][0],
                "inicio" => $inicio,
                "fin" => $fin
            );

            $desglose["deposit"] = $desglose["deposit"];
            $desglose["reembolsar"] = $diferencia;
            $desglose["descuento"] = $descuento;

            $tipo_pago = "PAGO";
            if( $metas_orden['_payment_method_title'][0] == "Tienda" ){ $tipo_pago = "PAGO EN TIENDA"; }

            $correos = array(

                "cliente" => array(
                    "id" => $cliente_id,
                    "nombre" => $name_cliente,
                    "telefono" => $telefonos_cliente,
                    "email" => $email_cliente,
                    "mascotas" => $mascotas
                ),
                "cuidador" => array(
                    "id" => $cuidador_id,
                    "nombre" => $name_cuidador,
                    "telefono" => $telefonos_cuidador,
                    "email" => $email_cuidador,
                    "direccion" => $dir_cuidador
                ),
                "servicio" => array(
                    "id_reserva" => $reserva->ID,
                    "id_orden" => $id,
                    "duracion" => $dias.' '.$dias_noches,
                    "tipo" => $tipo_servicio,
                    "inicio" => strtotime( str_replace("/", "-", $inicio) ),
                    "fin" => strtotime( str_replace("/", "-", $fin) ),
                    "metodo_pago" => $metas_orden['_payment_method_title'][0],
                    "desglose" => $desglose,
                    "tipo_pago" => $tipo_pago,

                    "variaciones" => $variaciones,
                    "transporte" => $transporte_desglose,
                    "adicionales" => $adicionales_desglose,

                    "pdf" => $metas_orden['_openpay_pdf'][0],
                    "vence" => $metas_orden['_openpay_tienda_vence'][0],

                    "aceptar_rechazar" => $aceptar_rechazar,

                    "flash" => $metas_reserva['_booking_flash'][0],

                    "checkin" => date("g:i a", strtotime( $metas_reserva['_booking_checkin'][0] ) ),
                    "checkout" => date("g:i a", strtotime( $metas_reserva['_booking_checkout'][0] ) )
                ),
            );

            $respuesta = $detalle;
            if( $email ){ $respuesta = $correos; }

            return $respuesta;

        }
    }
?>
<?php
    session_start();
    date_default_timezone_set('America/Mexico_City');

    $tema = dirname(dirname(dirname(dirname(__DIR__))));
    include_once($tema.'/lib/pagos/pagos_cuidador.php');

    $data = array(
        "data" => array()
    );

    $actual = time();

    extract( $_POST );

    $display_reserva_check = true;
    $display_btn_liberar = false;
    $display_total_input = false;
    $validar_cuenta = true;
    $display_comentario_input = false;
    
    switch ( strtolower($tipo) ) {
        case 'aprobar':
            $pagos_lists = $pagos->getPagosPorAprobar( $desde, $hasta );
            $validar_cuenta = false;
            break;
        case 'nuevo':
            $pagos_lists = $pagos->getPagosPendientes();
            $display_total_input = true;
            $display_reserva_check = false;
            $display_comentario_input = true;
            break;
        case 'generados':
            $pagos_lists = $pagos->getPagoGenerados( $desde, $hasta );
            $display_reserva_check = false;
            $display_btn_liberar = true;
            break;
        case 'completado':
            $pagos_lists = $pagos->getPagoCompletados( $desde, $hasta );
            $display_reserva_check = false;
            break;
    }

    $estatus_bloqueados = [
        'in_progress',
        'completed',
    ];

    $estatus=[
        "" => "",
        "pendiente" => "Pendiente",
        "por_autorizar" => "No procesado",
        "autorizado" => "No procesado",
        "negado" => "Negado",  
        "in_progress" => "En progreso",  
        "cancelled" => "Cancelado",  
        "completed" => "Completado",  
        "failed" => "Error",
        "error" => "Error",
    ];

    $_SESSION['pago_cuidador'] = [];

    if( $pagos_lists != false ){
        $i = 0;

        foreach ($pagos_lists as $pago) {

            $_SESSION['pago_cuidador'][ $pago->user_id ] = $pago;

            // Datos del cuidador
                $cuidador = $pagos->db->get_row("SELECT user_id, nombre, apellido, banco FROM cuidadores WHERE user_id = {$pago->user_id}");

            $botones = '';

            
            // Validar si el cuidador tiene datos bancarios
                $token = md5(serialize($pago->detalle));
                $checkbox = "<input type='checkbox' data-type='item_selected' data-total='".$pago->total."' name='item_selected[]' data-token='".$token."' value='".$pago->user_id."' data-global='".$pago->user_id."'>";
                if( !empty($cuidador->banco) || !$validar_cuenta ){
                    //$botones .= "<button style='padding:5px;'><i class='fa fa-money'></i> Generar Solicitud de pago</button>"; 
                }else{
                    $botones .= "No posee datos bancarios"; 
                    $checkbox = "<input type='checkbox' class='disabled' data-action='error' title='".utf8_encode($cuidador->nombre." ".$cuidador->apellido)." no posee datos bancarios'>";
                }

            if( $tipo == 'generados' || $tipo == 'completado' ){
                // Detalle
                    $pago->detalle = (!empty($pago->detalle))? unserialize($pago->detalle) : [] ;

                // Opciones
                    $botones = ""; 
            }

            // Agregar salto de linea a detalle
                $detalle = '';
                $count= 0;
                if( $pago->detalle ){
                    foreach ($pago->detalle as $item) {
                        $count++;

                        // Cargar cupones 
                        $cupon_sql = "SELECT items.order_item_name as name, meta.meta_value as monto  
                        FROM `wp_woocommerce_order_items` as items 
                        INNER JOIN wp_woocommerce_order_itemmeta as meta ON meta.order_item_id = items.order_item_id
                        INNER JOIN wp_posts as p ON p.ID = ".$item['reserva']." and p.post_type = 'wc_booking' 
                        WHERE 
                        meta.meta_key = 'discount_amount'
                        and items.`order_id` = p.post_parent";
                        $cupones = $pagos->db->get_results($cupon_sql);

                        $colores = [
                            'normal' => '#a6a5a5',
                            'cupon' => '#8d88e0',
                            'saldo' => '#88e093',
                            'ambos' => '#e0888c',
                        ];

                        $info = '';
                        $color = $colores['normal'];
                        $tipo_cupon='';
                        if( !empty($cupones) ){                    
                            foreach ($cupones as $cupon) {
                                if( $cupon->monto > 0 ){
                                    $cupon_tipo = $pagos->db->get_var("SELECT m.meta_value 
                                        FROM wp_posts as p 
                                        INNER JOIN wp_postmeta as m ON m.post_id = p.ID AND m.meta_key = 'descuento_tipo' 
                                        WHERE post_title = '".$cupon->name."' AND post_type = 'shop_coupon'");
                                    $cupon_tipo = ( $cupon_tipo != '' ) ? " Tipo: ".$cupon_tipo : '' ;
                                    // Informacion extra
                                        $info .= " [ ".$cupon->name .": $ " .$cupon->monto . $cupon_tipo . " ] ";
                                    
                                    // determinar tipo de cupones
                                        if( strpos($cupon->name, 'saldo-') !== false ){
                                            $tipo_cupon = 'saldo';
                                        }else if( $tipo_cupon == 'saldo' || $tipo_cupon == 'cupon' || $tipo_cupon == 'ambos' ){
                                            $tipo_cupon = 'ambos';
                                        }else{
                                            $tipo_cupon = 'cupon';
                                        }
                                        $color = $colores[ $tipo_cupon ];
                                }
                            }
                        }

                        $info = (!empty($info))? 'data-toggle="tooltip" data-placement="top" title="'.$info.'"' : '' ;

                        $reserva_checkbox = '';
                        if( $display_reserva_check ){
                            $reserva_checkbox = '
                                <input type="checkbox" checked
                                    value="'.$item['reserva'].'"
                                    data-target="reserva_check"
                                    data-monto="'.$item['monto'].'"
                                    data-cuidador="'.$pago->user_id.'"
                                    name="reservas_'.$pago->user_id.'[]"
                                >';
                        }

                        $detalle .= '
                            <small class="items-span" '.$info.' style="color:#fff; background:'.$color.'!important; ">
                                <label style="margin-bottom: 0px;">'.$reserva_checkbox.'
                                        <strong>'.$item['reserva'].'</strong>
                                        <span class="badge" style="margin-left: 10px;">
                                            $ '.number_format($item['monto'], 2, ",", ".").'
                                        </span>
                                </label>
                            </small>
                        ';

                        if( $count == 7 ){ 
                            $detalle .= "<br>"; 
                            $count = 0;
                        }
                    }
                }

            // Agregar descripcion de los Autorizados
                $autorizado_por = '';
                $comentarios = '';
                if( !empty($pago->autorizado) ){
                    $autorizar = unserialize($pago->autorizado);
                    foreach ($autorizar as $key => $value) {
                        $nombre = $pagos->db->get_var("SELECT meta_value FROM wp_usermeta WHERE meta_key='first_name' and user_id = {$key}", 'meta_value');
                        $apellido = $pagos->db->get_var("SELECT meta_value FROM wp_usermeta WHERE meta_key='last_name' and user_id = {$key}", 'meta_value');
                        $color_class = ( $value['accion'] == 'negado' )? 'item-danger' : 'item-success';
                        $autorizado_por .= "<div class='items-span {$color_class}'>".utf8_encode($nombre." ".$apellido)."<span class='badge'>{$value['accion']}</span></div>";

                        $comentarios .= $value['comentario'].'<br>';
                    }
                }

            // Agregar boton de comentarios    
                if( !empty($comentarios) ){
                    $botones .= "<button class='btn btn-default' style='padding:5px;margin:5px;' data-titulo='Comentarios' data-modal='comentarios' data-id='".$pago->id."'><i class='fa fa-comments-o' aria-hidden='true'></i></button>";
                } 

                if( $display_btn_liberar && !in_array( $pago->estatus, $estatus_bloqueados ) ){
                    $botones .= "<button class='btn btn-danger' style='padding:5px;margin:5px;' data-target='liberar' data-id='".md5($pago->id)."'><i class='fa fa-close' aria-hidden='true'></i> Cancelar</button>";
                } 

                $comentarios .= '<br>'.$pago->observaciones;

            $input_total = "$ <span id='monto_".$pago->user_id."'>".number_format($pago->total, 2,',', '.')."</span>";

            if( $display_total_input ){
                $input_total = "<input class='form-control' data-target='input_total' data-user='".$pago->user_id."' id='monto_".$pago->user_id."' value='".$pago->total."' >
                <div>Disponible: $ ".number_format($pago->total, 2,',', '.')."</div>";
            }
            if( $display_comentario_input ){
                $input_comentario = "<input class='form-control' id='comentario_".$pago->user_id."' value='' >";
            }

            $data["data"][] = array(
                $checkbox,
                date('Y-m-d',strtotime($pago->fecha_creacion)),
                strtoupper("<strong>".$estatus[$pago->estatus]."</strong>"),
                $pago->user_id,
                utf8_encode($cuidador->nombre),
                utf8_encode($cuidador->apellido),
                $input_total,
                $input_comentario,
                "<span id='cantidad_".$pago->user_id."'>".$pago->cantidad."</span>",
                $detalle,
                $autorizado_por,
                $botones,
                $comentarios,
            );
        }
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
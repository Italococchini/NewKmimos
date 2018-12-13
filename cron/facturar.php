<?php

    session_start();

    date_default_timezone_set('America/Mexico_City');
    
    include('../wp-load.php');
    include('../wp-content/themes/kmimos/lib/enlaceFiscal/CFDI.php');

echo '<pre>';

    // buscar total de reservas del mes anterior
    $mes_anterior = strtotime("now +1 month");
    $fecha_ini = date("Y-m-d 00:00:00", $mes_anterior);
    $fecha_fin = date("Y-m-d 23:59:59", $mes_anterior);

    $ordenes = $CFDI->db->get_results( "
            SELECT DATE_FORMAT( m.meta_value,'%Y-%m-%d 23:59:59' ) as fecha, p.ID, p.post_parent 
            FROM wp_postmeta as m
                INNER JOIN wp_posts as p ON p.ID = m.post_id
            WHERE 
                DATE_FORMAT( m.meta_value,'%Y-%m-%d 00:00:00' ) >= '{$fecha_ini}' AND 
                DATE_FORMAT( m.meta_value,'%Y-%m-%d 23:59:59' ) <= '{$fecha_fin}' AND 
                m.meta_key = '_booking_end' AND
                p.post_status IN ('complete', 'confirmed') " 
        );

    $cuidador_desglose = [];
    foreach ($ordenes as $key => $orden) {

        // Desglose de reserva
            $data_reserva = kmimos_desglose_reserva_data( $orden->post_parent, true);

        // Desglose Cuidador CFDI
            $cuidador_id= $data_reserva['cuidador']['id'];
            $orden_id = $data_reserva['servicio']['id_reserva'];

            // Cliente
            $cuidador_desglose[ $cuidador_id ]['cliente']['id'] = 0;
            $cuidador_desglose[ $cuidador_id ]['servicio']['id_orden'] = 0;

            // Cuidador
            $cuidador_desglose[ $cuidador_id ]['cuidador'] = $data_reserva['cuidador']; 
            $cuidador_desglose[ $cuidador_id ]['cuidador']['rfc'] = 'XAXX010101000';

            // Servicios
            $data_reserva['servicio']['desglose']['tipo'] = $data_reserva['servicio']['tipo'];
            $cuidador_desglose[ $cuidador_id ]['servicio']['tipo_pago'] = 'TRANSFERENCIA';
            $cuidador_desglose[ $cuidador_id ]['servicio']['desglose'][ $orden_id ] = $data_reserva['servicio']['desglose'];
            
            // Periodo            
            $cuidador_desglose[ $cuidador_id ]['periodo']['mes'] = date("m", $mes_anterior);    // 01 - 12
            $cuidador_desglose[ $cuidador_id ]['periodo']['anio'] = date("Y", $mes_anterior);   // 2018
            $cuidador_desglose[ $cuidador_id ]['periodo']['fecha'] = date("ym", $mes_anterior); // 1801


        // Validar si la factura del cliente esta generada
            $sql_cliente = "SELECT * FROM facturas WHERE receptor='cliente' and pedido_id = ".$orden->post_parent;
            $facturas = $CFDI->db->get_row( $sql_cliente );
      
            if( empty($facturas) ){
                $data_reserva['receptor']['rfc'] = 'XAXX010101000';
                $data_reserva['receptor']['razon_social'] = 'Publico en General';


                $AckEnlaceFiscal = $CFDI->generar_Cfdi_Cliente($data_reserva);
                if( !empty($AckEnlaceFiscal['ack']) ){
                    echo 'cliente: ';
                    print_r($AckEnlaceFiscal['ack']);
                    $ack = json_decode($AckEnlaceFiscal['ack']);
    
                    $data_reserva['comentario'] = '';
                    $data_reserva['subtotal'] = $AckEnlaceFiscal['cfdi']['CFDi']['subTotal'];
                    $data_reserva['impuesto'] = $AckEnlaceFiscal['cfdi']['CFDi']['Impuestos']['Totales']['traslados'];
                    $data_reserva['total'] = $AckEnlaceFiscal['cfdi']['CFDi']['total'];                
                    print_r( $CFDI->guardarCfdi( 'cliente', $data_reserva, $ack ) );
                }
            }else{
                echo 'factura no encontrada '.$sql_cliente;
            }
    }

        // facturar a cuidador
    foreach ($cuidador_desglose as $cuidador_id => $datos) {

        $ef = $CFDI->generar_Cfdi_Cuidador( $datos );
        
        if( !empty($ef['ack']) ){
            $ask = json_decode($ef['ack']);

            // Listado de reservas
                $all_reservas = array_keys( $datos['servicio']['desglose'] );
                $str_reservas = implode(',', $all_reservas);

            // Datos complementarios
                $datos['comentario'] = $str_reservas;
                $datos['subtotal'] = $ef['cfdi']['CFDi']['subTotal'];
                $datos['impuesto'] = $ef['cfdi']['CFDi']['Impuestos']['Totales']['traslados'];
                $datos['total'] = $ef['cfdi']['CFDi']['total'];

            $CFDI->guardarCfdi( 'cuidador', $datos, $ask );

            echo 'Cuidador: ';
            print_r($ef['ack']);
        }else{
        }
        echo '<br/>';
    }

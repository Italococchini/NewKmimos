<?php

session_start();
date_default_timezone_set('America/Mexico_City');

$tema = '../wp-content/themes/kmimos';
include('../wp-load.php');
include($tema.'/lib/enlaceFiscal/CFDI.php');

echo '<pre>';

// buscar total de reservas del dia de hoy
$fecha_ini = date("Y-m-d");

$ordenes = $CFDI->db->get_results( "
        SELECT DATE_FORMAT( m.meta_value,'%Y-%m-%d 23:59:59' ) as fecha, p.ID, p.post_parent 
        FROM wp_postmeta as m
            INNER JOIN wp_posts as p ON p.ID = m.post_id
        WHERE 
            DATE_FORMAT( m.meta_value,'%Y-%m-%d' ) = '{$fecha_ini}' AND 
            m.meta_key = '_booking_end' AND
            p.post_status IN ('confirmed') " 
    );

$cuidador_desglose = [];
foreach ($ordenes as $key => $orden) {

    // Orden y Reserva
    $orden = $orden->post_parent;
    $reserva_id = $orden->ID;
    if( $reserva_id > 0 ){
        
        // Validar si existe factura
        $factura = $CFDI->db->get_row( "select * from facturas where reserva_id = {$reserva_id}");
        if( !isset($factura->id) ){

            // Desglose de reserva
            $data_reserva = kmimos_desglose_reserva_data($orden, true);

            if( validar_datos_facturacion( $data_reserva['cliente']['id'] ) ){
    
                if( validar_datos_facturacion( $data_reserva['cuidador']['id'] ) ){

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

                    // Usuario ID
                    $user_id = $data_reserva['cliente']['id'];  

                    // Generar CFDI
                    $enlaceFiscal = $CFDI->generar_Cfdi_Cliente($data_reserva);

                    $respuesta = [];
                    if( !empty($enlaceFiscal['ack']) ){
                        $ack = json_decode($enlaceFiscal['ack']);

                        // Datos complementarios
                        $data_reserva['comentario'] = '';
                        $data_reserva['subtotal'] = $enlaceFiscal['cfdi']['CFDi']['subTotal'];
                        $data_reserva['impuesto'] = $enlaceFiscal['cfdi']['CFDi']['Impuestos']['Totales']['traslados'];
                        $data_reserva['total'] = $enlaceFiscal['cfdi']['CFDi']['total'];

                        $CFDI->guardarCfdi( 'cliente', $data_reserva, $ack );

                        if( $ack->AckEnlaceFiscal->estatusDocumento == 'aceptado' ){
                            $factura_generada = 'block';
                            $factura_datos = 'none';
                            $referencia = $ack->AckEnlaceFiscal->numeroReferencia;
                        }
                    }
                }
            }
        }
    }


}






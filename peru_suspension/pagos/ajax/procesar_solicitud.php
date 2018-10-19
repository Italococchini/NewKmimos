<?php
    session_start();

    date_default_timezone_set('America/Mexico_City');

    $raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
    include_once($raiz."/vlz_config.php");

    $tema = (dirname(dirname(dirname(dirname(__DIR__)))));
    include_once($tema."/procesos/funciones/db.php");
    include_once($tema."/procesos/funciones/generales.php");
    include_once($tema.'/lib/openpay/Openpay.php');
    // Cambiar credenciales -----------------------------------------
    $openpay = Openpay::getInstance('mbkjg8ctidvv84gb8gan', 
        'sk_883157978fc44604996f264016e6fcb7');
    // --------------------------------------------------------------


    $db = new db( new mysqli($host, $user, $pass, $db) );

    $list = $_POST['list'];
    $accion = $_POST['accion'];
    $user_id = $_POST['user_id'];
    $comentarios = $_POST['comentario'];
 
    $result = [];
    foreach ($list as $item) {

        $solicitud = $db->get_row('SELECT * FROM cuidadores_pagos WHERE id = '.$item['user_id'] );
        $cuidador = $db->get_row('SELECT * FROM cuidadores WHERE user_id = '.$solicitud->user_id );
        //if( $solicitud->admin_id != $user_id ){

            // Autorizar registro
                $autorizaciones = [];
                if( isset($solicitud->autorizado) && !empty($solicitud->autorizado)){
                    $autorizaciones = unserialize($solicitud->autorizado);
                }
                $autorizaciones[$user_id] = [
                    'fecha'=>date('Y-m-d'),
                    'user_id'=> $user_id,
                    'accion'=> $accion,
                    'comentario'=> $comentarios,
                ];
                $db->query("UPDATE cuidadores_pagos SET autorizado = '".serialize($autorizaciones)."' WHERE id=" . $item['user_id'] );

            // Contar las Autorizaciones
                $counter=[];
                foreach ($autorizaciones as $row) {
                    if( !isset($counter[ $row['accion'] ]) ){
                        $counter[ $row['accion'] ] = 1;
                    }else{
                        $counter[ $row['accion'] ]++;
                    }
                }
            
                $observaciones = '';
                $openpay_id = '';

            // Pagos Negados
                if( isset($counter['negado']) && $counter['negado'] > 0){
                    $estatus = 'Negado';

            // Procesar pago            
                }else if( isset($counter['autorizado']) && $counter['autorizado'] >= 1 ){
                 
                // Parametros solicitud
                    $payoutData = array(
                        'method' => 'bank_account',
                        'amount' => number_format($solicitud->total, 2, '.', ''),
                        'name' => $solicitud->titular,
                        'bank_account' => array(
                            'clabe' => $solicitud->cuenta,
                            'holder_name' => $solicitud->titular,
                        ),
                        'description' => '#'.$solicitud->id . " ".$cuidador->nombre." ".$cuidador->apellido
                    );

                // Enviar solicitud a OpenPay            
                    try{
                        $payout = $openpay->payouts->create($payoutData);
                        $estatus = 'Autorizado';
                        if( $payout->status == 'in_progress' ){
                            $observaciones = '';
                            $estatus = 'in_progress';
                            $openpay_id = $payout->id;
                        }else{
                            $observaciones = $payout->status;
                        }
                    }catch(OpenpayApiTransactionError $e){
                        $estatus = 'error';
                        switch ($e->getCode()) {
                            case 1001:
                                $observaciones = 'El n&utilde;mero de cuenta es invalido';
                                break;
                            case 4001:
                                $observaciones = 'No hay fondos suficientes en la cuenta de pago';
                                break;
                            default:
                                $observaciones = 'Error: ' . $e->getMessage() ;
                                break;
                        }
                    }
                
                //  Actualizar registro
                    $db->query("UPDATE cuidadores_pagos SET estatus='".$estatus."', observaciones='".$observaciones."', openpay_id='".$openpay_id."' WHERE id = " . $item['user_id'] );
                }

            $result['mensaje'] = 'Solicitudes procesadas';
        //}else{
        //    $result['error'] = 'No puedes autorizar las solicitudes de pagos registradas con tu usuario';
        //}

        if( !empty($result['mensaje']) && !empty($result['error']) ){
            $result['mensaje'] = 'Se procesaron las solicitudes que no fueron registradas por ti';
        }
         
    }

    echo json_encode($result);
        

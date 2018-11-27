<?php
	session_start();
error_reporting(E_ALL);
    date_default_timezone_set('America/Mexico_City');

    $raiz = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))));
    include_once($raiz."/vlz_config.php");

    $tema = (dirname(dirname(dirname(dirname(__DIR__)))));
	include_once($tema.'/lib/pagos/pagos_cuidador.php');
	include_once($tema.'/lib/openpay/Openpay.php');

//$openpay = Openpay::getInstance($MERCHANT_ID, $OPENPAY_KEY_SECRET);
//Openpay::setProductionMode( ($OPENPAY_PRUEBAS == 0) );

//	Test IC
 	$openpay = Openpay::getInstance('mbkjg8ctidvv84gb8gan', 'sk_883157978fc44604996f264016e6fcb7');

    $comentarios = $_POST['comentario'];
    $solicitudes = $_POST['users'];
    $admin_id = $_POST['ID'];
    $accion = $_POST['accion'];
    $_pagos = $_SESSION['pago_cuidador'];

    foreach ($solicitudes as $item) {
    	if( array_key_exists($item['user_id'], $_pagos) ){
    		$pago = $_pagos[ $item['user_id'] ];
    		$total = 0;

    		// Metadatos
	    		$cuidador = $pagos->db->get_row("SELECT user_id, nombre, apellido, banco, email FROM cuidadores WHERE user_id = {$pago->user_id}");
	    		$banco = unserialize($cuidador->banco);
			
		    // Autorizaciones
		    	$autorizaciones[$admin_id] = [
                    'fecha'=>date('Y-m-d'),
                    'user_id'=> $admin_id,
                    'accion'=> $accion,
                    'comentario'=> $comentarios,
                ];
				
 
			// Parametros solicitud
                $payoutData = array(
                    'method' => 'bank_account',
                    'amount' => number_format($item['monto'], 2, '.', ''),
                    'name' => $banco['titular'],
                    'bank_account' => array(
                        'clabe' => $banco['cuenta'],
                        'holder_name' => utf8_encode($banco['titular']),
                    ),
                    'description' => 'UID: #'.$row_id
                );
                
            //  Enviar solicitud a OpenPay
                $estatus = 'Autorizado';
                try{
                    $payout = $openpay->payouts->create($payoutData);
                    if( $payout->status == 'in_progress' ){
                        $observaciones = '';
                        $estatus = 'in_progress';
                        $openpay_id = $payout->id;
                    }else{
                        $observaciones = $payout->status;
                    }
                }catch(OpenpayApiConnectionError $c){
                    $estatus = 'error';
                    $observaciones = $c->getMessage();
                }catch(OpenpayApiRequestError $r){
                    $estatus = 'error';
                    $observaciones = $r->getMessage();
                }catch(OpenpayApiAuthError $a){
                    $estatus = 'error';
                    $observaciones = $a->getMessage();
                }catch(OpenpayApiTransactionError $t){
                    $estatus = 'error';
                    switch ( $t->getCode() ) {
                        case 1001:
                            $observaciones = 'El n&utilde;mero de cuenta es invalido';
                            break;
                        case 4001:
                            $observaciones = 'No hay fondos suficientes en la cuenta de pago';
                            break;
                        default:
                            $observaciones = 'Error: ' . $t->getMessage() ;
                            break;
                    }
                }
            
            //  Actualizar registro
	           	if( !empty($openpay_id) && $estatus != 'error'){
	                $pagos->registrar_pago( $item['user_id'], $item['monto'], $openpay_id, $item['comentario'] );
	                if( $item['parcial'] ){
	                	include($raiz.'/wp-load.php');
					    $mensaje = buildEmailTemplate(
					        'pagos/parcial',
					        [
	                			'name' => $pago->nombre.' '.$pago->apellido,
	                			'monto' => $item['monto'],
	                			'comentarios' => $item['comentario']
	                		]
					    );
					    $mensaje = buildEmailHtml(
					        $mensaje, 
					        []
					    );
					    wp_mail( $cuidador->email, "Notificación de pago", $mensaje );
	                }
	           	}else{
                    echo $estatus;
                }
    	}
    }
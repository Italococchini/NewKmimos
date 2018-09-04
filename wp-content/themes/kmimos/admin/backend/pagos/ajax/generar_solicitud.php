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

    $solicitudes = $_POST['users'];
    $admin_id = $_POST['ID'];
    $accion = $_POST['accion'];
    $comentarios = $_POST['comentario'];

    $pagos = $_SESSION['pago_cuidador'];

    foreach ($solicitudes as $item) {
    	if( array_key_exists($item['user_id'], $pagos) ){
    		$pago = $pagos[ $item['user_id'] ];

    		// Metadatos
	    		$cuidador = $db->get_row("SELECT user_id, nombre, apellido, banco FROM cuidadores WHERE user_id = {$pago->user_id}");
	    		$banco = unserialize($cuidador->banco);
	    		$detalle = serialize($pago->detalle);
			
	    	// validar si la solicitud se genero anteriormente
		    	$where = '';
		    	foreach( $pago->detalle as $row ){		
		    		$logica = ( $where != '' )? ' or ' : '' ;
		    		$str = 's:7:"reserva";s:'.strlen($row['reserva']).':"'.$row['reserva'].'";';
		    		$where .= " {$logica} detalle like '%{$str}%' ";
		    	}
		    	if( !empty($where) ){
					$reserva_procesada = $db->get_results("SELECT * FROM cuidadores_pagos WHERE {$where}" );
					if( $reserva_procesada ){
						$item['token'] = '';
					}
		    	}

		    // Autorizaciones
		    	$autorizaciones[$admin_id] = [
                    'fecha'=>date('Y-m-d'),
                    'user_id'=> $admin_id,
                    'accion'=> $accion,
                    'comentario'=> $comentarios,
                ];

			// Validar token    		
	    		if( md5($detalle) == $item['token'] ){
		    		$sql = "INSERT INTO cuidadores_pagos (
			    			admin_id,
			    			user_id,
			    			total,
			    			cantidad,
			    			detalle,
			    			estatus,
			    			autorizado,
			    			cuenta,
			    			titular,
			    			banco
			    		) VALUES (
			    			{$admin_id},
			    			".$pago->user_id.",
			    			'".$pago->total."',
			    			'".$pago->cantidad."',
			    			'{$detalle}',
			    			'por autorizar',
			    			'".serialize($autorizaciones)."',
			    			'".$banco['cuenta']."',
			    			'".$banco['titular']."',
			    			'".$banco['banco']."'
						);";
					$db->query($sql);
					$row_id = $db->insert_id();
					if( $row_id > 0 ){

						// Parametros solicitud
		                    $payoutData = array(
		                        'method' => 'bank_account',
		                        'amount' => number_format($pago->total, 2, '.', ''),
		                        'name' => $banco['titular'],
		                        'bank_account' => array(
		                            'clabe' => $banco['cuenta'],
		                            'holder_name' => $banco['titular'],
		                        ),
		                        'description' => '#'.$row_id." ".$cuidador->nombre." ".$cuidador->apellido
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
		                    $db->query("UPDATE cuidadores_pagos SET estatus='".$estatus."', observaciones='".$observaciones."', openpay_id='".$openpay_id."' WHERE id = " . $row_id );
					}


					print_r($sql);
	    		}else{
		    		print_r('no valido');
	    		}
    	}
    }
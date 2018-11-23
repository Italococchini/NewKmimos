<pre>
<?php

    session_start();

    date_default_timezone_set('America/Mexico_City');
    
    // include('../wp-load.php');
    include('../wp-content/themes/kmimos/lib/enlaceFiscal/Aliados.php');


    $pendientes = $aliados->db->get_results("SELECT * FROM FACTURAS_ALIADOS WHERE estatus = 'Pendiente' ");

    foreach ( $pendientes as $prospecto ) {
    	
    	// Registrar sucursal
    	$id_sucursal = $prospecto->idSucursal;
    	if( empty($id_sucursal) ){
	    	$_sucursal = $aliados->clienteSucursales( $prospecto->rfc );
	    	$sucursal = json_decode($_sucursal);
	    	if( $sucursal->AckEnlaceFiscal->estatusDocumento == 'aceptado' ){		
		    	foreach ( $sucursal->AckEnlaceFiscal->Sucursales as $key => $sucursal ){
		    		$id_sucursal = $sucursal->id;
		    		$aliados->db->query( "UPDATE FACTURAS_ALIADOS SET idSucursal = '{$id_sucursal}' WHERE id = ".$prospecto->id );
		    	}
	    	}    		
    	}

    	// Configurar serie 
    	if( !empty($id_sucursal) ){
            // Series para Facturas
            $_serie = $aliados->clienteSeries( 
                $prospecto->serie, 
                $prospecto->tipoComprobante, 
                $prospecto->regimenFiscal, 
                $prospecto->numFolioFiscal, 
                $prospecto->rfc,
                $id_sucursal
            );
	    	$serie = json_decode($_serie);

            // Serie Notas de Credito
            $_serieNC = $aliados->clienteSeries( 
                'NC',
                'devolucion_desc_bonif',
                $prospecto->regimenFiscal, 
                $prospecto->numFolioFiscal, 
                $prospecto->rfc,
                $id_sucursal
            );
    	}

    	// Buscar datos de prospectos
    	$_info = $aliados->clientesInfo( $prospecto->rfc );
    	$info = json_decode($_info);
    	if( $info->AckEnlaceFiscal->estatusDocumento == 'aceptado' ){
    		$apiKey = $info->AckEnlaceFiscal->Cliente->xApiKey;
    		$token = $info->AckEnlaceFiscal->Cliente->tokenAPI;
			$aliados->db->query("UPDATE FACTURAS_ALIADOS SET estatus='Activo', xApiKey='{$apiKey}', tokenAPI='{$token}' WHERE id=".$prospecto->id);
    	}

    }
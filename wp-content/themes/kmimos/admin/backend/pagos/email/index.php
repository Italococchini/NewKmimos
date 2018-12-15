<?php

	// Template
		$template = 'pagos/total';
	    if( $pago_parcial == true ){
			$template = 'pagos/parcial';
		}

	// Desglose y detalle de transaccion
		$desglose_detalle = '';
		$total = 0;
		if( isset($list_pagos_id) && count($list_pagos_id) > 0 ){
			$ids = implode(',', $list_pagos_id);
			$solicitudes_pagos = $pagos->db->get_results("SELECT * FROM cuidadores_pagos WHERE id in ({$ids})");
			foreach ($solicitudes_pagos as $row) {
				$detalle_pago = unserialize($row->detalle);
				if( !empty($detalle_pago) ){
					foreach ($detalle_pago as $r) {
						$descripcion = "Pago reserva #".$r['reserva'];
						if( isset($r['descripcion']) && !empty($r['descripcion']) ){
							$descripcion = $r['descripcion'];
						} 
						$desglose_detalle .= "
							<tr style='font-size: 14px;'>
								<td>". $descripcion."</td>
								<td style='text-align: right!important;'>$ ". number_format( $r['monto'], 2, ',', '.' )."</td>
							<tr>
						";
						$total += $r['monto'];
					}
				}
			}
		}


	// Construir y enviar email
		$desglose = buildEmailTemplate(
			'pagos/desglose',
			[
				'desglose_detalle' => $desglose_detalle,
				'total' => number_format($total, 2, ',', '.'),
				'transaccion_id' =>  $payout->id,
				'estatus' => "En progreso",
				'titular' => $banco['titular'],
				'cuenta' => substr($banco['cuenta'], 0, 3) ."**********". substr($banco['cuenta'], 13, 5),
			]
		);

		$mensaje = buildEmailTemplate(
			$template,
			[
				'name' => $pago->nombre.' '.$pago->apellido,
				'monto' => number_format($total, 2, ',', '.'),
				'comentarios' => $item['comentario'],
				'desglose' => $desglose,
			]
		);

		$mensaje = buildEmailHtml(
			$mensaje, 
			[]
		);
		// wp_mail( $cuidador->email, "Notificación de pago", $mensaje );
		wp_mail( 'italococchini@gmail.com', "Notificación de pago", $mensaje );

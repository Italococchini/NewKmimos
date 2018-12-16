
<br>
<div style="margin: 10px 0px;">
	<div style="font-size:17px;font-weight:600;letter-spacing:-0.1px;margin-bottom:15px;padding-bottom:5px;border-bottom:solid 1px #bbb;color:#bbb">
        DATOS DE TRANSFERENCIA
    </div>
	<br>
	<div style="padding:1px 0px;"><strong>Referencia:</strong>  [transaccion_id]</div>
	<div style="padding:1px 0px;"><strong>Titular:</strong>  [titular]</div>
	<div style="padding:1px 0px;"><strong>Cuenta:</strong>  [cuenta]</div>
	<div style="padding:1px 0px;"><strong>Total:</strong>  [total]</div>
	<div style="padding:1px 0px;"><strong>Estatus:</strong>  [estatus]</div>
</div>
<br>

<div style="font-size:17px;font-weight:600;letter-spacing:-0.1px;margin-bottom:15px;padding-bottom:5px;border-bottom:solid 1px #bbb">
	DETALLE DE PAGO
</div>
<br>
<table width="100%" style="width: 100%;">
	<thead>
		<tr style="color:#940d99;line-height:1.07;letter-spacing:0.3px;font-weight:600;font-size:14px;">
			<td width="70%">CONCEPTO</td>
			<td width="30%" style="text-align: right!important;">MONTO</td>
		</tr>
	</thead>
	<tbody>
		[desglose_detalle]		
		<tr style="font-weight: bold;border: 1px solid #ccc; background: #efefee;padding: 5px;">
			<td>TOTAL: </td>
			<td style="text-align: right;color:#940d99;"><strong>$ [total]</strong></td>
		</tr>
	</tbody>
</table>
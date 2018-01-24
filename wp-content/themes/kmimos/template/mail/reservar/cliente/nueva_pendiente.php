<div style='text-align:center; margin-bottom: 34px;'>
    <img src='[URL_IMGS]/header_nueva_reserva.png' style='width: 100%;' >
</div>

<div style='padding: 0px; margin-bottom: 34px;'>

    <div style='margin-bottom: 25px; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000;'>
        
        [MODIFICACION] 

        <div style='font-family: Arial; font-size: 20px; font-weight: bold; letter-spacing: 0.4px; color: #6b1c9b; padding-bottom: 19px; text-align: center;'>
            ¡Gracias [name_cliente]!
        </div>  
        <div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 25px; text-align: center;'>
            Recibimos tu solicitud de reserva de <strong>[tipo_servicio]</strong>, para que <strong>[name_cuidador]</strong> atienda a tu(s) peludo(s).
        </div>
        <div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 15px;'>
            Has seleccionado como método de pago: <strong>Pago por Tarjeta de Cr&eacute;dito</strong>. Tu reserva esta pendiente por confirmar.
        </div>
        <div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000;'>
            Te enviaremos la Confirmación o Rechazo del pago de tu Reserva en unos momentos (puede durar desde 30 min a 4 horas).
        </div>
    </div>
   
</div>

<div style='margin-bottom: 39px; text-align: left;'>

    <div style='font-family: Arial; font-size: 11px; font-weight: bold; letter-spacing: -0.1px; color: #0d7ad9; margin-bottom: 8px;'>
        DETALLE DE LAS MASCOTAS
    </div>

    <div style='border-radius: 2.8px; background-color: #f4f4f4;'>
        <table cellpadding="0" cellspacing="0" style='width: 100%;'>
            <tr style='border-bottom: solid 1px #000000;font-family: Arial; font-size: 10px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; font-weight: 600;'>
                <td style='padding: 7px; padding-left: 37px; width: 20px;'>
                    NOMBRE
                </td>
                <td style='padding: 7px; width: 100px;'>
                    RAZA
                </td>
                <td style='padding: 7px; width: 100px;'>
                    EDAD
                </td>
                <td style='padding: 7px; width: 50px;'>
                    TAMA&Ntilde;O
                </td>
                <td style='padding: 7px;'>
                    COMPORTAMIENTO
                </td>
            </tr>

            [mascotas]

        </table>
    </div>

</div>

<div style='margin-bottom: 44px; text-align: left;'>

    <div style='font-family: Arial; font-size: 11px; font-weight: bold; letter-spacing: -0.1px; color: #0d7ad9; margin-bottom: 17px;'>
        DETALLE DEL SERVICIO
    </div>

    [DETALLES_SERVICIO]                
    
    <div style='overflow: hidden;'>
        <table cellpadding="0" cellspacing="0" style='box-sizing: border-box; width: 100%; background-color: #FFF; font-family: Arial; font-size: 10px; font-weight: bold; line-height: 1.5; letter-spacing: 0.2px; color: #000000; border: solid 1px #CCC; border-radius: 2.8px; margin-bottom: 15px;'>
            <tr style=''>
                <td style=' width: 80px; background-color: #f4f4f4; text-align: center; vertical-align: middle;'>
                    <img src='[URL_IMGS]/dog.png'>
                </td>
                <td style=' width: 150px; padding: 7px; padding-left: 37px; border-bottom: solid 1px #CCC;'>
                    CANTIDAD
                </td>
                <td style=' width: 170px; padding: 7px; border-bottom: solid 1px #CCC;'>
                    TIEMPO
                </td>
                <td style=' width: 100px; padding: 7px; border-bottom: solid 1px #CCC;'>
                    PRECIO C/U
                </td>
                <td style=' width: 100px; padding: 7px; border-bottom: solid 1px #CCC; text-align: right;'>
                    SUBTOTAL
                </td>
            </tr>

            [desglose]

            [ADICIONALES]
            [TRANSPORTE]


        </table>
    </div>
    
    [TOTALES]

</div>
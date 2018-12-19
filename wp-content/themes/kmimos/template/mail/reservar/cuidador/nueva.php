<div style='text-align:center; margin-bottom: 34px;'>
	<img src='[URL_IMGS]/header_nueva_reserva.png' style='width: 100%;' >
</div>

<div style='padding: 0px; margin-bottom: 34px;'>

	<div style='margin-bottom: 30px; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; margin-bottom: 18px;'>
		
		[MODIFICACION]
		
		<div style='font-family: Arial; font-size: 20px; font-weight: bold; letter-spacing: 0.4px; color: #6b1c9b; padding-bottom: 19px;'>
			[tipo_servicio] por [name_cliente]
		</div>	
	    <div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000; padding-bottom: 10px;'>
	    	¡Hola <strong>[name_cuidador]</strong>!
	    </div>
	    <div style='font-family: Arial; font-size: 14px; line-height: 1.07; letter-spacing: 0.3px; color: #000000;'>
	    	El cliente <strong>[name_cliente]</strong> te ha enviado una solicitud de reserva.
	    </div>
	</div>

	<div style='text-align: center;'>

		<div style='display: block; border-radius: 2.8px; background-color: #f4f4f4; width: 147px; margin: 0px auto 29px; font-family: Arial; font-size: 12px; letter-spacing: 0.3px; color: #000000; padding: 12px 0px;'>
			Reserva #: <strong>[id_reserva] </strong>
		</div>
        
		<div style='font-family: Arial; font-size: 15px; font-weight: bold; letter-spacing: 0.3px; text-align: center; color: #000000; margin-bottom: 12px;'>
			¿Aceptas la solicitud?
		</div>

		<div style='text-align: center; margin-bottom: 12px;'>
			<a href='[ACEPTAR]' style='text-decoration: none;'><img src='[URL_IMGS]/btn_aceptar.png' style='width: 252px;' /></a>
		</div>

		<div style='font-family: Arial; font-size: 14px; line-height: 1.21; letter-spacing: 0.3px; color: #000000; margin-bottom: 39px;'>
			AHORA NO PUEDO, <a href='[RECHAZAR]' style='font-weight: 400; color: #000;'>RECHAZAR</a>
		</div>

        <div style='margin: 0px auto; width: 300px;'>
            
            <div style='display: table-cell; width: 62px; padding-right: 20px;'>
				<img src='[avatar]' style='width: 62px; height: 62px; border-radius: 50%;' >
			</div>
			<div style='display: table-cell; vertical-align: middle; padding-left: 0px; text-align: left; '>
				<div style='font-family: Arial; font-size: 11px; font-weight: bold; letter-spacing: -0.1px; color: #0d7ad9;'>
					DATOS DEL CLIENTE 
				</div>				    
			    <div style='font-family: Arial; font-size: 20px; font-weight: bold; letter-spacing: 0.4px; color: #000000; margin-bottom: 2px;'>
			    	[name_cliente]
			    </div>			    
			    <div style='font-family: Arial; font-size: 14px; letter-spacing: 0.3px; color: #000000; margin-bottom: 2px;'>
			    	[telefonos_cliente]
			    </div>		    
			    <div style='font-family: Arial; font-size: 14px; letter-spacing: 0.3px; color: #000000; margin-bottom: 12px;'>
			    	[correo_cliente]
			    </div>
			</div>
            
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
                <td style='width: 80px; background-color: #f4f4f4; text-align: center; vertical-align: middle;'>
                    <img src='[URL_IMGS]/dog.png'>
                </td>
                <td style='width: 150px; padding: 7px; padding-left: 37px; border-bottom: solid 1px #CCC;'>
                    CANTIDAD
                </td>
                <td style='width: 170px; padding: 7px; border-bottom: solid 1px #CCC;'>
                    TIEMPO
                </td>
                <td style='width: 100px; padding: 7px; border-bottom: solid 1px #CCC;'>
                    PRECIO UNITARIO
                </td>
                <td style='width: 100px; padding: 7px; border-bottom: solid 1px #CCC;'>
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

<div style='display: table; width: 100%;'>
    <div style='display: table-row;'>
        <div style='display: table-cell; width: 52px; vertical-align: top;'>
            <img src='[URL_IMGS]/exclamacion.png' style='width: 33px;' >
        </div>
        <div style='display: table-cell; font-family: Arial; font-size: 14px; letter-spacing: 0.2px; color: #666666; padding-bottom: 9px; vertical-align: top;'>
            Es necesario que confirmes si aceptas el servicio lo más pronto posible, de no ser así, pasadas las <strong>8 horas el sistema cancelará esta solicitud</strong> y enviará automáticamente una recomendación al cliente sobre otros cuidadores sugeridos para atenderlo.
        </div>
    </div>

    <div style='display: table-row;'>
        <div style='display: table-cell; width: 52px;'>
            &nbsp;
        </div>
        <div style='display: table-cell; font-family: Arial; font-size: 14px; letter-spacing: 0.2px; color: #666666; vertical-align: top;'>
            Si existe <strong>algún cambio</strong> en la reserva por favor <strong>asegúrate que el cliente esté enterado y de acuerdo con eso</strong>, posteriormente contacta al staff Kmimos a la brevedad para realizar los ajustes.
        </div>
    </div>
</div>

<img src='[URL_IMGS]/siguientes_pasos.png' style='display: block; margin: 0px auto 25px; padding-top: 45px;' >

<table cellpadding="0" cellspacing="0" style='width: 600px; margin-bottom: 27px;'>
	<tr>
		<td style='width: 300px; vertical-align: top;'>

			<table style='width: 300px;'>
				<tr>
					<td style='vertical-align: top; padding: 5px; width: 60px;'>
						<img src='[URL_IMGS]/group.png' style='display: block; margin: 0px auto 25px;' >
					</td>
					<td style='font-size: 13px; width: 240px; vertical-align: top; font-family: Arial; letter-spacing: 0.3px; color: #666666; padding: 5px;'>
						<strong style='color: #570089;'>Preséntate</strong> con el cliente cordial y  formalmente.<br> 
						<strong>Tip: Cuida tu imagen</strong> (Vestimenta casual)
					</td>
				</tr>
			</table>

		</td>
		<td style='width: 300px; vertical-align: top;'>

			<table style='width: 300px;'>
				<tr>
					<td style='vertical-align: top; padding: 5px; width: 60px;'>
						<img src='[URL_IMGS]/group-9.png' style='display: block; margin: 0px auto 25px;' >
					</td>
					<td style='font-size: 13px; width: 240px; vertical-align: top; font-family: Arial; letter-spacing: 0.3px; color: #666666; padding: 5px;'>
						En caso de no conocerse en persona, <strong>pide que te envíen fotos del perro</strong> que llegará a tu casa para <strong style='color: #570089;'>confirmar</strong> que sea tal cual lo describió su dueño. 
					</td>
				</tr>
			</table>

		</td>
	</tr>

	<tr>

		<td style='width: 300px; vertical-align: top;'>

			<table style='width: 300px;'>
				<tr>
					<td style='vertical-align: top; padding: 5px; width: 60px;'>
						<img src='[URL_IMGS]/group-9.png' style='display: block; margin: 0px auto 25px;' >
					</td>
					<td style='font-size: 13px; width: 240px; vertical-align: top; font-family: Arial; letter-spacing: 0.3px; color: #666666; padding: 5px;'>
						<div style='margin-bottom: 9px;'><strong>Solicita</strong> que te compartan la cartilla de vacunación del perrito y <strong style='color: #570089;'>verifica</strong> que sus <strong>vacunas</strong> estén al día.</div>
						<div><strong>Tip: Sin cartilla no se harán efectivos</strong> los beneficios veterinarios de Kmimos.</div>
					</td>
				</tr>
			</table>

		</td>

		<td style='width: 300px; vertical-align: top;'>

			<table style='width: 300px;'>
				<tr>
					<td style='vertical-align: top; padding: 5px; width: 60px;'>
						<img src='[URL_IMGS]/group-14.png' style='display: block; margin: 0px auto 25px;' >
					</td>
					<td style='font-size: 13px; width: 240px; vertical-align: top; font-family: Arial; letter-spacing: 0.3px; color: #666666; padding: 5px;'>
						<strong>Revisa al perrito</strong> y <strong style='color: #570089;'>detecta</strong> si hubiese algún rasguño o golpe que pueda traer antes recibirlo, si detectas algo coméntale cordialmente al cliente y envíanos fotos vía whatsapp o correo.
					</td>
				</tr>
			</table>

		</td>
		
	</tr>
</table>

<div style='float:left;width:100%;margin-bottom: 31px;'>   
    <div style='text-align:center;'>
        <p style='font-family: Arial;font-weight: bold; font-size:13px; color:#666; text-align: center; '>
            En caso de dudas, puedes contactarte con nuestro equipo de atención al cliente al <!-- teléfono <?php echo get_region('telefono'); ?>, --> Whatsapp <?php echo get_region('whatsapp'); ?>, o al correo <?php echo get_region('email'); ?>
        </p>
        <div  style='clear:both;'></div>
    </div>
    <div  style='clear:both;'></div>
</div>

<table cellpadding="0" cellspacing="0" style='width: 600px; text-align: center; margin-bottom: 20px;'>
	<tr>
		<td style='width: 167.5px;'>
			<img style='width: 15px; margin-right: 5px;' src='[URL_IMGS]/icon-hueso-color.png' >
			<img style='width: 15px; margin-right: 5px;' src='[URL_IMGS]/icon-hueso-color.png' >
			<img style='width: 15px; margin-right: 5px;' src='[URL_IMGS]/icon-hueso-color.png' >
			<img style='width: 15px; margin-right: 5px;' src='[URL_IMGS]/icon-hueso-color.png' >
		</td>
		<td style='width: 265px; font-family: Arial; font-size: 12px; font-weight: bold; letter-spacing: 0.2px; color: #666666;'>
			PRESÉNTATE Y CONOCE A TU KMIAMIGO
		</td>
		<td style='width: 167.5px;'>
			<img style='width: 15px; margin-left: 5px;' src='[URL_IMGS]/icon-hueso-color.png' >
			<img style='width: 15px; margin-left: 5px;' src='[URL_IMGS]/icon-hueso-color.png' >
			<img style='width: 15px; margin-left: 5px;' src='[URL_IMGS]/icon-hueso-color.png' >
			<img style='width: 15px; margin-left: 5px;' src='[URL_IMGS]/icon-hueso-color.png' >
		</td>
	</tr>
</table>

<div style='font-family: Arial; font-size: 14px; letter-spacing: 0.3px; text-align: center; color: #666666; margin-bottom: 20px;'>
	Recuerda que cada perro tiene un comportamiento diferente, por lo que deberás tener la mayor información posible sobre sus comportamientos.
</div>

<table cellpadding="0" cellspacing="0" style='width: 600px; font-size: 13px; margin-bottom: 27px; text-align: left;'>
	<tr>
		<td style='width: 200px; vertical-align: top;'>
			<div style='font-family: Arial; font-size: 11px; font-weight: bold; letter-spacing: 0.2px; color: #666666; margin-bottom: 9px;'>
				SOBRE SU RUTINA DIARIA
			</div>
			<div style='font-family: Arial; font-size: 13px; letter-spacing: 0.3px; color: #666666;'>
				Por ejemplo:<br>
				¿Sale a pasear?<br>
				¿A qué hora come y hace del baño?
			</div>
		</td>

		<td style='width: 200px; vertical-align: top;'>

			<div style='font-family: Arial; font-size: 11px; font-weight: bold; letter-spacing: 0.2px; color: #666666; margin-bottom: 9px;'>
				SOBRE SU COMPORTAMIENTO
			</div>
			<div style='font-family: Arial; font-size: 13px; letter-spacing: 0.3px; color: #666666;'>
				Por ejemplo:<br>
				¿Cómo interactúa con otros perros y personas?<br>
				¿Cómo reacciona con un extraño?
			</div>

		</td>

		<td style='width: 200px; vertical-align: top;'>

			<div style='font-family: Arial; font-size: 11px; font-weight: bold; letter-spacing: 0.2px; color: #666666; margin-bottom: 9px;'>
				SOBRE SU &Aacute;NIMO
			</div>
			<div style='font-family: Arial; font-size: 13px; letter-spacing: 0.3px; color: #666666;'>
				Por ejemplo:<br>
				¿Cómo se comporta cuando está triste o estresado?<br>
				¿Qué hace su dueño cuando está triste o estresado?
			</div>

		</td>
	</tr>
</table>
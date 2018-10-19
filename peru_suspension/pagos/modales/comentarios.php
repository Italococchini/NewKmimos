<?php include_once("../lib/pagos.php"); 
	extract($_POST);
	$pago = $pagos->db->get_row("SELECT * FROM cuidadores_pagos WHERE id =".$ID);
	$pago_data = unserialize($pago->autorizado);


	foreach ($pago_data as $key => $value) {

		if( $value['comentario'] != '' ){
			$nombre = $pagos->db->get_var("SELECT meta_value FROM wp_usermeta WHERE meta_key='first_name' and user_id = {$key}", 'meta_value');
	        $apellido = $pagos->db->get_var("SELECT meta_value FROM wp_usermeta WHERE meta_key='last_name' and user_id = {$key}", 'meta_value');
	        $color_class = ( $value['accion'] == 'negado' )? 'item-danger' : 'item-success';
		?>
		<article style="border-bottom: 1px solid #ccc;">
			<div>
				<small style="font-size:12px;"><?php echo $value['fecha']; ?></small>
				por
				<strong><?php echo utf8_encode($nombre)." ".utf8_encode($apellido); ?></strong>
				<span class="items-span <?php echo $color_class; ?>"><?php echo $value['accion']; ?></span>
			</div>
			<p> <strong>Observaci&oacute;n:</strong> 
				<?php echo $value['comentario']; ?>
			</p>
		</article>

	<?php } ?>
<?php } ?>
<?php
	$raiz = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
	include_once($raiz."/vlz_config.php");
	include_once("../funciones/db.php");
	include_once("../funciones/generales.php");

	if( !isset($_SESSION)){ session_start(); }

	extract($_POST);

	$cupones = ( isset($cupones) )? $cupones : [] ;


	$db = new db( new mysqli($host, $user, $pass, $db) );

	function aplicarCupon($db, $cupon, $cupones, $total, $validar, $cliente = "", $servicio = ""){
		
		/* Cupones Especiales */

			if( strtolower($cupon) == "buenfin17" ){
				$cuidador = $db->get_var("SELECT post_author FROM wp_posts WHERE ID = '{$servicio}'");
				$cuidador = $db->get_row("SELECT * FROM cuidadores WHERE user_id = '{$cuidador}'");
				$atributos = unserialize($cuidador->atributos);
				if( $atributos['destacado'] != 1 ){
					echo json_encode(array(
						"error" => "El cupón [ {$cupon} ] no puede ser aplicado con este cuidador."
					));
					exit;
				}
			}

		/* Fin Cupones Especiales */

		/* Get Data */

			$sub_descuento = 0; $otros_cupones = 0;
			if( count($cupones) > 0 ){
				foreach ($cupones as $value) {
					$sub_descuento += $value[1];
					if( strpos( $value[0], "saldo" ) === false ){
						$otros_cupones++;
					}
					if( $value[2] == 1 ){
						echo json_encode(array(
							"error" => "El cupón [ {$value[0]} ] ya esta aplicado y no puede ser usado junto a otros cupones"
						));
						exit;
					}
				}
			}

			$xcupon = $db->get_row("SELECT * FROM wp_posts WHERE post_title = '{$cupon}'");

			$xmetas = $db->get_results("SELECT * FROM wp_postmeta WHERE post_id = '{$xcupon->ID}'");
			$metas = array();
			foreach ($xmetas as $value) {
				$metas[ $value->meta_key ] = $value->meta_value;
			}

			$se_uso = $db->get_var("SELECT count(*) FROM wp_postmeta WHERE post_id = {$xcupon->ID} AND meta_key = '_used_by' AND meta_value = {$cliente}");

		/* Validaciones */

			if( $validar === true ){

				if( $otros_cupones > 0 && $metas["individual_use"] == "yes" ){
					echo json_encode(array(
						"error" => "El cupón [ {$cupon} ] no puede ser usado junto a otros cupones"
					));
					exit;
				}

				if( isset($cupones) ){
					if( ya_aplicado($cupon, $cupones) ){
						echo json_encode(array(
							"error" => "El cupón ya fue aplicado"
						));
						exit;
					}
				}

				if( $xcupon == false ){
					echo json_encode(array(
						"error" => "Cupón Invalido"
					));
					exit;
				}

				if( $metas["expiry_date"] != "" ){
				$hoy = time();
					$expiracion = (strtotime($metas["expiry_date"]))+86399;
					if( $hoy > $expiracion ){
						echo json_encode(array(
							"error" => "El cupón ya expiro"
						));
						exit;
					}
				}

				if( $metas["usage_limit_per_user"]+0 > 0 ){
					if( $se_uso >= $metas["usage_limit_per_user"]+0 ){
						echo json_encode(array(
							"error" => "El cupón ya fue usado"
						));
						exit;
					}
				}

				if( $metas["usage_limit"]+0 > 0 ){
					if( $se_uso >= $metas["usage_limit"]+0 ){
						echo json_encode(array(
							"error" => "El cupón ya fue usado"
						));
						exit;
					}
				}
				
			}

		/* Calculo */
			$descuento = 0;
			switch ( $metas["discount_type"] ) {
				case "percent":
					$descuento = $total*($metas["coupon_amount"]/100);
				break;
				case "fixed_cart":
					$descuento = $metas["coupon_amount"];
				break;
			}

			if( $servicio != 0){
				if( !isset($_SESSION)){ session_start(); }
				$id_session = 'MR_'.$servicio."_".md5($cliente);
				if( isset($_SESSION[$id_session] ) ){
					if( strpos( $cupon, "saldo" ) !== false ){
						$descuento += $_SESSION[$id_session]['saldo_temporal'];
					}
				}
			}

			$sub_descuento += $descuento;

			if( ($total-$sub_descuento) < 0 ){
				$descuento += ( $total-$sub_descuento );
			}

			if( $metas["individual_use"] == "yes" ){
				return array(
					$cupon,
					$descuento,
					1
				);
			}else{
				return array(
					$cupon,
					$descuento,
					0
				);
			}
	}

	if( $reaplicar == "1" ){
		$xcupones = array();
		if( count($cupones) > 0 ){
			foreach ($cupones as $cupon) {
				$xcupones[] = aplicarCupon($db, $cupon[0], $xcupones, $total, false, $cliente, $servicio);
			}
			$cupones = $xcupones;
		}
	}else{
		$cupones[] = aplicarCupon($db, $cupon, $cupones, $total, true, $cliente, $servicio);
	}

	/* Retorno */
		echo json_encode(array(
			"cupones" => $cupones,
			"reaplicar"    => $reaplicar,
			"post"		=> $_POST
		));

?>
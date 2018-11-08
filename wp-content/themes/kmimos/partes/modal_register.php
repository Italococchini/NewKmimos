<?php
$datos = kmimos_get_info_syte();
$referidos = get_referred_list_options();
$referidos_options = ""; $pixel_petco = "";
if( $_SESSION["wlabel"] != "petco" ){
	$referidos_options = '<option value="">Dónde nos conoció?</option>';
}else{
	$pixel_petco = "";
}
foreach ($referidos as $key => $value) {
	$selected="";
	if(array_key_exists("wlabel",$_SESSION)){
		$wlabel=$_SESSION['wlabel'];

		if($key=='Volaris' && $wlabel=='volaris'){
			$selected='selected';

		}else if($key=='Vintermex' && $wlabel=='viajesintermex'){
			$selected='selected';
		}else if($key=='Petco' && $wlabel=='petco'){
			$selected='selected';
		}
	}

	$referidos_options.= "<option value='{$key}' $selected>{$value}</option>";
}

include dirname(__DIR__).'/terminos_HTML.php';

$NEW_HTML_TERMINOS = "";
$parrafos = explode("\n", $HTML_TERMINOS);
foreach ($parrafos as $parrafo) {
	$NEW_HTML_TERMINOS .= "<p>".$parrafo."</p>";
}

$_comportamiento_gatos = get_post_meta($pet_id, 'comportamiento_gatos', true);
$_comportamiento_gatos = (array) json_decode($_comportamiento_gatos);

$comportamientos_db = $wpdb->get_results("SELECT * FROM comportamientos_mascotas");
foreach ($comportamientos_db as $value) {
    $comportamientos[ $value->slug ] =  $value->nombre;
}

$comportamientos_str = '<div class="km-registro-checkbox">';
$i = 0;
if( !empty($comportamientos) ){
	foreach ($comportamientos as $key => $value) {
	    $comportamientos_str .= '
	    	<div class="km-registro-checkbox-opcion">
				<p>'.$value.'</p>
				<div class="km-check-gato">
					<input type="checkbox" value="0" id="km-check-gato-'.$i.'" class="km-check-gatos" name="comportamiento_gatos_'.$key.'" />
					<label for="km-check-gato-'.$i.'"></label>
				</div>
			</div>
	    ';
	    $i++;
	}
}

$comportamientos_str .= '</div>';

$HTML .='
<!-- POPUPS REGISTRARTE -->
<div class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" id="popup-registrarte" style="padding: 40px;">
	<div class="modal-dialog">
		<div class="modal-content">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="btn_cerrar" >×</button>
			<div class="popup-registrarte-1">
				<p class="popup-tit">REGISTRARME</p>
				
				<a href="#" class="km-btn-fb hidden" onclick="login_facebook();">
					<img src="'.getTema().'/images/new/icon/km-redes/icon-fb-blanco.svg">
					REGISTRARME CON FACEBOOK
				</a>
				
				<a href="#" class="google_auth hidden km-btn-border" id="customBtn1">
					<img src="'.getTema().'/images/new/icon/km-redes/icon-gmail.svg">
					REGISTRARME CON GOOGLE
				</a>
			
				<div class="alert alert-danger" style="
					display:none;
		            -webkit-transition: All 1s; /* Safari */
		            transition: All 1s;
					" 
					data-error="auth"></div>

				<div class="line-o hidden">
					<p class="text-line">o</p>
					<div class="bg-line"></div>
				</div>


				<a 	href="javascript: '.$pixel_petco.';" 
					class="km-btn-correo km-btn-popup-registrarte-1" 
					data-target="social-next-step">
					<img src="'.getTema().'/images/new/icon/km-redes/icon-mail-blanco.svg">
					REGISTRARME POR CORREO ELECTRÓNICO
				</a>


				<p style="color: #979797; margin-top: 20px;">Al crear una cuenta, aceptas las <a style="color: blue;" target="_blank" href="'.site_url().'/terminos-y-condiciones/">condiciones del servicio y la Política de privacidad</a> de Kmimos.</p>

				<!--
				<p><b>Dudas escríbenos</b></p>
				<div class="row">
					<div class="col-xs-12"><p><img style="width: 20px; margin-right: 5px; position: relative; top: -3px;" src="'.getTema().'/images/new/icon/km-redes/icon-wsp.svg">En caso de dudas escríbenos al whatsapp '.$datos["whatsapp"].'</p></div>
				</div>
				-->

				<p><b>Dudas escríbenos</b></p>
				<div class="row">
					<div class="col-xs-12 col-sm-4"><p><img style="width: 20px; margin-right: 5px; position: relative; top: -3px;" src="'.getTema().'/images/new/icon/km-redes/icon-wsp.svg"> +52 (33) 1261 4186</p></div>
					<div class="col-xs-12 col-sm-8"><p><img style="width: 12px; margin-right: 5px; position: relative; top: -1px;" src="'.getTema().'/images/new/icon/km-redes/icon-cel.svg"> Llamada : 01 (55) 8526 1162</p></div>
				</div>

				<hr>
				<div class="row">
					<div class="col-xs-5">
						<p>¿Ya tienes una cuenta?</p>
					</div>
					<div class="col-xs-7">
						<a href="#" class="modal_show km-btn-border" data-modal="#popup-iniciar-sesion"><b>INICIAR SESIÓN</b></a>
					</div>
				</div>
			</div>
			<div class="popuphide popup-registrarte-nuevo-correo">
				
				<span class="hidden">
					<p style="color: #979797; text-align: center;">Regístrate por <a href="#" onclick="login_facebook();">Facebook</a> o <a href="#" class="google_auth" id="customBtn2">Google</a></a></p>
				</span>

					<h3 style="margin: 0; text-align: center;">Completa tus datos</h3>
				<form id="form_nuevo_cliente" name="form_nuevo_cliente" enctype="multipart/form-data" method="POST" autocomplete="off">	
					<div class="km-box-form">
						<div class="content-placeholder">

							<input type="hidden" id="facebook_cliente_id" name="facebook_auth_id" 
								class="social_facebook_id" value="">
							<input type="hidden" id="google_cliente_id" name="google_auth_id" 
								class="social_google_id " value="">

							<div class="img_registro_cliente" style="position: relative">
								<div class="km-datos-foto vlz_rotar" id="km-datos-foto-profile" style="background-image: url('.getTema().'/images/popups/registro-cuidador-foto.png);">
									<div id="loading-perfil" style="width:100%;line-height: 100%;display:none" class="vlz_cargando">
										<img src="'.getTema().'/images/new/bx_loader.gif" class="img-responsive">
									</div>
								</div>

								<div id="rotar_i" class="btn_rotar" style="display: none;" data-orientacion="left"> <i class="fa fa-undo" aria-hidden="true"></i> </div>
				                <div id="rotar_d" class="btn_rotar" style="display: none;" data-orientacion="right"> <i class="fa fa-repeat" aria-hidden="true"></i> </div>

				                <div class="btn_aplicar_rotar" style="display: none;"> Aplicar Cambio </div>
							</div>
							
							<input type="file" class="hidden" id="carga_foto_profile" accept="image/*">
							<input type="hidden" id="img_profile" name="img_profile" value="" class="vlz_rotar_valor">

							<div class="label-placeholder">
								<label>Nombre</label>
								<input type="text" id="nombre" name="nombre" maxlength="30" data-charset="alfxlf" data-change="alfxlf" class="input-label-placeholder social_firstname" pattern=".{3,}">
							</div>
							<div class="label-placeholder">
								<label>Apellido</label>
								<input type="text" name="apellido" id="apellido" maxlength="30"  data-charset="alfxlf" data-change="alfxlf" class="input-label-placeholder social_lastname" pattern=".{3,}">
							</div>

							<div class="label-placeholder">
								<label>IFE/Documento de Identidad</label>
								<input type="text" name="ife" id="ife" class="input-label-placeholder" data-charset="num" data-change="num" maxlength="13">
							</div>
							<div class="label-placeholder verify">
								<label>Correo electrónico</label>
								<input
									type="email" 
									name="email_1" 
									data-verify="active" 
									id="email_1" 
									class="verify_mail input-label-placeholder social_email" 
									data-charset="cormlfnum" 
									data-change="cor" 
									pattern="[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@[a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{1,5}">
								<span class="verify_result"></span>
							</div>
							<div class="label-placeholder">
								<label>Crea tu contraseña</label>
								<input type="password" name="pass" id="pass" maxlength="20"  class="input-label-placeholder" autocomplete="off">
							</div>
							<div class="label-placeholder">
								<label>Teléfono</label>
								<input type="text" name="movil" id="movil" class="input-label-placeholder" data-charset="num" data-change="num" maxlength="10">
							</div>
							<div class="km-datos-mascota">
								<select class="km-datos-mascota-opcion bg-select-custom" name="genero" id="genero">
									<option value="">Género</option>
									<option value="hombre">Masculino</option>
									<option value="mujer">Femenino</option>
								</select>
							</div>
							<div class="km-datos-mascota">
								<select class="km-datos-mascota-opcion bg-select-custom" name="edad" id="edad">
									<option value="">Edad</option>
									<option value="18-25">18-25 años</option>
									<option value="25-35">26-35 años</option>
									<option value="Mayor a 36">Mayor 36 años</option>
								</select>
							</div>
							<div class="km-datos-mascota hidden">
								<select class="km-datos-mascota-opcion bg-select-custom" name="fumador" id="fumador">
									<option value="">Es Fumador</option>
									<option value="SI">Si</option>
									<option value="NO">No</option>
								</select>
							</div>
							<div class="km-datos-mascota">
								<select id="referido" name="referido bg-select-custom" class="km-datos-mascota-opcion" data-title="Debes seleccionar una opción" required>
									'.$referidos_options.'
								</select>
							</div>


						</div>
					</div>
				</form>
				
				<!-- span id="guardando"></span -->
				<!-- div id="resp"></div -->
				
				<a href="#" id="siguiente" class="km-btn-correo km-btn-popup-registrarte-nuevo-correo">SIGUIENTE</a>

				<p style="color: #979797; margin-top: 20px;">Al crear una cuenta, aceptas las <a style="color: blue;" target="_blank" href="'.site_url().'/terminos-y-condiciones/">condiciones del servicio y la Política de privacidad</a> de Kmimos.</p>
				
				<div class="row">
					<div class="col-xs-12 col-sm-4"><p><img style="width: 20px; margin-right: 5px; position: relative; top: -3px;" src="'.getTema().'/images/new/icon/km-redes/icon-wsp.svg">  +52 (33) 1261 4186</p></div>
					<div class="col-xs-12 col-sm-8"><p><img style="width: 12px; margin-right: 5px; position: relative; top: -1px;" src="'.getTema().'/images/new/icon/km-redes/icon-cel.svg"> Llamada: 01 (55) 8526 1162</p></div>
				</div>

				<!-- p><img style="width: 20px; margin-right: 5px; position: relative; top: -3px;" src="'.getTema().'/images/new/icon/km-redes/icon-wsp.svg">En caso de dudas escríbenos al whatsapp '.$datos["whatsapp"].'</p -->
				
				<hr>
				<div class="row">
					<div class="col-xs-5">
						<p>¿Ya tienes una cuenta?</p>
					</div>
					<div class="col-xs-7">
						<a href="#" class="modal_show km-btn-border km-link-login" data-modal="#popup-iniciar-sesion"><b>INICIAR SESIÓN</b></a>
					</div>
				</div>
			</div>


			<div class="popuphide popup-condiciones">
				<h3 style="margin: 0; text-align: center;">TERMINOS Y CONDICIONES</h3>
				<div class="terminos_container">
					'.$NEW_HTML_TERMINOS.'
				</div>
				<a 
					href="#" 
					id="btn_si_acepto" 
					class="km-btn-correo btn_disable"
				>
					ACEPTAR TERMINOS Y CONDICIONES
				</a>
				<a 
					href="#" 
					id="btn_no_acepto" 
					class=""
				>
					No acepto los terminos y condiciones
				</a>
			</div>


			<div class="popuphide popup-registrarte-final">
				<h3 style="margin: 0; text-align: center;">¡FELICIDADES,<br>TU REGISTRO SE REALIZÓ CON ÉXITO!</h3>
				<img src="'.getTema().'/images/popups/km-registro-exitoso.png">
				<a 
					href="javascript:;" 
					id="btn_registrar_mascota" 
					class="km-btn-correo"
				>
					REGISTRAR UNA MASCOTA
				</a>
			</div>


			<div class="popuphide popup-registrarte-datos-mascota">
				<h3 style="margin: 0; text-align: center;">Datos de tus Mascotas</h3>
				<p style="text-align: center;">Queremos conocer más sobre tus mascotas, llena los campos</p>

				<form id="nueva_mascota" enctype="multipart/form-data" method="POST">

					<div class="img_registro_cliente" style="position: relative">
						<div class="km-datos-foto vlz_rotar" id="km-datos-foto" data-init-img="'.getTema().'/images/popups/registro-cuidador-foto.png" style="background-image: url('.getTema().'/images/popups/registro-cuidador-foto.png);">
							<div id="loading-mascota" style="width:100%;line-height: 100%;display:none" class="vlz_cargando">
								<img src="'.getTema().'/images/new/bx_loader.gif" class="img-responsive">
							</div>
						</div>

						<div id="rotar_i" class="btn_rotar" style="display: none;" data-orientacion="left"> <i class="fa fa-undo" aria-hidden="true"></i> </div>
		                <div id="rotar_d" class="btn_rotar" style="display: none;" data-orientacion="right"> <i class="fa fa-repeat" aria-hidden="true"></i> </div>

		                <div class="btn_aplicar_rotar" style="display: none;"> Aplicar Cambio </div>
					</div>
					
					<input type="file" class="hidden" id="carga_foto" accept="image/*">
					<input type="hidden" id="img_pet" name="img_pet" value="" class="vlz_rotar_valor">
					
					<div class="km-box-form">
						<div class="content-placeholder">
							<div class="label-placeholder">
								<label>Nombre de tu mascota</label>
								<input type="text" name="nombre_mascota" data-charset="xlf" data-change="xlf" id="nombre_mascota" class="input-label-placeholder">
							</div>
							<div class="km-datos-mascota">
								<select class="km-datos-mascota-opcion bg-select-custom" name="tipo_mascota" id="tipo_mascota">
									<option value="">Tipo de Mascota</option>
									<option value="2605">Perros</option>
									<option value="2608">Gatos</option>
								</select>
								<select class="km-datos-mascota-opcion bg-select-custom" name="raza_mascota" id="raza_mascota">
									<option value="">Raza de la Mascota</option>
								</select>
							</div>
							<div class="label-placeholder">
								<label>Color de tu mascota</label>
								<input type="text" name="color_mascota" data-charset="xlf" data-change="xlf" id="color_mascota" class="input-label-placeholder">
							</div>
							<div class="km-fecha-nacimiento">
								<input type="text" name="date_birth" id="datepets" placeholder="Fecha de Nacimiento" class="date_birth" readonly>
							</div>
							<div class="km-datos-mascota">
								<select class="km-datos-mascota-opcion bg-select-custom" name="genero_mascota" id="genero_mascota">
									<option value="">Género</option>
									<option value="1">Macho</option>
									<option value="2">Hembra</option>
								</select>
							</div>
						</div>
					</div>
					<div id="tamanios_mascota" class="row row-sin-padding" class="tamano-mascota-content" style="margin-bottom: 20px;">
						<div class="col-xs-6 col-sm-3">
							<div class="km-opcion" id="select_1" value="0">
								<img src="'.getTema().'/images/new/icon/icon-pequenio.svg" width="25">
								<br>
								<div class="km-opcion-text">
									<b>PEQUEÑO</b><br> 0 a 25 cm
								</div>
							</div>
						</div>
						<div class="col-xs-6 col-sm-3">
							<div class="km-opcion" id="select_2" value="1">
								<img src="'.getTema().'/images/new/icon/icon-mediano.svg" width="25">
							<br>
								<div class="km-opcion-text">
									<b>MEDIANO</b><br> 25 a 58 cm
								</div>
							</div>
						</div>
						<div class="col-xs-6 col-sm-3">
							<div class="km-opcion" id="select_3" value="2">
								<img src="'.getTema().'/images/new/icon/icon-grande.svg" width="25">
							<br>
								<div class="km-opcion-text">
									<b>GRANDE</b><br> 58 a 73 cm</div>
								</div>
						</div>
						<div class="col-xs-6 col-sm-3">
							<div class="km-opcion" id="select_4" value="3">
								<img src="'.getTema().'/images/new/icon/icon-gigante.svg" width="25">
							<br>
								<div class="km-opcion-text"><b>
									GIGANTE</b><br> 73 a 200 cm
								</div
							></div>
						</div>
					</div>
					<div class="km-registro-checkbox">
						<div class="km-registro-checkbox-opcion">
							<p>Mascota Esterilizada</p>
							<div class="km-check-1">
								<input type="checkbox" value="0" id="km-check-1" name="estilizada" />
								<label for="km-check-1"></label>
							</div>
						</div>
						<div class="km-registro-checkbox-opcion">
							<p>Mascota Sociable</p>
							<div class="km-check-2">
								<input type="checkbox" value="0" id="km-check-2" name="sociable" />
								<label for="km-check-2"></label>
							</div>
						</div>
					</div>
					<div class="km-registro-checkbox" style="margin-top: 0px;">
						<div class="km-registro-checkbox-opcion">
							<p>Agresiva con Humanos</p>
							<div class="km-check-3">
								<input type="checkbox" value="0" id="km-check-3" name="agresiva_humano" />
								<label for="km-check-3"></label>
							</div>
						</div>
						<div class="km-registro-checkbox-opcion">
							<p>Agresiva con Mascotas</p>
							<div class="km-check-4">
								<input type="checkbox" value="0" id="km-check-4" name="agresiva_mascota" />
								<label for="km-check-4"></label>
							</div>
						</div>
					</div>

					<div id="comportamiento_gatos" style="display: none;">
						<span style="font-weight: 600; font-size: 15px; color: #777;">Comportamientos para Gatos</span>
						<div>
							'.$comportamientos_str.'
						</div>
						<div class="error_seleccionar_uno no_error">
				            Debe seleccionar al menos un comportamiento
				        </div>
					</div>

				</form>						
				<a href="#" class="km-btn-correo km-btn-popup-registrarte-datos-mascota">REGISTRARME</a>
				<p style="color: #979797; margin-top: 20px;">Al crear una cuenta, aceptas las <a style="color: blue;" target="_blank" href="'.site_url().'/terminos-y-condiciones/">condiciones del servicio y la Política de privacidad</a> de Kmimos.</p>
				<p><img style="width: 20px; margin-right: 5px; position: relative; top: -3px;" src="'.getTema().'/images/new/icon/km-redes/icon-wsp.svg">En caso de dudas escríbenos al whatsapp '.$datos["whatsapp"].'</p>
			</div>
			<div class="popuphide popup-registrarte-final-0">
				<h3 style="margin: 0; text-align: center;">¡FELICIDADES,<br>TU MASCOTA SE REGISTRO EXITOSAMENTE!</h3>
				<img src="'.getTema().'/images/popups/km-registro-exitoso.png">
				<a href="javascript:;" onclick="finalizar_proceso();" id="btn_iniciar_sesion" data-url="'.get_home_url().'/perfil-usuario/" class="km-btn-correo">INICIAR SESIÓN</a>
			</div>
		</div>
	</div>
</div>	

';
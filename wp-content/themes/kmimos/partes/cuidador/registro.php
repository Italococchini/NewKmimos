<!-- POPUPS REGISTRO -->
<?php $info = kmimos_get_info_syte(); ?>
<div id="popup-registro-cuidador1" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onClick="redireccionar();" >×</button>
			<div class="popup-registro-cuidador active">
				
				<a href="javascript:;" onClick="login_facebook();" class="km-btn-fb hidden"><img src="<?php echo getTema(); ?>/images/new/icon/km-redes/icon-fb-blanco.svg">REGISTRARME CON FACEBOOK</a>
				
				<a href="#"  id="registro_cuidador_google" class="google_auth km-btn-border hidden"><img src="<?php echo getTema(); ?>/images/new/icon/km-redes/icon-gmail.svg">REGISTRARME CON GOOGLE</a>

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
				<a href="#" data-target="social-next-step" class="km-btn-correo km-btn-popup-registro-cuidador">
					<img src="<?php echo getTema(); ?>/images/new/icon/km-redes/icon-mail-blanco.svg">REGISTRARME POR CORREO ELECTRÓNICO
				</a>
				<p style="color: #979797">Al crear una cuenta, <a href="<?php echo get_home_url(); ?>/terminos-y-condiciones/">aceptas las condiciones del servicio y la Política de privacidad</a> de Kmimos.</p>
				<p><b>Dudas escríbenos</b></p>
				<div class="row">
					<div class="col-xs-6"><p><img style="width: 20px; margin-right: 5px; position: relative; top: -3px;" src="<?php echo getTema(); ?>/images/new/icon/km-redes/icon-wsp.svg"> <?php echo get_region('whatsapp'); ?></p></div>
					<div class="col-xs-6"><p><a href="#"><img style="width: 15px; margin-right: 5px; position: relative; top: -1px;" src="<?php echo getTema(); ?>/images/new/icon/km-redes/icon-mail.svg"><?php echo get_region('email'); ?></a></p></div>
				</div>
				<hr>
				<div class="row">
					<div class="col-xs-5">
						<p>¿Ya tienes una cuenta?</p>
					</div>
					<div class="col-xs-7">
						<a href="javascrip:;" data-modal="#popup-iniciar-sesion" class="modal_show km-btn-border"><b>INICIAR SESIÓN</b></a>
					</div>
				</div>
			</div>

		<form id="vlz_form_nuevo_cuidador" style="padding-bottom: 0px;" autocomplete="off" method="POST">
			<input type="hidden" name="google_auth_id"   class="social_google_id"	 value="">
			<input type="hidden" name="facebook_auth_id" class="social_facebook_id"  value="">

			<div class="popuphide popup-registro-cuidador-correo">
				
				<p class="hidden" style="color: #979797; text-align: center;">Regístrate por 
				<a href="javascript:;" onClick="login_facebook();">Facebook</a> o 
				<a href="#" class="google_auth" >Google</a></a></p>

				<h3 style="margin: 0; text-align: center;">Completa tus datos</h3>
				<div class="km-box-form">
					<div class="content-placeholder">
						<div class="label-placeholder">
							<label>Nombre</label>
							<input type="text" data-charset="xlf" id="rc_nombres" name="rc_nombres" value="" class="input-label-placeholder social_firstname solo_letras" maxlength="20">
							<small data-error="rc_nombres" style="visibility: hidden;"></small>
						</div>
						<div class="label-placeholder">
							<label>Apellido</label>
							<input type="text" data-charset="xlf" name="rc_apellidos" value="" class="input-label-placeholder social_lastname solo_letras"  maxlength="20">
							<small data-error="rc_apellidos" style="visibility: hidden;"></small>
						</div>
						<div class="label-placeholder">
							<label><?php echo get_region("identidad"); ?></label>

							<input type="text"  maxlength="<?php echo get_region("identidad-length"); ?>"  data-charset="num" name="rc_ife" value="" class="input-label-placeholder solo_numeros">

							<small data-error="rc_ife" style="visibility: hidden;"></small>
						</div>
						<div class="label-placeholder fecha_placeholder">
							<label>Fecha de Nacimiento</label>
							<input type="text" name="fecha" id="fecha" class="input-label-placeholder" placeholder="dd/mm/yyyy" readonly />
							<small data-error="fecha" style="visibility: hidden;"></small>
						</div>
						<div class="label-placeholder">
							<label>Correo electrónico</label>
							<input type="email" name="rc_email"  maxlength="250" data-charset="cormlfnum" autocomplete="off" type='text' id='email_1' value="" class="social_email input-label-placeholder">
							<small data-error="rc_email" style="visibility: hidden;"></small>
						</div>
						<div class="label-placeholder">
							<label>Crea tu contraseña</label>
							<input type="password" data-clear name="rc_clave"  maxlength="50" value="" class="input-label-placeholder" autocomplete="off">
							<small data-error="rc_clave" style="visibility: hidden;"></small>
						</div>
						<div class="label-placeholder">
							<label>Teléfono</label>
							<input type="text" name="rc_telefono" data-charset="num" maxlength="<?php echo get_region("identidad-length"); ?>" value="" class="input-label-placeholder solo_numeros">
							<small data-error="rc_telefono" style="visibility: hidden;"></small>
						</div>
						<div class="label-placeholder">
							<label>¿Cómo nos conoció?</label>
							<select class="km-datos-estado-opcion km-select-custom" name="rc_referred">
								<option value="">¿Cómo nos conoció?</option>
								<?php $list = get_referred_list_options();
									foreach( $list as $key => $item ){ ?>
									<option value="<?php echo $key; ?>"><?php echo $item; ?></option>
								<?php } ?>
							</select>
							<small data-error="rc_referred" style="visibility: hidden;"></small>
						</div>
					</div>
				</div>
				<a href="#" class="km-btn-correo km-btn-popup-registro-cuidador-correo">SIGUIENTE</a>

				<p style="color: #979797">Al crear una cuenta, <a href="<?php echo get_home_url(); ?>/terminos-y-condiciones/">aceptas las condiciones del servicio y la Política de privacidad</a> de Kmimos.</p>
				
				<p><b>Dudas escríbenos</b></p>
				<div class="row">
					<div class="col-xs-6"><p><img style="width: 20px; margin-right: 5px; position: relative; top: -3px;" src="<?php echo getTema(); ?>/images/new/icon/km-redes/icon-wsp.svg"> <?php echo get_region('whatsapp'); ?></p></div>
					<div class="col-xs-6"><p><a href="#"><img style="width: 15px; margin-right: 5px; position: relative; top: -1px;" src="<?php echo getTema(); ?>/images/new/icon/km-redes/icon-mail.svg"><?php echo get_region('email'); ?></a></p></div>
				</div>

				<hr>
				<div class="row">
					<div class="col-xs-5">
						<p>¿Ya tienes una cuenta?</p>
					</div>
					<div class="col-xs-7">
						<a href="javascrip:;" data-modal="#popup-iniciar-sesion" class="modal_show km-btn-border"><b>INICIAR SESIÓN</b></a>
					</div>
				</div>
			</div>
			
			<div class="popuphide popup-registro-exitoso">
				<div class="overlay"></div>
				<div class="popup-registro-exitoso-text">
					<h3>¡Genial! <span data-target="name"></span>,<br>ya creaste tu perfil como Cuidador Kmimos con éxito</h3>
					<p style="font-size: 15px;">A mayores datos, mayor ganancia.</p>
					<p style="font-size: 15px;">Te invitamos a seguir enriqueciendo tu perfil en</p>
					<h5 style="font-size: 20px">¡3 simples pasos!</h5>
					<a href="#" class="km-btn km-btn-popup-registro-exitoso">COMENZAR</a>
				</div>
			</div>

			<div class="popuphide popup-registro-cuidador-paso1">
				<div class="page-reservation" style="background-color: transparent; margin-bottom: 30px;">
					<ul class="steps-numbers">
						<li>
							<span data-step="1" class="number active">1</span>
						</li>
						<li class="line"></li>
						<li>
							<span class="number">2</span>
						</li>
						<li class="line"></li>
						<li>
							<span class="number">3</span>
						</li>
					</ul>
				</div>
				<h3 style="margin: 0;">Foto de perfil</h3>
				<p style="color: #979797">Brinda a tus futuros amigos</p>

				<div class="img_registro_cliente" style="position: relative">
					<div class="km-datos-foto vlz_rotar" id="perfil-img-a" style="background-image: url(<?php echo getTema(); ?>/images/new/icon/icon-fotoperfil.svg);">
						<div id="loading-perfil" style="width:100%; height: 100%; display:none;" class="vlz_cargando">
							<img src="<?php echo getTema(); ?>/images/new/bx_loader.gif">
						</div>
					</div>

					<div id="rotar_i" class="btn_rotar" style="display: none;" data-orientacion="left"> <i class="fa fa-undo" aria-hidden="true"></i> </div>
	                <div id="rotar_d" class="btn_rotar" style="display: none;" data-orientacion="right"> <i class="fa fa-repeat" aria-hidden="true"></i> </div>

	                <div class="btn_aplicar_rotar" style="display: none;"> Aplicar Cambio </div>

	                <input type="hidden" id="vlz_img_perfil" name="rc_vlz_img_perfil" value="" class="vlz_rotar_valor">
					<br><small data-error="rc_vlz_img_perfil" style="visibility: hidden;"></small>
	                
				</div>

				<!-- <a href="#" data-load='portada' id="perfil-img-a" class="vlz_rotar">
					<img class="img-circle" id="perfil-img" src="<?php echo getTema(); ?>/images/new/icon/icon-fotoperfil.svg">
				</a>
				<div class="kmimos_cargando" style="visibility: hidden;">
					<span>Cargando...</span>
				</div>
				<div id="rotar" data-id="perfil-img-a" class="km-btn-border" style="display: none;">ROTAR</div> -->
				
				<a href="#" data-load='portada' class="km-btn-border">ACCEDER A TU GALERÍA</a>
            	<input class="hidden" type="file" id="portada" name="rc_portada" accept="image/*" />

				<h3 style="margin-top: 20px;">Descripción de tu perfil</h3>
				<p style="color: #979797">Preséntate en la comunidad de Cuidadores Kmimos</p>

				<textarea name="rc_descripcion" class="km-descripcion-peril-cuidador" placeholder="Ejemplo: Hola soy María, soy Cuidadora profesional desde hace 15 años, mi familia y yo amamos a los perros, esto no es solo un trabajo sino una pasión para mí, poder darle todo el cuidado y hacerlo sentir en casa es mi propósito. Te garantizo tu mascota regresará feliz."><?php echo get_region('cuidador_descripcion'); ?>
				</textarea>
				<small data-error="rc_descripcion" style="visibility: hidden;"></small>

				<a href="#" class="km-btn-correo km-btn-popup-registro-cuidador-paso1">SIGUIENTE</a>
				<!-- <a href="#km-registro-tip1" class="km-registro-tip" role="button" data-toggle="modal"></a> -->
			</div>
			
			<div class="popuphide popup-registro-cuidador-paso2">
				<div class="page-reservation" style="background-color: transparent; margin-bottom: 30px;">
					<ul class="steps-numbers">
						<li>
							<span data-step="1" class="number checked">1</span>
						</li>
						<li class="line"></li>
						<li>
							<span class="number active">2</span>
						</li>
						<li class="line"></li>
						<li>
							<span class="number">3</span>
						</li>
					</ul>
				</div>
				<h3 style="margin: 0;"><?php echo get_region('direccion'); ?></h5>
				<p style="color: #979797">Queremos saber tu <?php echo get_region('direccion'); ?> actual</p>
				<a href="#" class="km-btn-border obtener_direccion">UBICACIÓN ACTUAL</a>
				<div class="line-o">
					<p class="text-line">o</p>
					<div class="bg-line"></div>
				</div>
				<div class="km-box-form">
					<div class="content-placeholder">
						<div class="label-placeholder">

							<label><?php echo get_region('estado'); ?></label>
							<select class="km-datos-estado-opcion km-select-custom" name="rc_estado">
								<option value="">Selección de <?php echo get_region('estado'); ?></option>
								<?php
									global $wpdb;
								    $estados = $wpdb->get_results("SELECT * FROM states WHERE country_id = 1 ORDER BY name ASC");
								    $str_estados = "";
								    foreach($estados as $estado) { 
								        $str_estados .= "<option value='".$estado->id."'>".$estado->name."</option>";
								    } 
								    echo $str_estados = utf8_decode($str_estados);
								?>
							</select>
							<small data-error="rc_estado" style="visibility: hidden;"></small>

							 
						</div>
						<div class="label-placeholder">
							<label><?php echo get_region("Localidad")." / ".get_region("Barrio"); ?></label>
							<select class="km-datos-municipio-opcion km-select-custom" name="rc_municipio">
								<option value="">Selección de <?php echo get_region("Barrio"); ?></option>
							</select>
							<small data-error="rc_municipio" style="visibility: hidden;"></small>
						</div>
						<div class="label-placeholder">
							<label><?php echo get_region('direccion'); ?></label>
							<input type="text" id="rc_direccion" name="rc_direccion" value="" class="input-label-placeholder">
							<small data-error="rc_direccion" style="visibility: hidden;"></small>
						</div>

						<input type="hidden" id="latitud" name="latitud" />
						<input type="hidden" id="longitud" name="longitud" />

					</div>
				</div>
				<a href="#" class="km-btn-correo km-btn-popup-registro-cuidador-paso2">SIGUIENTE</a>
				<!-- <a href="#" class="km-registro-tip" role="button" data-toggle="modal"></a> -->
			</div>
			
			<div class="popuphide popup-registro-cuidador-paso3">
				<div class="page-reservation" style="background-color: transparent; margin-bottom: 30px;">
					<ul class="steps-numbers">
						<li>
							<span data-step="1" class="number checked">1</span>
						</li>
						<li class="line"></li>
						<li>
							<span data-step="2" class="number checked">2</span>
						</li>
						<li class="line"></li>
						<li>
							<span class="number active">3</span>
						</li>
					</ul>
				</div>
				<h3 style="margin: 0;"><span data-target="name"></span>,</h5>
				<h3 style="margin: 0 0 10px;">¡TE FALTA MUY POCO!</h5>
				<p style="color: #979797">Llena tus datos para un mayor perfil en la Comunidad Kmimos</p>
				<div class="km-block">
					<div class="km-block-1">
						<p>Número de mascotas que aceptas</p>
					</div>
					<div class="km-block-2">
						<div class="page-reservation km-cantidad">
							<div class="km-content-step">
								<div class="km-content-new-pet">
									<div class="km-quantity">

										<a href="#" id="cr_minus" class="cr_minus disabled">-</a>
										<span class="km-number">1</span>
										<a href="#" id="cr_plus" class="cr_minus">+</a>

										<input  type="text" name="rc_num_mascota" value="1" 
												style="display:none;">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<a href="#" class="km-btn-correo km-btn-popup-registro-cuidador-paso3">SIGUIENTE</a>
				<!-- <a href="#" class="km-registro-tip"></a href="#"> -->
			</div>
			
			<div class="popuphide popup-registro-exitoso-final">
				<div class="overlay"></div>
				<div class="popup-registro-exitoso-text">
					<h2 style="font-size: 18px; color: white;">¡LISTO <span data-target="name"></span>!</h2>
					<p style="font-size: 12px;">Recibimos tu solicitud para sumarte a la familia de cuidadores Kmimos.</p>		

					<aside class="text-center col-sm-10 col-sm-offset-1">
						<h2 style="font-size: 18px; color: white;">SIGUIENTES PASOS PARA ACTIVAR TU PERFIL:</h2>
						<p style="font-size: 12px; text-align: justify;">1. En el transcurso del día, te enviaremos un correo con la liga de las pruebas de conocimientos y psicometría, es necesario que las contestes para poder continuar con el proceso.</p>
						<p style="font-size: 12px; text-align: justify;">2. El equipo de certificación Kmimos te contactará en breve para acompañarte y resolver tus dudas, también puedes contactarnos a través de Whatsapp al +57 318 665 7330.</p>
						<p style="font-size: 12px;">Te recordamos tus credenciales para tu perfil de Kmimos:</p>
						<p style="font-size: 12px;">
							<strong>Usuario:</strong> <span data-id="ilernus-user"></span>
							</br>
							<strong>Contraseña:</strong> <span data-id="ilernus-pass"></span>
						</p>
					</aside>
					<div class="col-sm-12">
						<a style="cursor:pointer;"  id="finalizar-registro-cuidador" 
							data-href="<?php echo get_home_url(); ?>/perfil-usuario/" 
							class="km-btn">CONTINUAR</a>
					</div>
					<!--
						<p style="font-size: 15px;">Completaste tu perfil perfectamente</p>
					-->
				</div>
			</div>
		</form>

		</div>
	</div>
</div>

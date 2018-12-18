<?php
    global $wpdb;

    $userdata = get_user_meta($user_id);

    $_banco = $wpdb->get_var("select banco from cuidadores where user_id = ".$user_id);
    if( !empty($_banco) ){
        $datos_banco = unserialize($_banco);
    }else{
        $datos_banco = [
            'banco' => '',
            'titular' => '',
            'cuenta' => '',
        ];
    }
    $array_bancos = ['BBVA BANCOMER','BANAMEX','SANTANDER','SCOTIABANK','BANCO DEL BAJIO','BANORTE','BANCO HSBC','BANCO INBURSA','BANCO AZTECA','BANCA MIFEL','BANCA AFIRME','BANSI','BANK OF AMERICA','BANREGIO','BANJERCITO','BANCO INTERACCIONES','AMERICAN EXPRESS','BANCO INVEX','BANCO VE POR MAS','ING BANCO','COMPARTAMOS','BANCO MULTIVA','BANCOPPEL','AHORRO FAMSA','AUTOFIN','MONEX','JP MORGAN','PRUDENTIAL BANK','BANCO VOLKSWAGEN','BANCO DE MEXICO','ABC CAPITAL','ACTINVER','BANCO BASE','BANCO CREDIT SUISSE','BANCO FINTERRA','BANCO FORJADORES','BANCO INMOBILIARIO MEXICANO','BANCO PAGATODO','UNIÓN PROGRESO','BANCO SABADELL','BANCREA','BANK OF CHINA MEXICO','BANK OF TOKYO MITSUBISHI','BANKAOOL','BARCLAYS BANK MEXICO','CIBANCO','CONSUBANCO','DEUTSCHE BANK MEXICO','FUNDACION DONDE BANCO','ICBC MEXICO','INTERCAM BANCO','INVESTA BANCO','MIZUHO BANCO','SHINHAN BANCO','UBS BANCO MEXICO','IXE BANCO'];

    foreach ($array_bancos as $banco) {
        $select='';
        if( $banco == $datos_banco['banco'] ){
            $select = 'selected';
        }
        $bancos_option .= '<option value="'.$banco.'" '.$select.'>'.$banco.'</option>';
    }

    $referred = $userdata['user_referred'][0];

    $opciones = get_referred_list_options(); $ref_str = "";
    foreach($opciones as $key=>$value){
        $selected = ($referred==$key)? ' selected':'';
        $ref_str .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
    }

    $user_recibir_fotos = $userdata['user_recibir_fotos'][0];

    $opciones = array(
        "1" => "Si, 2 veces al d&iacute;a",
        "2" => "Si, 1 veces al d&iacute;a",
        "3" => "Si, 1 veces cada 2 d&iacute;as",
        "4" => "No, no deseo recibir fotos"
    );
    $recibir_fotos = "";
    foreach($opciones as $key => $value){
        $selected = ($user_recibir_fotos == $key) ? ' selected':'';
        $recibir_fotos .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
    }

    if (!isset($_SESSION)) { session_start(); }
    if( $_SESSION["nuevo_registro"] == "YES" ){
        $pixel = "
            <script> fbq ('track','CompleteRegistration'); </script>
        ";
        $_SESSION["nuevo_registro"] = "";
        unset($_SESSION["nuevo_registro"]);
    }else{
        $pixel = "";
    }

    $tipoUsuario = unserialize($userdata['wp_capabilities'][0]);

/*    echo "<pre>";
        print_r($tipoUsuario);
    echo "</pre>";*/

    $validacionesDescripcion = '';
    if( isset($tipoUsuario['vendor']) ){
        $validacionesDescripcion = '
            data-valid="min:200"
            data-title="La descripci&oacute;n debe tener al menos 200 caracteres" 
            data-toggle="tooltip"
            title="Cuentanos sobre ti, tus cualidades y porque deberían permitirte cuidar sus perritos"
        ';
    }

    $CONTENIDO .=  $pixel.'
        <input type="hidden" name="accion" value="perfil" />
        <input type="hidden" name="user_id" value="'.$user_id.'" />
        <input type="hidden" name="core" value="SI" />
        <input type="hidden" id="sub_path" name="sub_path" value="'.$img_perfil["sub_path"].'" />

        <h1 style="margin: 0px; padding: 0px;">Mi Perfil</h1><hr style="margin: 5px 0px 10px;">
        <section>
            <div class="vlz_img_portada_perfil">
                <div class="vlz_img_portada_fondo vlz_rotar" style="background-image: url('.$avatar.');"></div>
                <div class="vlz_img_portada_normal vlz_rotar" style="background-image: url('.$avatar.');"></div>
                <div class="vlz_img_portada_cargando vlz_cargando" style="background-image: url('.getTema().'/images/cargando.gif);"></div>
                <div class="vlz_cambiar_portada">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                    Cargar Foto
                    <input type="file" id="portada" name="xportada" accept="image/*" />
                </div>
                <div id="rotar_i" class="btn_rotar" style="display: none;" data-orientacion="left"> <i class="fa fa-undo" aria-hidden="true"></i> </div>
                <div id="rotar_d" class="btn_rotar" style="display: none;" data-orientacion="right"> <i class="fa fa-repeat" aria-hidden="true"></i> </div>
            </div>
            <input type="hidden" class="vlz_img_portada_valor vlz_rotar_valor" name="portada" data-valid="requerid" />

            <div class="btn_aplicar_rotar" style="display: none;"> Aplicar Cambio </div>
        </section>

        <div class="inputs_containers">

            <section>
                <label for="firstname" class="lbl-text">'.esc_html__('Nombres','kmimos').':</label>
                <label class="lbl-ui">
                    <input type="text" id="first_name" name="first_name" value="'.$userdata['first_name'][0].'" data-valid="requerid" autocomplete="off" />
                </label>
            </section>

            <section>
                <label for="lastname" class="lbl-text">'.esc_html__('Apellidos','kmimos').':</label>
                <label class="lbl-ui">
                    <input type="text" id="last_name" name="last_name" value="'.$userdata['last_name'][0].'" data-valid="requerid" autocomplete="off" />
                </label>
            </section>

            <section style="display: none;">
                <label for="nickname" class="lbl-text">'.esc_html__('Apodo (Nombre a mostrar)','kmimos').':</label>
                <label class="lbl-ui">
                    <input  type="text" id="nickname" name="nickname" value="'.$userdata['nickname'][0].'" data-valid="requerid" autocomplete="off" />
                </label>
            </section>

            <section>
                <label for="phone" class="lbl-text">'.esc_html__('Teléfono','kmimos').':</label>
                <label class="lbl-ui">
                    <input type="number" id="phone" name="phone" data-title="El teléfono es requerido y debe tener al menos 10 digitos" value="'.$userdata['user_phone'][0].'" data-valid="requerid,min:10" autocomplete="off" />
                </label>
            </section>

            <section>
                <label for="mobile" class="lbl-text">'.esc_html__('Móvil','kmimos').':</label>
                <label class="lbl-ui">
                    <input type="number" id="mobile" name="mobile" data-title="El teléfono es requerido y debe tener al menos 10 digitos" value="'.$userdata['user_mobile'][0].'" data-valid="requerid,min:10" autocomplete="off" />
                </label>
            </section>

            <section>
                <label for="referred" class="lbl-text">'.esc_html__('¿Como nos conoció?','kmimos').':</label>
                <label class="lbl-ui">
                    <select id="referred" name="referred" data-valid="requerid" >
                        <option value="">Por favor seleccione</option>
                        '.$ref_str.'
                    </select>
                </label>
            </section>

            <section>
                <label for="user_recibir_fotos" class="lbl-text">'.esc_html__('¿Deseas recibir fotos durante tus reservas?','kmimos').':</label>
                <label class="lbl-ui">
                    <select id="user_recibir_fotos" name="user_recibir_fotos" data-valid="requerid" >
                        <option value="">Por favor seleccione</option>
                        '.$recibir_fotos.'
                    </select>
                </label>
            </section>
';
if( is_petsitters() ){
    $CONTENIDO .='
                <div>
                    <label for="banco" class="lbl-text">'.esc_html__('Banco','kmimos').':</label>
                    <label class="lbl-ui">
                        <!-- input type="text" id="banco" name="banco" value="'.$datos_banco['titular'].'" data-valid="requerid" autocomplete="off" / -->
                        <select id="banco" name="banco" data-valid="requerid" >
                            <option value="">SELECCIONE UN BANCO</option>
                            '.$bancos_option.'
                        </select>
                    </label>
                </div>
                <section>
                    <label for="titular" class="lbl-text">'.esc_html__('Nombre del titular','kmimos').':</label>
                    <label class="lbl-ui">
                        <input type="text" id="titular" name="titular" value="'.$datos_banco['titular'].'" data-valid="requerid" autocomplete="off" />
                    </label>
                </section>
                <section>
                    <label for="banco_cuenta" class="lbl-text">No. de Cuenta bancaria <strong style="color:#6b1c9b;">(CLABE)</strong>:</label>
                    <label class="lbl-ui">
                        <input type="text" id="banco_cuenta" name="banco_cuenta" value="'.$datos_banco['cuenta'].'" data-valid="requerid" autocomplete="off" />
                    </label>
                </section>
    ';
}
$CONTENIDO .='
            <section class="container_full">
                <label for="descr" class="lbl-text">'.esc_html__('Información biográfica','kmimos').':</label>
                <label class="lbl-ui">
                    <textarea 
                        id="descr" 
                        name="descr"
                        '.$validacionesDescripcion.'
                    >'.$userdata['description'][0].'</textarea>
                </label>
            </section>

            <section>
                <label for="username" class="lbl-text">'.esc_html__('Usuario','kmimos').':</label>
                <label class="lbl-ui">
                    <input type="text" id="username" value="'.$current_user->user_login.'" disabled />
                    <input type="hidden" name="username" value="'.$current_user->user_login.'" />
                </label>
            </section>

            <section>
                <label for="email" class="lbl-text">'.esc_html__('Correo Electrónico','kmimos').':</label>
                <label class="lbl-ui">
                    <input  type="email" id="email" value="'.$current_user->user_email.'" disabled />
                </label>
            </section>

            <section>
                <label for="password" class="lbl-text">'.esc_html__('Nueva contraseña','kmimos').':</label>
                <label class="lbl-ui">
                    <input 
                        type="password"
                        placeholder="Contraseña"
                        data-title="Las contraseñas deben ser iguales"
                        autocomplete="off" 
                        name="password" 
                        id="password" 
                        class="clv" 
                        data-valid="equalTo:password2"
                    />
                </label>
            </section>

            <section>
                <label for="password2" class="lbl-text">'.esc_html__('Repita la nueva contraseña','kmimos').':</label>
                <label class="lbl-ui">
                    <input 
                        type="password"
                        placeholder="Contraseña"
                        data-title="Las contraseñas deben ser iguales"
                        autocomplete="off" 
                        name="password2" 
                        id="password2" 
                        class="clv" 
                        data-valid="equalTo:password" 
                    />
                </label>
            </section>

            

        </div>
    ';
?> 
<?php
    

// Configuracion
    include("../../../../../vlz_config.php");
    include("../../../../../wp-load.php");
    include("../funciones/db.php");
    include("../funciones/generales.php");
    include('../../lib/Requests/Requests.php');

    include("../generales/save_terms.php");

    date_default_timezone_set('America/Mexico_City');
	$conn = new mysqli($host, $user, $pass, $db);
	$errores = array();
    
    // BEGIN DATA DEFAULT
    $mascotas_cuidador = array(
        "pequenos" => 0,
        "medianos" => 0,
        "grandes"  => 0,
        "gigantes" => 0
    );
    $mascotas_cuidador = serialize($mascotas_cuidador);

    $tamanos_aceptados = array(
        "pequenos" => 0,
        "medianos" => 0,
        "grandes"  => 0,
        "gigantes" => 0
    );
    $tamanos_aceptados = serialize($tamanos_aceptados);

    $edades_aceptadas = array(
        "cachorros" => 0,
        "adultos"   => 0
    );
    $edades_aceptadas = serialize($edades_aceptadas);

    $comportamientos_aceptados = array(
        "sociables"             => 0,
        "no_sociables"          => 0,
        "agresivos_perros"      => 0,
        "agresivos_personas"    => 0,
    );
    $comportamientos_aceptados = serialize($comportamientos_aceptados);

    $hospedaje = array(
        "pequenos" => 0,
        "medianos" => 0,
        "grandes"  => 0,
        "gigantes" => 0,
    );
    $hospedaje = serialize($hospedaje);
    // END DATA DEFAULT

    extract($_POST);

    switch ( $rc_tipo_documento ) {
        case 'IFE / INE':

            if( $rc_ife+0 == 0 ){ 
                $error = array(
                    "error" => "SI",
                    'fields' => array(
                        array(
                            "name" => "ife",
                            "msg" => "Error, el IFE no es valido."
                        )
                    )
                );
                echo "(".json_encode( $error ).")";

                exit();
            }

            $ife = $rc_ife;
            
        break;
        case 'Pasaporte':

            if( strlen($rc_pasaporte) < 10 || strlen($rc_pasaporte) > 28 ){ 
                $error = array(
                    "error" => "SI",
                    'fields' => array(
                        array(
                            "name" => "pasaporte",
                            "msg" => "Error, el Pasaporte tiene una longitud valida."
                        )
                    )
                );
                echo "(".json_encode( $error ).")";

                exit();
            }

            $ife = $rc_pasaporte;

        break;
    }

    if ($conn->connect_error) {
        echo json_encode(['error'=>'NO','msg'=>'Error de conexion', 'fields'=>[]]);
    }else{
        // Valores default
        foreach ($_POST as $key => $value) {
            if($value == ''){ $_POST[$key] = 0; }
        }

        $email = $rc_email;
        $nombres = $rc_nombres;
        $apellidos = $rc_apellidos;
        $clave = $rc_clave;
        $telefono = $rc_telefono;
        $referido = $rc_referred;

        $username = $email;

        if( preg_match("/[\+]{1,}/", $email) || !filter_var($email, FILTER_VALIDATE_EMAIL) ){
            $fields[] = [ 'name'=>'email', 'msg'=>"Formato de E-mail invalido"];
            $error = array(
                "error" => "SI",
                "msg" => "Formato de E-mail invalido",
                "fields" => $fields,
            );

            echo "(".json_encode( $error ).")";
            exit();
        }

        // Validar si existe el usuario
        $existe_users = $conn->query( "SELECT * FROM wp_users WHERE  user_login = '{$username}' OR user_email = '{$email}'" );
        $existe_cuidador = $conn->query( "SELECT * FROM cuidadores WHERE  email = '{$email}'" );
        if( $existe_users->num_rows > 0 || $existe_cuidador->num_rows > 0 ){
            $msg = "Se encontraron los siguientes errores:\n\n";
            while($datos = $existe_users->fetch_assoc()){
                if( strtolower($datos['user_email']) == strtolower($email) ){
                    $msg .= "Este E-mail [{$email}] ya esta en uso\n";
                    $fields[] = [ 'name'=>'email', 'msg'=>"Este E-mail {$email} ya esta en uso"];
                }
                if( strtolower($datos['user_login']) == strtolower($username) ){
                    $msg .= "Este nombre de Usuario [{$username}] ya esta en uso\n";
                    $fields[] = [ 'name'=>'nickname', 'msg'=>"Este  nombre de Usuario {$username} ya esta en uso"];
                }
            }
            while($datos = $existe_cuidador->fetch_assoc()){
                if( strtolower($datos['email']) == strtolower($email) ){
                    $msg .= "Este E-mail [{$email}] ya esta en uso\n";
                    $fields[] = [ 'name'=>'email', 'msg'=>"Este E-mail {$email} ya esta en uso"];
                }
            }

            $error = array(
                "error" => "SI",
                "msg" => $msg,
                "fields" => $fields,
            );

            echo "(".json_encode( $error ).")";
            exit;
        }else{
            $token = md5(microtime());
            $temp = array( "token" => $token );
            Requests::register_autoloader();
            $options = array(
                'wstoken' => "e8738b6e6fad761768364d25c916f5e5",
                'wsfunction' => "kmimos_user_create_users",
                'moodlewsrestformat' => "json",
                'users' => array(
                    0 => array(
                        'username' => $username,
                        'password' => $clave,
                        'firstname' => $nombres,
                        "lastname" => $apellidos,
                        "email" => $email,
                        "preferences" => array(
                            0 => array(
                                "type" => 'kmimostoken',
                                "value" => $token
                            )
                        ),
                        "cohorts" => array(
                            0 => array(
                                "type" => 'idnumber',
                                "value" => "kmi-qsc"
                            )
                        )
                    )
                )
            );
            
            // Registro en iLernus
            $request = Requests::post('http://kmimos.ilernus.com/webservice/rest/server.php', array(), $options );

            $atributos = array(
                "nacimiento" => $fecha
            );

            $sql = "
                INSERT INTO cuidadores VALUES (
                    NULL,
                    '0',
                    '0',
                    '$nombres',
                    '$apellidos',
                    '$ife',
                    '$email',
                    '$telefono',
                    '',
                    '0',
                    '0',
                    '0',
                    '',
                    '',
                    '',
                    '0',
                    '0',
                    '08:00:00',
                    '18:00:00',
                    '{$mascotas_cuidador}',
                    '{$tamanos_aceptados}',
                    '{$edades_aceptadas}',
                    '{$comportamientos_aceptados}',
                    '{$hospedaje}',
                    0,
                    'a:0:{}',
                    '".serialize($atributos)."',
                    '0',
                    '0',
                    '',
                    0
                );
            ";

            if( $conn->query( utf8_decode( $sql ) ) ){
                $cuidador_id = $conn->insert_id;
                $hoy = date("Y-m-d H:i:s");     

                // Crear usuario
                $new_user = "
                    INSERT INTO wp_users VALUES (
                        NULL,
                        '".$username."',
                        '".md5($clave)."',
                        '".$username."',
                        '".$email."',
                        '',
                        '".$hoy."',
                        '',
                        0,
                        '".$nombres." ".$apellidos."'
                    );
                ";
                

                $conn->query( utf8_decode( $new_user ) );
                $user_id = $conn->insert_id;

                save_user_accept_terms ($user_id, new db( $conn ) );

                //WHITE_LABEL
                if (!isset($_SESSION)) {
                    session_start();
                }
                if(array_key_exists('wlabel',$_SESSION) || $referido=='Volaris' || $referido=='Vintermex'){
                    $wlabel='';
                    if(array_key_exists('wlabel',$_SESSION)){
                        $wlabel=$_SESSION['wlabel'];

                    }else if($referido=='Volaris'){
                        $wlabel='volaris';

                    }else if($referido=='Vintermex'){
                        $wlabel='viajesintermex';
                    }
                    if ($wlabel!=''){
                        $query_wlabel = "INSERT INTO wp_usermeta VALUES (NULL, '".$user_id."', '_wlabel', '".$wlabel."');";
                        $conn->query( utf8_decode( $query_wlabel ) );
                    }
                }
                
                // Update Cuidadores Usuario                
                $conn->query( "UPDATE cuidadores SET user_id = '".$user_id."' WHERE id = ".$cuidador_id);

                $sql = "
                    INSERT INTO wp_usermeta VALUES
                         (NULL, ".$user_id.", 'user_favorites',      '')
                        ,(NULL, ".$user_id.", 'user_pass',           '".$clave."')
                        ,(NULL, ".$user_id.", 'user_phone',          '".$telefono."')
                        ,(NULL, ".$user_id.", 'user_mobile',         '".$telefono."')
                        ,(NULL, ".$user_id.", 'user_country',        'México')
                        ,(NULL, ".$user_id.", 'nickname',            '".$username."')
                        ,(NULL, ".$user_id.", 'first_name',          '".$nombres."')
                        ,(NULL, ".$user_id.", 'last_name',           '".$apellidos."')
                        ,(NULL, ".$user_id.", 'user_referred',       '".$referido."')
                        ,(NULL, ".$user_id.", 'rich_editing',        'true')
                        ,(NULL, ".$user_id.", 'comment_shortcuts',   'false')
                        ,(NULL, ".$user_id.", 'admin_color',         'fresh')
                        ,(NULL, ".$user_id.", 'use_ssl',             '0')
                        ,(NULL, ".$user_id.", 'show_admin_bar_front','false')
                        ,(NULL, ".$user_id.", 'wp_capabilities',     'a:1:{s:6:\"vendor\";b:1;}')
                        ,(NULL, ".$user_id.", 'wp_user_level',       '0')                        
                        ,(NULL, ".$user_id.", 'google_auth_id' ,     '".$google_auth_id."'  )
                        ,(NULL, ".$user_id.", 'facebook_auth_id' ,   '".$facebook_auth_id."')
                        ,(NULL, ".$user_id.", 'nacimiento' ,         '".$rc_nacimiento."')
                        ;
                ";
                $conn->query( $sql );
                
                // Crear Post Cuidador
                $nombres    = trim($nombres);
                $apellidos  = trim($apellidos);
                $nom = strtoupper( substr($nombres, 0, 1) ).strtolower( substr($nombres, 1)  )." ".strtoupper( substr($apellidos, 0, 1) ).".";
                $sql_post_cuidador = "
                    INSERT INTO
                        wp_posts 
                    VALUES (
                        NULL, 
                        ".$user_id.", 
                        '".$hoy."', 
                        '".$hoy."', 
                        '', 
                        '".$nom."', 
                        '', 
                        'pending', 
                        'open', 
                        'closed', 
                        '', 
                        '".$user_id."', 
                        '', 
                        '',
                        '".$hoy."', 
                        '".$hoy."', 
                        '', 
                        0, 
                        'http://www.kmimos.com.mx/petsitters/".$user_id."/', 
                        0, 
                        'petsitters', 
                        '', 
                        0
                    );
                ";
                $conn->query( utf8_decode( $sql_post_cuidador ) );
                $id_post = $conn->insert_id;
                
                // Update POST_ID en cuidadores  
                $conn->query( "UPDATE cuidadores SET id_post = '".$id_post."' WHERE id = ".$cuidador_id);

                if (!isset($_SESSION)) { session_start(); }
                $_SESSION["nuevo_registro"] = "YES";

                // Auto Login
                $info = array();
                $info['user_login']     = sanitize_user($username, true);
                $info['user_password']  = sanitize_text_field($clave);
                $user_signon = wp_signon( $info, true );
                wp_set_auth_cookie($user_signon->ID);

                // Plantillas de Email
                // include( '../../partes/email/mensaje_web_registro_cuidador_viejo.php' );
                //include( '../../partes/email/mensaje_email_registro_cuidador.php' );

                $mail_file = realpath('../../template/mail/registro.php');

                $message_mail = file_get_contents($mail_file);

                $message_mail = str_replace('[name]', $nombres.' '.$apellidos, $message_mail);
                $message_mail = str_replace('[email]', $email, $message_mail);
                $message_mail = str_replace('[pass]', $clave, $message_mail);
                $message_mail = str_replace('[url]', get_home_url()."/perfil-usuario/", $message_mail);
                $message_mail = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $message_mail);

                // Envio de Email
                $mail_msg = get_email_html($message_mail, false, true, $user_id);
                wp_mail( $email, "Kmimos México – Gracias por registrarte como cuidador! Kmimos la NUEVA forma de cuidar a tu perro!", $mail_msg);

                // Respuesta
                $error = array(
                    "error"         => "NO",
                    "msg"           => $mensaje_web, 
                    'fields'=>[]
                );
                echo "(".json_encode( $error ).")";

            }else{
                // Error registro en iLernus
                $error = array(
                    "error" => "SI",
                    "msg"   => "No se ha podido completar el registro.", 
                    'fields'=>[]
                );
                echo "(".json_encode( $error ).")";
            }
        }        
	}

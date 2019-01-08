<?php
    session_start();
    date_default_timezone_set('America/Mexico_City');
    
    include_once(dirname(__DIR__).'/lib/nps.php');
    $encuestas = $nps->get_comentarios_byCode( $_POST['code'] );

    $comentarios = '';
    $email = '';
    $respuesta_id = 0;
    $color = [
        'cliente' => "info", 
        'admin' => "default", 
    ];

    if( $encuestas != false ){
        $count=0;
        foreach ($encuestas as $encuesta) {
            $nombre = '';
            $apellido = '';

            $respuesta = $nps->db->get_row("SELECT * FROM nps_respuestas WHERE md5( CONCAT(pregunta, email) ) = '".$_POST['code']."'");

            if( isset($respuesta->email) ){
                $email = $respuesta->email;
                $respuesta_id = $respuesta->id;

                $user_id = $nps->db->get_var("SELECT ID FROM wp_users WHERE user_email = '".$respuesta->email."'");
                if( $user_id > 0 ){
                    $nombre = $nps->db->get_var( "SELECT meta_value FROM wp_usermeta WHERE meta_key='first_name' AND user_id = {$user_id}" );
                    $apellido = $nps->db->get_var( "SELECT meta_value FROM wp_usermeta WHERE meta_key='last_name' AND user_id = {$user_id}" );
                }
      
                $align = '';
                $titulo = utf8_encode($nombre .' '. $apellido);
                $encuesta_email = ' | <small style="font-size:12px;"><strong>Email: '. $respuesta->email .'</strong></small>';
                if(strtolower($encuesta->tipo)=='admin') { 
                    $align = 'alert box-admin'; 
                    $titulo = 'Administrador';
                    $encuesta_email = '';
                }

                $comentarios .= '
                <div class="media alert alert-'.$color[$encuesta->tipo].' '.$align.'">
                  <div class="media-body">
                    <h5 style="font-size:16px;" class="media-heading">'.$titulo.'</h5>
                    <small style="font-size: 10px;font-style:italic;">'.date('Y-m-d H:i:s', strtotime($encuesta->fecha)).'</small> '.$encuesta_email.' 
                    <hr>
                    <p>'.$encuesta->comentario.'</p>
                  </div>
                </div>';
            }


        }
    }

    echo json_encode(['comentarios'=>$comentarios, 'email'=>$email, 'id' => $respuesta_id], JSON_UNESCAPED_UNICODE);


<?php
	
    $file = $PATH_TEMPLATE.'/template/mail/conocer/cliente/confirmar.php';
    $mensaje_cliente = file_get_contents($file);
/*    
	$wpdb->query("UPDATE wp_postmeta SET meta_value = '2' WHERE post_id = $id_orden AND meta_key = 'request_status';");
	$wpdb->query("UPDATE wp_posts SET post_status = 'publish' WHERE ID = '{$id_orden}';");
*/
    $mensaje_cliente = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cliente);
    $mensaje_cliente = str_replace('[name_cuidador]', $cuidador_name, $mensaje_cliente);
    $mensaje_cliente = str_replace('[name_cliente]', $cliente_name, $mensaje_cliente);

    $fin = strtotime( str_replace("/", "-", $_POST['service_end']) );
    
    $mensaje_cliente = str_replace('[name]', $cliente_web, $mensaje_cliente);
    $mensaje_cliente = str_replace('[avatar]', kmimos_get_foto($cuidador->user_id), $mensaje_cliente);
    $mensaje_cliente = str_replace('[nombre_usuario]', $nombre_cuidador, $mensaje_cliente);
    $mensaje_cliente = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cliente);
    $mensaje_cliente = str_replace('[telefonos]', $telf_cuidador, $mensaje_cliente);
    $mensaje_cliente = str_replace('[email]', $email_cuidador, $mensaje_cliente);
    $mensaje_cliente = str_replace('[id_solicitud]', $request_id, $mensaje_cliente);
    $mensaje_cliente = str_replace('[fecha]', $_POST['meeting_when'], $mensaje_cliente);
    $mensaje_cliente = str_replace('[hora]', $_POST['meeting_time'], $mensaje_cliente);
    $mensaje_cliente = str_replace('[lugar]', $_POST['meeting_where'], $mensaje_cliente);
    $mensaje_cliente = str_replace('[desde]', date("d/m", strtotime( str_replace("/", "-", $metas_solicitud["service_start"][0]) )), $mensaje_cliente);
    $mensaje_cliente = str_replace('[hasta]', date("d/m", $fin), $mensaje_cliente);
    $mensaje_cliente = str_replace('[anio]', date("Y", $fin), $mensaje_cliente);

    $mensaje_cliente = get_email_html($mensaje_cliente, true, false, $cliente );    

    if( isset($NO_ENVIAR) ){
        echo $mensaje_cliente;
        try{   
            email_log( json_encode(['result'=>'NO_ENVIAR']) );        
        }catch(Exception $e){}
    }else{
        $email_cliente = 'italococchini@gmail.com';
        $send_mail_response = wp_mail( $email_cliente, "Confirmación de Solicitud para Conocer Cuidador", $mensaje_cliente);
        try{
            email_log( json_encode([
                'result'=>$send_mail_response,
                'email'=>$email_cliente,
                'ID'=>$id_orden,
                ]) 
            );
        }catch(Exception $e){}            
    } 
    
    $file = $PATH_TEMPLATE.'/template/mail/conocer/cuidador/confirmar.php';
    $mensaje_cuidador = file_get_contents($file);

    $mensaje_cuidador = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_cuidador);
    $mensaje_cuidador = str_replace('[name_cuidador]', $cuidador_name, $mensaje_cuidador);
    $mensaje_cuidador = str_replace('[name_cliente]', $cliente_name, $mensaje_cuidador);

    $mensaje_cuidador = get_email_html($mensaje_cuidador, true, false, $cliente );   

    if( isset($NO_ENVIAR) ){
        echo $mensaje_cuidador;
        try{   
            email_log( json_encode(['result'=>'NO_ENVIAR']) );        
        }catch(Exception $e){}
    }else{
        $email_cuidador = 'italococchini@gmail.com';
        $send_mail_response = wp_mail( $email_cuidador, "Confirmación de Solicitud para Conocerte", $mensaje_cuidador);
        try{
            email_log( json_encode([
                'result'=>$send_mail_response,
                'email'=>$email_cuidador, 
                'ID'=>$id_orden,
                ]) 
            );
        }catch(Exception $e){}            
    } 



	$file = $PATH_TEMPLATE.'/template/mail/conocer/admin/confirmar.php';
    $mensaje_admin = file_get_contents($file);

    $mensaje_admin = str_replace('[URL_IMGS]', get_home_url()."/wp-content/themes/kmimos/images/emails", $mensaje_admin);
    $mensaje_admin = str_replace('[id_solicitud]', $id_orden, $mensaje_admin);
    $mensaje_admin = str_replace('[name_cuidador]', $cuidador_name, $mensaje_admin);
    $mensaje_admin = str_replace('[name_cliente]', $cliente_name, $mensaje_admin);

    $mensaje_admin = get_email_html($mensaje_admin, true, false, $cliente );   

    if( isset($NO_ENVIAR) ){
        echo $mensaje_admin;
    }else{
        kmimos_mails_administradores_new("Confirmación de Solicitud para Conocer a ".$cuidador_name, $mensaje_admin);
    } 
    
    $CONTENIDO .= "<div class='msg_acciones'>
        <strong>¡Todo esta listo!</strong><br>
        La solicitud para conocer cuidador <strong>#".$id_orden."</strong>, ha sido confirmada exitosamente de acuerdo a tu petición.
    </div>";




function email_log( $mensaje ){
    echo $PATH_TEMPLATE;
    try{
        if( $archivo = fopen('wp-content/uploads/temp/log_email.txt', "a") ){
            fwrite($archivo, date("d m Y H:m:s"). " ". $mensaje. "\n");
            fclose($archivo);
        }
    }catch(Exception $e){}
}
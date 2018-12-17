<?php

	$PATH_TEMPLATE = (dirname(dirname(dirname(__DIR__))));
    
    global $wpdb;

    $id_orden = vlz_get_page();

    $acc = "CFM"; $usu = "CUI";

    include($PATH_TEMPLATE."/procesos/reservar/emails/index.php");

    $CONTENIDO .= "<a class='km-btn-primary volver_msg' href='".get_home_url()."/perfil-usuario/reservas/'>Volver</a>";

    //italo
    exit();
?>
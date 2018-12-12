<?php

global $wpdb;

if( isset($_GET["update_rating"])){
    $cuidadores = $wpdb->get_results("SELECT * FROM cuidadores");
    foreach ($cuidadores as $key => $cuidador) {
        vlz_actualizar_ratings($cuidador->id_post);
    }    
}

if( isset($_GET["update_experiencia"])){
    $cuidadores = $wpdb->get_results("SELECT * FROM cuidadores");
    foreach ($cuidadores as $key => $cuidador) {
        if( $cuidador->experiencia+0 < 1900){
            $anio = 2018-$cuidador->experiencia;
        }else{
            $anio = 2018-(2018-$cuidador->experiencia);
        }
        $wpdb->query("UPDATE cuidadores SET experiencia = '{$anio}' WHERE id = ".$cuidador->id);
    }    
}

$datos = kmimos_get_info_syte();
global $margin_extra_footer;
global $no_display_footer;

if( !isset($no_display_footer)  ){
$HTML = '
        <!-- SECCIÓN FOOTER -->
        <footer class="'.$margin_extra_footer.'">
            <div class="container">
                <div class="row">

                    <div class="col-xs-12 col-sm-5">
                        <h5>ENTÉRATE DE LOS ÚLTIMOS CUIDADOS PARA TU MASCOTA</h5>
                        <p>¡Inscríbete a nuestro blog y conócelas!</p>
                        <form onsubmit="form_subscribe(this); return false;" class="subscribe" data-subscribe="'.get_home_url().'/wp-content/plugins/kmimos">
                            <div class="km-inscripcion">
                                <div class="input-group" style="width:100%!important;">
                                    <input type="hidden" name="section" value="home" class="form-control" placeholder="Ingresa tu correo">
                                    <input type="hidden" id="wlabelSubscribeFooter" name="wlabelSubscribeFooter" value="'.$_SESSION["wlabel"].'" class="form-control" placeholder="Ingresa tu correo">
                                    <input type="text" name="mail" class="form-control" placeholder="Ingresa tu correo">
                                    <span class="input-group-btn">
                                        <button class="btn" type="submit">INSCRIBIRME AL BLOG</button>
                                    </span>
                                </div>
                            </div>
                            <div class="message message-especial"></div>
                        </form>
                    </div>

                    <div class="col-xs-12 col-sm-2">
                        <h5>SERVICIOS</h5>
                        <p><a href="'.get_home_url().'/quiero-ser-cuidador-certificado-de-perros">Quiero ser cuidador</a></p>
                        <p><a href="'.get_home_url().'/busqueda">Buscar cuidador certificado</a></p>
                        <p><a target="blank" href="https://kmimos.com.mx/blog">Blog</a></p>
                    </div>
                    <div class="col-xs-12 col-sm-2">
                        <h5>NAVEGA</h5>
                        <p><a href="'.get_home_url().'">Nosotros</a></p>
                        <p><a href="'.get_home_url().'/faq">Preguntas y Respuestas</a></p>
                        <p><a href="'.get_home_url().'/coberturas-de-servicios-veterinarios/">Cobertura Veterinaria</a></p>
                        <p><a href="'.get_home_url().'">Comunicados de prensa</a></p>
                        <p><a href="'.get_home_url().'/terminos-y-condiciones/">Términos y Condiciones</a></p>
                        <p><a href="'.get_home_url().'">Nuestros Aliados</a></p>
                        <p><a href="'.get_home_url().'/contacta-con-nosotros/">Contáctanos</a></p>                
                    </div>

                    <div class="col-xs-12 col-sm-3" style="padding: 5px;">
                        <h5>CONTÁCTANOS</h5>
                        
                        <p>Telef. Local: 01 (55) 8526 1162</p>
                        <!-- <p>Llamada Sin Costo: '.$datos['telefono_sincosto'].'</p> -->
                        <p>WhatsApp 1: +52 1 (33) 1261 41 86</p>
                        <p>WhatsApp 2: +52 1 55 6892 2182</p>
                        <p>WhatsApp 3: +52 1 55 6560 2472</p>
                        <p>Email: '.$datos['email'].'</p>

                        <div class="km-icon-redes">
                            <a target="blank" href="https://www.facebook.com/Kmimosmx/"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 25"><path class="cls-1" d="M12.5,0A12.5,12.5,0,1,0,25,12.5,12.5,12.5,0,0,0,12.5,0Zm3.66,7.56H14.41c-.61,0-.74.25-.74.89V10h2.49l-.25,2.7H13.67v8H10.48v-8H8.82V10h1.66V7.83c0-2,1.07-3.07,3.47-3.07h2.21Z"/></svg></a>
                        </div>
                        <div class="km-icon-redes">
                            <a target="blank" href="https://twitter.com/kmimosmx/"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 25"><path class="cls-1" d="M12.5,0A12.5,12.5,0,1,0,25,12.5,12.5,12.5,0,0,0,12.5,0Zm5.59,9.64A8.21,8.21,0,0,1,5.47,16.92,5.9,5.9,0,0,0,9.8,15.69a2.92,2.92,0,0,1-2.7-2,3,3,0,0,0,1.29-.06,2.89,2.89,0,0,1-2.3-2.86,2.63,2.63,0,0,0,1.29.37,2.86,2.86,0,0,1-.89-3.84,8.12,8.12,0,0,0,5.93,3,2.86,2.86,0,0,1,4.88-2.61A5.35,5.35,0,0,0,19.13,7a3,3,0,0,1-1.26,1.6,6,6,0,0,0,1.66-.46A6.12,6.12,0,0,1,18.09,9.64Z"/></svg></a>
                        </div>
                        <div class="km-icon-redes">
                            <a target="blank" href="https://www.instagram.com/kmimosmx/"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 25 25"><circle class="cls-1" cx="12.5" cy="12.53" r="2.4"/><path class="cls-1" d="M18.15,8.26a2.62,2.62,0,0,0-.55-.83,1.88,1.88,0,0,0-.83-.55,4.37,4.37,0,0,0-1.35-.25c-.77,0-1,0-2.92,0s-2.15,0-2.92,0a4.17,4.17,0,0,0-1.35.25,2.62,2.62,0,0,0-.83.55,1.88,1.88,0,0,0-.55.83A4.37,4.37,0,0,0,6.6,9.61c0,.77,0,1,0,2.92s0,2.15,0,2.92a4.17,4.17,0,0,0,.25,1.35,2.62,2.62,0,0,0,.55.83,1.88,1.88,0,0,0,.83.55,4.38,4.38,0,0,0,1.35.25c.77,0,1,0,2.92,0s2.15,0,2.92,0a4.18,4.18,0,0,0,1.35-.25,2.62,2.62,0,0,0,.83-.55,1.89,1.89,0,0,0,.55-.83,4.38,4.38,0,0,0,.25-1.35c0-.77,0-1,0-2.92s0-2.15,0-2.92A4.18,4.18,0,0,0,18.15,8.26Zm-5.66,8a3.72,3.72,0,1,1,3.72-3.72A3.71,3.71,0,0,1,12.5,16.25Zm3.87-6.73a.86.86,0,1,1,.86-.86A.86.86,0,0,1,16.37,9.52Z"/><path class="cls-1" d="M12.5,0A12.5,12.5,0,1,0,25,12.5,12.5,12.5,0,0,0,12.5,0Zm7.19,15.48a5,5,0,0,1-.34,1.75,3.44,3.44,0,0,1-.83,1.29,3.7,3.7,0,0,1-1.29.83,5,5,0,0,1-1.75.34c-.77,0-1,0-3,0s-2.21,0-3,0a5,5,0,0,1-1.75-.34,3.44,3.44,0,0,1-1.29-.83,3.7,3.7,0,0,1-.83-1.29,5,5,0,0,1-.34-1.75c0-.77,0-1,0-3s0-2.21,0-3a5,5,0,0,1,.34-1.75,3.44,3.44,0,0,1,.83-1.29,3.7,3.7,0,0,1,1.29-.83,5,5,0,0,1,1.75-.34c.77,0,1,0,3,0s2.21,0,3,0a5,5,0,0,1,1.75.34,3.44,3.44,0,0,1,1.29.83,3.7,3.7,0,0,1,.83,1.29,5,5,0,0,1,.34,1.75c0,.77,0,1,0,3S19.72,14.71,19.69,15.48Z"/></svg></a>
                        </div>
                    </div>

                </div>
            </div>
        </footer>
    ';
}    
    echo '<script> var URL_PROCESOS_PERFIL = "'.getTema().'/procesos/perfil/"; </script>';

    wp_enqueue_script('boostrap.min.js', getTema()."/js/bootstrap.min.js", array("jquery"), '1.0.0');
    wp_enqueue_script('global_js', getTema()."/js/global.js", array("jquery"), '2.0.0');
    wp_enqueue_script('main', getTema()."/js/main.js", array("jquery"), '1.0.0');
    wp_enqueue_script('favorites', getTema()."/js/favoritos.js", array("jquery"), '1.0.0');
    wp_enqueue_script('comments', getTema()."/js/comment.js", array("jquery"), '1.0.0');

    wp_enqueue_script('jquery.datepick', getTema()."/lib/datapicker/jquery.datepick.js", array("jquery"), '1.0.0');
    wp_enqueue_script('jquery.plugin', getTema()."/lib/datapicker/jquery.plugin.js", array("jquery"), '1.0.0');
    
    wp_enqueue_script('bxslider', getTema()."/js/jquery.bxslider.js", array("jquery"), '1.0.0');

    // Descomentar para inicializar la tabla fotos
    /*    global $wpdb;
    $reservas = $wpdb->get_results("SELECT * FROM wp_postmeta WHERE meta_key = '_booking_end'");
    foreach ($reservas as $key => $value) {
        $status = $wpdb->get_var("SELECT post_status FROM wp_posts WHERE ID = '{$value->post_id}'");
        if( $status == "confirmed" ){
            if( strtotime(date("Y-m-d")." 00:00:00") < strtotime($value->meta_value) ){
                kmimos_registros_fotos($value->post_id);
            }
        }
    }*/

    if( !is_user_logged_in() ){
        /* 
        include_once("partes/head/script_google_auth.php");
        include_once("partes/head/script_facebook_auth.php");
        */
        wp_enqueue_script('modales', getTema()."/js/registro_cliente.js", array("jquery"), '1.0.0');
    }
    
    //wp_enqueue_script('console', getTema()."/js/console.js", array("jquery"), '1.0.0');


    if(  $_SESSION['admin_sub_login'] == 'YES' ){
        $HTML .= "
            <a href='".get_home_url()."/?i=".md5($_SESSION['id_admin'])."&admin=YES' class='theme_button' style='
                position: fixed;
                display: inline-block;
                left: 50px;
                bottom: 50px;
                padding: 20px;
                font-size: 48px;
                font-family: Roboto;
                z-index: 999999999999999999;
            '>
                X
            </a>
        ";
    }

    echo comprimir_styles($HTML);

    wp_footer();

/*    $HTML = "
        <script type='text/javascript'>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','".get_home_url().'/wp-content/plugins/kmimos/javascript/analytics.js'."','ga');

            ga('create', 'UA-56422840-1', 'auto');
            ga('send', 'pageview');
        </script>

        <link type='text/css' href='".getTema()."/css/fontello.min.css' rel='stylesheet' />

        <script type='text/javascript'>
            jQuery('img').attr('alt', '".get_bloginfo('title', false)."');
        </script>        
    ";   */ 
    
    $HTML = "
        <link type='text/css' href='".getTema()."/css/fontello.min.css' rel='stylesheet' />
        <script type='text/javascript'>
            jQuery('img').attr('alt', '".get_bloginfo('title', false)."');
        </script>        
    ";

    if( !isset($_SESSION[ "llego_al_home" ]) ){
        /*$HTML .= '
            <script>
                evento_google("llego_al_home");  
                evento_fbq("track", "traking_code_llego_al_home");   
            </script>
        ';
        $_SESSION[ "llego_al_home" ] = "YA_ENTRO";*/
    }

    if( !isset($_SESSION) ){ session_start(); }

    if( isset($_SESSION["recordar_subir_fotos"]) ){
        unset($_SESSION["recordar_subir_fotos"]);
        $HTML .= "
            <div class='vlz_modal'>
                <div>
                    <div>
                        <i class='fa fa-times vlz_cerrar_modal' aria-hidden='true'></i>
                        Recuerda subir las fotos diarias de tus huéspedes:<br>
                        Ingresa en tus reservas activas y da click en <strong>“Subir fotos”</strong>

                        <div class='botonera_modal_subir'>
                            <a href='#' id='btn_modal_subir_tarde'>M&aacute;s Tarde</a>
                            <a href='".get_home_url()."/perfil-usuario/fotos/' id='btn_modal_subir'>Subir Fotos</a>
                        </div>
                    </div>
                </div>
            </div>
        ";
    }

    if( !empty($wlabel) ){
        wp_enqueue_script( 'wlabel_js', getTema()."/js/wlabel-content.js",array(), '1.0.0' );
    }

    if( !is_user_logged_in() ){
        /*$HTML .= "<script> startApp(); </script>";*/
    }

    // SubscribeSite
    if( $_SERVER["HTTP_REFERER"] != "https://www.kmimos.com.mx/google-adwords/" && $_SERVER["HTTP_REFERER"] != "https://kmimos.com.mx/google-adwords/" ){
        //include_once( 'partes/footer/SubscribeSite.php' );       
    }

    echo comprimir_styles($HTML);

/*    echo '
        <script>
            jQuery("#popup-registrarte").modal().show();
            jQuery(".popup-registrarte-1").css("display", "none");
            jQuery(".popup-registrarte-datos-mascota").css("display", "block");
        </script>
    ';*/

/*    echo "
        <!-- BEGIN Evento objetivo GA -->        
        <script>
        window.addEventListener(\"load\",function(){
            var timer = setInterval(function(){
            
                if(jQuery('#PageSubscribe .section3:contains(\"*Dentro de 48 hrs. Te enviaremos vía email tu cúpon de descuento\")').is(\":visible\")){
                    ga('send','event','message','submit','subscribe')
                    clearInterval(timer)
                }

            },2000)
        })
        </script>
        <!-- END Evento objetivo GA -->        
    ";*/

/*	echo "<pre style='display: none;'>";
        	print_r($_SERVER);
    	echo "</pre>";*/
        echo '
        <script>
          !function() {
          var t; if (t = window.botsify = window.botsify = window.botsify || [], !t.init) return t.invoked ? void (window.console && console.error && console.error("Botsify snippet included twice.")) : (
          t.load =function(e){  var o,n;    o=document.createElement("script"); e.type="text/javscript"; o.async=!0; o.crossorigin="anonymous";
          o.src="https://botsify.com/web-bot/script/frame/"+e+"/botsify.js";    n=document.getElementsByTagName("script")[0];   n.parentNode.insertBefore(o,n); });
          }(); botsify.load(\'n6LBSxHkX1HrbXuJrLMjSdkJLFyCZqQdcAQlsjN9\');
        </script>';

    echo "</body></html>";

?>
        

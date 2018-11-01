jQuery( document ).ready(function() {
    String.prototype.replaceAll = function(search, replacement) {
        var target = this;
        return target.replace(new RegExp(search, 'g'), replacement);
    };

    jQuery('#leer_testimonio').on('click', function(e){
        jQuery( "#popup-iniciar-sesion .popup-login-condiciones .terminos_container" ).scrollTop(999999999999999999999999999999);
    });

    jQuery( "#popup-iniciar-sesion .popup-login-condiciones .terminos_container" ).scroll(function() {
        if( jQuery( "#popup-iniciar-sesion .popup-login-condiciones .terminos_container" )[0].scrollHeight <= ( parseInt( jQuery( "#popup-iniciar-sesion .popup-login-condiciones .terminos_container" ).scrollTop() ) + 700  ) ){
            jQuery( "#login_submit" ).removeClass("btn_disable");
        }
    });


    jQuery('#mostrar_terminos').on('click', function(e){
        e.preventDefault();
        if( validar_login() ){
            jQuery('.popup-iniciar-sesion-1').addClass('popuphide');
            jQuery('.popup-login-condiciones').removeClass('popuphide');
        }
    });


    jQuery("#close_login").on("click", function(e){
        close_login_modal();
    });

    jQuery(".vlz_cerrar_modal").on("click", function(e){
        jQuery(".vlz_modal").css("display", "none");
    });

	jQuery("#btn_modal_subir_tarde").on("click", function(e){
        jQuery(".vlz_modal").css("display", "none");
    });

    jQuery("#login").on("click", function(e){
        show_login_modal("login");
    });

	jQuery("#login_movil").on("click", function(e){
        show_login_modal("login");
    });

	jQuery("#recuperar").on("click", function(e){
        show_login_modal("recuperar");
    });

	jQuery("#login_submit").on("click", function(e){

        if( !jQuery( "#login_submit" ).hasClass('btn_disable')  ){
            logear();
        }else{
            alert("Debe leer los terminos y condiciones primero.");
        }
		e.preventDefault();
    });

    jQuery("#form_login").submit(function(e){ 
        if( validar_login() ){
            jQuery('#mostrar_terminos').click();
        }
        e.preventDefault();
   	});

    jQuery("#ver_menu").on("click", function(e){
        if( jQuery("#menu_movil").css("left") == "0px" ){
            jQuery("#menu_movil").css("left", "-110%");
            block_scroll_body(true);
            visible_boton_mapa(true);
        }else{
            jQuery("#menu_movil").css("left", "0px");
            block_scroll_body(false);
            visible_boton_mapa(false);
        }
    });

    jQuery('#km-checkbox').on('click', function(){
        if( jQuery("#km-checkbox").val() == 'none' ){
            jQuery("#km-checkbox").val('active');
            jQuery(this).parent().css('background', '#98F0DE');
            jQuery(this).parent().find('label').css('background', '#33E1BE');
        }else{
            jQuery(this).parent().css('background', '#ccc');
            jQuery(this).parent().find('label').css('background', '#aaa');
            jQuery("#km-checkbox").val('none');
        }
    });

    jQuery('#usuario').on({
        change: function(){ validar_login(['usuario']); },
        blur: function(){ validar_login(['usuario']); },
    });

    jQuery('#clave').on({
        change: function(){ validar_login(['clave']); },
        blur: function(){ validar_login(['clave']); },
    });

    jQuery(".cerrar_menu_movil").on("click", function(e){
        jQuery("#menu_movil").css("left", "-110%");
        block_scroll_body(true);
        visible_boton_mapa(true);
    });

    jQuery('#menu_movil').on("click", function(e) {
        if ( e.target.id == "menu_movil" ) {
            jQuery("#menu_movil").css("left", "-110%");
            block_scroll_body(true);
            visible_boton_mapa(true);
        };
    }); 


    jQuery(".btn_rotar").on("click", function(e){
        rotar( jQuery( this ).attr("data-orientacion") );
    });

    jQuery(".btn_aplicar_rotar").on("click", function(e){
        if( !jQuery(".btn_aplicar_rotar").hasClass("btn_aplicar_rotar_inactivo") ){
            jQuery('.btn_aplicar_rotar').addClass("btn_aplicar_rotar_inactivo");
            var img_rotada = jQuery("#kmimos_redimencionar_imagenes img").attr("src");
            jQuery('.vlz_cargando').css("display", "block");
            redimencionar(img_rotada, function(img_reducida){
                var a = RAIZ+"imgs/procesar.php";
                var img_pre = jQuery(".vlz_rotar_valor").attr("value");
                jQuery.ajax({
                    async:true, cache:false, type: 'POST', url: a,
                    data: { img: img_reducida, previa: img_pre }, 
                    success:  function(url){
                        jQuery(".vlz_rotar").css("background-image", "url("+RAIZ+"imgs/Temp/"+url+")" );
                        jQuery(".vlz_rotar_valor").attr("value", url);
                        jQuery('.vlz_cargando').css("display", "none");
                        jQuery('.btn_aplicar_rotar').css("display", "none");
                        jQuery('.btn_aplicar_rotar').removeClass("btn_aplicar_rotar_inactivo");
                    },
                    beforeSend:function(){},
                    error:function(objXMLHttpRequest){}
                });
            });
        }
    });

    jQuery(".km-contenedor-favorito").on("click", function(e){
        e.preventDefault();
    });
});

jQuery(window).on('resize', function(){
    var w = jQuery(window).width();
    if( w < 768 ){

        if( jQuery(".km-map-content").hasClass('showMap') ){
            jQuery(".km-map-content").addClass("showMap");
        }

        var show = jQuery('#menu_movil').css('left');
        if( show == '0px' ){
            block_scroll_body(false);
        }else{
            block_scroll_body(true);
        }
    }else{
        block_scroll_body(true);
    }
});

jQuery(document).on('click', '[data-PdfXml]', function(e){
    e.preventDefault();
    var file = [];
        file.push( jQuery(this).attr('data-PdfXml') );
    download( file );
});

jQuery(document).on("focusin", '[data-target="help"]', function(e){
    mostrarAyuda( jQuery(this) );
});

function mostrarAyuda( objID ){
    jQuery('[data-help]').css('display', 'none');
    jQuery('[data-help="'+jQuery(objID).attr('name')+'"]').css('display', 'inline-block');
}

function download( archivos ){
    jQuery.post(HOME+"procesos/generales/download_zip.php", {'fact_selected': archivos}, function(e){
        e = JSON.parse(e);
        if( e['estatus'] == "listo" ){
            location.href = e['url'];
        }
    });
}

function block_scroll_body( block=true ){
    jQuery('body').css('overflow', 'auto');
    if( !block ){
        jQuery('body').css('overflow', 'hidden');
    }
}

function visible_boton_mapa( display=true){
    if( !display ){
        jQuery('.btnOpenPopupMap').css('display', 'none');
        jQuery('.btnOpenPopupMap').css('display', 'none');  
    }else{
        jQuery('.btnOpenPopupMap').css('display', 'block');
        jQuery('.btnOpenPopupMap').css('display', 'block');
    }
}


function social_auth( f ){
    jQuery.get(HOME+"/procesos/login/login_social_id.php?init="+f, function(e){
        e = JSON.parse(e);
        jQuery('[data-error="auth"]').fadeOut("fast");
        if( e['status'] == 'true' ){
            console.log("as");
            location.reload();
        }else{
            jQuery('[data-error="auth"]').html(e['msg']);
            jQuery('[data-error="auth"]').fadeIn("fast");
            setTimeout(function() {
                jQuery('[data-error="auth"]').fadeOut(1500);
            },3000);
        }
    });
}

function social_verificar( social_network, id, email ){

}


function validar_login( fields = ['usuario', 'clave'] ){
    jQuery('[data-id="alert_login"]').remove();
    var sts = true;
    jQuery.each(fields, function(i,v){    
        jQuery('[data-id="'+v+'"]').remove();
        if( jQuery('#'+v).val() == '' ){        
            jQuery('#'+v).after('<div style="width: 100%;padding: 5px;" data-id="'+v+'" class="alert-danger"><strong>Este campo no puede estar vacio</strong></div>');
            sts = false;
        }
    });
    return sts;
}
function logear(){

    var btn = jQuery('#login_submit');
        btn.html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> INICIANDO SESI&Oacute;N');

    if( validar_login() ){

        jQuery.post( 
            HOME+"/procesos/login/login.php", 
            {
                usu: jQuery("#form_login #usuario").val(),
                clv: jQuery("#form_login #clave").val()
            },
            function( data ) {
                if( data.login ){
                    location.reload();
                }else{
                    jQuery('#login_submit').before('<div data-id="alert_login" class="alert alert-danger"><strong>'+data.mes+'</strong></div>');
                    setTimeout(function() {
                        jQuery('[data-id="alert_login"]').remove();
                    },3000);
                }
                jQuery('.popup-iniciar-sesion-1').removeClass('popuphide');
                jQuery('.popup-login-condiciones').addClass('popuphide');
                btn.html('ACEPTAR TERMINOS Y CONDICIONES');
//                btn.html('INICIAR SESIÓN AHORA');
            },
            "json"
        );
    }else{
        btn.html('INICIAR SESIÓN AHORA');
    }
}

function getAjaxData(url,method, datos){
    return jQuery.ajax({
        data: datos,
        type: method,
        url: HOME+url,
        async:false,
        success: function(data){
            return data;
        }
    }).responseText;
}

jQuery('#popup-iniciar-sesion').on('shown.bs.modal', function () {
    jQuery('.popup-iniciar-sesion-1').removeClass('popuphide');
    jQuery('.popup-login-condiciones').addClass('popuphide');
    jQuery('.popup-olvidaste-contrasena').addClass('popuphide');
});

/*MODAL SHOW*/
jQuery(document).on('click', '.modal_show' ,function(e){
    modal_show(this)
});
function modal_show(element){
    var modal = jQuery(element).data('modal');
    jQuery('.modal').modal('hide');
    jQuery(modal).modal("show");
}


function show_login_modal(seccion){
	switch(seccion){
        case "login":
            jQuery("#modal_login form").css("display", "none");
            jQuery('.popup-login-condiciones').addClass('popuphide');
            jQuery("#form_login").css("display", "block");
            jQuery("#modal_login").css("display", "table");
        break;
		case "servicios":
			jQuery("#modal_servicio").css("display", "table");
		break;
		case "recuperar":
			jQuery(".modal_login form").css("display", "none");
			jQuery("#form_recuperar").css("display", "block");
            jQuery("#modal_login").css("display", "table");
		break;
	}
}

function close_login_modal(){
    jQuery(".modal_login").hide();
}

function postJSON(FORM, URL, ANTES, RESPUESTA, TIPO = ""){
	jQuery("#"+FORM).submit(function( event ) {
	  	event.preventDefault();

        console.log( "Hola" );

        if( validarAll(FORM) ){
            ANTES();
            if( TIPO == "json" ){
                jQuery.post(URL, jQuery("#"+FORM).serialize(), RESPUESTA, 'json').fail(function(e) {
                    console.log( e );
                });
            }else{
                jQuery.post(URL, jQuery("#"+FORM).serialize(), RESPUESTA).fail(function(e) {
                    console.log( e );
                });
            }
        }
	});
}

function subirImg(evt){
	var files = evt.target.files;
    var padre = jQuery(this).parent().parent();
    getRealMime(this.files[0]).then(function(MIME){
        if( MIME.match("image.*") ){

            padre.children('.vlz_img_portada_cargando').css("display", "block");
            var reader = new FileReader();
            reader.onload = (function(theFile) {
                return function(e) {
                    redimencionar(e.target.result, function(img_reducida){
                        var img_pre = jQuery(".vlz_rotar_valor").attr("value");
                        jQuery.post( RUTA_IMGS+"/procesar.php", {img: img_reducida, previa: img_pre}, function( url ) {
                            padre.children('.vlz_img_portada_fondo').css("background-image", "url("+RUTA_IMGS+"/Temp/"+url+")");
                            padre.children('.vlz_img_portada_normal').css("background-image", "url("+RUTA_IMGS+"/Temp/"+url+")");
                            padre.children('.vlz_img_portada_cargando').css("display", "none");
                            padre.siblings('.vlz_img_portada_valor').val(url);
                            padre.children('.vlz_cambiar_portada').children('input').val("");

                            jQuery(".btn_rotar").css("display", "block");
                        });
                    });      
                };
           })(files[0]);
           reader.readAsDataURL(files[0]);
        }else{
            padre.children('.vlz_cambiar_portada').children('input').val("");
            padre.children('.vlz_img_portada_cargando').css("display", "none");
            alert("Solo se permiten imagenes");
        }
    }).catch(function(error){
        padre.children('.vlz_cambiar_portada').children('input').val("");
        padre.children('.vlz_img_portada_cargando').css("display", "none");
        alert("Solo se permiten imagenes");
    });  	
}

function getRealMime(file) {
    return new Promise((resolve, reject) => {
        if (window.FileReader && window.Blob) {
            let slice = file.slice(0, 4);
            let reader = new FileReader();
          
            reader.onload = () => {
                let buffer = reader.result;
                let view = new DataView(buffer);
                let signature = view.getUint32(0, false).toString(16);
                let mime = 'unknown';

                switch ( String(signature).toLowerCase() ) {
                    case "89504e47":
                        mime = "image/png";
                    break;
                    case "47494638":
                        mime = "image/gif";
                    break;
                    case "ffd8ffe0":
                        mime = "image/jpeg";
                    break;
                    case "ffd8ffe1":
                        mime = "image/jpeg";
                    break;
                    case "ffd8ffe2":
                        mime = "image/jpeg";
                    break;
                    case "ffd8ffe3":
                        mime = "image/jpeg";
                    break;
                    case "ffd8ffe8":
                        mime = "image/jpeg";
                    break;
                }

                resolve(mime);

            }
            reader.readAsArrayBuffer(slice);
        } else {
            reject(new Error('Usa un navegador moderno para una mejor experiencia'));
        }
    });
}

function initImg(id){
    document.getElementById(id).addEventListener("change", subirImg, false);
}  


/* Procesado de imagenes */

function d(s){ return jQuery(s)[0].outerHTML; }
function c(i){
   var e = document.getElementById(i);
   if(e && e.getContext){
        var c = e.getContext('2d');
        if(c){ return c; }
   }
   return false;
}

function contenedor_temp(){
    if( jQuery("#kmimos_redimencionar_imagenes").html() == undefined ){
        var img = jQuery("<img>", {
            id: "kmimos_img_temp"
        })[0].outerHTML;

        var cont_canvas = jQuery("<span>", {
            id: "kmimos_canvas_temp"
        })[0].outerHTML

        var cont_general = jQuery("<div>", {
            id: "kmimos_redimencionar_imagenes",
            html: cont_canvas+img,
            style: "display: none;"
        })[0].outerHTML;

        return jQuery("body").append(cont_general);
    }else{
        var img = jQuery("<img>", {
            id: "kmimos_img_temp"
        })[0].outerHTML;

        var cont_canvas = jQuery("<span>", {
            id: "kmimos_canvas_temp"
        })[0].outerHTML

        var cont_general = jQuery("<div>", {
            id: "kmimos_redimencionar_imagenes",
            html: cont_canvas+img,
            style: "display: none;"
        })[0].outerHTML;

        jQuery("#kmimos_redimencionar_imagenes").html(cont_general);
    }
}

function rotar(orientacion){

    var img_rotada = _rotar(orientacion);
    if( img_rotada != null ){
        jQuery("#kmimos_redimencionar_imagenes img").attr("src", img_rotada);
        jQuery(".vlz_rotar").css("background-image", "url("+img_rotada+")" );
        jQuery('.btn_aplicar_rotar').css("display", "block");
    }else{
        alert("No hay imagen seleccionada");
    }

}



function rotar_img(orientacion, id){
    console.log(id+" "+orientacion);
    var img_rotada = _rotar(orientacion, id);
    if( img_rotada != null ){

        jQuery("#"+id).attr("src", img_rotada );


    }else{
        alert("No hay imagen seleccionada");
    }
}

function _rotar(orientacion, id = ""){
    if( typeof(CTX) != "undefined" ){
        if( id != "" ){
            jQuery("#kmimos_redimencionar_imagenes img").attr("src", jQuery("#"+id).attr("src") );
        }
        var rxi = jQuery("#kmimos_redimencionar_imagenes img")[0];
        var rh = rxi.width; var rw = rxi.height;
        var xw = 500; var xh = 500;
        if( (rw > xw) && (rh > xh) ){
            if( rw <= rh ){
                var porc = ((xw*100)/rw)/100; var w = rw*porc; var h = rh*porc;
            }else{
                var porc = ((xh*100)/rh)/100; var w = rw*porc; var h = rh*porc;
            }
        }else{ var w = rw; var h = rh; }
        CA = d("<canvas id='kmimos_canvas' width='"+w+"' height='"+h+"'>");
        jQuery("#kmimos_redimencionar_imagenes #kmimos_canvas_temp").html(CA);
        CA = jQuery("#kmimos_redimencionar_imagenes #kmimos_canvas_temp #kmimos_canvas");
        CTX = c('kmimos_canvas');
        if(CTX){

            var x = 0; var y = 0; 

            switch(orientacion){
                case "left":
                    x = h*-1;
                    CTX.rotate(Math.PI*-0.5);
                break;
                case "right":
                    y = w*-1;
                    CTX.rotate(Math.PI*0.5);
                break;
            }

            CTX.drawImage(rxi, x, y, h, w);
            var img_rotada = CA[ 0 ].toDataURL("image/jpg");
            //jQuery("#kmimos_redimencionar_imagenes img").attr("src", img_rotada);

            return img_rotada;
        }
    }else{
        return null;
    }
    return null;
}

function redimencionar(IMG_CACHE, CB){
    contenedor_temp();
    var ximg = new Image();
    ximg.src = IMG_CACHE;
    ximg.onload = function(){
        jQuery("#kmimos_redimencionar_imagenes #kmimos_img_temp").attr("src", ximg.src);
        var rxi = jQuery("#kmimos_redimencionar_imagenes #kmimos_img_temp")[0];
        var rw = rxi.width;
        var rh = rxi.height;
        var w = 800;
        var h = 600;
        if( rw > rh ){
            h = Math.round( ( rh * w ) / rw );
        }else{
            w = Math.round( ( rw * h ) / rh );
        }
        CA = d("<canvas id='kmimos_canvas' width='"+w+"' height='"+h+"'>");
        jQuery("#kmimos_redimencionar_imagenes #kmimos_canvas_temp").html(CA);
        CA = jQuery("#kmimos_redimencionar_imagenes #kmimos_canvas_temp #kmimos_canvas");
        CTX = c("kmimos_canvas");
        if(CTX){
            CTX.drawImage(ximg, 0, 0, w, h);
            CB( CA[ 0 ].toDataURL("image/jpeg") );
        }else{
            return false;
        }
    }
}

/*  Validaciones */

function validar(id){
    var e = jQuery("#"+id);
    var validaciones = String(e.attr("data-valid")).split(",");
    var error = false;

    jQuery.each(validaciones, function( index, value ) {
        var validacion = value.split(":");
        switch(validacion[0]){
            case "isChecked":
                if( !e.is(':checked')) {  error = true; break; }
            break;            
            case "requerid":
                if( e.val() == "" ){ error = true; break; }
            break;
            case "min":
                if( e.val().length < validacion[1] ){ error = true; break; }
            break;
            case "max":
                if( e.val().length > validacion[1] ){ error = true; break; }
            break;
            case "equalTo":
                if( e.val() != jQuery("#"+validacion[1]).val() ){ 
                    error = true; 
                    aplicar_error(validacion[1], true);
                    break; 
                }else{
                    aplicar_error(validacion[1], false);
                }
            break;
        }
    });

    aplicar_error(id, error);

    return error;
}

function pre_validar(elemento){
    if( elemento.attr("data-valid") != undefined ){
        var error = jQuery("<div class='no_error' id='error_"+( elemento.attr('id') )+"' data-id='"+( elemento.attr('id') )+"'></div>");
        var txt = elemento.attr("data-title");
        if( txt == "" || txt == undefined ){ txt = "Completa este campo."; }
        error.html( txt );
        elemento.parent().append( error );
        elemento.on("keyup", function(event){
            validar(event.target.id);
        }); 
        elemento.on("change", function(event){
            validar(event.target.id);
        }); 
            
    } 
}

function aplicar_error(id, error){
    if ( error  ) {
        if( jQuery("#error_"+id).hasClass( "no_error" ) ){
            jQuery("#error_"+id).removeClass("no_error");
            jQuery("#error_"+id).addClass("error");
        } 
    } else {
        if( jQuery("#error_"+id).hasClass( "error" ) ){
            jQuery("#error_"+id).removeClass("error");
            jQuery("#error_"+id).addClass("no_error");
        }
    }
}

function validarAll(Form){
    var submit = true;
    jQuery( "#"+Form+" [data-valid]" ).each(function( index ) {
        if( validar( jQuery( this ).attr("id") ) ){
            submit = false;
        }
    });
    if(!submit){
        var primer_error = ""; var z = true;
        jQuery( ".error" ).each(function() {
            if( jQuery( this ).css( "display" ) == "block" ){
                if( z ){ primer_error = "#"+jQuery( this ).attr("data-id"); z = false; }
            }
        });
        jQuery('html, body').animate({ scrollTop: jQuery(primer_error).offset().top-130 }, 2000);
    }
    return submit;
}

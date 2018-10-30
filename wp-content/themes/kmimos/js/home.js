var hasGPS=false;
var crd;
var prueba_ubicacion = false;

var limites = {
    'norte': {
        "lat": parseFloat("32.7187629"),
        "lng": parseFloat("-86.5887")
    },
    'sur': {
        "lat": parseFloat("14.3895"),
        "lng": parseFloat("-118.6523001")
    }
};

(function(jQuery) {
    'use strict';

    jQuery("#mi_ubicacion").on("click", function(e){

        jQuery(".icon_left").removeClass("fa-crosshairs");
        jQuery(".icon_left").addClass("fa-spinner fa-spin");
        
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                crd = pos.coords;

                /*if( 
                    (
                        limites.norte.lat >= crd.latitude && limites.sur.lat <= crd.latitude &&
                        limites.norte.lng >= crd.longitude && limites.sur.lng <= crd.longitude
                    ) || 
                    prueba_ubicacion == true
                ){*/
                    jQuery( '[data-error="ubicacion"]' ).parent().removeClass('has-error');
                    jQuery( '[data-error="ubicacion"]' ).addClass('hidden');

                    jQuery('#latitud').val(crd.latitude);
                    jQuery('#longitud').val(crd.longitude);

                    var geocoder = new google.maps.Geocoder();

                    var latlng = {lat: parseFloat(crd.latitude), lng: parseFloat(crd.longitude)};
                    geocoder.geocode({'location': latlng}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            var address = results[0]['formatted_address'];
                            jQuery("#ubicacion_txt").val(address);
                            jQuery("#ubicacion").val("");

                            jQuery(".icon_left").removeClass("fa-spinner fa-spin");
                            jQuery(".icon_left").addClass("fa-crosshairs");
                        }
                    });
                /*}else{
                    jQuery( '[data-error="ubicacion"]' ).parent().addClass('has-error');
                    jQuery( '[data-error="ubicacion"]' ).removeClass('hidden');
                }*/
            }, 
            function error(err) {
                jQuery(".icon_left").removeClass("fa-spinner fa-spin");
                jQuery(".icon_left").addClass("fa-crosshairs");
                if( err.message == 'User denied Geolocation' ){
                    alert("Estimado usuario, para poder acceder a esta función, es necesario desbloquear a kmimos en la configuración de ubicación de su dispositivo.");
                }else{
                    alert(err.message);
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    });
    
    jQuery('#servicios_adicionales').on('click', function () {
       jQuery('#servicios_adicionales').dropdown('show');
    });
    
    jQuery('[data-target="patitas-felices"]').on('click', function(){

        if( jQuery('#cp_email').val() != '' && jQuery('#cp_nombre').val() != ''){

            jQuery('#msg').html('Enviando solicitud.');
            jQuery('#cp_loading').removeClass('hidden');
            jQuery('#cp_loading').fadeIn(1500);

            var urls = RAIZ+"/landing/list-subscriber.php?source=kmimos-mx-clientes-referidos&email="+jQuery('#cp_email').val();
            jQuery.get( urls, function(e){});

            var urluser = RAIZ+"landing/registro-usuario.php?email="+jQuery('#cp_email').val()+"&name="+jQuery('#cp_nombre').val()+"&referencia=kmimos-home";
            jQuery.get( urluser, function(e){

                var redirect = RAIZ+"/referidos/compartir/?e="+jQuery('#cp_email').val();
                switch (jQuery.trim(e)){
                    case '0':
                        jQuery('#msg').html('¡No pudimos completar su solicitud!');
                        break;
                    case '1':
                        jQuery('#msg').html('¡Felicidades, ya formas parte de nuestro Club!');
                        jQuery('a[data-redirect="patitas-felices"]').attr('href', redirect);
                        jQuery('a[data-redirect="patitas-felices"]').click();
                        window.open( redirect, '_blank' );
                        break;
                    case '2':
                        jQuery('#msg').html('¡Ya formas parte de nuestro Club!');
                        jQuery('a[data-redirect="patitas-felices"]').attr('href', redirect);
                        jQuery('a[data-redirect="patitas-felices"]').click();
                        window.open( redirect, '_blank' );
                        break;
                    default:
                        jQuery('#msg').html('Registro: No pudimos completar su solicitud, intente nuevamente');
                        jQuery('#cp_loading').addClass('hidden');
                        break;
                }
                setTimeout(function() {
                    jQuery('#cp_loading').fadeOut(1500);
                },3000);
            })
            .fail(function() {
                jQuery('#msg').html('Registro: No pudimos completar su solicitud, intente nuevamente');
                jQuery('#cp_loading').addClass('hidden');
            }); 

        }else{
           
            var danger_color =  '#c71111';
            var border_color =  '#c71111';
            var visible = 'visible';
 
            jQuery('[data-error="cp_nombre"]').css('visibility', visible);
            jQuery('[data-error="cp_nombre"]').css('color', danger_color);
            jQuery('[data-error="cp_nombre"]').html(msg);
            jQuery('[name="cp_nombre"]').css('border-bottom', '1px solid ' + border_color);
            jQuery('[name="cp_nombre"]').css('color', danger_color);

            jQuery('[data-error="cp_email"]').css('visibility', visible);
            jQuery('[data-error="cp_email"]').css('color', danger_color);
            jQuery('[data-error="cp_email"]').html(msg);
            jQuery('[name="cp_email"]').css('border-bottom', '1px solid ' + border_color);
            jQuery('[name="cp_email"]').css('color', danger_color);

        }
    });

    jQuery('.adicionales_button').on('click', function(){
        if( jQuery('.modal_servicios').css('display') == 'none' ){
            jQuery('.modal_servicios').css('display', 'table');
        }else{
            jQuery('.modal_servicios').css('display', 'none');
        }
    });

    jQuery('#close_mas_servicios').on('click', function(){
        jQuery('.modal_servicios').css('display', 'none');
    });

    jQuery(function(){

        jQuery("#boton_buscar").on("click", function(e){
            jQuery("#buscador").submit();
        });

        jQuery("#close_video").on("click", function(e){
            close_video();
        });

    });

    jQuery('#buscador .km-opcion, #popup-servicios .km-opcion').on('click', function(e) {
        if( jQuery(this).hasClass("km-opcionactivo") ){
            jQuery(this).removeClass("km-opcionactivo");
            jQuery(this).children("input").prop("checked", false);
        }else{
            jQuery(this).addClass("km-opcionactivo");
            jQuery(this).children("input").prop("checked", true);
        }
    });


})(jQuery);

window.addEventListener("load", loadBGVideoHOME);


function loadBGVideoHOME(){
    if( jQuery(window).width() >= 768 ){
        jQuery('.km-video-bg').html(
            '<div class="overlay"></div>'+
            '<video loop muted autoplay poster="'+HOME+'/images/new/km-hero-desktop.jpg" class="km-video-bgscreen">'+
                '<source src="'+HOME+'/images/new/videos/km-home/km-video.webm" type="video/webm">'+
                '<source src="'+HOME+'/images/new/videos/km-home/km-video.mp4" type="video/mp4">'+
                '<source src="'+HOME+'/images/new/videos/km-home/km-video.ogv" type="video/ogg">'+
            '</video>'
        );
    }else{
        jQuery('.km-video-bg').html('<div class="overlay"></div>');
    }
}

var fecha = new Date();
jQuery(document).ready(function(){
    
    jQuery('.bxslider').bxSlider({
        buildPager: function(slideIndex){
            switch(slideIndex){
                case 0:
                    return '<img src="'+HOME+'images/new/km-testimoniales/thumbs/testimonial-3.jpg">';
                case 1:
                    return '<img src="'+HOME+'images/new/km-testimoniales/thumbs/testimonial-2.jpg">';
                case 2:
                    return '<img src="'+HOME+'images/new/km-testimoniales/thumbs/testimonial-1.jpg">';
            }
        }
    });

    jQuery('#popup-servicios').on('hidden.bs.modal', function () {
        jQuery("#buscar_no").click();
    });

    jQuery("#buscar").on("click", function ( e ) {
        e.preventDefault();
        jQuery("#buscador").submit();
    });

    jQuery("#buscar_no").on("click", function ( e ) {
        e.preventDefault();
        jQuery("#buscador").submit();
    });

    jQuery("#form_cuidador").submit(function(e){
        if( jQuery("#checkin").val() == "" ){
            jQuery("#checkin").css("border", "solid 1px red");
            jQuery("#checkout").css("border", "solid 1px red");
            jQuery(".validacion_fechas").css("display", "block");

            jQuery(".validacion_fechas").css("display", "block");
            jQuery(".km-ficha-fechas").css("margin-bottom", "0px");
            e.preventDefault();
        }
    });

    jQuery(".datepick td").on("click", function(e){
        jQuery( this ).children("a").click();
    });
});

function show_video(){
    jQuery(".modal_video iframe").attr("src", "https://www.youtube.com/embed/xjyAXaTzEhM?rel=0&showinfo=0&autoplay=1");
    jQuery(".modal_video").css("display", "table");
}

function close_video(){
    jQuery(".modal_video iframe").attr("src", "");
    jQuery(".modal_video").hide();
}

function validar_busqueda_home(){
    var IN  = validar( 'checkin' );
    var OUT = validar( 'checkout' );
    var primer_error = '';
    
    jQuery( '#checkin' ).parent().removeClass('has-error');
    jQuery( '[data-error="checkin"]' ).addClass('hidden');
    jQuery( '#checkout' ).parent().removeClass('has-error');
    jQuery( '[data-error="checkout"]' ).addClass('hidden');

    jQuery( '#ubicacion' ).parent().removeClass('has-error');
    jQuery( '[data-error="ubicacion"]' ).addClass('hidden');

    if( OUT ){
        jQuery( '#checkout' ).parent().addClass('has-error');
        jQuery( '[data-error="checkin"]' ).removeClass('hidden');
        primer_error = "#checkout";
    }
    if( IN ){
        jQuery( '#checkin' ).parent().addClass('has-error');
        jQuery( '[data-error="checkin"]' ).removeClass('hidden');
        primer_error = "#checkin";
    }

    if( !IN && !OUT ){
        return true;
    }

    jQuery('html, body').animate({ scrollTop: jQuery(primer_error).offset().top-180 }, 2000);    
    return false;
}


jQuery(document).on('click', '[data-target="iframe-testimonio"]', function(){
    if( jQuery(this).data('video') != '' ){
        jQuery('#iframe-testimonio').attr( 'src', jQuery(this).data('video')+"?rel=0&amp;showinfo=0&amp;autoplay=1" );

        jQuery('#testimonio').css('margin-top', jQuery('nav').height());
        jQuery('#testimonio').modal('show');
    }
});

jQuery(document).on('click', '[data-action="validate"]', function(e){
    if( validar_busqueda_home() ){
        jQuery('#popup-servicios').modal('show');
    }
});


jQuery(document).on('click', '[data-target="close-testimonio"]', function (e) {
    stop_video();
});


jQuery(document).keyup(function(e) {
    if (e.keyCode == 27){
        stop_video();
    }
});

function stop_video(){
    jQuery('#iframe-testimonio').attr( 'src', 'http://');
    jQuery('#testimonio').modal('hide');

    console.log('stop video');
}

(function(d, s){
    map = d.createElement(s), e = d.getElementsByTagName(s)[0];
    map.async=!0;
    map.setAttribute("charset","utf-8");
    map.src="//maps.googleapis.com/maps/api/js?v=3&key=AIzaSyD-xrN3-wUMmJ6u2pY_QEQtpMYquGc70F8";
    map.type="text/javascript";
    e.parentNode.insertBefore(map, e);
})(document,"script");
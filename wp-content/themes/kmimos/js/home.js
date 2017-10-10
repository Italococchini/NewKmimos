var hasGPS=false;

(function(jQuery) {
    'use strict';



    jQuery(document).on('click', '[data-action="dropdown"]', function(){
       alert('click dropdown');
    });

    
    jQuery('#servicios_adicionales').on('click', function () {
       jQuery('#servicios_adicionales').dropdown('show');
    });
    
    jQuery(document).on('click', '[data-action="validate"]', function(e){
        if( validar_busqueda_home() ){
            jQuery('#popup-servicios').modal('show');
        }
    });

    jQuery('[data-target="patitas-felices"]').on('click', function(){

        if( jQuery('#cp_email').val() != '' && jQuery('#cp_nombre').val() != ''){

            jQuery('#msg').html('Enviando solicitud.');
            jQuery('#cp_loading').removeClass('hidden');
            jQuery('#cp_loading').fadeIn(1500);

            jQuery.ajax( RAIZ+"landing/registro-usuario.php?email="+jQuery('#cp_email').val()+"&name="+jQuery('#cp_nombre').val()+"&referencia=kmimos-home" )
            .done(function(e) {
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

    if (navigator.geolocation) {
        /*navigator.geolocation.getCurrentPosition(coordenadas);*/
    } else {
        jQuery('#selector_locacion').removeClass('hide');
        jQuery('#selector_coordenadas').addClass('hide');
        jQuery('#selector_tipo').addClass('hide');
    }
    if(navigator.platform.substr(0, 2) == 'iP') jQuery('html').addClass('iOS');
    jQuery(function(){
        var edos = jQuery('#estado_cuidador').val();
        
        function cargar_municipios(CB){
            var estado_id = jQuery('#estado_cuidador').val();       
            if( estado_id != '' ){
                jQuery.getJSON( 
                    URL_MUNICIPIOS, 
                    {estado: estado_id} 
                ).done(
                    function( data, textStatus, jqXHR ) {
                        var html = "<option value=''>Seleccione un municipio</option>";
                        if( data != undefined ){
                            jQuery.each(data, function(i, val) {
                                html += '<option value='+val.id+'>'+val.name+'</option>';
                            });
                            jQuery('#municipio_cuidador').html(html);
                        }

                        if( CB != undefined) {
                            CB();
                        }
                    }
                ).fail(
                    function( jqXHR, textStatus, errorThrown ) {
                        console.log( 'Error: ' +  errorThrown );
                    }
                );
            }
        }
        jQuery('#estado_cuidador').on('change', function(e){
            cargar_municipios();
        });
        /*cargar_municipios(function(){
            jQuery("#municipio_cuidador > option[value='"+jQuery('#municipio_cache').val()+"']").attr('selected', 'selected');
        });*/
        jQuery('#municipio_cuidador').on('change', function(e){
            jQuery('#municipio_cache').attr('value', jQuery('#municipio_cuidador').val() );
        });

        jQuery('.boton_servicio > input').on('change',function(e){
            var activo = jQuery(this).prop('checked');
            if(activo) jQuery( this ).parent().addClass('check_select');
            else jQuery( this ).parent().removeClass('check_select');
        });

        jQuery('label > input').on('change',function(e){
            var activo = jQuery(this).prop('checked');
            jQuery( ".por_ubicacion" ).removeClass('input_select');
            if(activo) jQuery( this ).parent().addClass('input_select');

            switch( jQuery(this).parent().attr("for") ){
                case "mi-ubicacion":
                    jQuery(".selects_ubicacion_container").hide();
                break;
                case "otra-localidad":
                    jQuery(".selects_ubicacion_container").show();
                break;
            }

        });

        jQuery("#boton_buscar").on("click", function(e){
            jQuery("#buscador").submit();
        });

        jQuery("#close_video").on("click", function(e){
            close_video();
        });

        jQuery('.km-opcion').on('click', function(e) {
            jQuery(this).toggleClass('km-opcionactivo');
            jQuery(this).children("input:checkbox").prop("checked", !jQuery(this).children("input").prop("checked"));
        });

    });
})(jQuery);


var fecha = new Date();
jQuery(document).ready(function(){

    jQuery.post(
        HOME+"/procesos/busqueda/ubicacion.php",
        {},
        function(data){
            jQuery("#ubicacion_list").html(data);
            jQuery("#ubicacion_list div").on("click", function(e){
                jQuery("#ubicacion_txt").val( jQuery(this).html() );
                jQuery("#ubicacion").val( jQuery(this).attr("value") );
                jQuery("#ubicacion").attr( "data-value", jQuery(this).attr("data-value") );

                jQuery("#ubicacion_list").css("display", "none");
            });
            jQuery("#ubicacion_txt").attr("readonly", false);
        }
    );

    function getCleanedString(cadena){
        cadena = cadena.toLowerCase();
        cadena = cadena.replace(/ /g," ");
        cadena = cadena.replace(/á/gi,"a");
        cadena = cadena.replace(/é/gi,"e");
        cadena = cadena.replace(/í/gi,"i");
        cadena = cadena.replace(/ó/gi,"o");
        cadena = cadena.replace(/ú/gi,"u");
        cadena = cadena.replace(/ñ/gi,"n");
        return cadena;
    }

    jQuery("#ubicacion_txt").on("keyup", function ( e ) {       
        var buscar_1 = getCleanedString( String(jQuery("#ubicacion_txt").val()).toLowerCase() );

        jQuery("#ubicacion_list div").css("display", "none");
        jQuery("#ubicacion_list div").each(function( index ) {
            if( String(jQuery( this ).attr("data-value")).toLowerCase().search(buscar_1) != -1 ){
                jQuery( this ).css("display", "block");
                if( index == 0 ){
                    jQuery("#ubicacion").val( jQuery( this ).html() );
                    jQuery("#ubicacion").attr( "data-value", jQuery( this ).attr("data-value") );
                }
            }
        });
    });

    jQuery("#ubicacion_txt").on("focus", function ( e ) {       
        var buscar_1 = getCleanedString( String(jQuery("#ubicacion_txt").val()).toLowerCase() );

        jQuery("#ubicacion_list").css("display", "block");
        jQuery("#ubicacion_list div").css("display", "none");
        jQuery("#ubicacion_list div").each(function( index ) {
            if( String(jQuery( this ).attr("data-value")).toLowerCase().search(buscar_1) != -1 ){
                jQuery( this ).css("display", "block");
            }
        });
    });

    jQuery("#ubicacion_txt").on("change", function ( e ) {      
        var txt = getCleanedString( String(jQuery("#ubicacion_txt").val()).toLowerCase() );
        if( txt == "" ){
            jQuery("#ubicacion").val( "" );
            jQuery("#ubicacion").attr( "data-value", "" );
        }
    });


    jQuery('.bxslider').bxSlider({
        buildPager: function(slideIndex){
            switch(slideIndex){
                case 0:
                    return '<img src="'+HOME+'images/new/km-testimoniales/thumbs/testimonial-1.jpg">';
                case 1:
                    return '<img src="'+HOME+'images/new/km-testimoniales/thumbs/testimonial-2.jpg">';
                case 2:
                    return '<img src="'+HOME+'images/new/km-testimoniales/thumbs/testimonial-3.jpg">';
            }
        }
    });

    function initCheckin(date, actual){
        if(actual){
            jQuery('#checkout').datepick({
                dateFormat: 'dd/mm/yyyy',
                defaultDate: date,
                selectDefaultDate: true,
                minDate: date,
                onSelect: function(xdate) {
                    if(typeof calcular === 'function') {
                        calcular();
                    }
                },
                yearRange: date.getFullYear()+':'+(parseInt(date.getFullYear())+1),
                firstDay: 1,
                onmonthsToShow: [1, 1]
            });
        }else{
            jQuery('#checkout').datepick({
                dateFormat: 'dd/mm/yyyy',
                minDate: date,
                onSelect: function(xdate) {
                    if(typeof calcular === 'function') {
                        calcular();
                    }
                },
                yearRange: date.getFullYear()+':'+(parseInt(date.getFullYear())+1),
                firstDay: 1,
                onmonthsToShow: [1, 1]
            });
        }
    }

    jQuery('#checkin').datepick({
        dateFormat: 'dd/mm/yyyy',
        minDate: fecha,
        onSelect: function(date1) {
            var ini = jQuery('#checkin').datepick( "getDate" );
            var fin = jQuery('#checkout').datepick( "getDate" );
            if( fin.length > 0 ){
                var xini = ini[0].getTime();
                var xfin = fin[0].getTime();
                if( xini > xfin ){
                    jQuery('#checkout').datepick('destroy');
                    initCheckin(date1[0], true);
                }else{
                    jQuery('#checkout').datepick('destroy');
                    initCheckin(date1[0], false);
                }
            }else{
                jQuery('#checkout').datepick('destroy');
                initCheckin(date1[0], true);
            }
            if(typeof calcular === 'function') {
                calcular();
            }
            if(typeof validar_busqueda_home === 'function') {
                validar_busqueda_home();
            }
        },
        yearRange: fecha.getFullYear()+':'+(parseInt(fecha.getFullYear())+1),
        firstDay: 1,
        onmonthsToShow: [1, 1]
    });

    jQuery('#checkout').datepick({
        dateFormat: 'dd/mm/yyyy',
        minDate: fecha,
        onSelect: function(xdate) {
            if(typeof calcular === 'function') {
                calcular();
            }
        },
        yearRange: fecha.getFullYear()+':'+(parseInt(fecha.getFullYear())+1),
        firstDay: 1,
        onmonthsToShow: [1, 1]
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


function coordenadas(position){
    if(position.coords.latitude != '' && position.coords.longitude != '') {
        document.getElementById('latitud').value=position.coords.latitude;
        document.getElementById('longitud').value=position.coords.longitude;        
    } else {
        var mensaje = 'No es posible leer su ubicación,\nverifique si su GPS está encendido\ny vuelva a recargar la página.'+jQuery('#latitud').val()+','+jQuery('#longitud').val();
        alert(mensaje);        
    }
}

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

    jQuery( '#checkin' ).parent().removeClass('has-error');
    jQuery( '[data-error="checkin"]' ).addClass('hidden');
    jQuery( '#checkout' ).parent().removeClass('has-error');
    jQuery( '[data-error="checkout"]' ).addClass('hidden');

    if( IN ){
        jQuery( '#checkin' ).parent().addClass('has-error');
        jQuery( '[data-error="checkin"]' ).removeClass('hidden');
    }
    if( OUT ){
        jQuery( '#checkout' ).parent().addClass('has-error');
        jQuery( '[data-error="checkin"]' ).removeClass('hidden');
    }

    if( !IN && !OUT ){
        return true;
    }
    return false;
}

function playVideo(e) {
    var el = jQuery(e);
    var p = el.parent().parent().parent();
    jQuery('video', p).get(0).play();
    jQuery('.km-testimonial-text').css('display','none');
    jQuery('.img-testimoniales').css('display','none');
    jQuery('video').css('display','block');
}
function stopVideo(){
    jQuery.each( jQuery('video'), function(i, e){
        e.pause();
        e.currentTime = 0;
    });
    jQuery('.km-testimonial-text').css('display','block');
    jQuery('.img-testimoniales').css('display','block');
    jQuery('video').css('display','none');  
}

jQuery(document).on('click', '.control-video', function(e){
    stopVideo();
});
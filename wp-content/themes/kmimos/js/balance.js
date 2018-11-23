var timer;
var table;
var tr = document.getElementById('tiempo_restante');

jQuery(document).ready(function(){
    // habilitar boton de retiro
    contador();

    jQuery('[data-action="popover"]').on('click', function(e){
        var content_old = jQuery( '#help' ).html();
        var content = jQuery(this).attr('data-content');
        if( content != content_old ){       
            jQuery( '#help' ).css( 'display', 'block' );
            jQuery( '#help' ).html( content );
        }else{
            jQuery( '#help' ).css( 'display', 'none' );
            jQuery( '#help' ).html( '' );
        }
    });

    jQuery(".ver_reserva_init").on("click", function(e){
        jQuery(this).parent().parent().parent().addClass("vlz_desplegado");
    });

    jQuery(".ver_reserva_init_fuera").on("click", function(e){
	    jQuery(this).parent().parent().find('.vlz_tabla_inferior').removeClass("inactive_control");
	    jQuery(this).parent().find('.ver_reserva_init_closet').removeClass("inactive_control");

	    jQuery(this).addClass("inactive_control");
    });

    jQuery(".ver_reserva_init_closet").on("click", function(e){
	    jQuery(this).parent().parent().find('.vlz_tabla_inferior').addClass("inactive_control");
	    jQuery(this).parent().find('.ver_reserva_init_fuera').removeClass("inactive_control");

	    jQuery(this).addClass("inactive_control");
    });

    jQuery('[name="periodo"]').on('change', function(e){
        
        jQuery('#semanal').css('display', 'none');
        jQuery('#primera_quincena').css('display', 'none');
        jQuery('#segunda_quincena').css('display', 'none');

        switch( jQuery(this).val() ) {
            case 'semanal':
                jQuery('#semanal').css('display', 'inline-block');
                break;
            case 'quincenal':
                jQuery('#primera_quincena').css('display', 'inline-block');
                jQuery('#segunda_quincena').css('display', 'inline-block');

                jQuery('#lbl-p-quincena').html('1er. pago');
                jQuery('#lbl-s-quincena').html('2do. pago');
                break;
            case 'mensual':
                jQuery('#primera_quincena').css('display', 'inline-block');
                jQuery('#lbl-p-quincena').html('D&iacute;a de pago');
                break;
        }
        update_periodo();
    });

    jQuery('[name="periodo_dia"]').on('change', function(e){
        update_periodo();
    });
    jQuery('[name="primera_quincena"]').on('change', function(e){
        update_periodo();
    });
    jQuery('[name="segunda_quincena"]').on('change', function(e){
        update_periodo();
    });

    jQuery('#search-transacciones').on('click', function(e){
        e.preventDefault();
        loadTabla();
    });

    loadTabla();


    jQuery('[data-target="modal-retiros"]').on('click', function(e){
        retiro_total = 0;
        jQuery('#retiros').modal('show');
    });

    jQuery('[name="monto"]').on('change', function(e){
        var monto = jQuery(this).val();
        var total = jQuery(this).attr('data-value');

        if( monto > total ){
            console.log( monto +' > '+ total );
            jQuery(this).val( total );
            monto = total;
            alert( "El monto no debe ser mayor al saldo disponible" );
        }

        if( monto < 20 ){
            jQuery(this).val( 20 );
            monto = 20;
            alert( "El monto no debe ser menor a $20" );
        }

        jQuery('#modal-subtotal').html( monto );
        jQuery('#modal-total').html( monto - 10 );

    });

    jQuery('#retirar').on('click', function(e){
        var obj = jQuery(this);
        if( !obj.hasClass('disabled') ){        
            obj.addClass('disabled');
            jQuery.post(
                HOME+'admin/frontend/balance/ajax/retirar.php',
                {'monto': jQuery('[name="monto"]').val(), 'ID': user_id, 'descripcion': jQuery('[name="descripcion"]').val()},
                function(d){
                    console.log(d);
                    location.reload();
                }
            );
        }else{
            alert('Ya se envio la solicitud de retiro, por favor espere.');
        }
    });

});

function update_periodo(){        
    jQuery.post(
        HOME+'admin/frontend/balance/ajax/update_periodo.php',
        {
            'periodo': jQuery('[name="periodo"]').val(), 
            'dia': jQuery('[name="periodo_dia"]').val(), 
            'primera_quincena': jQuery('[name="primera_quincena"]').val(), 
            'segunda_quincena': jQuery('[name="segunda_quincena"]').val(), 
            'ID': user_id
        },
        function(d){
            jQuery('#fecha_pago').html(d);
            console.log(d);
            alert( "Datos actualizados" );
    });
}

function loadTabla(){
     
    table = jQuery('#example').DataTable();
    table.destroy();

    table = jQuery('#example').DataTable({
        "language": {
            "emptyTable":           "No hay datos disponibles en la tabla.",
            "info":                 "Del _START_ al _END_ de _TOTAL_ ",
            "infoEmpty":            "Mostrando 0 registros de un total de 0.",
            "infoFiltered":         "(filtrados de un total de _MAX_ registros)",
            "infoPostFix":          " (actualizados)",
            "lengthMenu":           "Mostrar _MENU_ registros",
            "loadingRecords":       "Cargando...",
            "processing":           "Procesando...",
            "search":               "Buscar:",
            "searchPlaceholder":    "Dato para buscar",
            "zeroRecords":          "No se han encontrado coincidencias.",
            "paginate": {
                "first":            "Primera",
                "last":             "Última",
                "next":             "Siguiente",
                "previous":         "Anterior"
            },
            "aria": {
                "sortAscending":    "Ordenación ascendente",
                "sortDescending":   "Ordenación descendente"
            }
        },
        "dom": '<B><f><t><"col-sm-6 text-left"i><"col-sm-6"p>',
        "buttons": [
            {
              extend: "csv",
              className: "btn-sm"
            },
            {
              extend: "excelHtml5",
              className: "btn-sm"
            },
        ],       
        "scrollX": true,
        "ajax": {
            "url": HOME+'admin/frontend/balance/ajax/transacciones.php',
            "data": { 'ID': user_id, 'desde': jQuery('[name="ini"]').val(), "hasta":jQuery('[name="fin"]').val() },
            "type": "POST"
        }
    });

}

function contador(){
    jQuery("#hour").text( tiempo.hora + ' h ' );
    jQuery("#minute").text( tiempo.minuto + ' min ');
    jQuery("#second").text( tiempo.segundo + ' s ');

    tiempo_corriendo = setInterval(function(){
        // Segundos
        tiempo.segundo--;
        if(tiempo.segundo <= 0)
        {
            tiempo.segundo = 59;
            tiempo.minuto--;
        }      

        // Minutos
        if(tiempo.minuto <= 0)
        {
            tiempo.minuto = 59;
            tiempo.hora--;
        }

        jQuery("#hour").text( tiempo.hora + ' h ' );
        jQuery("#minute").text( tiempo.minuto + ' min ');
        jQuery("#second").text( tiempo.segundo + ' s ');

        if( tiempo.hora <= 0 && tiempo.minuto <= 0 && tiempo.segunso <= 0 ){
            clearInterval(tiempo_corriendo);
        }

    }, 1000);
}

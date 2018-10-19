var table = ""; var CTX = "";
var fechas = '';

// Variables por defecto de busqueda
var _hiddenDefault = { "nuevo":[1,2,9,11], 'generados': [0] };
var _tipo = 'nuevo';
var _hiddenColumns = _hiddenDefault.nuevo;

jQuery(document).ready(function() {

	loadTabla( _tipo, _hiddenColumns );

    jQuery("#close_modal").on("click", function(e){
        cerrar(e);
    });
 
    jQuery("#form-search").on("submit", function(e){
		e.preventDefault();
    });

    jQuery("#btn-search").on("click", function(e){
		loadTabla( _tipo, _hiddenColumns );
	});

    jQuery(document).on('click', "[data-modal='comentarios']", function(){
		abrir_link( jQuery(this) );
    });

    jQuery('[data-action="popover"] li').on('click', function(e){
    	var content_old = jQuery( '#popover-content > span' ).html();
    	var content = jQuery(this).attr('data-content');
    	jQuery('[data-action="popover"] li a div').css('background-color', '#ccc'); 	    	
    	if( content != content_old ){		
	    	jQuery( '#popover-content' ).css( 'visibility', 'visible' );
	    	jQuery( '#popover-content > span' ).html( content );
	    	jQuery(this).children('a').children('div').css('background-color', '#22712c'); 	    	
    	}else{
	    	jQuery( '#popover-content' ).css( 'visibility', 'hidden' );
	    	jQuery( '#popover-content > span' ).html( '' );
    	}
    });

    jQuery(document).on('click', "[data-action='error']", function(e){
    	e.preventDefault();
    	alert( jQuery(this).attr('title') );
    	return false;
    });

    jQuery("#select-all").on("click", function(e){
    	if( jQuery("input[data-type='item_selected']:checked").size() > 0 ){
			jQuery("input[data-type='item_selected']").prop('checked', '' );
    	}else{
			jQuery("input[data-type='item_selected']").prop('checked', 'checked' );
    	}
		updateTotalTag();
    });

    jQuery(document).on('click', '[data-type="item_selected"]' ,function(e){
    	updateTotalTag();
    });

	jQuery('.nav-link').on('click', function (e) {
		e.preventDefault();
		jQuery('.nav-link').removeClass('active');

	  	_tipo = jQuery(this).attr('href'); 

		var id = jQuery(this).attr('id');
		jQuery('#'+id).addClass('active');
		jQuery('#'+id+'-tab').addClass('active');

		_hiddenColumns = _hiddenDefault.nuevo;
		jQuery('[name="ini"]').val(fecha.ini);
		jQuery('[name="fin"]').val(fecha.fin);
		jQuery('#opciones-nuevo').css('display', 'block');

		if( jQuery('#'+id).attr('href') == 'generados' || jQuery('#'+id).attr('href') == 'completado'){
			jQuery('#opciones-nuevo').css('display', 'none');
			jQuery('[name="ini"]').val("YYYY-MM-DD");
			jQuery('[name="fin"]').val("YYYY-MM-DD");

			jQuery('[name="fin"]').removeAttr('min');
			jQuery('[name="fin"]').removeAttr('max');
			_hiddenColumns = _hiddenDefault.generados;
		}
  		 
	  	loadTabla( _tipo, _hiddenColumns );
		updateTotalTag();
	});

	jQuery('#quitar-filtro').on('click', function(){
		jQuery('[name="ini"]').val("YYYY-MM-DD");
		jQuery('[name="fin"]').val("YYYY-MM-DD");
		loadTabla( _tipo, _hiddenColumns );
	});
 
    jQuery(document).on('click', '[data-action="procesar"]', function(e){
    	e.preventDefault();
    	if( jQuery(this).attr('data-target') == 'autorizado' ){		
	    	var users = [];
			jQuery.each(jQuery("input:checked"), function(){
				var user = jQuery(this).val();
				var token = jQuery(this).attr('data-token');	
				users.push({'token':token, 'user_id':user});
			});
			generar_solicitud( users, jQuery(this).attr('data-target') );
    	}else{
    		cerrar();
    	}
	});
	  
    jQuery("[data-modal='autorizar']").on('click', function(){
    	if( jQuery("input:checked").size() > 0 ){
    		abrir_link( jQuery(this) );
    	}else{
    		alert('Debe seleccionar los registros a procesar');
    	}
    });  

	jQuery('[name="ini"]').on('change', function(){
		console.log(_tipo);

		jQuery('[name="fin"]').removeAttr('min');
		jQuery('[name="fin"]').removeAttr('max');
		if( _tipo == 'nuevo' ){		
			var d = new Date( jQuery(this).val() );
			var limitDate = sumarDias(d, 30);

			jQuery('[name="fin"]').val( limitDate );
			jQuery('[name="fin"]').attr('min', jQuery(this).val() );
			jQuery('[name="fin"]').attr('max', limitDate );
		}
	});

});

function sumarDias(fecha, dias){
	fecha.setDate(fecha.getDate() + dias);
	var d = '0'+fecha.getDate();
		d = d.substring(d.length-2, d.length);
	var m = fecha.getMonth();
		m += 1;
		m = '0'+m;
		m = m.substring(m.length-2, m.length);
	var y = fecha.getFullYear();
 
	return y+'-'+m+'-'+d;
}

function updateTotalTag(){
	var total = 0;
    jQuery.each( jQuery("input[data-type='item_selected']:checked"), function(i,v){
    	total += parseFloat(jQuery(this).attr('data-total'));
    });
	jQuery('#pagosNuevos-tab span').html('$ '+total);
}

function total_pagos_generados(){
	jQuery.post(
		TEMA+'/admin/backend/pagos/ajax/total_pagos_procesados.php',
		{ "tipo": _tipo, 'desde': jQuery('[name="ini"]').val(), "hasta":jQuery('[name="fin"]').val() },
		function(data){
			data = JSON.parse(data);
			jQuery('#pagosGenerados-tab span').html('$ '+data['total']);
		}
	);
}

function generar_solicitud( users, accion ){
	jQuery.post(
		TEMA+'/admin/backend/pagos/ajax/generar_solicitud.php',
		{
			'ID':ID, 
			'users':users,
			'accion':accion, 
			'comentario': jQuery('[name="observaciones"]').val()
		},
		function(data){
			loadTabla( _tipo, _hiddenColumns );	
			cerrar();
		}
	);
}

function loadTabla( tipo, hiddenColumns ){
 	 
	table = jQuery('#example').DataTable();
	table.destroy();

    table = jQuery('#example').DataTable({
    	"language": {
			"emptyTable":			"No hay datos disponibles en la tabla.",
			"info":		   			"Del _START_ al _END_ de _TOTAL_ ",
			"infoEmpty":			"Mostrando 0 registros de un total de 0.",
			"infoFiltered":			"(filtrados de un total de _MAX_ registros)",
			"infoPostFix":			" (actualizados)",
			"lengthMenu":			"Mostrar _MENU_ registros",
			"loadingRecords":		"Cargando...",
			"processing":			"Procesando...",
			"search":				"Buscar:",
			"searchPlaceholder":	"Dato para buscar",
			"zeroRecords":			"No se han encontrado coincidencias.",
			"paginate": {
				"first":			"Primera",
				"last":				"Última",
				"next":				"Siguiente",
				"previous":			"Anterior"
			},
			"aria": {
				"sortAscending":	"Ordenación ascendente",
				"sortDescending":	"Ordenación descendente"
			}
		},
		"dom": '<B><f><t>ip',
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
		columnDefs: [
            {
            	"targets": hiddenColumns,
                "visible": false
            }
        ],
        "scrollX": true,
        "ajax": {
            "url": TEMA+'/admin/backend/pagos/ajax/pagos.php',
            "data": { "tipo": _tipo, 'desde': jQuery('[name="ini"]').val(), "hasta":jQuery('[name="fin"]').val() },
            "type": "POST"
        }
	});

	total_pagos_generados();
}

function abrir_link(e){
	init_modal({
		"titulo": e.attr("data-titulo"),
		"modulo": "pagos",
		"modal": e.attr("data-modal"),
		"info": {
			"ID": e.attr("data-id")
		}
	});
}

 








 
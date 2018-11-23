var table = ""; var CTX = "";
var fechas = '';
var tipo_servicio = '';
// Variables por defecto de busqueda
var _hiddenDefault = {};
var _tipo = 'cuidador';
var _hiddenColumns = [];

jQuery(document).ready(function() {

	loadTabla( _tipo, _hiddenColumns );

    jQuery("#close_modal").on("click", function(e){
        cerrar(e);
    });

	jQuery("[data-modal='reserva']").on('click', function(e){
		abrir_link( jQuery(this) );
	});
 
    jQuery("#form-search").on("submit", function(e){
		e.preventDefault();
    });

    jQuery("#btn-search").on("click", function(e){
		loadTabla( _tipo, _hiddenColumns );
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

	jQuery('.nav-link').on('click', function (e) {
		e.preventDefault();
		jQuery('.nav-link').removeClass('active');

	  	_tipo = jQuery(this).attr('href'); 

		var id = jQuery(this).attr('id');
		jQuery('#'+id).addClass('active');
		jQuery('#'+id+'-tab').addClass('active');

		_hiddenColumns = {};
		jQuery('[name="ini"]').val(fecha.ini);
		jQuery('[name="fin"]').val(fecha.fin);
  		 
	  	loadTabla( _tipo, _hiddenColumns );
	});

	jQuery('[name="ini"]').on('change', function(){
		var d = new Date( jQuery(this).val() );
		var limitDate = sumarDias(d, 30);

		jQuery('[name="fin"]').val( limitDate );
		jQuery('[name="fin"]').attr('min', jQuery(this).val() );
		jQuery('[name="fin"]').attr('max', limitDate );
	});

	jQuery(document).on('click', '.mas_info', function(){
		jQuery("#mas_info").toggle();
	});

	jQuery(document).on('change', 'input[name="s_principal[]"]', function(e){
		var id = jQuery(this).attr('data-group');
		jQuery('[data-target="'+id+'"]').css('display', 'none');
		if( jQuery(this).is(":checked") ){
			jQuery('[data-target="'+id+'"]').css('display', 'block');
		}
	});

	jQuery(document).on('change', '[data-name="hasta"]', function(e){
		actualizar( jQuery(this).attr('data-code') );
	});
	jQuery(document).on('change', '[data-name="cant_mascotas"]', function(e){
		actualizar( jQuery(this).attr('data-code') );
	});

	jQuery(document).on('change', '[name="reserva"]', function(e){
		if( jQuery(this).val() != '' ){
			jQuery('#show_notas_creditos').attr('data-titulo', 'Crear Nota de Cr&eacute;dito  - Reserva #' + jQuery(this).val());	
			jQuery('#show_notas_creditos').attr('data-id', jQuery(this).val());	
		}else{
			alert( "Debe agregar el numero de reserva" );
		}
	});

	jQuery(document).on('change', '[name="tipo_usuario"]', function(e){
		jQuery(".servicios").css('display', 'none');
		if(jQuery(this).val() == 'cliente' || jQuery(this).val() == 'cuidador'){
			jQuery(".servicios").css('display', 'block');
		}
	});	

	jQuery(document).on('click', "#nc_save", function(e){
		jQuery.post(
			TEMA+'/admin/backend/notas_creditos/ajax/generar.php',
			jQuery('[name="form-nc"]').serialize(),
			function(data){
				loadTabla( _tipo, _hiddenColumns );	
				cerrar();
			}
		);
	});


	// actualizar servicios adicionales
	jQuery(document).on('change', '[name="servicios[]"]', function(e){
		actualizar_monto_adicional( jQuery(this).attr('data-group') );
	 	calcular_total();
	});
	jQuery(document).on('change', '[name="transporte[]"]', function(e){
	 	calcular_total();
	});
	// jQuery(document).on('çhange', '.update', function(e){
	// 	actualizar_monto_adicional( jQuery(this).attr('data-group') );
	// 	calcular_total();
	// });

	jQuery(document).on('change', '[data-action="adic_update"]', function(e){
		actualizar_monto_adicional( jQuery(this).attr('data-group') );
	});



});

function actualizar_monto_adicional( ID ){
	var check = jQuery('[data-check="'+ID+'"');
	var selec = jQuery('[data-select="'+ID+'"');
	var total = parseFloat( check.attr('data-costo') ) * parseFloat( selec.val() );

	console.log('pago:'+total);

	jQuery('[data-monto="'+ID+'"]').html( total );
	check.attr("data-costo", total);
}

function actualizar( code ){

	// var code = jQuery('[name="hasta_'+code+'"]').attr('data-code');
	// monto por noche
	var monto= jQuery('[name="hasta_'+code+'"]').attr('data-monto');		
	// Fecha final de reserva
	var max = jQuery('[name="hasta_'+code+'"]').attr('max');
	// Nueva fecha hasta reserva
	var hasta = jQuery('[name="hasta_'+code+'"]').val();

	var cant_masc = jQuery('[name="mascotas_'+code+'"]').val();
	// diferencia de noches/dias restantes
	var noches = num_noches( hasta, max );			

	console.log(tipo_servicio);		
	if( tipo_servicio.trim() != 'hospedaje' ){
		noches += 1;
	}

	var total = 0;
	if( cant_masc > 0 ){
		total = (cant_masc) * (monto) * (noches);
	}
	// diferencia en monto 
	jQuery('[name="noches_'+code+'"]').val( noches );
	jQuery('[name="prorrateo_'+code+'"]').val( total );

	calcular_total();
}

function num_noches( ini, fin ){

	console.log( 'ini: '+ini+' - fin: '+fin );

	var date_1 = new Date(ini);
	var date_2 = new Date(fin);

	var day_as_milliseconds = 86400000;
	var diff_in_millisenconds = date_2 - date_1;
	var diff_in_days = diff_in_millisenconds / day_as_milliseconds;

	return diff_in_days;
}

function calcular_total(){	
		var total = 0;

		// servicio principal
		jQuery.each( jQuery('[name="s_principal[]"]:checked'), function(i){
			total += parseFloat( jQuery('[name="'+jQuery(this).attr('data-group')+'"]').val() );
		});

		// servicios adicionales
		jQuery.each( jQuery('[name="servicios[]"]:checked'), function(i){
			total += parseFloat( jQuery(this).attr('data-monto') );
		});

		// servicios transporte
		jQuery.each( jQuery('[name="transporte[]"]:checked'), function(i){
			total += parseFloat( jQuery(this).attr('data-monto') );
		});

		jQuery('[data-target="total"]').html('$ '+total);
}

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
        "scrollX": true,
        "ajax": {
            "url": TEMA+'/admin/backend/notas_creditos/ajax/notas_creditos.php',
            "data": { "tipo": _tipo, 'desde': jQuery('[name="ini"]').val(), "hasta":jQuery('[name="fin"]').val() },
            "type": "POST"
        }
	});
}

function abrir_link(e){
	init_modal({
		"titulo": e.attr("data-titulo"),
		"modulo": "notas_creditos",
		"modal": e.attr("data-modal"),
		"info": {
			"ID": e.attr("data-id")
		}
	});
}

 








 
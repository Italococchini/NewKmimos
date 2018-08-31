var table = ""; var CTX = "";
var fechas = '';

// Variables por defecto de busqueda
var _tipo = 'cliente';
var _rfc = '0';
var _hiddenColumns = [];

jQuery(document).ready(function() {

	loadTabla(_tipo, _rfc);

    jQuery("#close_modal").on("click", function(e){
        cerrar(e);
    });
    
    jQuery("#download-all").on("click", function(e){

		var _this = jQuery(this);
    	if( !jQuery(this).hasClass('disabled') ){
	    	_this.attr('disabled', 'disabled');
	    	_this.addClass('disabled');
	    	jQuery.post(
	            TEMA+'/admin/backend/facturas/ajax/download_zip.php', 
	            {
	                'fact_selected': 'all'
	            },
	            function(data){
			    	_this.removeAttr('disabled');
			    	_this.removeClass('disabled');
	                data = JSON.parse(data);
	                if( data['estatus'] == "listo" ){
		                location.href = data['url'];
	                }
	            }
	        );
    	}
    	
    });
	jQuery("#download-pdf").on("click", function(e){
		var _this = jQuery(this);
    	if( !jQuery(this).hasClass('disabled') ){
	    	_this.attr('disabled', 'disabled');
	    	_this.addClass('disabled');
	    	jQuery.post(
	            TEMA+'/admin/backend/facturas/ajax/download_zip.php', 
	            {
	                'fact_selected': 'pdf'
	            },
	            function(data){
			    	_this.removeAttr('disabled');
			    	_this.removeClass('disabled');
	                data = JSON.parse(data);
	                if( data['estatus'] == "listo" ){
		                location.href = data['url'];
	                }
	            }
	        );
    	}

    });

    jQuery("#download-zip").on("click", function(e){
    	var list = [];
    	if( jQuery("input[data-type='fact_selected']:checked").size() == 0 ){		
	    	jQuery("[data-type='fact_selected']").each(function(e){
		    	list.push( jQuery(this).val() );
	    	});
    	}else{
	    	jQuery("input[data-type='fact_selected']:checked").each(function(e){
		    	list.push( jQuery(this).val() );
	    	});    		
    	}

    	if( list.length > 0 ){
    		jQuery.post(
                TEMA+'/admin/backend/facturas/ajax/download_zip.php', 
                {
                    'fact_selected': list
                },
                function(data){
                    data = JSON.parse(data);
                    if( data['estatus'] == "listo" ){
		                location.href = data['url'];
                    }
                }
            );
    	}
    });

    jQuery("#form-search").on("submit", function(e){
		e.preventDefault();
    });

    jQuery("#btn-search").on("click", function(e){
    	table.destroy();
		loadTabla(_tipo, _rfc, _hiddenColumns);
	});

    jQuery("#select-all").on("click", function(e){
    	if( jQuery("input[data-type='fact_selected']:checked").size() > 0 ){
			$("input[data-type='fact_selected']").prop('checked', '' );
    	}else{
			$("input[data-type='fact_selected']").prop('checked', 'checked' );
    	}
    });

	jQuery('.nav-link').on('click', function (e) {
		e.preventDefault();
		jQuery('.nav-link').removeClass('active');

		_hiddenColumns=[];
	  	_tipo = jQuery(this).attr('href');
	  	_rfc = jQuery('#tipo_receptor').val();

		var id = jQuery(this).attr('id');
		jQuery('#'+id).addClass('active');
		jQuery('#'+id+'-tab').addClass('active');

  		jQuery('#container_tipo_receptor').addClass('hidden');
	  	if( jQuery(this).attr('href') == 'cliente' ){
	  		jQuery('#container_tipo_receptor').removeClass('hidden');
	  	}else{
	  		_hiddenColumns = [5];
	  	}
	  	loadTabla(_tipo, _rfc, _hiddenColumns);
	});

	jQuery('#quitar-filtro').on('click', function(){
		jQuery('[name="ini"]').val("YYYY-MM-DD");
		jQuery('[name="fin"]').val("YYYY-MM-DD");
		loadTabla( _tipo, _rfc, _hiddenColumns );
	});

	jQuery('#tipo_receptor').on('change', function(){	
		_tipo = 'cliente';
		_rfc = jQuery(this).val();	
		_hiddenColumns = [];
	  	loadTabla(_tipo, _rfc, _hiddenColumns );
	})

    jQuery(document).on('click','[data-pdfxml]', function(e){

        e.preventDefault();
		console.log("file");
        var file = [];
            file.push( jQuery(this).attr('data-PdfXml') );
        download( file );
    });


} );

function download( archivos ){
    jQuery.post(HOME+"procesos/generales/download_zip.php", {'fact_selected': archivos}, function(e){
        e = JSON.parse(e);
        if( e['estatus'] == "listo" ){
            location.href = e['url'];
        }
    });
}

function loadTabla( tipo, rfc , hiddenColumns){
console.log(_tipo+"--"+_rfc+"--"+_hiddenColumns);

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
		"columnDefs": [
            {
                "targets": hiddenColumns,
                "visible": false
            }
        ],
        "scrollX": true,
        "ajax": {
            "url": TEMA+'/admin/backend/facturas/ajax/facturas.php',
            "data": { "rfc": rfc, "tipo": tipo, 'ini': jQuery('[name="ini"]').val(), "fin":jQuery('[name="fin"]').val() },
            "type": "POST"
        }
	});
}

function abrir_link(e){
	init_modal({
		"titulo": e.attr("data-titulo"),
		"modulo": "facturas",
		"modal": e.attr("data-modal"),
		"info": {
			"ID": e.attr("data-id")
		}
	});
}

 








 
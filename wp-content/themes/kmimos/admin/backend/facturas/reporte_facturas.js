var table = ""; var CTX = "";
var fechas = '';
jQuery(document).ready(function() {

	loadTabla();

    jQuery("#close_modal").on("click", function(e){
        cerrar(e);
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
    	loadTabla();
	});

    jQuery("#select-all").on("click", function(e){
    	if( jQuery("input[data-type='fact_selected']:checked").size() > 0 ){
			$("input[data-type='fact_selected']").prop('checked', '' );
    	}else{
			$("input[data-type='fact_selected']").prop('checked', 'checked' );
    	}
    });

} );

function loadTabla(){
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
        "scrollX": true,
        "ajax": {
            "url": TEMA+'/admin/backend/facturas/ajax/facturas.php',
            "data": { ini: jQuery('[name="ini"]').val(), fin:jQuery('[name="fin"]').val() },
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

 








 
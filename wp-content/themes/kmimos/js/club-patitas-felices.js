var table;
jQuery(document).ready( function (){

	jQuery('#form-registro').on('submit', function(e){
		e.preventDefault();
		var btn = jQuery('#form-registro button[type="submit"]');
		if( !btn.hasClass('disabled') ){
			btn.addClass('disabled');
			jQuery.post(
		        HOME+'procesos/clubPatitasFelices/registro-usuario.php',
		        jQuery(this).serialize(),
		        function(d){
		        	console.log(d);
					btn.removeClass('disabled');
					location.href=RAIZ+'/club-patitas-felices/compartir';
		    });
		}
	});

});

function loadTabla(){
 	 
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
            "url": TEMA+'/procesos/clubPatitasFelices/ajax/creditos.php',
            "type": "POST"
        }
	});
}
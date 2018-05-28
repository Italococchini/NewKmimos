var table;
jQuery(document).ready(function(){

    jQuery('#guardar').on('click', function(){
        jQuery.post( 
            HOME+"monitor/reportes/marketing/ajax/nuevo.php", 
            jQuery('#frm_nuevo').serialize(), 
            function( data ) {
                data = jQuery.parseJSON(data);
                if(data['sts']==1){
                    jQuery('#nuevo').modal('hide');
                    
                    jQuery('[name="id"]').val( '' ); 
                    jQuery('[name="nombre"]').val( '' ); 
                    jQuery('[name="costo"]').val( '' ); 
                    jQuery('[name="fecha"]').val( '' );
                    jQuery('[name="tipo"]').val( '' );
                    jQuery('[name="canal"]').val( '' );
                    jQuery('[name="plataforma"]').val( '' );

                    table.ajax.reload();
                }
        });

    });


    jQuery(document).on('click', '[data-target="delete"]' , function(){
        
        if( confirm("¿Desea eliminar el registro?") ){
            jQuery.post( 
                HOME+"monitor/reportes/marketing/ajax/eliminar.php", 
                {id: jQuery(this).attr('data-id') }, 
                function( data ) {
                    data = jQuery.parseJSON(data);
                    if(data['sts']==1){
                        table.ajax.reload();
                    }
            });
        }

    });

    jQuery(document).on('click', '[data-target="update"]' , function(){
        
        jQuery.post( 
            HOME+"monitor/reportes/marketing/ajax/select.php", 
            {id: jQuery(this).attr('data-id')}, 
            function( data ) {
                data = jQuery.parseJSON(data);
                var valores = data['data']; 
                jQuery('#nuevo').modal('hide');
                if(data['sts']==1){
                    jQuery('[name="id"]').val( valores['id'] ); 
                    jQuery('[name="nombre"]').val( valores['nombre'] ); 
                    jQuery('[name="costo"]').val( valores['costo'] ); 
                    jQuery('[name="fecha"]').val( valores['fecha'] );
                    jQuery('[name="tipo"]').val( valores['tipo'] );
                    jQuery('[name="canal"]').val( valores['canal'] );
                    jQuery('[name="plataforma"]').val( valores['plataforma'] );
                    jQuery('#nuevo').modal('show');
                }
        });

    });

    cargar_tabla();
});

function cargar_tabla(){
	
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
        "ajax": {
            "url": HOME+'monitor/reportes/marketing/ajax/marketing.php',
            "type": "POST"
        },
	    "scrollX": true,
	    "scrollCollapse": true,
	    "autoWidth": true,
	    "paging": true,
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
	});
}



function cargar_grafico(){
	var chart = AmCharts.makeChart("chartdiv", {
        "type": "serial",
        "theme": "light",
        "legend": {
            "equalWidths": false,
            "useGraphSettings": true,
            "valueAlign": "left",
            "valueWidth": 120
        },

        "dataProvider": graficoData,
        "valueAxes": [
            {
                "id": "ventasAxis",
                "axisAlpha": 0,
                "gridAlpha": 0,
                "position": "left",
                "title": "Ventas"
            }, 
            {
                "id": "clientesAxis",
                "clientes": "",
                "clientesUnits": {
                  "hh": "",
                  "mm": ""
                },
                "axisAlpha": 0,
                "gridAlpha": 0,
                "inside": true,
                "position": "right",
                "title": "Clientes Nuevos"
            }
        ],
        "graphs": [
            {
                "alphaField": "alpha",
                "balloonText": "[[value]]",
                "dashLengthField": "dashLength",
                "fillAlphas": 0.7,
                "legendPeriodValueText": "total: [[value.sum]]",
                "legendValueText": "[[value]]",
                "title": "Ventas",
                "type": "column",
                "valueField": "eventos_de_compra",
                "valueAxis": "ventasAxis"
            }, 
            {
                "balloonText": "[[value]]",
                "bullet": "round",
                "bulletBorderAlpha": 1,
                "useLineColorForBulletBorder": true,
                "bulletColor": "#FFFFFF",
                "bulletSizeField": "townSize",
                "dashLengthField": "dashLength",
                "descriptionField": "townName",
                "labelPosition": "right",
                "labelText": "[[townName2]]",
                "legendPeriodValueText": "total: [[value.sum]]",
                "legendValueText": "[[value]]",
                "title": "Clientes Nuevos",
                "fillAlphas": 0,
                "valueField": "clientes_nuevos",
                "valueAxis": "clientesAxis"
            }
        ],

        "chartScrollbar": {
            "enabled": false
        },
        "chartCursor": {
            "categoryBalloonDateFormat": "MM",
            "cursorAlpha": 0.1,
            "cursorColor": "#000000",
            "fullWidth": true,
            "valueBalloonsEnabled": false,
            "zoomable": true
        },

        "dataDateFormat": "YYYY-MM",
        "categoryField": "date",
        "categoryAxis": {
            "dateFormats": [{
                  "period": "DD",
                  "format": "DD"
                },{
                  "period": "WW",
                  "format": "MMM"
                },{
                  "period": "MM",
                  "format": "MMM"
                },{
                  "period": "YYYY",
                  "format": "YYYY"
                }
            ],
            "parseDates": false,
            "autoGridCount": false,
            "axisColor": "#555555",
            "gridAlpha": 0.1,
            "gridColor": "#FFFFFF",
            "gridCount": 50
        },
        "export": {
            "enabled": true
        },
        "listeners": [{
            "event": "clickGraphItem",
            "method": function(e) {  
              // Find out X
              var x = Math.round( e.graph.categoryAxis.dateToCoordinate(e.item.category) );
              // Find out Y
              var y = Math.round( e.graph.valueAxis.getCoordinate(e.item.values.value) );              
              console.log("x: " + x, "y: " + y);
            }
        }]
    });
}
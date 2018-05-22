

function cargar_tabla(){
	jQuery('[data-header="in"]').html(header);

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
	    "data": data,
	    "scrollX": true,
	    "scrollCollapse": true,
	    "autoWidth": true,
	    "paging": false,
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
                "id": "cuidadoresAxis",
                "axisAlpha": 0,
                "gridAlpha": 0,
                "position": "left",
                "title": "Nuevos Cuidadores"
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
                "title": "Nuevos Cuidadores",
                "type": "column",
                "valueField": "total",
                "valueAxis": "cuidadoresAxis"
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
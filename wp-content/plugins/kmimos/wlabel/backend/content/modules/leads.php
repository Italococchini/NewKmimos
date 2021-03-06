<?php
	$kmimos_load=dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))).'/wp-load.php';
	if(file_exists($kmimos_load)){
	    include_once($kmimos_load);
	}
?>

<div class="module_title">
    Clientes
</div>

<div class="module_botones">
    <table>
        <tr>
            <td><strong>Desde:</strong></td>
            <td><strong>Hasta:</strong></td>
        </tr>
        <tr>
            <td><input type="date" id="desde" name="desde" class="form-control form-control-sm" value="2018-09-01" /></td>
            <td><input type="date" id="hasta" name="hasta" class="form-control form-control-sm" value="<?= date("Y-m-d"); ?>" /></td>
        </tr>
    </table>
</div>

<table id="_example_" class="table table-striped table-bordered nowrap" style="width:100%" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha Registro</th>
            <th>Email</th>
            <th>Donde nos conocio?</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>


<script type="text/javascript">

    /* Tabla y Filtros de Fechas */

        var table = "";
        jQuery(document).ready(function() {
            table = jQuery('#_example_').DataTable({
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
                dom: '<"top"l>Bfrtip',
                buttons: [
                    'csv', 'excel'
                ],
                "scrollX": true,
                "ajax": {
                    "url": "<?= get_home_url(); ?>/wp-content/plugins/kmimos/wlabel/backend/content/ajax/leads_data.php",
                    "type": "POST",
                    "dataSrc":  function ( json ) {
                        if(typeof postCargaTable === 'function') {
                            json = postCargaTable(json, 1);
                        }
                        return json.data;
                    } 
                }
            });
        } );
        jQuery("#desde").on("change", function(e){ table.ajax.reload(); });
        jQuery("#hasta").on("change", function(e){ table.ajax.reload(); });

    /* Fin Tabla y Filtros de Fechas */

</script>
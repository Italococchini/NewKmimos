<pre>
<?php  
    include_once('lib/notas_creditos.php');
    $fecha = $NotasCredito->getRangoFechas();
    $user = wp_get_current_user();
?>
</pre>
<script>
    var ID = <?php echo $user->ID; ?>;
    var fecha = { 
        ini:'<?php echo $fecha['ini']; ?>', 
        fin:'<?php echo $fecha['fin']; ?>'
    };
</script>
<link rel='stylesheet' type='text/css' href='<?php echo getTema() ?>/admin/backend/notas_creditos/reporte.css'>
<script src='<?php echo getTema(); ?>/admin/backend/notas_creditos/reporte.js'></script>

<div class="container_listados">

    <div class='titulos'>
        <h2>Control de Notas de Cr&eacute;ditos</h2>
        <hr>
    </div>

    <div class='col-md-12'>
        <div class='row'>
            <div class="col-sm-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-addon" id="basic-addon1"># Reserva: </span>
                    <input name="reserva" type="text" class="form-control" placeholder="000000" aria-describedby="basic-addon1" required>
                    <span class="input-group-btn">
                        <button id="show_notas_creditos" class="btn btn-success" data-titulo='Datos de la Reserva' data-modal='reserva' data-id="0" > Crear Nota de Cr&eacute;dito</button>
                    </span>
                </div>
            </div>
            <div class="col-sm-12 col-md-7 container-search text-right">
                <form id="form-search" name="search">
                    <span><label class="fecha">Desde: </label><input type="date" name="ini" value="<?php echo $fecha['ini']; ?>"></span>
                    <span><label class="fecha">Hasta: </label><input type="date" name="fin" 
                        min="<?php echo $fecha['ini']; ?>" 
                        max="<?php echo $fecha['fin']; ?>" 
                        value="<?php echo $fecha['fin']; ?>"></span> 
                    <button class="btn btn-defaut" id="btn-search"><i class="fa fa-search"></i> Buscar</button>
                </form>
            </div>
        </div>
        <hr>
    </div>
    <div class="clear"></div>

    <div class='col-md-12'>

        <dir class="leyenda text-right">
            <div class="col-md-12">
                <ul data-action="popover" class="list-inline">
                    <li><strong>LEYENDA DE ESTATUS: </strong></li>
                    <li data-content="<strong>Pendiente: </strong> La nota de credito no posee factura asociada">
                        <a href="javascript:;">
                            <div></div> Pendiente
                        </a>
                    </li>
                    <li data-content="<strong>Procesada: </strong> La nota de credito posee una factura asociada">
                        <a href="javascript:;">
                            <div></div> Procesada
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <div id="popover-content" class="pull-right text-left alert alert-info">
                    <i style="margin-right:5px;padding:5px 15px 5px 0px;border-right: 1px solid #ccc;" class="fa fa-info-circle" aria-hidden="true"></i> 
                    <span></span>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="margin-left: 20px;">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="clear"></div>
            </div>
        </dir>

        <div>
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" role="tab" href="cuidador" aria-controls="cuidador" aria-selected="false">Cuidadores</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" role="tab" href="cliente" aria-controls="cliente" aria-selected="true">Clientes</span></a>
              </li>
            </ul>
            <table id="example" class="table table-striped table-bordered nowrap" cellspacing="0" style="min-width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th># Reserva</th>
                        <th>Total</th>
                        <th>Factura</th>
                        <th>Detalle</th>
                        <th>Estatus</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>


</div>

 <?php
    include_once(dirname(dirname(__DIR__)).'/recursos/modal.php');
?>
 
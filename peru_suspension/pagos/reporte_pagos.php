<pre>
<?php 
    include_once('lib/pagos.php'); 
    $fecha = $pagos->getRangoFechas();
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
<link rel='stylesheet' type='text/css' href='<?php echo getTema() ?>/admin/backend/pagos/reporte_pagos.css'>
<script src='<?php echo getTema(); ?>/admin/backend/pagos/reporte_pagos.js'></script>

<div class="container_listados">

    <div class='titulos'>
        <h2>Control de Pagos a cuidadores</h2>
        <hr>
    </div>

    <div class='col-md-12'>
        <div class='row'>
            <div class="col-sm-12 col-md-5">
                <button class="btn btn-defaut" id="select-all"><i class="fa fa-list"></i> Marcar/Desmarcar Todos</button>
                <button class="btn btn-defaut" id="quitar-filtro"><i class="fa fa-filter"></i> Mostrar todos</button>
            </div>
            <div class="col-sm-12 col-md-7 container-search text-right">
                <form id="form-search" name="search">
                    <span><label class="fecha">Desde: </label><input type="date" name="ini" value="<?php echo $fecha['ini']; ?>"></span>
                    <span><label class="fecha">Hasta: <input type="date" name="fin" 
                        min="<?php echo $fecha['min']; ?>" 
                        max="<?php echo $fecha['max']; ?>" 
                        value="<?php echo $fecha['fin']; ?>"></label></span> 
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
                    <li><strong>LEYENDA DET. RESERVAS: </strong></li>
                    <li data-content="<strong>Normal: </strong> No posee descuento">
                        <a href="javascript:;">
                            <aside style="background:#a6a5a5;"></aside> Normal
                        </a>
                    </li>
                    <li data-content="<strong>Cupón: </strong> Un Cupón fue aplicado">
                        <a href="javascript:;">
                            <aside style="background:#8d88e0;"></aside> Cupón 
                        </a>
                    </li>
                    <li data-content="<strong>Saldo a favor: </strong> El Saldo a favor fue aplicado.">
                        <a href="javascript:;">
                            <aside style="background:#88e093;"></aside> Saldo a favor
                        </a>
                    </li>
                    <li data-content="<strong>Ambos: </strong> Se aplico el Saldo a favor y un Cupón de Descuento">
                        <a href="javascript:;">
                            <aside style="background:#e0888c;"></aside> Ambos  
                        </a>
                    </li>
                </ul>
                
                <ul data-action="popover" class="list-inline">
                    <li><strong>LEYENDA DE ESTATUS: </strong></li>
                    <li data-content="<strong>Por autorizar: </strong> El supervisor debe autorizar la solicitud">
                        <a href="javascript:;">
                            <div></div> Por autorizar
                        </a>
                    </li>
                    <li data-content="<strong>Autorizado: </strong> La solicitud esta autorizada y el pago no fue procesado">
                        <a href="javascript:;">
                            <div></div> Autorizado
                        </a>
                    </li>
                    <!-- li data-content="<strong>Negado: </strong> La solicitud fue negada por los supervisores"><a href="javascript:;"><div></div> Negado</a></li -->
                    <li data-content="<strong>En progreso: </strong> La solicitud esta en proceso de pago por la entidad bancaria"><a href="javascript:;"><div></div> En progreso</a></li>
                    <li data-content="<strong>Cancelado: </strong> La solicitud de pago fue cancelada"><a href="javascript:;"><div></div> Cancelado</a></li>
                    <li data-content="<strong>Completado: </strong> La solicitud de pago fue procesada"><a href="javascript:;"><div></div> Completado</a></li>
                    <li data-content="<strong>Error: </strong> Ocurrio un error al procesar la solicitud de pago"><a href="javascript:;"><div></div> Error</a></li>
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
                <a class="nav-link active" id="pagosNuevos-tab" data-toggle="tab" href="nuevo" role="tab" aria-controls="pagosNuevos" aria-selected="true">Pendientes de pago <span class="badge-total">$ 0</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="pagosGenerados-tab" data-toggle="tab" href="generados" role="tab" aria-controls="pagosGenerados" aria-selected="false">Enviadas a pago <span class="badge-total">$ 0</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="pagosCompletado-tab" data-toggle="tab" href="completado" role="tab" aria-controls="pagosCompletado" aria-selected="false">Pago completado </a>
              </li>
            </ul>

            <div class="botones_container" id="opciones-nuevo">
                <button class="btn btn-success" data-titulo='Procesar solicitudes de pago' data-modal='autorizar' data-id="0" ><i class="fa fa-money"></i> Generar Solicitud de pago</button>
            </div>
            
            <table id="example" class="table table-striped table-bordered nowrap" cellspacing="0" style="min-width: 100%;">
                <thead>
                    <tr>
                        <th></th>
                        <th>Fecha</th>
                        <th>Estatus</th>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Total a pagar</th>
                        <th>Cant. Reservas</th>
                        <th>Det. Reservas</th>
                        <th>Autorizado por.</th>
                        <th>Opciones</th>
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
 
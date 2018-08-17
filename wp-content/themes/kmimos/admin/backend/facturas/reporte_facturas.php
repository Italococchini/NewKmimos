<link rel='stylesheet' type='text/css' href='<?php echo getTema() ?>/admin/backend/facturas/reporte_facturas.css'>
<script src='<?php echo getTema(); ?>/admin/backend/facturas/reporte_facturas.js'></script>

<div class="container_listados">



    <div class='titulos'>
        <h2>Control de Facturas</h2>
        <hr>
    </div>

    <div class='col-md-12'>
        <div class='row'>
            <div class="col-sm-12 col-md-6">
                <button class="btn btn-defaut" id="select-all"><i class="fa fa-list"></i> Marcar/Desmarcar Todos</button>
                <button class="btn btn-defaut" id="download-zip"><i class="fa fa-cloud-download"></i> Zip</button>
            </div>
            <div class="col-sm-12 col-md-6 container-search">
                <form id="form-search" name="search">
                    <span><label class="fecha">Desde: </label><input type="date" name="ini" value=""></span>
                    <span><label class="fecha">Hasta: <input type="date" name="fin" value=""></label></span> 
                    <button class="btn btn-defaut" id="btn-search"><i class="fa fa-search"></i></button>
                </form>
            </div>
        </div>
        <hr>
    </div>
    <div class="clear"></div>

    <div class='col-md-12'>
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="FacturasCuidadores-tab" data-toggle="tab" href="cliente" role="tab" aria-controls="FacturasCuidadores" aria-selected="true">Facturas Cuidadores</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="FacturasKmimos-tab" data-toggle="tab" href="cuidador" role="tab" aria-controls="FacturasKmimos" aria-selected="false">Facturas Kmimos</a>
          </li>
          <li class="nav-item hidden">
            <a class="nav-link pull-right" id="FacturasError-tab" data-toggle="tab" href="error" role="tab" aria-controls="FacturasError" aria-selected="false">Facturas con Problemas</a>
          </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div id="container_tipo_receptor" style="padding: 20px 10px 0px 20px">
                <label>Tipo de Receptor: </label>
                <select id="tipo_receptor" style="border-radius:10px;">
                    <option value="0">Clientes</option>
                    <option value="XAXX010101000">Publico en General</option>
                </select>
            </div>
            <br />
            <table id="example" class="table table-striped table-bordered nowrap" cellspacing="0" style="min-width: 100%;" >
                <thead>
                    <tr>
                        <th></th>
                        <th>Dia</th>
                        <th>Mes</th>
                        <th>Año</th>
                        <th>Reserva</th>
                        <th>Servicio</th>
                        <th>Total</th>
                        <th>Serie y Folio</th>
                        <th>Cuidador</th>
                        <th>Cliente</th>
                        <th>No. Referencia</th>
                        <th>Receptor</th>
                        <th>Estado</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div> 
    </div>


</div>

 
<?php
    session_start();
    date_default_timezone_set('America/Mexico_City');

    include_once('../lib/notas_creditos.php');

    $data = array(
        "data" => array()
    );

    $actual = time();

    extract( $_POST );

    $sql = "SELECT * FROM notas_creditos WHERE tipo = '".strtolower($tipo)."' and fecha >= '{$desde} 00:00:00' and fecha <= '{$hasta} 23:59:59'";
    $notas_creditos = $NotasCredito->db->query( $sql );

    if( $notas_creditos != false ){
        $i = 0;
        foreach ($notas_creditos as $item) {
            $item = (object) $item;
            if( $item->tipo == 'cuidador' ){
                $cuidador = $NotasCredito->db->get_row("SELECT user_id, nombre, apellido, banco FROM cuidadores WHERE user_id = {$item->user_id}");
                $nombre = utf8_encode($cuidador->nombre);
                $apellido = utf8_encode($cuidador->apellido);
            }else{
                $_nombre = $NotasCredito->db->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id = {$item->user_id} and meta_key = 'first_name'");
                $_apellido = $NotasCredito->db->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id = {$item->user_id} and meta_key = 'last_name'");
                $nombre = utf8_encode($_nombre);
                $apellido = utf8_encode($_apellido);                
            }

            $detalle = '';
            $detalles = unserialize(utf8_encode($item->detalle));
            foreach ($detalles as $det) {
                $det['costo'] = '$ '.number_format($det['costo'], 2, '.', ',');
                $detalle .= "<div>". str_replace('<br>', ' ', $det['titulo'] ) . " ( ".$det['costo']. " ) </div>";
            }

            $data["data"][] = array(
                $item->id,
                date( 'Y-m-d', strtotime($item->fecha) ),
                $item->user_id,
                utf8_encode($nombre),
                utf8_encode($apellido),
                $item->reserva_id,
                number_format($item->monto, 2, ',', '.'),
                $item->factura,
                $detalle,
                $item->estatus,
                '<button style="padding:5px;" data-pdfxml="<?php echo $item->factura."_".$item->cfdi_referencia; ?>">
                    <i class="fa fa-cloud-download"></i> Descargar PDF y XML 
                </button>',
                utf8_encode($item->observaciones)
            );

        }
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
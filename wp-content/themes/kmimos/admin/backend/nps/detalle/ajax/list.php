<?php
    session_start();
    date_default_timezone_set('America/Mexico_City');


    $data = array(
        "data" => array()
    );

    $actual = time();

    extract( $_POST );

    include_once(dirname(__DIR__).'/lib/nps.php');
    $preguntas = $nps->get_preguntas( $desde, $hasta );

    $estatus = $nps->get_estatus(); 

    if( $preguntas != false ){
        $count=0;
        foreach ($preguntas as $pregunta) {
            $feedback = 0;
            $pto_nps = 0;
            $botones= '';

            $botones = '<button class="btn btn-sm btn-default" data-titulo="INTEGRACION" data-modal="generador_codigo" data-id="'.$pregunta->id.'">LINK / HTML</button>';

            $score = $nps->get_score_nps_detalle( $pregunta->id );
            
            $nps_score = '';
            if( $score['total_rows'] > 0 ){
                $nps_score = '
                <div class="table-container-progress">
                    <div class="progress table-progress">
                        <div class="progress-bar progress-bar-success" style="width: '.$score['promoters']['porcentaje'].'%"></div>
                        <div class="progress-bar progress-bar-warning" style="width: '.$score['pasivos']['porcentaje'].'%"></div>
                        <div class="progress-bar progress-bar-danger"  style="width: '.$score['detractores']['porcentaje'].'%"></div>
                    </div>
                </div>
                <div class="table-ptos-nps">' . $score['score_nps'] . '</div>';
            }

            $data["data"][] = array(
                ++$count,
                utf8_encode($pregunta->titulo),
                $estatus[ $pregunta->estatus ]['html'],
                date('Y-m-d',strtotime($pregunta->fecha_inicio)),
                $score['total_rows'],
                $nps_score,
                $botones
            );
        }
    }


    echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
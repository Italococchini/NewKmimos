<?php
require __DIR__.'/vendor/autoload.php';
require dirname(__DIR__).'/wp-load.php';

ob_start();
use Spipu\Html2Pdf\Html2Pdf;
include_once( '../wp-content/themes/kmimos/page-clubpatitas-compartir.php' );
$html = ob_get_contents();
ob_end_clean();

$html2pdf = new Html2Pdf();
$html2pdf->writeHTML( $html );
$html2pdf->output();

<?php
		
	session_start();

	include dirname(dirname(__DIR__)).'/wp-load.php';

	global $wpdb;

	$_SESSION["landing_test"] = 'b';
	$ult_landing = $_SESSION["landing_test"];

	$param = ( !empty($_SERVER['QUERY_STRING']) && isset($_GET['utm_campaign']) )? '&'.$_SERVER['QUERY_STRING'] : '&utm_source=web&utm_medium=banner&utm_campaign=petco_kmimos&utm_term=white_label_petco' ;	
	
	if( $ult_landing == 'a' ){
		$url = get_home_url().'/?wlabel=petco'.$param;	
	}else{
		$url = get_home_url().'/?landing='.$ult_landing.'&wlabel=petco'.$param;	
	}	

	header('Location:'.$url );

	exit();
?>


<?php
session_start();
include('../funciones.php');
include('../modulos/tableros.php');
$mtableros = new modeltableros(); 




$efectivo = $_POST['efectivoap'];
$acrof = busca($_SESSION['emp'],'empresas','e_id','e_siglas');
$folio = getmax('ct_folio','crm_tableros');
//$cliente = '1';
$cliente = busca($_SESSION['emp'],'crm_clientes','c_publico = "1" AND c_empresa','c_id');
$fecha = date('Y-m-d').'T'.date('H:i:s');
$fecha2 = date('Y-m-d H:i:s');
$fini = explode("T",$fecha);
$nmb = 'CAJA '.$_SESSION['uid'].' '.date('d-m-Y').' ';
$mtableros->setdata(NULL,$nmb,$folio,$cliente,$_SESSION['uid'],$fini[0],$fini[1],"1",date('Y-m-d', strtotime($fini[0].' +1 day'))," ","N");
$mtableros->insert();
//$sql = 'SELECT * FROM crm_tableros WHERE ct_nmb = "'.$nmb.'"';
$sql = 'SELECT * FROM crm_tableros WHERE ct_nmb = "'.$nmb.'" AND ct_estatus = "N"';
$result = setq($sql);
$row = $result->fetch_array();
//die($row['ct_id']);

$sql = 'INSERT INTO pv_tableroscaja SET
        tc_tablero = "'.$row['ct_id'].'",
        tc_empresa = "'.$_SESSION['emp'].'",
        tc_fondo = "'.$efectivo.'",
        tc_cajero = "'.$_SESSION['uid'].'",
        tc_fini = "'.$fecha2.'",
        tc_ffin = "'.date('Y-m-d 23:59:59').'",
        tc_estatus = "N",
        tc_efectivo = "0",
        tc_tarjeta = "0",
        tc_vales = "0",
        tc_trans = "0",
        tc_cheque = "0"';

setq($sql);
header('location: ../popup/puntodeventa.php');


//foreach($_POST as $campo => $valor){	echo "POST->". $campo ."= ". $valor.'<br>'; }
//foreach($_GET as $campo => $valor) {	echo "GET->". $campo ."= ". $valor.'<br>'; }
//die();




?>
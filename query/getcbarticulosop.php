<?php
//ini_set('display_errors', 1);
include('../funciones.php');

$forecast = $_POST['forecast']; //ID del forecast
$ordenp = $_POST['ordenp'];//ID de la orden de producción

$sqlmax = 'SELECT COUNT(*) AS consecutivo FROM pr_articulosop WHERE pra_op = "'.$ordenp.'"';
$resultmax = setq($sqlmax);
list($consec) = $resultmax->fetch_array();
$consecutivo = intval($consec) + 1;

$longitud = intval(strlen($consecutivo));

if($longitud == 4){
    $txtConsecutivo = '';
} else if($longitud == 3){
    $txtConsecutivo = '0';
} else if($longitud == 2){
    $txtConsecutivo = '00';
} else{
    $txtConsecutivo = '000';
}

$cb = $forecast.$ordenp.$txtConsecutivo.$consecutivo;

echo $cb;
?>
<?php 
//ini_set('display_errors', 1);
include_once('../funciones.php');
session_start();

$datos = array();
$mov = $_POST['mov'];
$sqlcd = 'SELECT cm_monto, cm_tipo, cm_descripcion, cm_ugen, cm_fgen FROM cajas_movimientos WHERE cm_id = "'.$mov.'"';
$resultcd = setq($sqlcd);
list($monto, $tipo, $obs, $ugen, $fgen) = $resultcd -> fetch_array();

if($tipo == "I") $tipo = "INGRESO A CAJA";
else $tipo = "RETIRO DE LA CAJA";

$datos['fecha']= fecha_formato($fgen, true, true);
$datos['monto']= number_format($monto, 2);
$datos['tipo']= $tipo;
$datos['ugen']= busca($ugen, 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
$datos['obs']= $obs;

echo json_encode($datos);
?>


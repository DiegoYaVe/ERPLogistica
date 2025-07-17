<?php
session_start();
include_once('../funciones.php');

$respuesta = 1;
$sql = 'SELECT COUNT(*), cc_fini FROM crm_capturaleads WHERE cc_estatus = "A"';
$result = setq($sql);
list($numero, $fecha) = $result->fetch_array();
if($numero > 0){
  if($fecha != date("Y-m-d")){
    $respuesta = 3; // Respuesta si hay una hoja abierta de un día diferente a hoy
  } else{
    $respuesta = 2; // Respuesta si hay una hoja abierta del día de hoy previamente
  }
}

echo $respuesta;
?>
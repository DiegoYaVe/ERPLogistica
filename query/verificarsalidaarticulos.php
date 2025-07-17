<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$guia = $_POST['guia'];

$check = busca(busca(busca($giua, 'guias_articulos', 'ga_id','ga_cotizacion'), 'crm_cotizaciones', 'cc_id', 'cc_remision'), 'remision_faltantes', 'rf_estatus != "F" AND rf_remision', 'COUNT(*)');
if($check > 0) 

if($check > 0){
  /* echo 'Es necesario que el gerente confirme la salida de estos productos'; */
  $grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
  if($grupo == "GERENCIA" || $grupo == "ADMIN"){
    $respuesta = 0;
  } else{
    $respuesta = 1;
  }
} else{
  $respuesta = 0;
}

echo $respuesta;
?>
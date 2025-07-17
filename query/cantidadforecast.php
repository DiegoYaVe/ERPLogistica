<?php 
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 1 Jul 2000 05:00:00 GMT"); // Fecha en el pasado
session_start();
include_once('../funciones.php');
//foreachdie();
$articulo = $_POST['articulo'];
$modelo = $_POST['modelo'];
$cantidad = $_POST['cantidad'];
$forecast = $_POST['forecast'];
$estatus = busca($forecast, 'pr_forecast', 'prf_id', 'prf_estatus');
$cp = $_POST['cp'];
//echo 'estatus: '.$estatus;


if($cantidad <= 0){
  if($estatus != "F"){
    $sqldel='DELETE FROM pr_forecastd WHERE prfd_articulo = "'.$articulo.'" AND prfd_modelo = "'.$modelo.'" AND prfd_forecast = "'.$forecast.' AND prfd_checkpoint = "'.$cp.'"';
    setq($sqldel);
  }
} else {
  $existe = busca($articulo, 'pr_forecastd', 'prfd_forecast = "'.$forecast.'" AND prfd_modelo = "'.$modelo.'" AND prfd_checkpoint = "'.$cp.'" AND prfd_articulo', 'prfd_id');
  if($existe){
    if($estatus == "F"){
     $cantidad += busca($existe, 'pr_forecastd', 'prfd_id', 'prfd_cantidad'); 
    }
    $sqlupd = 'UPDATE pr_forecastd SET prfd_cantidad = "'.$cantidad.'" WHERE prfd_id="'.$existe.'"';
    setq($sqlupd);
  } else{
    $sqlins = 'INSERT pr_forecastd SET prfd_forecast = "'.$forecast.'",
                                       prfd_articulo = "'.$articulo.'",
                                       prfd_modelo = "'.$modelo.'",
                                       prfd_cantidad = "'.$cantidad.'",
                                       prfd_checkpoint = "'.$cp.'"';
    setq($sqlins);
  }
}




?>
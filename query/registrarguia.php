<?php
session_start();
/* ini_set('display_errors',1); */
include_once('../funciones.php');
$cotizacion = $_POST['remision'];
$guia = $_POST['guia'];
$paqueteria = $_POST['paqueteria'];
$sucursal = $_POST['sucursal'];
$tipoGuia = $_POST['tipoGuia'];
$idguia = 0;
$ugenera = $_SESSION['uid'];
$tipo = $_POST['tipo'];
$costo = $_POST['costo'];
$respuesta = 0;

/* $result = intval(busca($guia, 'guias_articulos', 'ga_nbm IS NOT NULL AND ga_cotizacion != "'.$cotizacion.'" AND ga_nmb', 'COUNT(*)')); */
//Buscamos si existe una guía con ese nombre en los registros
if(empty($guia)){
  $result = 0;
} else{
  $result = intval(busca($guia, 'guias_articulos', 'ga_nmb', 'COUNT(*)'));
}

$result2 = intval(busca($guia, 'guias_articulos', 'ga_cotizacion = "'.$cotizacion.'" AND ga_nmb', 'COUNT(*)'));
if($result2 > 0){
  //Es de la misma cotizacion, lo cargamos
  $result = 0;
} 

if($result > 0){
  $respuesta = 3; //Hay una guía registrada con ese nombre en otra cotización o la guía está finalizada
} else{
  if($tipo == 1){

    $sql = 'INSERT INTO guias_articulos SET
      ga_nmb = "' . $guia . '",
      ga_paqueteria = "' . $paqueteria . '",
      ga_estatus = "A",
      ga_ugenera = "'.$_SESSION['uid'].'",
      ga_fini = "'.date("Y-m-d H:i:s").'",
      ga_cotizacion = "' . $cotizacion . '",
      ga_costo = "' . $costo . '"';
    setq($sql);
    $gaid = getmax('ga_id', 'guias_articulos', false, false);
    $response['paqueteria'] = $paqueteria;
    $response['costo'] = $costo;
  } else{
    $gaid = intval(busca($guia, 'guias_articulos', 'ga_nmb', 'ga_id'));
    $response['paqueteria'] = busca($gaid, 'guias_articulos', 'ga_id', 'ga_paqueteria');
    $envio = busca($gaid, 'guias_articulos', 'ga_id', 'ga_costo');
    $response['costo'] = $envio;
  }

  $response['idguia'] = $gaid;
  $guiasreg = intval(busca($cotizacion, 'guias_articulos', 'ga_nmb IS NOT NULL AND ga_cotizacion', 'COUNT(*)'));
  $response['guiasreg'] = $guiasreg;
  $respuesta = 0; //No hay ninguna guía registrada con ese nombre
}

$response['respuesta'] = $respuesta;

echo json_encode($response);

?>
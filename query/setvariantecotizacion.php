<?php
ini_set('display_errors', 1);
include_once('../funciones.php');

//foreachdie(); 
$cotizacion= $_POST['cot'];
$cotizaciond= $_POST['cotd'];
$accion= $_POST['accion'];
$cant= $_POST['cant'];
$modelo= $_POST['modelo'];
$articulo= $_POST['articulo'];
$cmb= $_POST['cmb'];
$maximo= $_POST['maximo'];
$idrv = busca($cotizacion, 'crm_cotizacion_variantes', 'ccv_cotizaciond="'.$cotizaciond.'" AND ccv_articulo = "'.$articulo.'" AND ccv_modelo = "'.$modelo.'" AND ccv_cotizacion', 'ccv_id');

$datos = array();

if($accion == "1"){
    $sql = 'INSERT INTO crm_cotizacion_variantes SET
        ccv_cotizacion="'.$cotizacion.'",
        ccv_cotizaciond="'.$cotizaciond.'",
        ccv_articulo="'.$articulo.'",
        ccv_modelo ="'.$modelo.'",
        ccv_cantidad="'.$cant.'"';
} else if($accion == "2"){
    $sql = 'DELETE FROM crm_cotizacion_variantes WHERE ccv_id = "'.$idrv.'"';
} else if($accion == "3"){
    $sql = 'UPDATE crm_cotizacion_variantes SET ccv_cantidad = "'.$cant.'" WHERE
          ccv_id= "'.$idrv.'"';
} 
$cantidad = $maximo;

$count = busca($cotizacion, 'crm_cotizacion_variantes', 'ccv_id != "'.$idrv.'" AND ccv_articulo = "'.$articulo.'" AND ccv_cotizaciond = "'.$cotizaciond.'" AND ccv_cotizacion', 'SUM(ccv_cantidad)');
/* echo 'cantidad: '.$cantidad.' count: '.$count.' cant: '.$cant;
die(); */
$datos['cantidad'] = number_format($cantidad, 0);
$datos['count'] = number_format($count, 0);
$datos['suma'] = $count+$cant;
if($cantidad == ($count+$cant)){
  $datos['valida'] = "1";
} else {
  $datos['valida'] = "0";
}

if ($accion == "2"){
  $datos['error']= '4';
  setq($sql);
} else if($cantidad == $count){
  $datos['error'] ='3';
} else if($cantidad < ($count+$cant)){
    $sql = 'DELETE FROM crm_cotizacion_variantes WHERE ccv_id = "'.$idrv.'"';
    setq($sql);
    $datos['error'] = '2'; 
}else {
  $datos['error']= '1'; 
  setq($sql);
}

echo json_encode($datos);
?>
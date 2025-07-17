<?php

//ini_set('display_errors',1);
include_once('../funciones.php');
$repuesta = 0;

$embarque = $_POST['embarque'];

$tipoembarque = busca($embarque, 'embarques', 'e_id', 'e_paqueteria');

if($tipoembarque == -1){
    $idguia = $_POST['guia'];
    $cotizacion = busca($idguia, 'guias_articulos', 'ga_id','ga_cotizacion');
} else{
    $cotizacion = $_POST['cotizacion'];
}


$check = intval(busca(busca($cotizacion, 'crm_cotizaciones', 'cc_id', 'cc_remision'), 'remision_faltantes', 'rf_estatus != "F" AND rf_remision', 'COUNT(*)'));
if($check > 0){
    $repuesta = 1; //Es necesario que el gerente confirme la salida de estos productos
}
echo $respuesta;
?>
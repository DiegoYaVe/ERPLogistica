<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
/* session_start(); */

$arreglo = array();
//Buscamos las guías que esten pendientes de embarque
$sqlpaq = 'SELECT p_id, p_nmb FROM paqueterias WHERE p_estatus = "A"';
$resultpaq = setq($sqlpaq);
while($rowpaq = $resultpaq->fetch_array()){
    $paqueteria = $rowpaq['p_nmb'];

    $registros = intval(busca($rowpaq['p_id'], 'guias_articulos', 'ga_estatus = "P" AND ga_paqueteria', 'COUNT(*)'));
    if($registros > 0){
        if($registros  == 1){
            $registros .= " guía";
        } else{
            $registros .= " guías";
        }
        $arreglo[] = array($paqueteria,$registros);
    }
}
//Buscamos los artículos que recoge el cliente
$registrosc = 0;
$tipoenvio = "RECOGE CLIENTE";
//Buscamos los artículos que recoge el cliente
/* $sql = 'SELECT COUNT(*) FROM crm_cotizacionesc 
        INNER JOIN crm_cotizaciones ON ccc_cotizacion = cc_id 
        INNER JOIN remisiones ON cc_remision = r_id WHERE cc_estatus = "V" 
        AND r_estatus IN ("F", "FE") AND ccc_estatus IN ("P") AND ccc_tipoenvio = "C"'; */

$sql = 'SELECT COUNT(*) FROM remisionesc 
        INNER JOIN remisiones ON rc_remision = r_id WHERE
        r_estatus IN ("F", "A") AND rc_estatus IN ("P") AND rc_tipoenvio = "C"';
$result = setq($sql);
list($registros) = $result->fetch_array();
$registrosc = intval($registros);


if($registrosc > 0){
    if($registrosc == 1){
        $registrosc .= " artículo";
    } else{
        $registrosc .= " artículos";
    }
    $arreglo[] = array($tipoenvio,$registrosc);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>
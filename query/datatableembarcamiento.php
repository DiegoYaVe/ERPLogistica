<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$ugen = $_POST['ugen'];
$fini = $_POST['fini'];
$ffin = $_POST['ffin'];
$arreglo = array();
/* $listAlmacenes = $_POST['arregloJS']; */
$sql = 'SELECT * FROM embarques WHERE 
        e_fini >= "'.$fini.'" AND e_fini <= "'.$ffin.'" AND e_estatus IN ("A","F")';
if(!empty($ugen)){
  $sql .= ' AND e_ugenera = "'.$ugen.'"';
}
$sql .= ' ORDER BY CASE WHEN e_estatus = "A" 
THEN 1 WHEN e_estatus = "F" THEN 2 ELSE 3 END, e_ffin DESC';

$result = setq($sql);
while ($row = $result->fetch_array()) {

  $acciones = '
  <div style="display: flex;">
  <div>
  <a href="?modulo=embarcamiento&accion=show&id='.$row['e_id'].'">
    <button type="button" class="btn btn-sm btn-primary text-white">
      <i class="fas fa-eye"></i>
    </button> 
  </a>
  </div>
  ';
  /* $acciones .= '<div style="padding-left: 3px;">
  <a data-fancybox data-type="ajax" data-src="popup/setenviarembarque.php?id='.$row['e_id'].'" href="javascript:;">
    <button type="button" class="btn btn-sm btn-success text-white" data-toggle="tooltip" data-placement="top" title="Ver los productos del embarque listos para enviar">
    <i class="fas fa-paper-plane"></i>
    </button> 
  </a>
  </div>
  <div>'; */
  if($row['e_paqueteria'] == 0){
    $paqueteria = "OCURRE";
  }else if($row['e_paqueteria'] == -1){
    $paqueteria = "RECOGE CLIENTE";
  } else {
    $paqueteria = busca($row['e_paqueteria'], 'paqueterias', 'p_id', 'p_nmb');
  }
  
  if($row['e_estatus'] == "A"){
    $estatus = "Activo";
  } else if($row['e_estatus'] == "F"){
    $estatus = "Finalizado";
  } else{
    $estatus = "Cancelado";
  }

  /* $fini = fecha_formato($row['e_fini'], false, false); */
  $fini = fecha_formato($row['e_fini'], true, false);


  if(!empty($row['e_ffin'])){
    if(!empty($row['e_hfin'])){
      $ffin = fecha_formato(($row['e_ffin'].' '.$row['e_hfin']), true, false);
    } else{
      $ffin = fecha_formato(($row['e_ffin']), false, false);
    }
  } else{
    $ffin = fecha_formato($row['e_ffin'], false, false);
  }

  if(!empty($row['e_ufinaliza'])){
    $ufinaliza = $row['e_ufinaliza'];
  } else{
    $ufinaliza = "No disponible";
  }
  

  $arreglo[] = array($row['e_nmb'],$paqueteria, $row['e_ugenera'], $fini, $ffin, $ufinaliza, $estatus, $acciones);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);

?>
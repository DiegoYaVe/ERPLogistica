<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$fini = $_POST['fini'];
$ffin = $_POST['ffin'];
$proveedor = $_POST['proveedor'];
$estatus = $_POST['estatus'];
$aestatus = array("A" => "Finalizado","P" => "Asignación de Almacen","N" => "En Captura","C" => "Cancelado");

$sql = 'SELECT * FROM recepciones WHERE DATE(r_fechagen) BETWEEN "'.$fini.'" AND "'.$ffin.'" ';
if(!empty($proveedor)) $sql.=' AND r_proveedor IN (SELECT p_id FROM proveedores WHERE p_nmb LIKE "%'.$proveedor.'%" OR p_alias LIKE "%'.$proveedor.'%")';
if(!empty($estatus)) $sql.=' AND r_estatus = "'.$estatus.'"';
$sql.=' ORDER BY r_id DESC';
$result = setq($sql);
$arreglo = array();
$i = 0;
$arropen = array("A","I");

while($row = $result->fetch_array()){
  if($row['r_estatus'] == "A"){
    $lastm = "Aplicación: <b>".$row['r_uapli'].'</b><br>'.date('d-m-Y',strtotime($row['r_fechaapli']));
  }elseif($row['r_estatus'] == "C"){
    $lastm = "Cancelación: <b>".$row['r_ucan'].'</b><br>'.date('d-m-Y',strtotime($row['r_fechacan']));
  }else{
    $lastm = "Registro: <b>".$row['r_ugen'].'</b><br>'.date('d-m-Y',strtotime($row['r_fechagen']));
  }
  if($row['r_tiporec'] == "A") $tipor = "Recepción de artículos";
  else $tipor = "Compra de activos";

  if($row['r_estatus'] == "P" || $row['r_estatus'] == "A") $btnedit = "";
  else $btnedit = '<a class="btn btn-sm btn-primary text-white" data-fancybox data-type="ajax" data-src="popup/setrecepcion.php?id='.$row['r_id'].'"><i class="fas fa-edit"></i></a>';

  if($row['r_estatus'] == "P") $dir = "showalm"; else $dir = "show";
  
  $acciones = '
  <div class="" style="display: flex;">
  <div>
    <a href="?modulo=recepciones&accion='.$dir.'&id='.$row['r_id'].'">
      <button type="button" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></button>
    </a>
  </div>
  <div style="padding-left: 2px;"> '.$btnedit;
    if($row['r_estatus'] == "A")
    $acciones .= '<button type="button" onClick="imprimirr('.$row['r_id'].')" class="btn btn-sm btn-secondary"><i class="fas fa-print"></i></button>';
    $acciones .= '</div>
  <div>';

  $arreglo[] = array($row['r_folio'],busca($row['r_proveedor'],'proveedores','p_id','p_alias'),$tipor,$lastm,busca($row['r_almacen'],'almacenes','a_id','a_nmb'),$row['r_foliodoc'], $aestatus[$row['r_estatus']], $acciones);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>
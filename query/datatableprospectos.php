<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$fini = $_POST['fini'];
$ffin = $_POST['ffin'];
$hini = $_POST['hini'];
$hfin = $_POST['hfin'];
$estatus = $_POST['estatus'];
$ugen = $_POST['ugen'];

$sql = 'SELECT * FROM crm_capturaleads WHERE cc_fini BETWEEN "'.$fini.'" AND "'.$ffin.'"';      
if(!empty($hini)) $sql.=' AND cc_hini >= "'.$hini.'"';

if(!empty($hfin)) $sql.=' AND cc_hfin <= "'.$hfin.'"';

if(!empty($estatus)) $sql.=' AND cc_estatus = "'.$estatus.'"';

if(!empty($ugen)) $sql.= ' AND (cc_ugen LIKE "%'.$ugen.'%")';

$sql.=' ORDER BY cc_id DESC';
$result = setq($sql);
$arreglo = array();
$i = 0;
$arropen = array("A","I");
while($row = $result->fetch_array()){
  if($row['cc_estatus'] == "A"){
    $estatus = "Activo";
  }else{
    $estatus = "Finalizado";
  }
  if($row['cc_hfin'] == NULL){
    $horafin = "En captura";
  } else{
    $horafin = $row['cc_hfin'];
  }

    $acciones = "";
    
    $acciones = '
    <div style="display: flex; height: 34px;">';
    if($row['cc_estatus'] == "A" || $row['cc_estatus'] == "F"){
    $acciones .= '<div>
      <a href="?modulo=prospectos&accion=show&id='.$row['cc_id'].'">
        <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-eye"></i></button>
      </a>
    </div>';
    }
    if($row['cc_estatus'] == "A"){
    $acciones .= '<div style="padding-left: 5px;">
        <button type="button" onClick="finalizar('.$row['cc_id'].');" class="btn btn-sm btn-danger" /><i class="fa fa-window-close"></i></button>
    </div>';
    }
  $acciones .= '</div>';
  $arreglo[] = array($row['cc_id'],$row['cc_ugen'],busca($row['cc_id'], "crm_leads", "cl_lead", "COUNT(*)"),fecha_formato($row['cc_fini'],false,true),$row['cc_hini'],$horafin, $row['cc_observacion'], $estatus, $acciones);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>
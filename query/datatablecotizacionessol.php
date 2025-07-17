<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$fini = $_POST['fini'];
$ffin = $_POST['ffin'];
$ugen = $_POST['ugen'];
if(empty($fini)){
  $fini = date('Y-m-d',strtotime('-20 days'));
}

if(empty($ffin)){
  $ffin = date('Y-m-d',strtotime('last day of this month'));
}

/* $sql = 'SELECT * FROM crm_cotizaciones WHERE cc_fini BETWEEN "'.$fini.'" AND "'.$ffin.'" AND cc_estatus = "L" AND cc_agente = "'.$_SESSION['uid'].'"'; */
$sql = 'SELECT * FROM crm_cotizaciones WHERE cc_fini BETWEEN "'.$fini.'" AND "'.$ffin.'" AND cc_estatus = "L"';
if(!empty($ugen)) $sql.= ' AND (cc_agente LIKE "%'.$ugen.'%")';
$sql.=' ORDER BY cc_id DESC';    
$result = setq($sql);
$arreglo = array();
$i = 0;
while($row = $result->fetch_array()){  

  $fini = substr($row['cc_fini'], 0, 10);
  $finih = substr($row['cc_fini'], 10, 18);
  $fini = cambiar_fecha($fini)."<br>".$finih;
  $nmbvendedor = busca($row['cc_agente'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');

  $ffin = cambiar_fecha($row['cc_ffin']);
    $acciones = '
      <a data-fancybox data-type="ajax" data-src="popup/setcotizacionsol.php?id='.$row['cc_id'].'" href="javascript:;" >
          <button type="button" class="btn btn-sm btn-primary">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
          </button>
        </a>
      ';
  $arreglo[] = array($row['cc_folio'],$row['cc_nmb'],$row['cc_destino'],$nmbvendedor,$fini,$ffin,$row['cc_importe'],$acciones);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>
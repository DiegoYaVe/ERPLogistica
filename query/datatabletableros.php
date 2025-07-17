<?php
include_once('../funciones.php');
session_start();
$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');

if(!isset($_POST['nmb']) || $_POST['nmb'] == "") $_POST['nmb'] = NULL;
if(!isset($_POST['estatus']) || $_POST['estatus'] == "") $_POST['estatus'] = "O";
if(!isset($_POST['fini']) || $_POST['fini'] == "")$_POST['fini'] = date('Y-m-d',strtotime('-15 days'));
if(!isset($_POST['ffin']) || $_POST['ffin'] == "") $_POST['fini'] = date('Y-m-d',strtotime('last day of this month'));
if($grupo == "ADMIN" || $grupo == "GERENTE"){if(!isset($_POST['agente']) || $_POST['agente'] == "") $_POST['agente'] = NULL;}
else{$_POST['agente'] = $_SESSION['uid']; }

$sql = 'SELECT * FROM crm_tableros WHERE ct_fini BETWEEN "'.$_POST['fini'].'" AND "'.$_POST['ffin'].'"';
if($_POST['agente']) $sql.=' AND ct_agente = "'.$_POST['agente'].'"';
$sql.= ' LIMIT 0,50';
/* if($_POST['estatus'] == "N") $sql.=' AND ct_estatus IN ("N","P","G","V","A")';
elseif($_POST['estatus'] == "P") $sql.=' AND ct_estatus IN ("F","V","A") ';
elseif($_POST['estatus'] == "G") $sql.=' AND ct_estatus IN ("N","P") ';
elseif($_POST['estatus'] == "V") $sql.=' AND ct_estatus IN ("X","C") ';
elseif($_POST['estatus'] == "C") $sql.=' ';
elseif($_POST['estatus'] == "X") $sql.=' '; */

$nmb = $_POST['nmb'];
if($nmb) $sql.= ' AND (ct_nmb LIKE "%'.$nmb.'%" OR ct_cliente IN
                  (SELECT c_id FROM crm_clientes WHERE c_nmb LIKE "%'.$nmb.'%" OR c_alias LIKE "%'.$nmb.'%" OR c_apellidos LIKE "%'.$nmb.'%"))';

$result = setq($sql);

$arreglo = array();
$arropen = array("N","P","G","V","C","X");
$estatust = array("N"=>"Abierto","P"=>"Construcción","G"=>"Negociación","L"=>"Aplazado","A"=>"Vendido","F"=>"Finalizado","C"=>"Cancelado","O"=>"En Linea","X"=>"Perdido","W"=>"Ganados");
while($row = $result->fetch_array()){
  $boton = ""; 
  $acciones = "";
  $chneg1 = '';
  $activeneg1 = '';
  $chneg2 = '';
  $activeneg2 = '';
  $chneg3 = '';
  $activeneg3 = '';
  $chneg4 = '';
  $activeneg4 = '';
  $bgbutton = '';
  if($row['ct_nnegociacion'] == 1) {$chneg1 = 'checked'; $activeneg1 = 'btn-danger active'; $bgbutton = $activeneg1; }
  elseif($row['ct_nnegociacion'] == 2) {$chneg2 = 'checked'; $activeneg2 = 'btn-warning active'; $bgbutton = $activeneg2; }
  elseif($row['ct_nnegociacion'] == 4) {$chneg4 = 'checked'; $activeneg4 = 'btn-danger active'; $bgbutton = $activeneg4; }
  else {$chneg3 = 'checked'; $activeneg3 = 'btn-info active'; $bgbutton = $activeneg3; }
  
  $acciones .= '<a href="?modulo=tableros&accion=show&id='.$row['ct_id'].'">
                <button type="button" class="btn btn-sm btn-info" /><i class="fa fa-eye"></i></button>
            </a>';

  $arrayneg = array("N","P","G","V","O","L");
  if(in_array($row['ct_estatus'],$arrayneg)){
    $acciones .= '<button type="button" class="btn '.$bgbutton.' btn-sm text-white" data-bs-toggle="modal" data-bs-target="#tabnegociacion'.$row['ct_id'].'"   data-placement="top" >
    <span class="glyphicon glyphicon-cog"></span><i class="fa fa-thermometer"></i>
    </button>';
  }

  $countcotN = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus IN ("N", "D", "R")  AND cc_tablero' ,'COUNT(*)');
  $countcotL = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus = "L"  AND cc_tablero' ,'COUNT(*)');
  $countcotA = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus = "A"  AND cc_tablero' ,'COUNT(*)');
  $countcotP = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus = "P"  AND cc_tablero' ,'COUNT(*)');
  $countremN = busca($row['ct_id'], 'remisiones', 'r_estatus = "N" AND r_tablero', 'COUNT(*)');
  $countremdp = busca($row['ct_id'], 'remisiones', 'r_estatus IN ("D", "P") AND r_tablero', 'COUNT(*)');   
  $countremV = busca($row['ct_id'], 'remisiones INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_estatus IN ("N", "A") AND r_estatus = "A" AND r_tablero' ,'COUNT(*)');
  $countremF = busca($row['ct_id'], 'remisiones INNER JOIN cxcobrar ON cx_referencia = r_id', 'cx_estatus = "F" AND r_estatus IN ("F") AND r_tablero' ,'COUNT(*)');
  $countenvP = busca($row['ct_id'], 'remisiones INNER JOIN guias_articulos ON ga_cotizacion = r_id', 'ga_estatus IN ("P", "A") AND r_tablero' ,'COUNT(*)');
  $countenvF = busca($row['ct_id'], 'remisiones INNER JOIN guias_articulos ON ga_cotizacion = r_id', 'ga_estatus = "F" AND r_tablero' ,'COUNT(*)');
  $countartF = busca($row['ct_id'], 'remisiones INNER JOIN remisionesc ON rc_remision = r_id', 'rc_estatus = "F" AND r_tablero' ,'COUNT(*)');
  $countcotC = busca($row['ct_id'], 'crm_cotizaciones', 'cc_estatus = "C" AND cc_tablero' ,'COUNT(*)');
  $countremC = busca($row['ct_id'], 'remisiones', 'r_estatus = "C" AND r_tablero' ,'COUNT(*)');
  if($countcotN > 0){
    $estatus = '<div class="alert" style="background:#F4D03F"><center>EN CONSTRUCCIÓN</center></div>';
  } else if($countcotL >0){
    $estatus = '<div class="alert" style="background:#F5B041"><center>ESPERANDO COSTO DE LOGÍSTICA</center></div>';
  }else if($countcotP>0){
    $estatus = '<div class="alert" style="background:#E67E22"><center>COTIZACIÓN PENDIENTE DE ENVÍO</center></div>';
  }else if($countcotA>0){
    $estatus = '<div class="alert" style="background:#58D68D"><center>COTIZACIÓN ENVIADA</center></div>';
  }else if($countremN>0){
    $estatus = '<div class="alert text-white" style="background:#C0392B"><center>ESPERANDO APLICACIÓN DE REMISIÓN</center></div>';
  }else if($countremdp>0){
    $estatus = '<div class="alert text-white" style="background:#8B57E5"><center>ESPERANDO PRIMER ABONO</center></div>';
  }else if($countremV>0){
    $estatus = '<div class="alert" style="background:#45B39D"><center>VENDIDO-ABONADO</center></div>';
  }else if($countremF>0){
    $estatus = '<div class="alert" style="background:#AAB7B8"><center>ARTÍCULOS PENDIENTES DE ENVÍO</center></div>';
  }else if($countenvP>0){
    $estatus = '<div class="alert text-white" style="background:#AAB7B8"><center>PREPARADO PARA ENVÍO</center></div>';
  }else if($countenvF>0  || $countartF>0){
    $estatus = '<div class="alert" style="background:#8E44AD"><center>ENVIADO</center></td>';
  } else if($row['ct_id'] == "X"){
    $estatus = '<div class="alert text-white" style="background:#C0392B"><center>TABLERO PERDIDO</center></div>';
  } else if($countcotC>0){
    $estatus = '<div class="alert text-white" style="background:#000000"><center>Cotizaciones canceladas</center></div>';
  } else if($countremC>0){
    $estatus = '<div class="alert text-white" style="background:#000000"><center>Remisiones canceladas</center></div>';
  }

  
  $arreglo[] = array($row['ct_nmb'], busca($row['ct_cliente'],'crm_clientes','c_id','c_alias'), busca($row['ct_agente'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)'), $row['ct_fini'], $row['ct_fcierre'], $estatus, $acciones);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>
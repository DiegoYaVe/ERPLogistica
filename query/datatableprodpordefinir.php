<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();

$fini = $_POST['fini'];
$ffin = $_POST['ffin'];
$ugen = $_POST['ugen'];
$tipo = $_POST['tipo'];
$remision = $_POST['remision'];
$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');


/* $ffin = "2023-09-15"; */
/* $sql = 'SELECT * FROM remisiones WHERE cc_fini BETWEEN "'.$fini.'" AND "'.$ffin.'" AND cc_estatus = "L" AND cc_agente = "'.$_SESSION['uid'].'"'; */
/* $sql = 'SELECT * FROM remisiones WHERE r_estatus = "F"'; */
$sql = 'SELECT * FROM remisiones 
        INNER JOIN remisionesc ON  rc_remision = r_id 
        WHERE r_faplica BETWEEN "'.$fini.'" AND "'.$ffin.'" AND r_estatus IN ("F","A") AND rc_tipoenvio = "P"';
if($grupo == "ADMIN" || $grupo == "GERENCIA" || $grupo == "SUBGERENCIA"){
  /* $vendedor = busca($remision, 'remisiones', 'r_id', 'r_encargado');
  $sql.= ' AND r_encargado LIKE "%'.$vendedor.'%"'; */
} else{
  if(!empty($ugen)) $sql.= ' AND r_encargado LIKE "%'.$ugen.'%"';
}
$sql.=' GROUP BY r_id';  
/*  */

$result = setq($sql); 
$arreglo = array();
$i = 0;
while($row = $result->fetch_array()){ 
  /* $idcotiza = busca($row['r_id'], 'remisiones', 'cc_remision', 'cc_id');
  $est = busca($idcotiza, 'remisionesc', 'rc_estatus = "N" AND rc_remision', 'COUNT(*)'); */

  $fini = $row['r_fliquidacion'];  
  $almacen = busca($row['r_almacen'], 'almacenes', 'a_id', 'a_nmb');
  $nmb = busca($row['r_cliente'], 'crm_clientes', 'c_id', 'c_nmb');
  $apellidos = busca($row['r_cliente'], 'crm_clientes', 'c_id', 'c_apellidos');
  $cliente = $nmb.' '.$apellidos;
  if($row['r_estatus'] == "A"){
    $estatus = "Abonada";
  } else{
    $estatus = "Liquidada";
  }

  $nmbvendedor = busca($row['r_encargado'], 'usuarios', 'u_id', 'CONCAT(u_nmb, " ", u_apellidos)');
  $vendedor = $nmbvendedor." - ".$row['r_encargado'];
  /*} else if($row['r_estatus'] == "D"){
    $estatus = "Esperando guía";
  } */
    $acciones = '
      <a data-fancybox data-type="ajax" data-src="popup/setpordefinir.php?id='.$row['r_id'].'&tipo='.$tipo.'" href="javascript:;" >
          <button type="button" class="btn btn-sm btn-primary">
            <span class="glyphicon glyphicon-cog"></span><i class="fa fa-eye"></i>
          </button>
        </a>
      ';
  $arreglo[] = array($row['r_folio'],$row['r_nmb'],$vendedor,$almacen,$cliente,$estatus,$acciones);
  /* $arreglo[] = array($row['r_folio'],$row['r_nmb'],$row['r_encargado'],$almacen,$cliente,$fini,$estatus,$acciones); */
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);
?>
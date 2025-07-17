<?php
include_once('../funciones.php');
session_start();

if(!isset($_REQUEST['nmb'])){
  $_REQUEST['nmb'] = NULL;
  $estatus = "A";
}
else{
  if(isset($_REQUEST['estatus'])) $estatus = "A";
  else $estatus  = "I";
}

$grupo = busca($_SESSION['uid'],'usuarios','u_id','u_grupo');
if($grupo == "USER") $_REQUEST['almacen'] = busca($_SESSION['uid'],'usuarios','u_id','u_almacen');
if(!isset($_REQUEST['page'])) $_REQUEST['page'] = 0;

//$grupo = busca($_SESSION['uid'], 'usuarios', 'u_id', 'u_grupo');
$sql = 'SELECT * FROM crm_clientes WHERE ';
if($grupo != "ADMIN" && $grupo != "GERENCIA" && $grupo != "SUBGERENCIA") $sql.='c_uregistro = "'.$_SESSION['uid'].'"';
else $sql .= "1=1";
$sql.=' ORDER BY c_id ASC ';
$result = setq($sql); 
$arreglo = array();
$acciones = '';
while($row = $result->fetch_array()){
  $acciones = '
  <a href="?modulo=clientes&accion=edit&id='.$row['c_id'].'">
    <button type="button" data-toggle="tooltip" title="Editar datos del cliente" class="btn btn-sm btn-primary" /><i class="fas fa-edit"></i></button>
  </a>';    
  $arreglo[] = array($row['c_nmb'].' '. $row['c_apellidos'],$row['c_telefono2'],$row['c_correo1'],$row['c_fregistro'], $acciones);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);

?>
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
$sql = 'SELECT * FROM crm_clientes';
$sql.=' ORDER BY c_id ASC ';
$result = setq($sql); 
$arreglo = array();
$acciones = '';
while($row = $result->fetch_array()){
  $sqldoc = 'SELECT cd_estatus, COUNT(*) as cantidad FROM crm_documentos WHERE cd_cliente = "'.$row['c_id'].'" GROUP BY cd_estatus ';
  $resultdoc = setq($sqldoc);
  $sumn=0;
  $sumc=0;
  $suma=0;
  if($resultdoc->num_rows > 0){
    while($rowdoc = $resultdoc -> fetch_array() ){
      if($rowdoc['cd_estatus'] == "N") $sumn += $rowdoc['cantidad'];
      if($rowdoc['cd_estatus'] == "A") $suma += $rowdoc['cantidad'];
      if($rowdoc['cd_estatus'] == "C") $sumc += $rowdoc['cantidad'];
    }
    if($sumn > 0)$estatus = 'DOCUMENTACIÓN EN REVISION';
    if($sumn == 0 && $suma > 0)$estatus = 'CLIENTE LOGRADO';
  }else{
    $estatus = 'CLIENTE NUEVO';
  }
  
  $acciones = '
  <a href="?modulo=clientes&accion=edit&id='.$row['c_id'].'">
    <button type="button" data-toggle="tooltip" title="Editar datos del cliente" class="btn btn-sm btn-primary" /><i class="fas fa-edit"></i></button>
  </a>';    
  $arreglo[] = array($row['c_nmb'].' '. $row['c_apellidos'],$row['c_telefono2'],$row['c_correo1'],$row['c_fregistro'], $estatus,$acciones);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);

?>
<?php
include_once('../funciones.php');
/* ini_set('display_errors',1); */
session_start();
$busca = $_POST['busca'];
$arreglo = array();

if($busca == "categoria"){
$sql = 'SELECT * FROM catcorreosop';    
$result = setq($sql);

while ($row = $result->fetch_array()){
  $accion1 =  $row['c_id'];
  $nmb = $row['c_nmb'];
  if($row['c_estatus']=="A")$est="Activo";
  if($row['c_estatus']=="D")$est="Inactivo";
  $accion2 = '
  <a id="nuevo" class="btn btn-sm btn-warning" type="button" data-fancybox data-type="ajax" data-src="popup/setcategoriacop.php?id='.$row['c_id'].'" href="javascript:;">
    <i class="fas fa-edit"></i> Editar
  </a>';
  $arreglo[] = array($accion1, $nmb, $est, $accion2);
}
} else if($busca == "index"){
$sql = 'SELECT * FROM correosop ORDER BY correosop.c_id ASC';
$result = setq($sql);

while ($row = $result->fetch_array()){
  /* $accion1 = '<a href="?modulo=correosop&accion=show&id='.$row['c_id'].'">'.$row['c_id'].'</a>'; */
  $id = '<a href="?modulo=correosop&accion=show&id='.$row['c_id'].'">'.$row['c_id'].'</a>';
  $sqlc = 'SELECT c_nmb FROM catcorreosop WHERE c_id = "'.$row['c_categoria'].'"';
  $resultc = setq($sqlc);
  list($cat) = mysqli_fetch_array($resultc);
  
  $titulo = $row['c_titulo'];
  $nmb = $row['c_nmb'];
  $mail = $row['c_mail'];
  $accion = '<a href="?modulo=correosop&accion=show&id='.$row['c_id'].'" type="button" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
  $accion .= '<a href="?modulo=correosop&accion=edit&id='.$row['c_id'].'" type="button" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>';
  $arreglo[] = array($id, $cat, $titulo, $nmb, $mail, $accion);
}
}

$new_array = array("data"=>$arreglo);
echo json_encode($new_array);
?>
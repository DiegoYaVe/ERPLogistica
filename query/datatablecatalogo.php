<?php 
include_once('../funciones.php');
/* ini_set('display_errors',1); */

$sql = 'SELECT * FROM catalogo_perdidos';
$result = setq($sql);
$arreglo = array();
while($row = $result->fetch_array()){
  
  if($row['cp_estatus'] == "A"){
    $estatus = "Activo";
  } else{
    $estatus = "Inactivo";
  }
  $arreglo[] = array($row['cp_nmb'], $estatus);
}

$new_array = array("data"=>$arreglo);

echo json_encode($new_array);
?>
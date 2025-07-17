<?php
session_start();
include_once('../funciones.php');

$modelo = $_POST['modelo'];
$esquema = $_POST['esquema'];

if($modelo != NULL){
  $sql = 'SELECT * FROM articulos_precios INNER JOIN articulos ON a_id = ap_articulo WHERE a_modelo = "'.$modelo.'" AND ap_esquema = "'.$esquema.'" AND ap_activo = "1" AND a_empresa = "'.$_SESSION['emp'].'"';
  $result = setq($sql);
  $row = $result->fetch_array();
  
  echo $row['ap_precio'];
}




?>
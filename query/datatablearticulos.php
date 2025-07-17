<?php
include_once('../funciones.php');
session_start();
  ini_set('display_errors',1);

if($_POST['estatus'] == "T") $estatus = NULL;
else $estatus = $_POST['estatus'];
if($_POST['categoria'] == "0" || $_POST['categoria'] == "") $categoria = NULL;
else $categoria = $_POST['categoria'];

$sql = 'SELECT * FROM articulos INNER JOIN categorias ON a_categoria = cat_id INNER JOIN articulos_precios ON a_id = ap_articulo';
if($estatus == "A" || $estatus == "I") $sql.= ' WHERE a_estatus = "'.$estatus.'"';
else $sql.= ' WHERE 1';
if($categoria) $sql.=' AND a_categoria = "'.$categoria.'" ';
if($unidad) $sql.=' AND a_unidad = "'.$unidad.'" ';
$sql.=' AND ap_activo = "1" GROUP BY a_id ORDER BY cat_nmb,a_nmb ASC ';
$result = setq($sql);
$arreglo = array();
while($row = $result->fetch_array()){
  $acciones = '
  <a class="btn btn-info btn-sm" href="?modulo=articulos&accion=edit&articulo='.$row['a_id'].'"><i class="fa fa-eye"></i></a>
  ';
  $arreglo[] = array($row['a_cb'],$row['a_sku'],$row['a_nmb'],$row['a_modelo'],$row['cat_nmb'],"$ ".$row['ap_precio'],$acciones);
}

$new_array  = array("data"=>$arreglo);
echo json_encode($new_array);

?>
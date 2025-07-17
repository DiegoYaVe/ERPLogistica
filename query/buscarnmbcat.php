<?php
include_once('../funciones.php');
session_start();

$id = $_POST['id'];

$sql = 'SELECT cat_nmb FROM categorias WHERE cat_id = "'.$id.'" AND cat_estatus = "A" AND cat_empresa = "'.$_SESSION['emp'].'" ';
$result = setq($sql);
list($nmb) = $result->fetch_array();

echo $nmb;

?>
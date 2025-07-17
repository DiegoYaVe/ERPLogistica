<?php
ini_set('display_errors', 1);
include_once('../funciones.php');

$ruta = '../img/garantias/';
$id = $_POST['id'];

$sql = 'SELECT gi_nmb, gi_ext FROM garantias_imagenes
          WHERE gi_id = "' . $id . '"';
//i_idp = "'.$producto.'" AND 
$result = setq($sql);
list($img, $ext) = $result->fetch_array();
//echo 'ruta: '.$ruta.$img.'.'.$ext;
unlink($ruta . $img . '.' . $ext);
$sqld = 'DELETE FROM garantias_imagenes WHERE gi_id = "' . $id . '"';
setq($sqld);
?>
<?php
ini_set('display_errors', 1);
include_once('../funciones.php');

$remision = $_POST['id'];
$ruta = '../img/remisiones/';

$sql = 'SELECT ra_nmb, ra_ext FROM remisiones_adjuntos
          WHERE ra_id = "' . $remision . '"';
//i_idp = "'.$producto.'" AND 
$result = setq($sql);
list($img, $ext) = $result->fetch_array();
//echo 'ruta: '.$ruta.$img.'.'.$ext;
unlink($ruta . $img . '.' . $ext);
$sqld = 'DELETE FROM remisiones_adjuntos WHERE ra_id = "' . $remision . '"';
setq($sqld);
?> 
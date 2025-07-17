<?php
include('../funciones.php');

$cb = $_POST['cb'];
$sql = 'SELECT * FROM imagenes WHERE i_idproducto = "'.$cb.'"';
$result = setq($sql);
$row = $result->fetch_array();

$ruta = 'http://jdshop.mx/productos/'.$row['i_nmb'].'.'.$row['i_ext'].'';


echo $ruta;


?>
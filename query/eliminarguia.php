<?php
ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$id = $_POST['id'];

$sqld = 'UPDATE remisionesc SET rc_guia = NULL WHERE rc_guia = "'.$id.'"';
setq($sqld);

$sql = 'DELETE FROM guias_articulos WHERE ga_id = "'.$id.'"';
$result = setq($sql);
if($result){
  $respuesta = 1;
}

echo $respuesta;
?>
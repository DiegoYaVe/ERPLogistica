<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$idguia = $_POST['idguia'];

$existe = intval(busca($idguia, 'remisionesc', 'rc_guia', 'COUNT(*)'));

if($existe > 0){
  $respuesta = 1;
} else{
  $respuesta = 0;
}

echo $respuesta;
?>
<?php
session_start();
//ini_set('display_errors',1);
include_once('../funciones.php');
$respuesta = 0;
$operador = $_POST['operador'];
$tuser = $_POST['tuser'];
$ordenprod = $_POST['ordenprod'];
$proceso = $_POST['proceso'];

$sql = 'UPDATE pr_procesosop SET pp_operador = "'.$operador.'", pp_toperador = "'.$tuser.'" WHERE pp_id = "'.$proceso.'" AND pp_ordenp = "'.$ordenprod.'"';
$result = setq($sql);

echo $respuesta;
?>